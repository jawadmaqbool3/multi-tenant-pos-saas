<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderUser extends Model
{
    use HasFactory;
    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }
    public function user()
    {
        return $this->morphTo('user', 'user_type', 'user_id', 'id');
    }
}
