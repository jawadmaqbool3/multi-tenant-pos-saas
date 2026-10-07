<?php

namespace App\Repositories;

use App\Models\PermissionRole;

class PermissionRoleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PermissionRole());
    }
}
