<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Coupon;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Price;
use Modules\Billing\Repositories\PriceRepository;

class StripeSyncService
{
    protected $stripe;
    protected $prices;

    public function __construct(StripeService $stripe, PriceRepository $prices)
    {
        $this->stripe = $stripe;
        $this->prices = $prices;
    }

    /**
     * Push a plan (product) and its prices to Stripe so checkout sessions
     * can be created against real Stripe objects.
     *
     * When Stripe isn't configured the plan is kept local-only and the
     * `stripe_*` columns stay null.
     *
     * @param  \Modules\Billing\Models\Plan  $plan
     * @return void
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function syncPlan(Plan $plan)
    {
        if (!$this->stripe->isConfigured()) {
            return;
        }

        $client = $this->stripe->client();

        $this->stripe->request(function () use ($client, $plan) {
            $this->syncProduct($client, $plan);

            foreach ($this->prices->query()->where('plan_id', $plan->id)->get() as $price) {
                $this->syncPrice($client, $plan, $price);
            }
        });
    }

    /**
     * Push a coupon/promotion to Stripe as a Coupon (the discount) plus a
     * Promotion Code (the customer-facing code). Both ids are stored on the
     * local record so checkout sessions can apply the discount.
     *
     * Stripe coupons are immutable: only the name/metadata can be updated
     * once created, so discount-defining fields are only sent on creation.
     *
     * @param  \Modules\Billing\Models\Coupon  $coupon
     * @return void
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function syncCoupon(Coupon $coupon)
    {
        if (!$this->stripe->isConfigured()) {
            return;
        }

        $client = $this->stripe->client();

        $this->stripe->request(function () use ($client, $coupon) {
            $this->syncStripeCoupon($client, $coupon);
            $this->syncStripePromotionCode($client, $coupon);
        });

        $coupon->save();
    }

    /**
     * Create or update the Stripe Coupon backing this record.
     *
     * @param  \Stripe\StripeClient  $client
     * @param  \Modules\Billing\Models\Coupon  $coupon
     * @return void
     */
    protected function syncStripeCoupon($client, Coupon $coupon)
    {
        $data = [
            'name' => $coupon->name ?: $coupon->code,
            'metadata' => [
                'local_id' => (string) $coupon->id,
                'local_uid' => $coupon->uid,
            ],
        ];

        if ($coupon->stripe_coupon_id) {
            $client->coupons->update($coupon->stripe_coupon_id, $data);
            return;
        }

        $data['duration'] = $coupon->duration ?: 'once';

        if ($coupon->percent_off !== null) {
            $data['percent_off'] = (float) $coupon->percent_off;
        } else {
            $data['amount_off'] = (int) $coupon->amount_off;
            $data['currency'] = strtolower($coupon->currency ?: config('billing.currency'));
        }

        if ($coupon->duration === 'repeating') {
            $data['duration_in_months'] = max(1, (int) $coupon->duration_in_months);
        }

        if ($coupon->max_redemptions) {
            $data['max_redemptions'] = (int) $coupon->max_redemptions;
        }

        if ($coupon->expires_at) {
            $data['redeem_by'] = $coupon->expires_at->timestamp;
        }

        $stripeCoupon = $client->coupons->create($data);
        $coupon->stripe_coupon_id = $stripeCoupon->id;
    }

    /**
     * Create (or toggle) the Stripe Promotion Code customers type at checkout.
     *
     * @param  \Stripe\StripeClient  $client
     * @param  \Modules\Billing\Models\Coupon  $coupon
     * @return void
     */
    protected function syncStripePromotionCode($client, Coupon $coupon)
    {
        if ($coupon->stripe_promotion_code_id) {
            $client->promotionCodes->update($coupon->stripe_promotion_code_id, [
                'active' => $coupon->status === 'active',
            ]);
            return;
        }

        $data = [
            'promotion' => [
                'type' => 'coupon',
                'coupon' => $coupon->stripe_coupon_id,
            ],
            'code' => $coupon->code,
            'active' => $coupon->status === 'active',
            'metadata' => [
                'local_id' => (string) $coupon->id,
                'local_uid' => $coupon->uid,
            ],
        ];

        if ($coupon->expires_at) {
            $data['expires_at'] = $coupon->expires_at->timestamp;
        }

        $promotionCode = $client->promotionCodes->create($data);
        $coupon->stripe_promotion_code_id = $promotionCode->id;
    }

    /**
     * Best-effort deactivate/remove the Stripe objects behind a deleted
     * coupon. Promotion codes cannot be reused once deactivated, but the
     * code is retired together with the local record.
     *
     * @param  \Modules\Billing\Models\Coupon  $coupon
     * @return void
     */
    public function archiveCoupon(Coupon $coupon)
    {
        if (!$this->stripe->isConfigured()) {
            return;
        }

        $client = $this->stripe->client();

        try {
            $this->stripe->request(function () use ($client, $coupon) {
                if ($coupon->stripe_promotion_code_id) {
                    $client->promotionCodes->update($coupon->stripe_promotion_code_id, ['active' => false]);
                }
                if ($coupon->stripe_coupon_id) {
                    $client->coupons->delete($coupon->stripe_coupon_id);
                }
            });
        } catch (\Throwable $e) {
            // Local record is already gone; Stripe cleanup is best-effort.
        }
    }

    /**
     * Create or update the Stripe Product backing this plan.
     *
     * @param  \Stripe\StripeClient  $client
     * @param  \Modules\Billing\Models\Plan  $plan
     * @return void
     */
    protected function syncProduct($client, Plan $plan)
    {
        $data = [
            'name' => $plan->name,
            'description' => $plan->description,
            'metadata' => [
                'local_id' => (string) $plan->id,
                'local_uid' => $plan->uid,
            ],
        ];

        if ($plan->stripe_product_id) {
            $client->products->update($plan->stripe_product_id, $data);
            return;
        }

        $product = $client->products->create(
            $data + ['active' => $plan->is_public ? true : false]
        );
        $plan->stripe_product_id = $product->id;
        $plan->save();
    }

    /**
     * Create (or re-activate/archive) the Stripe Price for a local price.
     *
     * @param  \Stripe\StripeClient  $client
     * @param  \Modules\Billing\Models\Plan  $plan
     * @param  \Modules\Billing\Models\Price  $price
     * @return void
     */
    protected function syncPrice($client, Plan $plan, Price $price)
    {
        if (!$price->stripe_price_id) {
            $stripePrice = $client->prices->create([
                'product' => $plan->stripe_product_id,
                'unit_amount' => $price->unit_amount,
                'currency' => $price->currency,
                'recurring' => [
                    'interval' => $price->interval,
                    'interval_count' => $price->interval_count ?: 1,
                ],
                'active' => $price->active ? true : false,
                'metadata' => [
                    'local_id' => (string) $price->id,
                    'local_uid' => $price->uid,
                ],
            ]);
            $price->stripe_price_id = $stripePrice->id;
            $price->save();
            return;
        }

        $client->prices->update($price->stripe_price_id, [
            'active' => $price->active ? true : false,
        ]);

        if ($price->unit_amount || $price->interval) {
            $client->prices->update($price->stripe_price_id, [
                'metadata' => [
                    'unit_amount' => (string) $price->unit_amount,
                    'interval' => $price->interval,
                ],
            ]);
        }
    }
}