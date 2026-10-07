<?php

namespace App\Repositories;

use App\Models\AccountLevel4;

class AccountLevel4Repository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccountLevel4());
    }
}
