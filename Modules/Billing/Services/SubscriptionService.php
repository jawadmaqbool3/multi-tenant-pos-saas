<?php

namespace Modules\Billing\Services;

use App\Models\Company;
use Modules\Billing\Models\Coupon;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Price;
use Modules\Billing\Models\Redemption;
use Modules\Billing\Models\Subscription;

class SubscriptionService
{
    protected $stripe;
    protected $sync;

    public function __construct(StripeService $stripe, StripeSyncService $sync)
    {
        $this->stripe = $stripe;
        $this->sync = $sync;
    }

    /**
     * Whether the Stripe integration has been configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->stripe->isConfigured();
    }

    /**
     * Get (or lazily create) the Stripe customer mapped to a company.
     *
     * @param  \App\Models\Company  $company
     * @return \Modules\Billing\Models\Customer
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function ensureCustomer(Company $company): Customer
    {
        $customer = Customer::where('company_id', $company->id)->first();

        if ($customer) {
            return $customer;
        }

        $setting = $company->setting;
        $email = ($setting && $setting->email) ? $setting->email : ('company' . $company->id . '@example.com');
        $name = ($setting && $setting->name) ? $setting->name : ('Company #' . $company->id);

        $stripeCustomer = $this->stripe->createCustomer($email, $name, [
            'company_id' => (string) $company->id,
        ]);

        return Customer::create([
            'company_id' => $company->id,
            'stripe_customer_id' => $stripeCustomer->id,
        ]);
    }

    /**
     * Create a Stripe Checkout Session for a price and return its redirect URL.
     *
     * @param  \App\Models\Company  $company
     * @param  \Modules\Billing\Models\Price  $price
     * @param  \Modules\Billing\Models\Coupon|null  $coupon
     * @return string
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function startCheckout(Company $company, Price $price, ?Coupon $coupon = null): string
    {
        $price->loadMissing('plan');

        // Make sure the plan/product and its prices exist in Stripe first.
        $this->sync->syncPlan($price->plan->refresh());
        $price->refresh();

        if (!$price->stripe_price_id) {
            throw new \RuntimeException('This plan could not be synced to Stripe. Please try again later.');
        }

        $customer = $this->ensureCustomer($company);

        $metadata = [
            'company_id' => (string) $company->id,
            'plan_id' => (string) $price->plan_id,
            'price_id' => (string) $price->id,
        ];

        $params = [
            'mode' => 'subscription',
            'customer' => $customer->stripe_customer_id,
            'line_items' => [
                ['price' => $price->stripe_price_id, 'quantity' => 1],
            ],
            'success_url' => route('billing.subscribe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('billing.subscribe.cancel'),
            'client_reference_id' => (string) $company->id,
            'metadata' => $metadata,
            'subscription_data' => [
                'metadata' => [
                    'company_id' => (string) $company->id,
                ],
            ],
        ];

        if ($coupon && $coupon->isRedeemable()) {
            // Apply a specific promotion code entered on the pricing page.
            $params['discounts'] = [
                ['promotion_code' => $coupon->stripe_promotion_code_id],
            ];
            $params['metadata']['coupon_id'] = (string) $coupon->id;
        } else {
            // Otherwise let customers enter a promotion code on Stripe's page.
            $params['allow_promotion_codes'] = true;
        }

        $session = $this->stripe->createCheckoutSession($params);

        return $session->url;
    }

    /**
     * Activate the subscription behind a completed Checkout Session and return
     * the local subscription record (idempotent with the webhook).
     *
     * @param  string  $sessionId
     * @return \Modules\Billing\Models\Subscription|null
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function activateFromCheckoutSession(string $sessionId)
    {
        $session = $this->stripe->retrieveCheckoutSession($sessionId, ['subscription']);

        $subscriptionId = is_object($session->subscription)
            ? $session->subscription->id
            : $session->subscription;

        if (!$subscriptionId) {
            return null;
        }

        $stripeSubscription = $this->stripe->retrieveSubscription($subscriptionId, ['items.data.price']);

        $subscription = $this->syncFromStripe($stripeSubscription);

        $this->recordRedemption($session, $subscription);

        return $subscription;
    }

    /**
     * Record that a coupon was redeemed on a completed checkout session.
     *
     * @param  \Stripe\Checkout\Session  $session
     * @param  \Modules\Billing\Models\Subscription|null  $subscription
     * @return void
     */
    protected function recordRedemption($session, $subscription)
    {
        $couponId = isset($session->metadata->coupon_id) ? (int) $session->metadata->coupon_id : null;

        if (!$couponId) {
            return;
        }

        $companyId = isset($session->metadata->company_id)
            ? (int) $session->metadata->company_id
            : ($subscription ? $subscription->company_id : null);

        if (!$companyId) {
            return;
        }

        $coupon = Coupon::where('id', $couponId)->first();

        if (!$coupon) {
            return;
        }

        Redemption::firstOrCreate(
            [
                'coupon_id' => $coupon->id,
                'company_id' => $companyId,
                'subscription_id' => $subscription ? $subscription->id : null,
            ],
            [
                'stripe_promotion_code_id' => $coupon->stripe_promotion_code_id,
            ]
        );
    }

