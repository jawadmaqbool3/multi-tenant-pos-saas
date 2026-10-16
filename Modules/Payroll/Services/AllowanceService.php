<?php

namespace Modules\Payroll\Services;

use App\Exceptions\CustomException;
use Illuminate\Validation\ValidationException;
use Modules\Payroll\Repositories\AllowanceRepository;
use Modules\Payroll\Repositories\PayslipAllowanceRepository;

class AllowanceService
{
    protected $allowances;
    protected $payslipAllowances;

    public function __construct(
        AllowanceRepository $allowances,
        PayslipAllowanceRepository $payslipAllowances
    ) {
        $this->allowances = $allowances;
        $this->payslipAllowances = $payslipAllowances;
    }

    public function getList()
    {
        return $this->allowances->dataTable($this->allowances->query()->filter());
    }

    public function store($request)
    {
        $companyId = auth()->user()->company_id;

        if ($this->allowances->nameExistsForCompany($companyId, $request->name)) {
            throw ValidationException::withMessages([
                'name' => 'This allowance name already exists for your company.',
            ]);
        }

        return $this->allowances->create([
            'name' => $request->name,
            'status' => $request->status,
            'company_id' => $companyId,
        ]);
    }

    public function update($request, $allowance)
    {
        $companyId = auth()->user()->company_id;

        if ($this->allowances->nameExistsForCompany($companyId, $request->name, $allowance->id)) {
            throw ValidationException::withMessages([
                'name' => 'This allowance name already exists for your company.',
            ]);
        }

        return $this->allowances->update($allowance->uid, [
            'name' => $request->name,
            'status' => $request->status,
        ]);
    }

    public function delete($allowance)
    {
        if ($this->payslipAllowances->usedByNonDraftPayroll($allowance->id)) {
            throw new CustomException(
                'This allowance is used by a processed or paid payroll and cannot be deleted.',
                'error'
            );
        }

        return $this->allowances->delete($allowance->uid);
    }
}