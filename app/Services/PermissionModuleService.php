<?php

namespace App\Services;

use App\Repositories\PermissionModuleRepository;

class PermissionModuleService
{
    protected $permissionModule;

    public function __construct(PermissionModuleRepository $permissionModule)
    {
        $this->permissionModule = $permissionModule;
    }
}
