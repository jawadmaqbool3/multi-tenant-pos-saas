<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\PlanFeature;

class PlanFeatureRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PlanFeature());
    }
}
