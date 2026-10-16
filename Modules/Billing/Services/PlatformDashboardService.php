<?php

namespace Modules\Billing\Services;

use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\Cache;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Billing\Repositories\PaymentRepository;
use Modules\Billing\Repositories\SubscriptionRepository;
use Modules\Employee\Repositories\UserRepository;

/**
 * Data source for the platform (SaaS owner) dashboard widgets. Every method
 * degrades gracefully so one failing figure (e.g. Stripe) never blocks the
 * rest of the dashboard.
 */
class PlatformDashboardService
{
    protected $companies;
    protected $users;
    protected $subscriptions;
    protected $invoices;
    protected $payments;
    protected $stripe;

    public function __construct(
        CompanyRepository $companies,
        UserRepository $users,
        SubscriptionRepository $subscriptions,
        InvoiceRepository $invoices,
        PaymentRepository $payments,
        StripeService $stripe
    ) {
        $this->companies = $companies;
        $this->users = $users;
        $this->subscriptions = $subscriptions;
        $this->invoices = $invoices;
        $this->payments = $payments;
        $this->stripe = $stripe;
    }

    public function totalCompanies(): int
    {
        return $this->companies->query()->count();
    }

    public function activeCompanies(): int
    {
        return $this->companies->countStatus('active');
    }

    public function inactiveCompanies(): int
    {
        return $this->companies->countStatus('inactive');
    }

    public function newCompaniesThisMonth(): int
    {
        return $this->companies->newByMonth(1)[0] ?? 0;
    }

    public function tenantUsers(): int
    {
        return $this->users->query()->whereNotNull('company_id')->count();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function recentCompanies(int $limit = 5)
    {
        return $this->companies->query()->with('setting')->latest()->limit($limit)->get();
    }

    /**
     * Cached Stripe balance, or a graceful {configured:false} / {error:true}
     * payload when Stripe is unavailable.
     */
    public function stripeBalance(): array
    {
        if (!$this->stripe->isConfigured()) {
            return ['configured' => false];
        }

        try {
            $balance = Cache::remember('billing.stripe_balance', 300, function () {
                return $this->stripe->balance();
            });

            return [
                'configured' => true,
                'available' => round(collect($balance['available'] ?? [])->sum('amount') / 100, 2),
                'pending' => round(collect($balance['pending'] ?? [])->sum('amount') / 100, 2),
            ];
        } catch (\Throwable $e) {
            return ['configured' => true, 'error' => true];
        }
    }

    /**
     * Monthly recurring revenue normalized from active subscription prices.
     */
    public function mrr(): float
    {
        $mrr = 0.0;

        foreach ($this->subscriptions->activeWithPrice() as $subscription) {
            $price = $subscription->price;

            if (!$price) {
                continue;
            }

            $amount = $price->unit_amount / 100;

            if ($price->interval === 'year') {
                $amount = $amount / 12;
            } elseif ($price->interval === 'week') {
                $amount = $amount * 4.33;
            }

            $mrr += $amount;
        }

        return round($mrr, 2);
    }

    public function activeSubscriptionCount(): int
    {
        return $this->subscriptions->countByStatuses(['active', 'trialing']);
    }

    public function arpa(): float
    {
        $count = $this->activeSubscriptionCount();

        if ($count <= 0) {
            return 0.0;
        }

        return round($this->mrr() / $count, 2);
    }

    public function pastDueSubscriptions(): int
    {
        return $this->subscriptions->countByStatuses(['past_due', 'unpaid']);
    }

    /**
     * Open invoices past their due date: count + total amount_due (in major units).
     */
    public function duePayments(): array
    {
        $overdue = $this->invoices->overdueForPlatform();

        return [
            'count' => $overdue->count(),
            'total' => round($overdue->sum('amount_due') / 100, 2),
        ];
    }

    public function failedPayments(): array
    {
        return [
            'count' => $this->payments->failedCount(),
            'total' => round($this->payments->failedAmount() / 100, 2),
        ];
    }

    /**
     * Open/uncollectible invoices for the platform due-invoice table.
     *
     * @return \Illuminate\Support\Collection
     */
    public function dueInvoices(int $limit = 8)
    {
        return $this->invoices->dueForPlatform($limit);
    }

    /**
     * Revenue (paid invoices) + new companies, last $months months (chart payload).
     */
    public function revenueChart(int $months = 6): array
    {
        return [
            'chart_type' => 'bar',
            'series' => [
                ['name' => 'Revenue', 'data' => $this->invoices->paidRevenueByMonth($months)],
                ['name' => 'New companies', 'data' => $this->companies->newByMonth($months)],
            ],
            'categories' => $this->companies->monthLabels($months),
        ];
    }
}
