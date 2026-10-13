<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Price;

class StripeSyncService
{
    protected $stripe;

    public function __construct(StripeService $stripe)
    {
        $this->stripe = $stripe;
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

            foreach ($plan->prices as $price) {
                $this->syncPrice($client, $plan, $price);
            }
        });
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