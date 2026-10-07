<?php

namespace App\Repositories;

use App\Models\ProductCategories;

class ProductCategoriesRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ProductCategories());
    }
}
