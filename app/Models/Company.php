<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Traits\CompanyDataTableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends UIDModel
{
    use HasFactory, CompanyDataTableTrait;

    protected $guarded = ['id'];
    public function scopeFilter($query)
    {
        if (auth()->user()->hasRole('superadmin')) {
            return $query;
        } else {
            return $query->where('created_by', auth()->user()->company_id);
        }
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class, 'company_id', 'id');
    }
    public function manager(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'company_id', 'id');
    }
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'company_id', 'id');
    }
    public function dashboardSetting(): HasOne
    {
        return $this->hasOne(DashboardSetting::class, 'company_id', 'id');
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }

    public function hasRole($role)
    {
        return Role::where('company_id', $this->id)->where('name', $role)->exists();
    }
   
    public function hasPermission($permission)
    {
        return Permission::where('company_id', $this->id)->where('name', $permission)->exists();
    }
}
