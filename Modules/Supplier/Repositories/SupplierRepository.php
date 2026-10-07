<?php

namespace Modules\Supplier\Repositories;

use App\Repositories\BaseRepository;

use Modules\Supplier\Models\Supplier;

class SupplierRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Supplier());
    }
}
