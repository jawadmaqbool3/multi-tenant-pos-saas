<?php

namespace Modules\RBAC\Services;

use Modules\RBAC\Repositories\PermissionRepository;
use Modules\RBAC\Repositories\PermissionRoleRepository;
use Modules\RBAC\Repositories\UserRoleRepository;
use Modules\RBAC\Repositories\RoleRepository;

class RoleService
{
    protected $roles;
    protected $permissions;
    protected $permissionRoles;
    protected $userRoles;

    public function __construct(RoleRepository $roles, PermissionRepository $permissions, PermissionRoleRepository $permissionRoles, UserRoleRepository $userRoles)
    {
        $this->roles = $roles;
        $this->permissions = $permissions;
        $this->permissionRoles = $permissionRoles;
        $this->userRoles = $userRoles;
    }


    public function getRoleByUid($uid)
    {
        return $this->roles->query()->where('uid', $uid)->first();
    }

    public function getRoleByName($name)
    {
        $query = $this->roles->query()->where('name', $name);
        if (auth()->user()) {
            $query->where('company_id', auth()->user()->company_id);
        }

        return $query->first();
    }

    public function getRolesList()
    {
        return $this->roles->query()->filter()->get();
    }

    public function assignUserRole($userId, $roleId)
    {
        $this->userRoles->query()->where('user_id', $userId)->delete();

        return $this->userRoles->create([
            'role_id' => $roleId,
            'user_id' => $userId,
        ]);
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
