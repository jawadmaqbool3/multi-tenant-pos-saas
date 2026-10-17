<?php

namespace Modules\RBAC\Services;

use Modules\RBAC\Repositories\PermissionRepository;
use Modules\RBAC\Repositories\PermissionRoleRepository;
use Modules\RBAC\Repositories\UserRoleRepository;
use Modules\RBAC\Repositories\RoleRepository;
use App\Models\Company;

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
        if (auth()->user()) {
            $companyRole = $this->roles->query()
                ->where('name', $name)
                ->where('company_id', auth()->user()->company_id)
                ->first();

            if ($companyRole) {
                return $companyRole;
            }
        }

        // Fall back to the shared template role when a company-specific one
        // has not been provisioned.
        return $this->roles->query()
            ->where('name', $name)
            ->whereNull('company_id')
            ->first();
    }

    public function getRolesList()
    {
        return $this->roles->query()->filter()->whereNull('company_id')->get();
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
        return $this->roles->dataTable($this->roles->query()->filter());
    }

    public function store($request)
    {
        app(\Modules\Billing\Services\PlanLimitService::class)
            ->ensureWithinLimit('roles', $this->company());

        $role = $this->roles->create([
            'name' => $request->name,
            'status' => $request->status,
            'company_id' => auth()->user()->company_id
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

    /**
     * The company the current user belongs to (null for platform accounts).
     *
     * @return \App\Models\Company|null
     */
    protected function company()
    {
        if (!auth()->user() || !auth()->user()->company_id) {
            return null;
        }

        return Company::where('id', auth()->user()->company_id)->first();
    }
}
