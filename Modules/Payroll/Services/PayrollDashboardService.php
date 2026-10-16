<?php

namespace Modules\Payroll\Services;

use Illuminate\Support\Carbon;
use Modules\Payroll\Repositories\PayrollRepository;
use Modules\Payroll\Repositories\PayslipRepository;

class PayrollDashboardService
{
    protected $payrolls;
    protected $payslips;

    public function __construct(PayrollRepository $payrolls, PayslipRepository $payslips)
    {
        $this->payrolls = $payrolls;
        $this->payslips = $payslips;
    }

    /**
     * Draft (unprocessed) payroll runs waiting for action.
     */
    public function pendingCard(): array
    {
        return [
            'value' => $this->payrolls->draftCount($this->companyId()),
            'format' => 'number',
            'secondary_label' => 'Draft runs',
            'link' => ['label' => 'Open payroll', 'url' => route('payrolls.index')],
        ];
    }

    /**
     * The current month's run, highlighting whether it is still due.
     */
    public function dueCard(): array
    {
        $run = $this->payrolls->forPeriod($this->companyId(), Carbon::now()->format('Y-m'));

        if (!$run) {
            return [
                'empty' => 'Not generated',
                'link' => ['label' => 'Open payroll', 'url' => route('payrolls.index')],
            ];
        }

        return [
            'value' => (float) $run->total_net,
            'format' => 'currency',
            'badge' => $this->statusBadge($run->status),
            'secondary' => (int) $run->total_employees,
            'secondary_format' => 'number',
            'secondary_label' => 'Employees',
            'link' => ['label' => 'View run', 'url' => route('payrolls.show', $run->uid)],
        ];
    }

    /**
     * Net payroll paid during the current month (vs the previous month).
     */
    public function paidMonthCard(): array
    {
        $companyId = $this->companyId();
        $amount = $this->payrolls->paidTotalForMonth($companyId, Carbon::now()->format('Y-m'));
        $previous = $this->payrolls->paidTotalForMonth($companyId, Carbon::now()->subMonth()->format('Y-m'));

        return [
            'value' => round($amount, 2),
            'format' => 'currency',
            'direction' => $amount >= $previous ? 'positive' : 'negative',
        ];
    }

    /**
     * Net payroll cost across the last six months.
     */
    public function costChart(): array
    {
        $companyId = $this->companyId();

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = Carbon::now()->startOfMonth()->subMonths($i)->format('Y-m');
        }

        $byMonth = $this->payrolls->netByMonth($companyId, $months[0])->pluck('total', 'month');

        $series = [];
        $categories = [];
        $total = 0;

        foreach ($months as $month) {
            $value = round((float) ($byMonth[$month] ?? 0), 2);
            $series[] = $value;
            $total += $value;
            $categories[] = Carbon::createFromFormat('Y-m', $month)->format('M');
        }

        return [
            'chart_type' => 'bar',
            'series' => [['name' => 'Net payroll cost', 'data' => $series]],
            'categories' => $categories,
            'kpi' => [
                'value' => round($total, 2),
                'format' => 'currency',
                'label' => 'Payroll cost (6 months)',
            ],
        ];
    }

    /**
     * The most recent payroll runs with their status.
     */
    public function recentTable(): array
    {
        $runs = $this->payrolls->recentRuns($this->companyId(), 5);

        $rows = $runs->map(function ($run) {
            return [
                Carbon::parse($run->period)->format('M Y'),
                (int) $run->total_employees,
                ['text' => (string) round((float) $run->total_net, 2), 'badge' => 'primary'],
                ['text' => ucfirst($run->status), 'badge' => $this->badgeColor($run->status)],
            ];
        })->values()->all();

        return [
            'columns' => ['Period', 'Employees', 'Net', 'Status'],
            'rows' => $rows,
            'empty' => 'No payroll runs yet.',
            'link' => ['label' => 'View all payroll', 'url' => route('payrolls.index')],
        ];
    }

    /**
     * The acting employee's own payslip history (own scope).
     */
    public function myPayslipsTable(): array
    {
        $user = auth()->user();
        $slips = $this->payslips->latestForEmployee($user->id, $user->company_id, 5);

        $rows = $slips->map(function ($slip) {
            $period = $slip->payroll ? Carbon::parse($slip->payroll->period)->format('M Y') : '-';
            $status = $slip->payroll ? $slip->payroll->status : null;

            return [
                $period,
                (string) round((float) $slip->net_salary, 2),
                $status ? ['text' => ucfirst($status), 'badge' => $this->badgeColor($status)] : ['text' => '-', 'badge' => 'secondary'],
            ];
        })->values()->all();

        return [
            'columns' => ['Period', 'Net', 'Status'],
            'rows' => $rows,
            'empty' => 'No payslips yet.',
            'link' => ['label' => 'View all payslips', 'url' => route('payrolls.me')],
        ];
    }

    /**
     * @param  string  $status
     * @return array<string, string>
     */
    protected function statusBadge($status)
    {
        return ['text' => ucfirst($status), 'color' => $this->badgeColor($status)];
    }

    /**
     * @param  string  $status
     * @return string
     */
    protected function badgeColor($status)
    {
        return [
            'draft' => 'secondary',
            'processed' => 'warning',
            'paid' => 'success',
        ][$status] ?? 'secondary';
    }

    /**
     * @return int|null
     */
    protected function companyId()
    {
        return auth()->user() ? auth()->user()->company_id : null;
    }
}
