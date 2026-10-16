<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Billing\Models\Subscription;

class Company extends UIDModel
{
    use HasFactory;

    protected $guarded = ['id'];
    public function scopeFilter($query)
    {
        if (!auth()->check() || auth()->user()->hasRole('admin')) {
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'company_id', 'id');
    }

    /**
     * The subscription currently granting access to the app, if any.
     *
     * @return \Modules\Billing\Models\Subscription|null
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($query) {
                $query->whereNull('current_period_end')
                    ->orWhere('current_period_end', '>', now());
            })
            ->latest('id')
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    public function hasRole($role)
    {
        $roleClass = Relation::getMorphedModel('role');
        return $roleClass::where('company_id', $this->id)->where('name', $role)->exists();
    }

    public function hasPermission($permission)
    {
        $roleClass = Relation::getMorphedModel('role');
        return $roleClass::where('company_id', $this->id)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })->exists();
    }
}
