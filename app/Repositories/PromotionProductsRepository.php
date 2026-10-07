<?php

namespace App\Repositories;

use App\Models\PromotionProducts;

class PromotionProductsRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PromotionProducts());
    }
}
