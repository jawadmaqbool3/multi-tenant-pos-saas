<?php

namespace Modules\Supplier\Repositories;

use App\Repositories\BaseRepository;

use Modules\Supplier\Models\VoucherSupplier;

class VoucherSupplierRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new VoucherSupplier());
    }
}
