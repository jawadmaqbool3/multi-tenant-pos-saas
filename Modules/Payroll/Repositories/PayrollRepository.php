<?php

namespace Modules\Payroll\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\Payroll;

class PayrollRepository extends BaseRepository
{
    protected $dataTableWith = [];
    protected $dataTableRawColumns = ['action', 'period', 'status'];

    public function __construct()
    {
        parent::__construct(new Payroll());
        $this->dataTableEditColumns = [
            'period' => function ($payroll) {
                return \Illuminate\Support\Carbon::parse($payroll->period)->format('F Y');
            },
            'status' => function ($payroll) {
                $badge = [
                    'draft' => 'btn-secondary',
                    'processed' => 'btn-warning',
                    'paid' => 'btn-success',
                ][$payroll->status] ?? 'btn-secondary';

                return $this->dt->statusBadge(ucwords($payroll->status), $badge);
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($payroll) {
                $btns = [];
                if (auth()->user()->hasPermission('edit payroll')) {
                    $btns[] = $this->dt->editButton(route('payrolls.show', $payroll->uid), 'edit payroll', null, 'Show');
                }
                if ($payroll->status === 'draft' && auth()->user()->hasPermission('delete payroll')) {
                    $btns[] = $this->dt->deleteButton($payroll->uid, \Illuminate\Support\Carbon::parse($payroll->period)->format('F Y'), 'delete payroll');
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    /**
     * Number of draft payroll runs in a company.
     *
     * @param  int  $companyId
     * @return int
     */
    public function draftCount($companyId)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('status', 'draft')
            ->count();
    }

    /**
     * The payroll run for a given month (YYYY-MM), whatever its status.
     *
     * @param  int  $companyId
     * @param  string  $yearMonth
     * @return \Modules\Payroll\Models\Payroll|null
     */
    public function forPeriod($companyId, $yearMonth)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('period', Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth()->toDateString())
            ->first();
    }

    /**
     * Total net payroll paid in a given month (YYYY-MM).
     *
     * @param  int  $companyId
     * @param  string  $yearMonth
     * @return float
     */
    public function paidTotalForMonth($companyId, $yearMonth)
    {
        $start = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return (float) $this->query()
            ->where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('total_net');
    }

    /**
     * Net payroll cost grouped by month (YYYY-MM => total), from a starting
     * month forward. Draft runs are excluded (cost is not realised yet).
     *
     * @param  int  $companyId
     * @param  string  $fromYearMonth
     * @return \Illuminate\Support\Collection
     */
    public function netByMonth($companyId, $fromYearMonth)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('status', '!=', 'draft')
            ->where('period', '>=', Carbon::createFromFormat('Y-m', $fromYearMonth)->startOfMonth()->toDateString())
            ->selectRaw("DATE_FORMAT(period, '%Y-%m') as month, SUM(total_net) as total")
            ->groupBy(DB::raw("DATE_FORMAT(period, '%Y-%m')"))
            ->get();
    }

    /**
     * Most recent payroll runs in a company (newest period first).
     *
     * @param  int  $companyId
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function recentRuns($companyId, $limit = 5)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->orderByDesc('period')
            ->limit($limit)
            ->get(['period', 'status', 'total_employees', 'total_net']);
    }
}