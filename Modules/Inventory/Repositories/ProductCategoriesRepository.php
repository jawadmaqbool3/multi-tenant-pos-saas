<?php

namespace Modules\Inventory\Repositories;

use App\Repositories\BaseRepository;

use Modules\Inventory\Models\ProductCategories;

class ProductCategoriesRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ProductCategories());
    }
}
