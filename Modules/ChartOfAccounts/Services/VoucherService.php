<?php

namespace Modules\ChartOfAccounts\Services;

use Modules\ChartOfAccounts\Repositories\AccountLevel4Repository;
use Modules\ChartOfAccounts\Repositories\VoucherRepository;
use Modules\ChartOfAccounts\Repositories\VoucherTypeRepository;

class VoucherService
{
    protected $vouchers;
    protected $accountLevel4;
    protected $voucherTypes;
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

    public function __construct(VoucherRepository $vouchers, AccountLevel4Repository $accountLevel4, VoucherTypeRepository $voucherTypes)
    {
        $this->vouchers = $vouchers;
        $this->accountLevel4 = $accountLevel4;
        $this->voucherTypes = $voucherTypes;
    }

    public function getVoucherTypeByShort($short)
    {
        return $this->voucherTypes->query()->filter()->where('short', $short)->first();
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
