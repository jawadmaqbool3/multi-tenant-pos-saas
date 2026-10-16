<?php

namespace Modules\Billing\Services;

use App\Models\Company;
use App\Repositories\CompanyRepository;
use Carbon\Carbon;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Billing\Repositories\SubscriptionRepository;

class BillingDashboardService
{
    protected $companies;
    protected $subscriptions;
    protected $invoices;
    protected $planLimits;

    public function __construct(
        CompanyRepository $companies,
        SubscriptionRepository $subscriptions,
        InvoiceRepository $invoices,
        PlanLimitService $planLimits
    ) {
        $this->companies = $companies;
        $this->subscriptions = $subscriptions;
        $this->invoices = $invoices;
        $this->planLimits = $planLimits;
    }

    /**
     * The company behind the currently authenticated tenant user.
     */
    public function company(): ?Company
    {
        $user = auth()->user();

        if (!$user || !$user->company_id) {
            return null;
        }

        return $this->companies->query()->where('id', $user->company_id)->first();
    }

    public function activeSubscription(): ?Subscription
    {
        $company = $this->company();

        if (!$company) {
            return null;
        }

        return $this->subscriptions->activeForCompany($company->id);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * `billing.current_plan` payload.
     */
    public function currentPlan(): ?array
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            return null;
        }

        $subscription->loadMissing(['plan', 'price']);
        $price = $subscription->price;
        $plan = $subscription->plan;

        $interval = $price ? ucfirst($price->interval) : '-';

        return [
            'value' => $price ? round($price->unit_amount / 100, 2) : 0,
            'format' => 'currency',
            'currency' => $price ? strtoupper($price->currency) : strtoupper(config('app.currency')),
            'badge' => [
                'text' => strtoupper($subscription->status),
                'color' => $this->statusColor($subscription->status),
            ],
            'rows' => [
                ['label' => 'Plan', 'value' => $plan ? $plan->name : '-', 'format' => 'text'],
                ['label' => 'Billing', 'value' => $interval, 'format' => 'text'],
            ],
            'link' => ['label' => 'Manage plan', 'url' => route('billing.pricing')],
        ];
    }

    /**
     * `billing.renewal` payload: days left + period end.
     */
    public function renewal(): ?array
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            return null;
        }

        $end = $subscription->current_period_end ? Carbon::parse($subscription->current_period_end) : null;
        $days = $end ? (int) max(0, $end->diffInDays(now(), false)) : null;

        return [
            'value' => $days === null ? 'N/A' : $days,
            'format' => 'number',
            'secondary' => null,
            'secondary_label' => '',
            'badge' => [
                'text' => strtoupper($subscription->status),
                'color' => $this->statusColor($subscription->status),
            ],
            'rows' => [
                [
                    'label' => 'Period ends',
                    'value' => $end ? $end->format('M j, Y') : 'N/A',
                    'format' => 'text',
                ],
                [
                    'label' => 'Auto renew',
                    'value' => $subscription->cancel_at_period_end ? 'No' : 'Yes',
                    'format' => 'text',
                ],
            ],
            'link' => ['label' => 'Manage plan', 'url' => route('billing.pricing')],
        ];
    }

    /**
     * `billing.usage` payload (table): resource used vs plan limit.
     */
    public function usage(): ?array
    {
        $company = $this->company();

        if (!$company) {
            return null;
        }

        $plan = $this->planLimits->planForCompany($company);

        if (!$plan) {
            return null;
        }

        $rows = [];

        foreach (['employees', 'roles'] as $key) {
            $limit = $this->planLimits->limitFor($key, $plan);
            $used = $this->planLimits->usage($key, $company);

            $rows[] = [
                ucfirst($key),
                (string) $used,
                $limit === null ? 'Unlimited' : (string) $limit,
            ];
        }

        return [
            'columns' => ['Resource', 'Used', 'Limit'],
            'rows' => $rows,
            'empty' => 'No usage data',
            'link' => ['label' => 'Change plan', 'url' => route('billing.pricing')],
        ];
    }

    /**
     * `billing.next_invoice` payload.
     */
    public function nextInvoice(): ?array
    {
        $company = $this->company();

        if (!$company) {
            return null;
        }

        $invoice = $this->invoices->openForCompany($company->id);

        if (!$invoice) {
            return null;
        }

        $rows = [];

        if ($invoice->due_date) {
            $rows[] = [
                'label' => 'Due',
                'value' => Carbon::parse($invoice->due_date)->format('M j, Y'),
                'format' => 'text',
            ];
        }

        $rows[] = [
            'label' => 'Amount due',
            'value' => number_format($invoice->amount_due / 100, 2),
            'format' => 'text',
        ];

        return [
            'value' => round($invoice->amount_due / 100, 2),
            'format' => 'currency',
            'currency' => strtoupper($invoice->currency),
            'badge' => [
                'text' => strtoupper($invoice->status),
                'color' => $this->statusColor($invoice->status),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Most recent invoices for a company (used for visibility + payload).
     *
     * @return \Illuminate\Support\Collection
     */
    public function invoicesFor(int $companyId, int $limit = 5)
    {
        return $this->invoices->latestForCompany($companyId, $limit);
    }

    /**
     * `billing.invoices` payload (table).
     */
    public function invoices(): ?array
    {
        $company = $this->company();

        if (!$company) {
            return null;
        }

        $invoices = $this->invoicesFor($company->id, 5);

        $rows = $invoices->map(function ($invoice) {
            $links = [];
            if ($invoice->hosted_invoice_url) {
                $links[] = '<a href="' . $invoice->hosted_invoice_url . '" target="_blank">View</a>';
            }
            if ($invoice->invoice_pdf) {
                $links[] = '<a href="' . $invoice->invoice_pdf . '" target="_blank">PDF</a>';
            }

            return [
                [
                    'text' => '<a href="' . ($invoice->hosted_invoice_url ?: '#') . '" target="_blank">' .
                        strtoupper(substr($invoice->stripe_invoice_id, 0, 8)) . '</a>',
                ],
                number_format($invoice->amount_due / 100, 2) . ' ' . strtoupper($invoice->currency),
                $invoice->due_date ? Carbon::parse($invoice->due_date)->format('M j, Y') : '-',
                ['text' => strtoupper($invoice->status), 'badge' => $this->statusColor($invoice->status)],
                implode(' &middot; ', $links),
            ];
        })->values()->all();

        return [
            'columns' => ['Invoice', 'Amount', 'Due', 'Status', ''],
            'rows' => $rows,
            'empty' => 'No invoices yet',
        ];
    }

    protected function statusColor(string $status): string
    {
        switch ($status) {
            case 'active':
            case 'trialing':
            case 'paid':
                return 'success';
            case 'past_due':
            case 'open':
                return 'warning';
            case 'unpaid':
            case 'uncollectible':
            case 'canceled':
                return 'danger';
            case 'incomplete':
            case 'incomplete_expired':
            case 'processing':
                return 'info';
            default:
                return 'secondary';
        }
    }
}
