<?php

namespace App\Services;

use App\Repositories\AccountLevel4Repository;
use App\Repositories\VoucherRepository;

class VoucherService
{
    protected $vouchers;
    protected $accountLevel4;
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

    public function __construct(VoucherRepository $vouchers, AccountLevel4Repository $accountLevel4)
    {
        $this->vouchers = $vouchers;
        $this->accountLevel4 = $accountLevel4;
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
