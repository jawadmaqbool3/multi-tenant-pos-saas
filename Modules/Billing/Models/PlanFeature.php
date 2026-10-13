<?php

namespace Modules\Billing\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\RBAC\Models\PermissionModule;

class PlanFeature extends UIDModel
{
    protected $table = 'billing_plan_features';

    protected $guarded = ['id'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(PermissionModule::class, 'permission_module_id', 'id');
    }
}