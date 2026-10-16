<?php

namespace Modules\Payroll\Repositories;

use App\Repositories\BaseRepository;
use Modules\Payroll\Models\PayslipDeduction;

class PayslipDeductionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PayslipDeduction());
    }

    /**
     * Whether a given deduction is referenced by any payslip belonging to a
     * payroll that is no longer a draft (the delete guard used by DeductionService).
     *
     * @param  int  $deductionId
     * @return bool
     */
    public function usedByNonDraftPayroll($deductionId)
    {
        return $this->query()
            ->where('deduction_id', $deductionId)
            ->whereHas('payslip.payroll', function ($q) {
                $q->where('status', '!=', 'draft');
            })
            ->exists();
    }

    /**
     * The sum of deduction line items for a payslip.
     *
     * @param  int  $payslipId
     * @return float
     */
    public function sumForPayslip($payslipId)
    {
        return (float) $this->query()->where('payslip_id', $payslipId)->sum('amount');
    }

    /**
     * Drop every deduction line item for a payslip (used before re-creating them).
     *
     * @param  int  $payslipId
     * @return int
     */
    public function deleteForPayslip($payslipId)
    {
        return $this->query()->where('payslip_id', $payslipId)->delete();
    }
}