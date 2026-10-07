<?php

namespace Modules\RBAC\Repositories;

use App\Repositories\BaseRepository;

use Modules\RBAC\Models\Permission;

class PermissionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Permission());
    }
}
