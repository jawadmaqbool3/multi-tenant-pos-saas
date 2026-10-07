<?php

namespace App\Repositories;

use App\Models\AccountLevel1;

class AccountLevel1Repository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccountLevel1());
    }
}
