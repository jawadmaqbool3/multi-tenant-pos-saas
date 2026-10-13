<?php

namespace Modules\RBAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Role extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];


    public function scopeFilter($query)
    {
        if (auth()->user()->hasRole('admin')) {
            return $query->where('company_id', auth()->user()->company_id);
        } else {
            return $query->where('company_id', auth()->user()->company_id);
        }
    }

    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            PermissionRole::class,
            'role_id',
            'id',
            'id',
            'permission_id',
        );
    }

    public function hasPermission($name)
    {
        if (Role::where('id', $this->id)
            ->whereHas('permissions', function ($query) use ($name) {
                $query->where('name', $name);
            })->exists()
        ) {
            return true;
        }
        return false;
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }
}
