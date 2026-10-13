<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UIDModel;

class Promotion extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];


    public function scopeFilter($query)
    {
        if (auth()->user()->hasRole('admin')) {
            return $query;
        } else {
            return $query->where('company_id', auth()->user()->company_id);
        }
    }


    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            PromotionProducts::class,
            'product_id',
            'id',
            'id',
            'promotion_id',
        );
    }

    public function productPromotions()
    {
        return $this->hasMany(
            PromotionProducts::class,
            'promotion_id',
            'id',
        );
    }
}
