<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

class Company extends UIDModel
{
    use HasFactory;

    protected $guarded = ['id'];
    public function scopeFilter($query)
    {
        if (!auth()->check() || auth()->user()->hasRole('superadmin')) {
            return $query;
        }

        return $query->where('id', auth()->user()->company_id);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class, 'company_id', 'id');
    }

    public function dashboardSetting(): HasOne
    {
        return $this->hasOne(DashboardSetting::class, 'company_id', 'id');
    }

    public function hasRole($role)
    {
        $roleClass = Relation::getMorphedModel('role');
        return $roleClass::where('company_id', $this->id)->where('name', $role)->exists();
    }

    public function hasPermission($permission)
    {
        $permissionClass = Relation::getMorphedModel('permission');
        return $permissionClass::where('company_id', $this->id)->where('name', $permission)->exists();
    }
}
