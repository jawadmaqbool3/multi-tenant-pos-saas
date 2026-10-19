<?php

namespace Modules\Billing\Services;

use App\Exceptions\CustomException;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use Carbon\Carbon;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Price;
use Modules\Billing\Repositories\PlanFeatureRepository;
use Modules\Billing\Repositories\PlanLimitRepository;
use Modules\Billing\Repositories\PlanRepository;
use Modules\Billing\Repositories\PriceRepository;
use Modules\Billing\Repositories\SubscriptionRepository;

/**
 * Tenant-facing subscription self-service ("My Subscription"): the company's
 * current plan, live resource usage against plan limits, the plans available to
 * switch to, and in-place upgrade/downgrade.
 */
class CompanySubscriptionService
{
    /** @var array<string, string> */
    protected $limitKeys = [
        'employees' => 'Employees',
        'users' => 'Users',
        'roles' => 'Roles',
        'products' => 'Products',
        'suppliers' => 'Suppliers',
        'promotions' => 'Promotions',
        'purchase_orders' => 'Purchase Orders',
    ];

    protected $companies;
    protected $plans;
    protected $prices;
    protected $subscriptions;
    protected $planFeatures;
    protected $planLimits;
    protected $limitService;
    protected $planFeatureService;
    protected $stripe;
    protected $sync;

    public function __construct(
        CompanyRepository $companies,
        PlanRepository $plans,
        PriceRepository $prices,
        SubscriptionRepository $subscriptions,
        PlanFeatureRepository $planFeatures,
        PlanLimitRepository $planLimits,
        PlanLimitService $limitService,
        PlanFeatureService $planFeatureService,
        StripeService $stripe,
        StripeSyncService $sync
    ) {
        $this->companies = $companies;
        $this->plans = $plans;
        $this->prices = $prices;
        $this->subscriptions = $subscriptions;
        $this->planFeatures = $planFeatures;
        $this->planLimits = $planLimits;
        $this->limitService = $limitService;
        $this->planFeatureService = $planFeatureService;
        $this->stripe = $stripe;
        $this->sync = $sync;
    }

    /**
     * The company behind the currently authenticated tenant user.
     *
     * @return \App\Models\Company|null
     */
    public function company(): ?Company
    {
        $user = auth()->user();

        if (!$user || !$user->company_id) {
            return null;
        }

        return $this->companies->query()->where('id', $user->company_id)->first();
    }

    /**
     * Everything the "My Subscription" page needs.
     *
     * @param  \App\Models\Company|null  $company
     * @return array
     */
    public function overview(?Company $company = null): array
    {
        $company = $company ?: $this->company();

        if (!$company) {
            return [
                'company' => null,
                'subscription' => null,
                'plan' => null,
                'price' => null,
                'renewal' => null,
                'usage' => [],
                'plans' => [],
                'features' => [],
                'configured' => $this->stripe->isConfigured(),
            ];
        }

        $subscription = $this->subscriptions->activeForCompany($company->id);

        $plan = null;
        $price = null;

        if ($subscription) {
            $subscription->loadMissing(['plan', 'price']);
            $plan = $subscription->plan;
            $price = $subscription->price;
        }

        return [
            'company' => $company,
            'subscription' => $subscription,
            'plan' => $plan,
            'price' => $price,
            'renewal' => $this->renewal($subscription),
            'usage' => $this->usage($company, $plan),
            'plans' => $this->availablePlans($company),
            'features' => $this->featureChecklist($plan),
            'configured' => $this->stripe->isConfigured(),
        ];
    }

    /**
     * Renewal/status details for the header card.
     *
     * @param  \Modules\Billing\Models\Subscription|null  $subscription
     * @return array|null
     */
    public function renewal($subscription): ?array
    {
        if (!$subscription) {
            return null;
        }

        $end = $subscription->current_period_end ? Carbon::parse($subscription->current_period_end) : null;

        return [
            'status' => $subscription->status,
            'period_start' => $subscription->current_period_start,
            'period_end' => $end,
            'days_left' => $end ? (int) max(0, now()->diffInDays($end, false)) : null,
            'auto_renew' => !$subscription->cancel_at_period_end,
            'cancel_at_period_end' => (bool) $subscription->cancel_at_period_end,
        ];
    }

    /**
     * Per-resource usage rows: label, used, limit, remaining, percentage.
     *
     * @param  \App\Models\Company  $company
     * @param  \Modules\Billing\Models\Plan|null  $plan
     * @return array<int, array>
     */
    public function usage(Company $company, ?Plan $plan): array
    {
        if (!$plan) {
            return [];
        }

        $rows = [];

        foreach ($this->limitKeys as $key => $label) {
            $limit = $this->limitService->limitFor($key, $plan);
            $used = $this->limitService->usage($key, $company);

            $rows[] = [
                'key' => $key,
                'label' => $label,
                'used' => $used,
                'limit' => $limit,
                'unlimited' => $limit === null,
                'remaining' => $limit === null ? null : max(0, $limit - $used),
                'percentage' => $limit === null || $limit === 0
                    ? ($limit === null ? 0 : 100)
                    : (int) min(100, round(($used / $limit) * 100)),
                'exceeded' => $limit !== null && $used >= $limit,
            ];
        }

        return $rows;
    }

