<?php

namespace Modules\RBAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionModule extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'is_platform' => 'boolean',
    ];

    public function scopePlatform($query)
    {
        return $query->where('is_platform', true);
    }

    public function scopeTenant($query)
    {
        return $query->where('is_platform', false);
    }
}
