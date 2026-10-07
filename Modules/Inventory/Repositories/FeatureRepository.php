<?php

namespace Modules\Inventory\Repositories;

use App\Repositories\BaseRepository;

use Modules\Inventory\Models\Feature;

class FeatureRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Feature());
    }
}
