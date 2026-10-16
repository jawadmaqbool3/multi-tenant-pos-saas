<?php

namespace Modules\RBAC\Services;

use Modules\RBAC\Repositories\PermissionRepository;

class PermissionService
{
    protected $permissions;

    public function __construct(PermissionRepository $permissions)
    {
        $this->permissions = $permissions;
    }

    public function getPermissions()
    {
        return $this->permissions->dataTable($this->permissions->query()->filter()->where('is_platform', false));
    }

    public function getPermissionsList()
    {
        return $this->permissions->query()->filter()->where('is_platform', false)->get();
    }

    public function getPermissionsByCompanyId($companyId = null)
    {
        return $this->getPermissionsList();
    }
}