    /**
     * Retrieve a Stripe subscription by id and mirror it locally.
     *
     * @param  string  $stripeSubscriptionId
     * @return \Modules\Billing\Models\Subscription|null
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function syncByStripeId(string $stripeSubscriptionId)
    {
        $stripeSubscription = $this->stripe->retrieveSubscription($stripeSubscriptionId, ['items.data.price']);

        return $this->syncFromStripe($stripeSubscription);
    }

    /**
     * Upsert the local subscription mirror from a Stripe subscription object.
     *
     * @param  \Stripe\Subscription  $stripeSubscription
     * @return \Modules\Billing\Models\Subscription|null
     */
    public function syncFromStripe($stripeSubscription)
    {
        $stripeCustomerId = is_object($stripeSubscription->customer)
            ? $stripeSubscription->customer->id
            : $stripeSubscription->customer;

        $customer = $stripeCustomerId
            ? Customer::where('stripe_customer_id', $stripeCustomerId)->first()
            : null;

        $companyId = $customer ? $customer->company_id : null;

        if (!$companyId && isset($stripeSubscription->metadata->company_id)) {
            $companyId = (int) $stripeSubscription->metadata->company_id;
        }

        if (!$companyId) {
            return null;
        }

        $priceId = null;
        $planId = null;
        $firstItem = null;

        if (isset($stripeSubscription->items->data[0])) {
            $firstItem = $stripeSubscription->items->data[0];
            $stripePriceId = is_object($firstItem->price) ? $firstItem->price->id : $firstItem->price;

            if ($stripePriceId) {
                $price = Price::where('stripe_price_id', $stripePriceId)->first();
                if ($price) {
                    $priceId = $price->id;
                    $planId = $price->plan_id;
                }
            }
        }

        [$periodStart, $periodEnd] = $this->resolvePeriod($stripeSubscription, $firstItem);

        return Subscription::updateOrCreate(
            ['stripe_subscription_id' => $stripeSubscription->id],
            [
                'company_id' => $companyId,
                'plan_id' => $planId,
                'price_id' => $priceId,
                'billing_customer_id' => $customer ? $customer->id : null,
                'status' => $stripeSubscription->status,
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'cancel_at_period_end' => (bool) ($stripeSubscription->cancel_at_period_end ?? false),
                'metadata' => json_encode([
                    'stripe_status' => $stripeSubscription->status,
                ]),
            ]
        );
    }

    /**
     * Resolve the current billing period. Stripe moved these fields from the
     * subscription onto its items in recent API versions, so check both.
     *
     * @param  \Stripe\Subscription  $subscription
     * @param  mixed  $firstItem
     * @return array{0: string|null, 1: string|null}
     */
    protected function resolvePeriod($subscription, $firstItem): array
    {
        $start = $subscription->current_period_start ?? null;
        $end = $subscription->current_period_end ?? null;

        if ((!$start || !$end) && $firstItem) {
            $start = $start ?: ($firstItem->current_period_start ?? null);
            $end = $end ?: ($firstItem->current_period_end ?? null);
        }

        return [
            $start ? date('Y-m-d H:i:s', (int) $start) : null,
            $end ? date('Y-m-d H:i:s', (int) $end) : null,
        ];
    }
}
