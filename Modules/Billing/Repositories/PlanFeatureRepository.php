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

    /**
     * Module names included in a plan.
     *
     * @param  int|string  $planId
     * @return array<int, string>
     */
    public function moduleNamesForPlan($planId): array
    {
        return $this->query()
            ->where('plan_id', $planId)
            ->with('module')
            ->get()
            ->pluck('module.name')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
