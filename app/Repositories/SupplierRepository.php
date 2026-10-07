<?php

namespace App\Repositories;

use App\Models\Supplier;

class SupplierRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Supplier());
    }
}
