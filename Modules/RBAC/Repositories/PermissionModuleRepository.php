<?php

namespace Modules\RBAC\Repositories;

use App\Repositories\BaseRepository;
use Modules\RBAC\Models\PermissionModule;

class PermissionModuleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PermissionModule());
    }
}