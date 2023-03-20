<?php

namespace App\Http\Traits;

use App\Models\Order;
use App\Models\OrderUser;

/**
 * 
 */
trait OrderTrait
{
    public function orders()
    {
        return $this->hasManyThrough(
            Order::class,
            OrderUser::class,
            'user_id',
            'id',
            'id',
            'order_id',
        );
    }
}
