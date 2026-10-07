<?php

namespace App\Services;

use App\Repositories\PermissionRepository;
use App\Repositories\PermissionRoleRepository;
use App\Repositories\RoleRepository;

class RoleService
{
    protected $roles;
    protected $permissions;
    protected $permissionRoles;

    public function __construct(RoleRepository $roles, PermissionRepository $permissions, PermissionRoleRepository $permissionRoles)
    {
        $this->roles = $roles;
        $this->permissions = $permissions;
        $this->permissionRoles = $permissionRoles;
    }

    public function getRoles()
    {
        return $this->roles->query()->filter()->dataTable();
    }

    public function store($request)
    {
        $role = $this->roles->create([
            'name' => $request->name,
            'status' => $request->status,
            'company_id' => auth()->user()->compny_id
        ]);
        $role->refresh();
        if (is_array($request->permissions)) {
            $permissions = $this->permissions->query()->whereIn('uid', $request->permissions)->get();
            foreach ($permissions as $permission) {
                $this->permissionRoles->create([
                    'permission_id' => $permission->id,
                    'role_id' => $role->id
                ]);
            }
        }
        return $role;
    }

    public function update($request, $role)
    {
        $this->roles->query()->where('id', $role->id)->update([
            'name' => $request->name,
            'status' => $request->status
        ]);
        $this->permissionRoles->query()->where('role_id', $role->id)->delete();
        if (is_array($request->permissions)) {
            $permissions = $this->permissions->query()->whereIn('uid', $request->permissions)->get();
            foreach ($permissions as $permission) {
                $this->permissionRoles->create([
                    'permission_id' => $permission->id,
                    'role_id' => $role->id
                ]);
            }
        }
    }

    public function delete($role)
    {
        return $this->roles->delete($role->id);
    }
}
