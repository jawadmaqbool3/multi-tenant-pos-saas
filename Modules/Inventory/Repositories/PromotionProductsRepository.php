<?php

namespace Modules\Inventory\Repositories;

use App\Repositories\BaseRepository;

use Modules\Inventory\Models\PromotionProducts;

class PromotionProductsRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PromotionProducts());
    }
}
