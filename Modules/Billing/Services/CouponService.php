<?php

namespace Modules\Billing\Services;

use Illuminate\Http\Request;
use Modules\Billing\Models\Coupon;
use Modules\Billing\Repositories\CouponRepository;

class CouponService
{
    protected $coupons;
    protected $stripe;

    public function __construct(CouponRepository $coupons, StripeSyncService $stripe)
    {
        $this->coupons = $coupons;
        $this->stripe = $stripe;
    }

    /**
     * Server-side DataTable payload for the coupon catalog.
     *
     * @return mixed
     */
    public function getCoupons()
    {
        return $this->coupons->dataTable($this->coupons->query());
    }

    /**
     * View data for the create/edit form.
     *
     * @param  \Modules\Billing\Models\Coupon|null  $coupon
     * @return array
     */
    public function getFormData($coupon = null)
    {
        return [
            'coupon' => $coupon,
            'currency' => config('billing.currency'),
        ];
    }

    /**
     * Persist a new coupon and push it to Stripe.
     *
     * @return \Modules\Billing\Models\Coupon
     */
    public function store(Request $request)
    {
        $coupon = $this->coupons->create($this->attributes($request));
        $coupon->refresh();

        $this->stripe->syncCoupon($coupon);

        return $coupon->refresh();
    }

    /**
     * Update a coupon. Once a coupon exists in Stripe only its name and
     * status may change (Stripe coupons are immutable).
     *
     * @param  \Modules\Billing\Models\Coupon  $coupon
     * @return \Modules\Billing\Models\Coupon
     */
    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->attributes($request, $coupon));
        $coupon->refresh();

        $this->stripe->syncCoupon($coupon);

        return $coupon->refresh();
    }

    /**
     * @param  \Modules\Billing\Models\Coupon  $coupon
     * @return bool|null
     */
    public function delete(Coupon $coupon)
    {
        $this->stripe->archiveCoupon($coupon);

        return $this->coupons->delete($coupon->uid);
    }

    /**
     * Map the request onto coupon columns.
     *
     * @param  \Modules\Billing\Models\Coupon|null  $coupon
     * @return array
     */
    protected function attributes(Request $request, ?Coupon $coupon = null): array
    {
        if ($coupon && $coupon->stripe_coupon_id) {
            return [
                'name' => $request->name,
                'status' => $request->status,
            ];
        }

        $type = $request->discount_type;
        $value = (float) $request->discount_value;

        return [
            'code' => strtoupper(trim((string) $request->code)),
            'name' => $request->name,
            'percent_off' => $type === 'percent' ? (int) round($value) : null,
            'amount_off' => $type === 'amount' ? (int) round($value * 100) : null,
            'currency' => $type === 'amount'
                ? strtolower($request->currency ?: config('billing.currency'))
                : null,
            'duration' => $request->duration,
            'duration_in_months' => $request->duration === 'repeating'
                ? (int) $request->duration_in_months
                : null,
            'max_redemptions' => $request->max_redemptions ?: null,
            'expires_at' => $request->expires_at ?: null,
            'status' => $request->status,
        ];
    }
}
