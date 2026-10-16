<?php

namespace Modules\Payroll\Repositories;

use App\Repositories\BaseRepository;
use Modules\Payroll\Models\Payslip;

class PayslipRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Payslip());
    }

    /**
     * Aggregate the payslip rows belonging to a payroll run. Kept here so the
     * service never aggregates through a relation (architecture constraint).
     *
     * @param  int  $payrollId
     * @return object
     */
    public function aggregateForPayroll($payrollId)
    {
        return $this->query()
            ->where('payroll_id', $payrollId)
            ->selectRaw('
                COUNT(*) as total_employees,
                COALESCE(SUM(base_salary), 0) as total_base,
                COALESCE(SUM(total_allowances), 0) as total_allowances,
                COALESCE(SUM(total_deductions), 0) as total_deductions,
                COALESCE(SUM(net_salary), 0) as total_net
            ')
            ->first();
    }

    /**
     * Payslips in a given run — used by the posting service so no Model query
     * happens outside the repository layer.
     *
     * @param  int  $payrollId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listForPayroll($payrollId)
    {
        return $this->query()->where('payroll_id', $payrollId)->get();
    }

    /**
     * Payslips with their employee and line items eager-loaded for the run
     * detail screen.
     *
     * @param  int  $payrollId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listWithItemsForPayroll($payrollId)
    {
        return $this->query()
            ->with(['employee', 'allowanceItems.allowance', 'deductionItems.deduction'])
            ->where('payroll_id', $payrollId)
            ->get();
    }

    /**
     * A single payslip with its employee, line items and run loaded (print view).
     *
     * @param  int  $id
     * @return \Modules\Payroll\Models\Payslip|null
     */
    public function findWithItems($id)
    {
        return $this->query()
            ->with([
                'employee',
                'payroll',
                'allowanceItems.allowance',
                'deductionItems.deduction',
            ])
            ->where('id', $id)
            ->first();
    }
}