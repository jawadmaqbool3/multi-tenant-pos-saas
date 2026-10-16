<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\PlanLimit;

class PlanLimitRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PlanLimit());
    }

    /**
     * Limit values for a plan keyed by resource key.
     *
     * @param  int|string  $planId
     * @return \Illuminate\Support\Collection
     */
    public function valuesForPlan($planId)
    {
        return $this->query()
            ->where('plan_id', $planId)
            ->pluck('limit_value', 'resource_key');
    }
}
