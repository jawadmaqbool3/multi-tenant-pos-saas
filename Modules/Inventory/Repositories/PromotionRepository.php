<?php

namespace Modules\Inventory\Repositories;

use App\Repositories\BaseRepository;

use Modules\Inventory\Models\Promotion;

class PromotionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Promotion());
    }
}
