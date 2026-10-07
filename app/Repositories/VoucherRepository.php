<?php

namespace App\Repositories;

use App\Models\Voucher;

class VoucherRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Voucher());
    }
}
