<?php

namespace App\Repositories;

use App\Models\VoucherType;

class VoucherTypeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new VoucherType());
    }
}
