<?php

namespace Modules\Payroll\Services;

use App\Exceptions\CustomException;
use Illuminate\Validation\ValidationException;
use Modules\Payroll\Repositories\DeductionRepository;
use Modules\Payroll\Repositories\PayslipDeductionRepository;

class DeductionService
{
    protected $deductions;
    protected $payslipDeductions;

    public function __construct(
        DeductionRepository $deductions,
        PayslipDeductionRepository $payslipDeductions
    ) {
        $this->deductions = $deductions;
        $this->payslipDeductions = $payslipDeductions;
    }

    public function getList()
    {
        return $this->deductions->dataTable($this->deductions->query()->filter());
    }

    public function store($request)
    {
        $companyId = auth()->user()->company_id;

        if ($this->deductions->nameExistsForCompany($companyId, $request->name)) {
            throw ValidationException::withMessages([
                'name' => 'This deduction name already exists for your company.',
            ]);
        }

        return $this->deductions->create([
            'name' => $request->name,
            'status' => $request->status,
            'company_id' => $companyId,
        ]);
    }

    public function update($request, $deduction)
    {
        $companyId = auth()->user()->company_id;

        if ($this->deductions->nameExistsForCompany($companyId, $request->name, $deduction->id)) {
            throw ValidationException::withMessages([
                'name' => 'This deduction name already exists for your company.',
            ]);
        }

        return $this->deductions->update($deduction->uid, [
            'name' => $request->name,
            'status' => $request->status,
        ]);
    }

    public function delete($deduction)
    {
        if ($this->payslipDeductions->usedByNonDraftPayroll($deduction->id)) {
            throw new CustomException(
                'This deduction is used by a processed or paid payroll and cannot be deleted.',
                'error'
            );
        }

        return $this->deductions->delete($deduction->uid);
    }
}