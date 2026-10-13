<?php

namespace Modules\Supplier\Models;

use App\Models\UIDModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->order_no = 'ODR-' . Carbon::now()->format('ymd') . str_pad((PurchaseOrder::count() + 1), 4, 0, STR_PAD_LEFT);
            $model->user_id  = auth()->user()->id;
        });
    }
    public function scopeFilter($query)
    {
        if (auth()->user()->hasRole('admin')) {
            return $query;
        } else {
            return $query->where('company_id', auth()->user()->company_id);
        }
    }


    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }

    public function voucher()
    {
        return $this->morphTo('voucher', 'voucher_type', 'voucher_id', 'id');
    }
}
