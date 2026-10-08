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
        return $this->vouchers->query()->whereIn('id', $voucherIds)
            ->where('type', '!=', 'void')->supplierLedgerDataTable();
    }

    public function getOperation($account, $type)
    {
        return $this->rules[$account->name][$type];
    }

    public function getVouchers()
    {
        return $this->vouchers->query()->filter()->dataTable();
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
