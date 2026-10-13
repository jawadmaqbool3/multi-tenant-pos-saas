<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\PlanPermission;

class PlanPermissionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PlanPermission());
    }
}
