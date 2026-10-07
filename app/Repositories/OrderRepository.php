<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Order());
    }
}
