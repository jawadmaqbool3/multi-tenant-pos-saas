<?php

namespace Modules\RBAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Permission extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'is_platform' => 'boolean',
    ];

    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopePlatform($query)
    {
        return $query->where('is_platform', true);
    }

    public function scopeTenant($query)
    {
        return $query->where('is_platform', false);
    }
    public function module(): HasOne
    {
        return $this->hasOne(PermissionModule::class, 'id', 'module_id');
    }
}
