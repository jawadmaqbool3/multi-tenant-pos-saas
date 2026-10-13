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
}
