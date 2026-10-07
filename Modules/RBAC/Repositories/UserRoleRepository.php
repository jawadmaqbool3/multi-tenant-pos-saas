<?php

namespace Modules\RBAC\Repositories;

use App\Repositories\BaseRepository;

use Modules\RBAC\Models\UserRole;

class UserRoleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new UserRole());
    }
}
