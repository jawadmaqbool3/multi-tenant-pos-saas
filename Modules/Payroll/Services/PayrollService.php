<?php

namespace Modules\Payroll\Services;

use App\Exceptions\CustomException;
use App\Repositories\CompanyRepository;
use Illuminate\Validation\ValidationException;
use Modules\Employee\Repositories\EmployeePersonalInformationRepository;
use Modules\Employee\Repositories\UserRepository;
use Modules\Payroll\Repositories\AllowanceRepository;
use Modules\Payroll\Repositories\DeductionRepository;
use Modules\Payroll\Repositories\PayrollRepository;
use Modules\Payroll\Repositories\PayslipAllowanceRepository;
use Modules\Payroll\Repositories\PayslipDeductionRepository;
use Modules\Payroll\Repositories\PayslipRepository;

class PayrollService
{
    protected $payrolls;
    protected $payslips;
    protected $payslipAllowances;
    protected $payslipDeductions;
    protected $users;
    protected $employeeInfo;
    protected $companies;
    protected $allowances;
    protected $deductions;
    protected $posting;

    public function __construct(
        PayrollRepository $payrolls,
        PayslipRepository $payslips,
        PayslipAllowanceRepository $payslipAllowances,
        PayslipDeductionRepository $payslipDeductions,
        UserRepository $users,
        EmployeePersonalInformationRepository $employeeInfo,
        CompanyRepository $companies,
        AllowanceRepository $allowances,
        DeductionRepository $deductions,
        PayrollPostingService $posting
    ) {
        $this->payrolls = $payrolls;
        $this->payslips = $payslips;
        $this->payslipAllowances = $payslipAllowances;
        $this->payslipDeductions = $payslipDeductions;
        $this->users = $users;
        $this->employeeInfo = $employeeInfo;
        $this->companies = $companies;
        $this->allowances = $allowances;
        $this->deductions = $deductions;
        $this->posting = $posting;
    }

    public function getList()
    {
        return $this->payrolls->dataTable($this->payrolls->query()->filter());
    }

    /**
     * Payslips (with employee and line items) for the run detail screen.
     *
     * @param  \Modules\Payroll\Models\Payroll  $payroll
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function payslipsForPayroll($payroll)
    {
        return $this->payslips->listWithItemsForPayroll($payroll->id);
    }

    /**
     * A single payslip with its employee, line items and run loaded (print view).
     *
     * @param  \Modules\Payroll\Models\Payslip  $payslip
     * @return \Modules\Payroll\Models\Payslip|null
     */
    public function payslipForPrint($payslip)
    {
        return $this->payslips->findWithItems($payslip->id);
    }

    /**
     * The acting user's own payslips (for the personal payslip history screen).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function payslipsForCurrentEmployee()
    {
        return $this->payslips->listForEmployee(auth()->user()->id, auth()->user()->company_id);
    }

    /**
     * Active allowance catalog entries for the acting company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allowanceOptions()
    {
        return $this->allowances->activeForCompany($this->company()->id);
    }

    /**
     * Active deduction catalog entries for the acting company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function deductionOptions()
    {
        return $this->deductions->activeForCompany($this->company()->id);
    }

    /**
     * Payroll-eligible employees of the acting company, as uid/name pairs for
     * the run generator's employee picker.
     *
     * @return \Illuminate\Support\Collection
     */
    public function employeeOptions()
    {
        return $this->users->payrollEnabledForCompany($this->company()->id)
            ->map(function ($employee) {
                return ['uid' => $employee->uid, 'name' => $employee->name];
            })
            ->values();
    }

    /**
     * The acting user's company. Payroll is tenant-only, so a platform account
     * without a company cannot use it.
     *
     * @return \App\Models\Company
     */
    protected function company()
    {
        $companyId = auth()->user() ? auth()->user()->company_id : null;

        if (!$companyId) {
            throw new CustomException('Payroll is available to company accounts only.', 'error');
        }

        $company = $this->companies->query()->where('id', $companyId)->first();

        if (!$company) {
            throw new CustomException('Payroll is available to company accounts only.', 'error');
        }

        return $company;
    }

