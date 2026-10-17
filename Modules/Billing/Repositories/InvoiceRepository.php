<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Invoice;

class InvoiceRepository extends BaseRepository
{
    protected $dataTableWith = ['company'];
    protected $dataTableRawColumns = ['action', 'status'];

    public function __construct()
    {
        parent::__construct(new Invoice());

        $this->dataTableEditColumns = [
            'company_name' => function ($invoice) {
                return optional($invoice->company)->name;
            },
            'amount' => function ($invoice) {
                $amount = $invoice->amount_paid ?: $invoice->amount_due;
                return number_format($amount / 100, 2) . ' ' . strtoupper($invoice->currency);
            },
            'status' => function ($invoice) {
                return $this->dt->statusBadge($invoice->status, $this->statusColor($invoice->status));
            },
        ];

        $this->dataTableAddColumns = [
            'action' => function ($invoice) {
                $btns = [];
                if ($invoice->hosted_invoice_url) {
                    $btns[] = '<a href="' . $invoice->hosted_invoice_url . '" target="_blank" title="View invoice"'
                        . ' class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">'
                        . $this->dt->icon('eye') . '</a>';
                }
                if ($invoice->invoice_pdf) {
                    $btns[] = '<a href="' . $invoice->invoice_pdf . '" target="_blank" title="Download PDF"'
                        . ' class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">'
                        . $this->dt->icon('download') . '</a>';
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    /**
     * @param  string  $status
     * @return string
     */
    protected function statusColor($status)
    {
        switch ($status) {
            case 'paid':
                return 'btn-success';
            case 'open':
                return 'btn-warning';
            case 'uncollectible':
                return 'btn-danger';
            default:
                return 'btn-secondary';
        }
    }

    /**
     * Most recent invoices for a company (tenant invoice widget).
     *
     * @return \Illuminate\Support\Collection
     */
    public function latestForCompany(int $companyId, int $limit = 10)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * The next open (unpaid) invoice for a company, soonest due first.
     */
    public function openForCompany(int $companyId): ?Invoice
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('status', 'open')
            ->orderBy('due_date')
            ->first();
    }

    /**
     * Open invoices past their due date (platform collections widget).
     *
     * @return \Illuminate\Support\Collection
     */
    public function overdueForPlatform()
    {
        return $this->query()
            ->where('status', 'open')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with('company')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Open/past-due invoices for the platform due-invoice table.
     *
     * @return \Illuminate\Support\Collection
     */
    public function dueForPlatform(int $limit = 10)
    {
        return $this->query()
            ->whereIn('status', ['open', 'uncollectible'])
            ->with('company')
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }

    public function countStatus(string $status): int
    {
        return $this->query()->where('status', $status)->count();
    }

    public function countForCompany(int $companyId): int
    {
        return $this->query()->where('company_id', $companyId)->count();
    }

    public function paidTotalForCompany(int $companyId): float
    {
        return (float) $this->query()
            ->where('company_id', $companyId)
            ->where('status', 'paid')
            ->sum('amount_paid') / 100;
    }

    /**
     * Paid invoice totals per month for the last $months months for one company.
     *
     * @return array<int, float>
     */
    public function paidRevenueByMonthForCompany(int $companyId, int $months = 6): array
    {
        $totals = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->startOfMonth()->subMonths($i);
            $totals[] = (float) $this->query()
                ->where('company_id', $companyId)
                ->where('status', 'paid')
                ->where('created_at', '>=', $date->toDateString())
                ->where('created_at', '<', $date->copy()->addMonth()->toDateString())
                ->sum('amount_paid') / 100;
        }

        return $totals;
    }

    /**
     * Paid invoice totals per month for the last $months months (platform chart).
     *
     * @return array<int, float>
     */
    public function paidRevenueByMonth(int $months = 6): array
    {
        $totals = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->startOfMonth()->subMonths($i);
            $totals[] = (float) $this->query()
                ->where('status', 'paid')
                ->where('created_at', '>=', $date->toDateString())
                ->where('created_at', '<', $date->copy()->addMonth()->toDateString())
                ->sum('amount_paid') / 100;
        }

        return $totals;
    }
}
