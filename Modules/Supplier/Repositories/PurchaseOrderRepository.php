<?php

namespace Modules\Supplier\Repositories;

use App\Repositories\BaseRepository;

use Modules\Supplier\Models\PurchaseOrder;

class PurchaseOrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PurchaseOrder());
    }
}
