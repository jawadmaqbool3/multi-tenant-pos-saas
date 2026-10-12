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
        return $this->permissions->dataTable($this->permissions->query()->filter());
    }

    public function getPermissionsList()
    {
        return $this->permissions->query()->filter()->where('is_platform', false)->get();
    }

    public function getPermissionsByCompanyId($companyId)
    {
        return $this->permissions->query()
            ->where('company_id', $companyId)
            ->where('is_platform', false)
            ->get();
    }

    public function store($request)
    {
        return $this->permissions->create([
            'name' => $request->name,
            'module_id' => $request->module,
            'company_id' => auth()->user()->company_id
        ]);
    }

    public function update($request, $permission)
    {
        return $this->permissions->query()->where('name', $permission->name)->update([
            'name' => $request->name,
            'module_id' => $request->module
        ]);
    }

    public function delete($permission)
    {
        return $this->permissions->delete($permission->id);
    }
}
