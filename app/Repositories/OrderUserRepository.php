<?php

namespace App\Repositories;

use App\Models\OrderUser;

class OrderUserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new OrderUser());
    }
}
