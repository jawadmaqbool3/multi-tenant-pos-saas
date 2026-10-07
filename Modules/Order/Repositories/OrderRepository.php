<?php

namespace Modules\Order\Repositories;

use App\Repositories\BaseRepository;

use Modules\Order\Models\Order;

class OrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Order());
    }
}
