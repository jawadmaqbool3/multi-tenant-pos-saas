<?php

namespace Modules\RBAC\Services;

use Modules\RBAC\Repositories\PermissionRepository;
use Modules\RBAC\Repositories\PermissionRoleRepository;
use Modules\RBAC\Repositories\RoleRepository;
use Modules\RBAC\Support\DefaultRoles;

/**
 * Provisions the default roles for a company and attaches the shared global
 * permissions to them. Permissions are never copied per company; the global
 * (company_id = null) permission rows are attached directly via the
 * permission_roles pivot.
 */
class RoleProvisioningService
{
    protected $role;
    protected $permission;
    protected $permissionRole;

    public function __construct(
        RoleRepository $role,
        PermissionRepository $permission,
        PermissionRoleRepository $permissionRole
    ) {
        $this->role = $role;
        $this->permission = $permission;
        $this->permissionRole = $permissionRole;
    }

    /**
     * Create every default tenant role for the company (including the owner
     * 'company' role, which receives every tenant permission) and attach the
     * global permissions each role is granted.
     *
     * @param  int  $companyId
     * @param  string  $status
     * @return void
     */
    public function provisionForCompany($companyId, $status = 'active')
    {
        $globalPermissions = $this->permission->query()
            ->whereNull('company_id')
            ->where('is_platform', false)
            ->get()
            ->keyBy('name');

        // The owner role gets every tenant permission.
        $companyRole = $this->ensureRole($companyId, 'company', $status);
        foreach ($globalPermissions as $permission) {
            $this->attachPermission($companyRole->id, $permission->id);
        }

        // The remaining default roles get their defined permission subsets.
        foreach (DefaultRoles::roles() as $roleName => $permissionNames) {
            $tenantRole = $this->ensureRole($companyId, $roleName, $status);
            foreach ($permissionNames as $permissionName) {
                if (isset($globalPermissions[$permissionName])) {
                    $this->attachPermission($tenantRole->id, $globalPermissions[$permissionName]->id);
                }
            }
        }
    }

    /**
     * Find (or create) a company-scoped role.
     *
     * @param  int  $companyId
     * @param  string  $name
     * @param  string  $status
     * @return \Modules\RBAC\Models\Role
     */
    public function ensureRole($companyId, $name, $status = 'active')
    {
        $role = $this->role->query()
            ->where('company_id', $companyId)
            ->where('name', $name)
            ->first();

        if (!$role) {
            $role = $this->role->create([
                'name' => $name,
                'company_id' => $companyId,
                'status' => $status,
            ]);
            $role->refresh();
        }

        return $role;
    }

    /**
     * Attach a global permission to a role if not already attached.
     *
     * @param  int  $roleId
     * @param  int  $permissionId
     * @return void
     */
    public function attachPermission($roleId, $permissionId)
    {
        $exists = $this->permissionRole->query()
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->exists();

        if (!$exists) {
            $this->permissionRole->create([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    /**
     * Replace a role's attached permissions with the given global permission ids.
     *
     * @param  int  $roleId
     * @param  array  $permissionIds
     * @return void
     */
    public function syncRolePermissions($roleId, array $permissionIds)
    {
        $this->permissionRole->query()->where('role_id', $roleId)->delete();

        foreach ($permissionIds as $permissionId) {
            $this->attachPermission($roleId, $permissionId);
        }
    }
}