    /**
     * Active, public plans (with the company's current one flagged) that a
     * tenant may switch to.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Support\Collection<int, array>
     */
    public function availablePlans(Company $company)
    {
        $subscription = $this->subscriptions->activeForCompany($company->id);
        $currentPlanId = $subscription ? $subscription->plan_id : null;

        return $this->plans->query()
            ->where('status', 'active')
            ->where('is_public', true)
            ->with(['prices' => function ($query) {
                $query->where('active', true)->orderBy('unit_amount');
            }, 'limits'])
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->filter(fn ($plan) => $plan->prices->isNotEmpty())
            ->map(function (Plan $plan) use ($currentPlanId) {
                $price = $plan->prices->first();

                return [
                    'plan' => $plan,
                    'price' => $price,
                    'uid' => $price ? $price->uid : null,
                    'amount' => $price ? round($price->unit_amount / 100, 2) : 0,
                    'currency' => $price ? strtoupper($price->currency) : strtoupper(config('billing.currency')),
                    'interval' => $price ? $price->interval : 'month',
                    'is_current' => $plan->id === $currentPlanId,
                    'features' => $this->featureChecklist($plan),
                    'limits' => $this->planLimits->valuesForPlan($plan->id),
                ];
            })
            ->values();
    }

    /**
     * Feature checklist lines for a plan.
     *
     * @param  \Modules\Billing\Models\Plan|null  $plan
     * @return array<int, array>
     */
    public function featureChecklist(?Plan $plan): array
    {
        if (!$plan) {
            return [];
        }

        return app(PlanFeaturePresenter::class)->present($plan);
    }

    /**
     * Change the company's plan in place (upgrade/downgrade).
     *
     * Local state is always reconciled; when a Stripe subscription backs the
     * company and both the price is synced and the subscription is retrievable,
     * the Stripe item is repointed too. Errors from Stripe are surfaced so the
     * tenant can retry.
     *
     * @param  \App\Models\Company  $company
     * @param  string  $priceUid
     * @return array
     *
     * @throws \App\Exceptions\CustomException
     */
    public function changePlan(Company $company, string $priceUid): array
    {
        $subscription = $this->subscriptions->activeForCompany($company->id);

        if (!$subscription) {
            throw new CustomException('Your company has no active subscription to change.', 'error');
        }

        $price = $this->prices->query()->where('uid', $priceUid)->where('active', true)->first();

        if (!$price) {
            throw new CustomException('The selected plan is no longer available.', 'error', 'price');
        }

        if ((int) $price->plan_id === (int) $subscription->plan_id) {
            throw new CustomException('You are already on this plan.', 'error', 'price');
        }

        $price->loadMissing('plan');
        $plan = $price->plan;

        if (!$plan || $plan->status !== 'active') {
            throw new CustomException('The selected plan is no longer available.', 'error', 'price');
        }

        $this->guardUsageAgainstPlan($company, $plan);

        $stripeUpdated = $this->updateStripeSubscription($subscription, $price);

        $this->subscriptions->query()->where('id', $subscription->id)->update([
            'plan_id' => $plan->id,
            'price_id' => $price->id,
        ]);

        return [
            'subscription' => $subscription->refresh(),
            'plan' => $plan,
            'price' => $price,
            'stripe_updated' => $stripeUpdated,
        ];
    }

    /**
     * Block a downgrade that the company's current usage would immediately
     * exceed.
     *
     * @param  \App\Models\Company  $company
     * @param  \Modules\Billing\Models\Plan  $plan
     * @return void
     *
     * @throws \App\Exceptions\CustomException
     */
    protected function guardUsageAgainstPlan(Company $company, Plan $plan): void
    {
        foreach ($this->limitKeys as $key => $label) {
            $limit = $this->limitService->limitFor($key, $plan);

            if ($limit === null) {
                continue;
            }

            $used = $this->limitService->usage($key, $company);

            if ($used > $limit) {
                throw new CustomException(
                    "You currently have {$used} {$label} but the {$plan->name} plan allows only {$limit}. " .
                        'Remove some before switching to this plan.',
                    'error'
                );
            }
        }
    }

    /**
     * Repoint the underlying Stripe subscription to the new price.
     *
     * @param  \Modules\Billing\Models\Subscription  $subscription
     * @param  \Modules\Billing\Models\Price  $price
     * @return bool
     *
     * @throws \App\Exceptions\CustomException
     */
    protected function updateStripeSubscription($subscription, Price $price): bool
    {
        if (!$this->stripe->isConfigured() || !$subscription->stripe_subscription_id) {
            return false;
        }

        // Make sure the target price exists in Stripe before repointing.
        $this->sync->syncPlan($price->plan->refresh());
        $price->refresh();

        if (!$price->stripe_price_id) {
            throw new CustomException('This plan could not be synced to the payment provider. Please try again later.', 'error');
        }

        $stripeSubscription = $this->stripe->retrieveSubscription(
            $subscription->stripe_subscription_id,
            ['items.data.price']
        );

        $item = $stripeSubscription->items->data[0] ?? null;

        if (!$item) {
            throw new CustomException('Your subscription has no billable item to change.', 'error');
        }

        $this->stripe->updateSubscriptionPrice(
            $subscription->stripe_subscription_id,
            $item->id,
            $price->stripe_price_id
        );

        return true;
    }
}
