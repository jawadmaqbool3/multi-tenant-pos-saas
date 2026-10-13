<?php

namespace Modules\Billing\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\RBAC\Models\Permission;

class PlanPermission extends UIDModel
{
    protected $table = 'billing_plan_permissions';

    protected $guarded = ['id'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}