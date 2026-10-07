<?php

namespace Modules\RBAC\Http\Traits;

use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\UserRole;

/**
 * 
 */
trait RoleTrait
{
    public function hasPermission($permission)
    {
        return Role::where('id', $this->role->id)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission)->where('company_id', auth()->user()->company_id);
            })->exists();
    }
    public function hasRole($role)
    {
        return $this->role->name == $role;
    }

    public function role()
    {
        $company_id = $this->company_id;
        return $this->hasOneThrough(
            Role::class,
            UserRole::class,
            'user_id',
            'id',
            'id',
            'role_id',
        );
    }
}
