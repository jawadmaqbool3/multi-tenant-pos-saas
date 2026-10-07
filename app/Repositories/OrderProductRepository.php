<?php

namespace App\Repositories;

use App\Models\OrderProduct;

class OrderProductRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new OrderProduct());
    }
}
