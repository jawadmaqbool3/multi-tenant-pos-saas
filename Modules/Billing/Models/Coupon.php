<?php

namespace Modules\Billing\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends UIDModel
{
    protected $table = 'billing_coupons';

    protected $guarded = ['id'];

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class, 'coupon_id', 'id');
    }
}