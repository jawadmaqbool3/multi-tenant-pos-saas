<?php

namespace Modules\ChartOfAccounts\Services;

use Modules\ChartOfAccounts\Repositories\TransactionRepository;

class TransactionService
{
    protected $transactions;

    public function __construct(TransactionRepository $transactions)
    {
        $this->transactions = $transactions;
    }

    public function create($data)
    {
        return $this->transactions->create($data);
    }
}
