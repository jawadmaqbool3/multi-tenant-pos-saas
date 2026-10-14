<?php

namespace Modules\Billing\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends UIDModel
{
    protected $table = 'billing_coupons';

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Whether this coupon can currently be redeemed at checkout.
     *
     * @return bool
     */
    public function isRedeemable(): bool
    {
        if ($this->status !== 'active' || !$this->stripe_promotion_code_id) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Human readable discount summary, e.g. "20% off" or "5.00 USD off".
     *
     * @return string
     */
    public function discountLabel(): string
    {
        if ($this->percent_off) {
            return rtrim(rtrim(number_format($this->percent_off, 2), '0'), '.') . '% off';
        }

        if ($this->amount_off) {
            return number_format($this->amount_off / 100, 2) . ' '
                . strtoupper($this->currency ?: config('billing.currency')) . ' off';
        }

        return '—';
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class, 'coupon_id', 'id');
    }
}