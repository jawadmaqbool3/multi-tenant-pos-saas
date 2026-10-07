<?php

namespace Modules\Order\Repositories;

use App\Repositories\BaseRepository;

use Modules\Order\Models\OrderProduct;

class OrderProductRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new OrderProduct());
    }
}
