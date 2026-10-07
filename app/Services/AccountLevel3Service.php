<?php

namespace App\Services;

use App\Repositories\AccountLevel3Repository;

class AccountLevel3Service
{
    protected $accountLevel3;

    public function __construct(AccountLevel3Repository $accountLevel3)
    {
        $this->accountLevel3 = $accountLevel3;
    }
}
