<?php

namespace Modules\Billing\Models;

use App\Models\Company;
use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends UIDModel
{
    protected $table = 'billing_redemptions';

    protected $guarded = ['id'];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }
}