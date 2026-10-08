<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    

    public function product()
    {
        return $this->morphTo('product', 'product_type', 'product_id', 'id');
    }
    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }
}