    /**
     * Generate a draft payroll run for the given month (YYYY-MM). When a list
     * of employee uids is supplied, only those payroll-enabled staff are
     * included; otherwise every payroll-enabled staff member is.
     *
     * @param  string  $period
     * @param  string|null  $notes
     * @param  array|null  $employeeUids
     * @return array
     */
    public function generate($period, $notes = null, array $employeeUids = null)
    {
        $company = $this->company();
        $periodDate = \Illuminate\Support\Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString();

        $exists = $this->payrolls->query()
            ->where('company_id', $company->id)
            ->where('period', $periodDate)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'period' => 'A payroll run already exists for that month.',
            ]);
        }

        $payroll = $this->payrolls->create([
            'company_id' => $company->id,
            'period' => $periodDate,
            'status' => 'draft',
            'notes' => $notes,
            'created_by' => auth()->user()->id,
        ]);
        $payroll->refresh();

        $skipped = [];
        $employees = !empty($employeeUids)
            ? $this->users->payrollEnabledByUids($company->id, $employeeUids)
            : $this->users->payrollEnabledForCompany($company->id);

        foreach ($employees as $employee) {
            $info = $this->employeeInfo->query()->where('employee_id', $employee->id)->first();
            $salary = $info ? round((float) $info->salary, 2) : 0;

            if (!$info || $salary <= 0) {
                $skipped[] = $employee->name;
                continue;
            }

            $this->payslips->create([
                'payroll_id' => $payroll->id,
                'employee_id' => $employee->id,
                'company_id' => $company->id,
                'base_salary' => $salary,
                'total_allowances' => 0,
                'total_deductions' => 0,
                'net_salary' => $salary,
            ]);
        }

        $this->recomputeTotals($payroll);

        return [
            'payroll' => $payroll->refresh(),
            'skipped' => $skipped,
        ];
    }

    /**
     * Ensure a single employee has a payslip inside the run for a given month.
     * The run is created first (for every payroll-enabled staff member) when it
     * does not exist yet, so this always lands the employee in the one run the
     * month allows. An existing processed/paid run is never amended.
     *
     * @param  string  $period
     * @param  int  $employeeId
     * @return array
     */
    public function ensureEmployeeInRun($period, $employeeId)
    {
        $company = $this->company();
        $periodDate = \Illuminate\Support\Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString();

        $employee = $this->users->findPayrollEnabled($company->id, $employeeId);

        if (!$employee) {
            throw new CustomException('That employee is not eligible for payroll.', 'error');
        }

        $payroll = $this->payrolls->forPeriod($company->id, $period);
        $created = false;

        if (!$payroll) {
            $payroll = $this->payrolls->create([
                'company_id' => $company->id,
                'period' => $periodDate,
                'status' => 'draft',
                'created_by' => auth()->user()->id,
            ]);
            $payroll->refresh();
            $created = true;
        } elseif ($payroll->status !== 'draft') {
            throw new CustomException('Only draft payroll runs can be amended.', 'error');
        }

        $existing = $this->payslips->forEmployeeInRun($employee->id, $payroll->id);

        if ($existing) {
            $this->recomputeTotals($payroll);

            return [
                'payroll' => $payroll->refresh(),
                'payslip' => $existing,
                'created' => $created,
                'skipped' => [],
            ];
        }

        $info = $this->employeeInfo->forEmployee($employee->id);
        $salary = $info ? round((float) $info->salary, 2) : 0;

        if (!$info || $salary <= 0) {
            $this->recomputeTotals($payroll);

            return [
                'payroll' => $payroll->refresh(),
                'payslip' => null,
                'created' => $created,
                'skipped' => [$employee->name],
            ];
        }

        $payslip = $this->payslips->create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'company_id' => $company->id,
            'base_salary' => $salary,
            'total_allowances' => 0,
            'total_deductions' => 0,
            'net_salary' => $salary,
        ]);

        $this->recomputeTotals($payroll);

        return [
            'payroll' => $payroll->refresh(),
            'payslip' => $payslip,
            'created' => $created,
            'skipped' => [],
        ];
    }

    /**
     * Replace a draft payslip's allowance/deduction line items and recompute
     * the payslip and the parent run totals.
     *
     * @param  \Modules\Payroll\Models\Payslip  $payslip
     * @param  array  $allowanceItems
     * @param  array  $deductionItems
     * @param  string|null  $notes
     * @return \Modules\Payroll\Models\Payslip
     */
    public function syncPayslipItems($payslip, array $allowanceItems, array $deductionItems, $notes = null)
    {
        $this->guardDraft($payslip);
        $companyId = $this->company()->id;

        $this->payslipAllowances->deleteForPayslip($payslip->id);
        $seenAllowances = [];
        foreach ($allowanceItems as $item) {
            $uid = $item['allowance'] ?? null;
            if (!$uid || in_array($uid, $seenAllowances, true)) {
                continue;
            }
            $allowance = $this->allowances->query()
                ->where('company_id', $companyId)
                ->where('uid', $uid)
                ->first();
            if (!$allowance) {
                continue;
            }
            $seenAllowances[] = $uid;
            $this->payslipAllowances->create([
                'payslip_id' => $payslip->id,
                'allowance_id' => $allowance->id,
                'amount' => round((float) ($item['amount'] ?? 0), 2),
            ]);
        }

        $this->payslipDeductions->deleteForPayslip($payslip->id);
        $seenDeductions = [];
        foreach ($deductionItems as $item) {
            $uid = $item['deduction'] ?? null;
            if (!$uid || in_array($uid, $seenDeductions, true)) {
                continue;
            }
            $deduction = $this->deductions->query()
                ->where('company_id', $companyId)
                ->where('uid', $uid)
                ->first();
            if (!$deduction) {
                continue;
            }
            $seenDeductions[] = $uid;
            $this->payslipDeductions->create([
                'payslip_id' => $payslip->id,
                'deduction_id' => $deduction->id,
                'amount' => round((float) ($item['amount'] ?? 0), 2),
            ]);
        }

        $data = $this->recomputePayslip($payslip);
        if ($notes !== null) {
            $data['notes'] = $notes;
        }
        $this->payslips->update($payslip->uid, $data);

        $this->recomputeTotals($payslip->payroll);

        return $payslip->refresh();
    }

    /**
     * Update only the notes of a draft payslip.
     *
     * @param  \Modules\Payroll\Models\Payslip  $payslip
     * @param  string|null  $notes
     * @return void
     */
    public function updatePayslipNotes($payslip, $notes)
    {
        $this->guardDraft($payslip);
        $this->payslips->update($payslip->uid, ['notes' => $notes]);
    }

    /**
     * Advance a run through its lifecycle: draft -> processed -> paid. Marking a
     * run paid posts the ledger voucher within the same transaction.
     *
     * @param  \Modules\Payroll\Models\Payroll  $payroll
     * @param  string  $status
     * @return \Modules\Payroll\Models\Payroll
     */
    public function updateStatus($payroll, $status)
    {
        $allowed = [
            'draft' => 'processed',
            'processed' => 'paid',
        ];

        if (($allowed[$payroll->status] ?? null) !== $status) {
            throw new CustomException('Invalid payroll status transition.', 'error');
        }

        $this->recomputeTotals($payroll);

        $data = ['status' => $status];

        if ($status === 'processed') {
            $data['processed_at'] = now();
        }

        if ($status === 'paid') {
            $voucher = $this->posting->postVoucher($payroll);
            $data['paid_at'] = now();
            if ($voucher) {
                $data['voucher_id'] = $voucher->id;
            }
        }

        $this->payrolls->update($payroll->uid, $data);

        return $payroll->refresh();
    }

    /**
     * Delete a draft run (and its payslips/line items via DB cascade).
     *
     * @param  \Modules\Payroll\Models\Payroll  $payroll
     * @return void
     */
    public function destroy($payroll)
    {
        if ($payroll->status !== 'draft') {
            throw new CustomException('Only draft payroll runs can be deleted.', 'error');
        }

        $this->payrolls->delete($payroll->uid);
    }

    /**
     * Recompute the cached totals of a single payslip from its line items.
     *
     * @param  \Modules\Payroll\Models\Payslip  $payslip
     * @return array
     */
    public function recomputePayslip($payslip)
    {
        $totalAllowances = $this->payslipAllowances->sumForPayslip($payslip->id);
        $totalDeductions = $this->payslipDeductions->sumForPayslip($payslip->id);
        $base = round((float) $payslip->base_salary, 2);

        return [
            'total_allowances' => round($totalAllowances, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_salary' => round($base + $totalAllowances - $totalDeductions, 2),
        ];
    }

    /**
     * Recompute a run's cached totals from its payslips (via the repository
     * aggregate — never a relation sum).
     *
     * @param  \Modules\Payroll\Models\Payroll  $payroll
     * @return void
     */
    public function recomputeTotals($payroll)
    {
        $totals = $this->payslips->aggregateForPayroll($payroll->id);

        $this->payrolls->update($payroll->uid, [
            'total_employees' => (int) $totals->total_employees,
            'total_base' => round((float) $totals->total_base, 2),
            'total_allowances' => round((float) $totals->total_allowances, 2),
            'total_deductions' => round((float) $totals->total_deductions, 2),
            'total_net' => round((float) $totals->total_net, 2),
        ]);
    }

    /**
     * Guard that a payslip belongs to a draft run.
     *
     * @param  \Modules\Payroll\Models\Payslip  $payslip
     * @return void
     */
    protected function guardDraft($payslip)
    {
        $payroll = $payslip->payroll;
        if (!$payroll || $payroll->status !== 'draft') {
            throw new CustomException('Only draft payroll runs can be edited.', 'error');
        }
    }
}