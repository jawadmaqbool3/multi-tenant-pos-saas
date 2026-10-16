<?php

namespace Modules\Billing\Services;

use App\Repositories\CompanyRepository;
use Modules\Billing\Models\Coupon;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Price;
use Modules\Billing\Repositories\CouponRepository;
use Modules\Billing\Repositories\CustomerRepository;
use Modules\Billing\Repositories\PlanRepository;
use Modules\Billing\Repositories\PriceRepository;
use Modules\Billing\Repositories\RedemptionRepository;
use Modules\Billing\Repositories\SignupRepository;
use Modules\Billing\Repositories\SubscriptionRepository;
use App\Repositories\SettingRepository;

class SubscriptionService
{
    protected $stripe;
    protected $sync;
    protected $companies;
    protected $customers;
    protected $coupons;
    protected $redemptions;
    protected $prices;
    protected $subscriptions;
    protected $signups;
    protected $plans;
    protected $settings;

    public function __construct(
        StripeService $stripe,
        StripeSyncService $sync,
        CompanyRepository $companies,
        CustomerRepository $customers,
        CouponRepository $coupons,
        RedemptionRepository $redemptions,
        PriceRepository $prices,
        SubscriptionRepository $subscriptions,
        SignupRepository $signups,
        PlanRepository $plans,
        SettingRepository $settings
    ) {
        $this->stripe = $stripe;
        $this->sync = $sync;
        $this->companies = $companies;
        $this->customers = $customers;
        $this->coupons = $coupons;
        $this->redemptions = $redemptions;
        $this->prices = $prices;
        $this->subscriptions = $subscriptions;
        $this->signups = $signups;
        $this->plans = $plans;
        $this->settings = $settings;
    }

    public function isConfigured(): bool
    {
        return $this->stripe->isConfigured();
    }

    public function getCompany($companyId)
    {
        return $this->companies->query()->where('id', $companyId)->first();
    }

    public function getPublicPlans()
    {
        return $this->plans->query()
            ->where('status', 'active')
            ->where('is_public', true)
            ->with([
                'prices' => function ($query) {
                    $query->where('active', true)->orderBy('unit_amount');
                },
                'limits',
            ])
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->filter(function ($plan) {
                return $plan->prices->isNotEmpty();
            })
            ->values();
    }

    public function getSelectedPrice(string $email)
    {
        $signup = $this->signups->query()->where('email', $email)->latest('id')->first();
        return $signup && $signup->price_id ? $signup->price_id : null;
    }

    public function getActivePrice(string $uid)
    {
        return $this->prices->query()->where('uid', $uid)->where('active', true)->first();
    }

    public function getCoupon(string $code)
    {
        return $this->coupons->query()->where('code', $code)->first();
    }

    public function ensureCustomer($company): Customer
    {
        $customer = $this->customers->query()->where('company_id', $company->id)->first();

        if ($customer) {
            return $customer;
        }

        $setting = $company->setting;
        $email = ($setting && $setting->email) ? $setting->email : ('company' . $company->id . '@example.com');
        $name = ($setting && $setting->name) ? $setting->name : ('Company #' . $company->id);

        $stripeCustomer = $this->stripe->createCustomer($email, $name, [
            'company_id' => (string) $company->id,
        ]);

        return $this->customers->create([
            'company_id' => $company->id,
            'stripe_customer_id' => $stripeCustomer->id,
        ]);
    }

    public function startCheckout($company, Price $price, ?Coupon $coupon = null): string
    {
        $price->loadMissing('plan');

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
            $params['discounts'] = [
                ['promotion_code' => $coupon->stripe_promotion_code_id],
            ];
            $params['metadata']['coupon_id'] = (string) $coupon->id;
        } else {
            $params['allow_promotion_codes'] = true;
        }

        $session = $this->stripe->createCheckoutSession($params);

        return $session->url;
    }

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

        $coupon = $this->coupons->query()->where('id', $couponId)->first();

        if (!$coupon) {
            return;
        }

        $this->redemptions->firstOrCreate(
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

    public function syncByStripeId(string $stripeSubscriptionId)
    {
        $stripeSubscription = $this->stripe->retrieveSubscription($stripeSubscriptionId, ['items.data.price']);

        return $this->syncFromStripe($stripeSubscription);
    }

    public function syncFromStripe($stripeSubscription)
    {
        $stripeCustomerId = is_object($stripeSubscription->customer)
            ? $stripeSubscription->customer->id
            : $stripeSubscription->customer;

        $customer = $stripeCustomerId
            ? $this->customers->query()->where('stripe_customer_id', $stripeCustomerId)->first()
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
                $price = $this->prices->query()->where('stripe_price_id', $stripePriceId)->first();
                if ($price) {
                    $priceId = $price->id;
                    $planId = $price->plan_id;
                }
            }
        }

        [$periodStart, $periodEnd] = $this->resolvePeriod($stripeSubscription, $firstItem);

        return $this->subscriptions->updateOrCreate(
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

    protected function resolvePeriod($subscription, $firstItem)
    {
        $periodStart = null;
        $periodEnd = null;

        if (isset($firstItem->current_period_start)) {
            $periodStart = \Carbon\Carbon::createFromTimestamp($firstItem->current_period_start)->toDateTimeString();
        } elseif (isset($subscription->current_period_start)) {
            $periodStart = \Carbon\Carbon::createFromTimestamp($subscription->current_period_start)->toDateTimeString();
        }

        if (isset($firstItem->current_period_end)) {
            $periodEnd = \Carbon\Carbon::createFromTimestamp($firstItem->current_period_end)->toDateTimeString();
        } elseif (isset($subscription->current_period_end)) {
            $periodEnd = \Carbon\Carbon::createFromTimestamp($subscription->current_period_end)->toDateTimeString();
        }

        return [$periodStart, $periodEnd];
    }
}
