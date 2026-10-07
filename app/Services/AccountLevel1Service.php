<?php

namespace App\Services;

use App\Repositories\AccountLevel1Repository;

class AccountLevel1Service
{
    protected $accountLevel1;

    public function __construct(AccountLevel1Repository $accountLevel1)
    {
        $this->accountLevel1 = $accountLevel1;
    }
}
