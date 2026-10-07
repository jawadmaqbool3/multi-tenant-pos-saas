<?php

namespace Modules\RBAC\Repositories;

use App\Repositories\BaseRepository;

use Modules\RBAC\Models\PermissionRole;

class PermissionRoleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PermissionRole());
    }
}
