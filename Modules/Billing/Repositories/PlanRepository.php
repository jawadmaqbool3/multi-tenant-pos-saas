<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Plan;

class PlanRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Plan());
    }
}
