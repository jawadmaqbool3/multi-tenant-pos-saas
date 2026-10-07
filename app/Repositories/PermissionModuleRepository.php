<?php

namespace App\Repositories;

use App\Models\PermissionModule;

class PermissionModuleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PermissionModule());
    }
}
