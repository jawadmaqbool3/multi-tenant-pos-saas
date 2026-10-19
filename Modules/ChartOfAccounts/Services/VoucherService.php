<?php

namespace Modules\ChartOfAccounts\Services;

use Modules\ChartOfAccounts\Repositories\AccountLevel4Repository;
use Modules\ChartOfAccounts\Repositories\TransactionRepository;
use Modules\ChartOfAccounts\Repositories\VoucherRepository;
use Modules\ChartOfAccounts\Repositories\VoucherTypeRepository;
use App\Exceptions\InvalidFieldException;

class VoucherService
{
    protected $vouchers;
    protected $accountLevel4;
    protected $voucherTypes;
    protected $transactions;
    protected $rules = [
        'Asset' => [
            'debit' => '+',
            'credit' => '-'
        ],
        'Liability' => [
            'debit' => '-',
            'credit' => '+'
        ],
        'Equity' => [
            'debit' => '-',
            'credit' => '+'
        ],
        'Revenue' => [
            'debit' => '-',
            'credit' => '+'
        ],
        'Expense' => [
            'debit' => '+',
            'credit' => '-'
        ]
    ];

    public function __construct(VoucherRepository $vouchers, AccountLevel4Repository $accountLevel4, VoucherTypeRepository $voucherTypes, TransactionRepository $transactions)
    {
        $this->vouchers = $vouchers;
        $this->accountLevel4 = $accountLevel4;
        $this->voucherTypes = $voucherTypes;
        $this->transactions = $transactions;
    }

    public function getVoucherTypeByShort($short)
    {
        return $this->voucherTypes->query()->filter()->where('short', $short)->first();
    }

    public function getVoucherTypesForSelect()
    {
        return $this->voucherTypes->query()->filter()->get(['id', 'name', 'short']);
    }

    public function nextVoucherNo($short)
    {
        $voucherType = $this->getVoucherTypeByShort($short);

        return $voucherType->short . '-' . ($voucherType->vouchers->count() + 1);
    }

    public function createVoucher($data)
    {
        return $this->vouchers->create($data);
    }

    /**
     * Create a manual journal voucher with balanced debit/credit lines.
     * Each line must be a Level 4 account uid (already validated by
     * StoreVoucherRequest). Called inside the controller's DB transaction.
     *
     * @param  array  $data  voucher_type_id, date, description, lines[]
     * @return \Modules\ChartOfAccounts\Models\Voucher
     */
    public function createManualVoucher(array $data)
    {
        $voucherType = $this->voucherTypes->query()->filter()->where('id', $data['voucher_type_id'])->first();
        if (!$voucherType) {
            throw new InvalidFieldException('voucher_type_id');
        }

        $totalDebit = 0;
        foreach ($data['lines'] as $line) {
            $totalDebit += !empty($line['debit']) ? (float) $line['debit'] : 0;
        }

        $voucher = $this->createVoucher([
            'voucher_no' => $this->nextVoucherNo($voucherType->short),
            'voucher_type_id' => $voucherType->id,
            'date' => $data['date'],
            'amount' => $totalDebit,
            'type' => count($data['lines']) == 2 ? 'single' : 'multiple',
            'description' => $data['description'] ?? null,
            'created_by' => auth()->user()->id,
        ]);
        $voucher->refresh();

        $lineNumber = 0;
        foreach ($data['lines'] as $line) {
            $lineNumber++;
            $account = $this->accountLevel4->query()->filter()->where('uid', $line['account'])->first();
            if (!$account) {
                throw new InvalidFieldException('account');
            }

            $type = !empty($line['debit']) ? 'debit' : 'credit';
            $amount = !empty($line['debit']) ? (float) $line['debit'] : (float) $line['credit'];

            $this->transactions->create([
                'transaction_no' => $voucher->voucher_no . '-' . $lineNumber,
                'voucher_id' => $voucher->id,
                'account_id' => $account->id,
                'amount' => $amount,
                'type' => $type,
                'source' => 'manual',
                'description' => $line['description'] ?? null,
            ]);

            $this->applyBalance($account->id, $type, $amount);
        }

        return $voucher;
    }

    public function updateVoucher($id, $data)
    {
        return $this->vouchers->query()->where('id', $id)->update($data);
    }

    public function getVouchersByIds($voucherIds)
    {
        return $this->vouchers->supplierLedgerDataTable($this->vouchers->query()->whereIn('id', $voucherIds)
            ->where('type', '!=', 'void'));
    }

    public function getOperation($account, $type)
    {
        return $this->rules[$account->name][$type];
    }

    /**
     * Apply the double-entry sign for a Level 1 account type to an amount.
     * Positive means the transaction increases the account's natural balance.
     * Callers pass a type name (Asset, Liability, Equity, Revenue, Expense) and
     * a transaction type (debit|credit) — the sign is never hardcoded outside
     * this service.
     *
     * @param  string  $accountType
     * @param  string  $type  debit|credit
     * @param  float  $amount
     * @return float
     */
    public function signedAmount($accountType, $type, $amount)
    {
        $operation = $this->rules[$accountType][$type] ?? '+';

        return $operation === '+' ? (float) $amount : (float) $amount * -1;
    }

    /**
     * Apply a signed balance change to a Level 4 account. The debit/credit sign
     * is resolved from the account's Level 1 type via $rules, so callers pass an
     * account id and a transaction type only — never a hardcoded sign.
     *
     * @param  int  $accountId
     * @param  string  $type  debit|credit
     * @param  float  $amount
     * @return void
     */
    public function applyBalance($accountId, $type, $amount)
    {
        $account = $this->accountLevel4->query()
            ->with('level3.level2.level1')
            ->where('id', $accountId)
            ->first();

        if (!$account) {
            return;
        }

        $operation = $this->getOperation($account->level3->level2->level1, $type);
        $new = $operation === '+' ? $account->balance + $amount : $account->balance - $amount;

        $this->accountLevel4->query()->where('id', $accountId)->update(['balance' => $new]);
    }

    public function getVouchers()
    {
        return $this->vouchers->dataTable($this->vouchers->query()->filter());
    }

    public function voidVoucher($voucher)
    {
        if ($voucher->type === 'void') {
            return false;
        }
        if (!auth()->user()->hasRole('admin')) {
            $voucherType = $voucher->voucherType;
            if (!$voucherType || $voucherType->company_id != auth()->user()->company_id) {
                return false;
            }
        }
        foreach ($voucher->transactions as $transaction) {
            $account = $transaction->account;
            $level1 = $account->level3->level2->level1;
            $operation = $this->getOperation($level1, $transaction->type);
            //Void works in reverse
            if ($operation == '+') {
                $this->accountLevel4->query()->where('id', $account->id)->update([
                    'balance' => $account->balance - $transaction->amount
                ]);
            } elseif ($operation == '-') {
                $this->accountLevel4->query()->where('id', $account->id)->update([
                    'balance' => $account->balance + $transaction->amount
                ]);
            }
        }
        $this->vouchers->query()->where('id', $voucher->id)->update([
            'type' => 'void'
        ]);
        return true;
    }
}
