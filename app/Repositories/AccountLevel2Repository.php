<?php

namespace App\Repositories;

use App\Models\AccountLevel2;

class AccountLevel2Repository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccountLevel2());
    }
}
