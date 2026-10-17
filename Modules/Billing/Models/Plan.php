<?php

namespace Modules\Billing\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends UIDModel
{
    protected $table = 'billing_plans';

    protected $guarded = ['id'];

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'plan_id', 'id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id', 'id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(PlanPermission::class, 'plan_id', 'id');
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PlanLimit::class, 'plan_id', 'id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id', 'id');
    }
}