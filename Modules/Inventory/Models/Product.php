<?php

namespace Modules\Inventory\Models;

use App\Models\UIDModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends UIDModel
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
    public function scopePublished($query)
    {
        $today = Carbon::now();
        return $query->where('status', 'published')->orWhere(function ($query) use ($today) {
            $query->where('status', 'scheduled')->whereDate('published_date', '<=', $today->format('Y-m-d'));
        });
    }

    public function scopeFeatured($query)
    {
        return $query->whereHas('feature');
    }

    public function categories()
    {
        return $this->hasManyThrough(
            Category::class,
            ProductCategories::class,
            'product_id',
            'id',
            'id',
            'category_id',
        );
    }

    public function feature()
    {
        return $this->hasOne(
            Feature::class,
            'feature_id',
            'id'
        )->where('type', 'product');
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class, 'product_id', 'id');
    }
}
