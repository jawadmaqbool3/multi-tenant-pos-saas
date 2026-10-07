<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;

class PurchaseOrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PurchaseOrder());
    }
}
