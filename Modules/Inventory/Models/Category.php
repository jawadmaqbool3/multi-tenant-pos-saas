<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UIDModel;

class Category extends UIDModel
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
            ProductCategories::class,
            'category_id',
            'id',
            'id',
            'product_id',
        );
    }

    public function publishedProducts()
    {
        return $this->hasManyThrough(
            Product::class,
            ProductCategories::class,
            'category_id',
            'id',
            'id',
            'product_id',
        )->published();
    }

    public function featuredProducts()
    {
        return $this->hasManyThrough(
            Product::class,
            ProductCategories::class,
            'category_id',
            'id',
            'id',
            'product_id',
        )->featured();
    }

    public function feature()
    {
        return $this->hasOne(
            Feature::class,
            'feature_id',
            'id'
        )->where('type', 'category');
    }
}
