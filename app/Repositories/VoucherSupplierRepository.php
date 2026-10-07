<?php

namespace App\Repositories;

use App\Models\VoucherSupplier;

class VoucherSupplierRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new VoucherSupplier());
    }
}
