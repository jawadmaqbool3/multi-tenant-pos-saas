<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;

trait RoleTrait
{
    public function hasPermission($permission)
    {
        if (!$this->role) {
            return false;
        }

        return $this->morphClassName('role')::where('id', $this->role->id)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })->exists();
    }
    public function hasRole($role)
    {
        return optional($this->role)->name == $role;
    }

    public function role()
    {
        return $this->hasOneThrough(
            $this->morphClassName('role'),
            $this->morphClassName('user_role'),
            'user_id',
            'id',
            'id',
            'role_id',
        );
    }

    protected function morphClassName($alias)
    {
        return Relation::getMorphedModel($alias) ?? $alias;
    }
}
