<?php

namespace Modules\Order\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UIDModel;

class Order extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];

    
    public function scopeFilter($query)
    {
        if (auth()->user()->hasRole('superadmin')) {
            return $query;
        } else {
            return $query->where('company_id', auth()->user()->company_id);
        }
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'id');
    }
    public function orderUser()
    {
        return $this->hasMany(OrderUser::class, 'order_id', 'id');
    }

    public function invVoucher()
    {
        return $this->morphTo('invVoucher', 'inv_voucher_type', 'inv_voucher_id', 'id');
    }

    public function payVoucher()
    {
        return $this->morphTo('payVoucher', 'pay_voucher_type', 'pay_voucher_id', 'id');
    }

    public function scopeGet($query)
    {
        return $query;
    }
}
