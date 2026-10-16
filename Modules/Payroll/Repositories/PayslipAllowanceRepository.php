<?php

namespace Modules\Payroll\Repositories;

use App\Repositories\BaseRepository;
use Modules\Payroll\Models\PayslipAllowance;

class PayslipAllowanceRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PayslipAllowance());
    }

    /**
     * Whether a given allowance is referenced by any payslip belonging to a
     * payroll that is no longer a draft (the delete guard used by AllowanceService).
     *
     * @param  int  $allowanceId
     * @return bool
     */
    public function usedByNonDraftPayroll($allowanceId)
    {
        return $this->query()
            ->where('allowance_id', $allowanceId)
            ->whereHas('payslip.payroll', function ($q) {
                $q->where('status', '!=', 'draft');
            })
            ->exists();
    }

    /**
     * The sum of allowance line items for a payslip.
     *
     * @param  int  $payslipId
     * @return float
     */
    public function sumForPayslip($payslipId)
    {
        return (float) $this->query()->where('payslip_id', $payslipId)->sum('amount');
    }

    /**
     * Drop every allowance line item for a payslip (used before re-creating them).
     *
     * @param  int  $payslipId
     * @return int
     */
    public function deleteForPayslip($payslipId)
    {
        return $this->query()->where('payslip_id', $payslipId)->delete();
    }
}