<?php

namespace Modules\Order\Repositories;

use App\Repositories\BaseRepository;

use Modules\Order\Models\OrderUser;

class OrderUserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new OrderUser());
    }
}
