<?php

namespace App\Services;

use App\Repositories\AccountLevel2Repository;

class AccountLevel2Service
{
    protected $accountLevel2;

    public function __construct(AccountLevel2Repository $accountLevel2)
    {
        $this->accountLevel2 = $accountLevel2;
    }
}
