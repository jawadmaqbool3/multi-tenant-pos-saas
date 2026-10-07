<?php

namespace App\Services;

use App\Repositories\AccountLevel4Repository;

class AccountLevel4Service
{
    protected $accountLevel4;

    public function __construct(AccountLevel4Repository $accountLevel4)
    {
        $this->accountLevel4 = $accountLevel4;
    }
}
