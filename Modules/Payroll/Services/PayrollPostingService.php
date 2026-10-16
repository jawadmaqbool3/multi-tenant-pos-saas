<?php

namespace Modules\Payroll\Services;

use Modules\ChartOfAccounts\Services\AccountSettingService;
use Modules\ChartOfAccounts\Services\TransactionService;
use Modules\ChartOfAccounts\Services\VoucherService;
use Modules\Payroll\Repositories\PayrollRepository;
use Modules\Payroll\Repositories\PayslipRepository;

class PayrollPostingService
{
    protected $payslips;
    protected $payrolls;
    protected $accountSettings;
    protected $vouchers;
    protected $transactions;

    public function __construct(
        PayslipRepository $payslips,
        PayrollRepository $payrolls,
        AccountSettingService $accountSettings,
        VoucherService $vouchers,
        TransactionService $transactions
    ) {
        $this->payslips = $payslips;
        $this->payrolls = $payrolls;
        $this->accountSettings = $accountSettings;
        $this->vouchers = $vouchers;
        $this->transactions = $transactions;
    }

    /**
     * Post the single salary voucher for a run. Idempotent — returns the already
     * stored voucher when the run has been posted. Must be called inside a DB
     * transaction; a missing COA setting aborts the transition before any row is
     * written.
     *
     * @param  \Modules\Payroll\Models\Payroll  $payroll
     * @return \Modules\ChartOfAccounts\Models\Voucher
     */
    public function postVoucher($payroll)
    {
        if ($payroll->voucher_id) {
            return null;
        }

        // Resolve accounts first: this throws when a setting is missing so the
        // caller's transaction rolls back without a partial voucher.
        $salaryExpense = $this->accountSettings->getAccount('SEA');
        $deductionsPayable = $this->accountSettings->getAccount('SDP');
        $officeAccount = $this->accountSettings->getAccount('OSA');

        $payslips = $this->payslips->listForPayroll($payroll->id);

        $gross = 0;
        foreach ($payslips as $payslip) {
            $gross += (float) $payslip->base_salary + (float) $payslip->total_allowances;
        }

        $voucherType = $this->vouchers->getVoucherTypeByShort('SLV');
        $voucherNo = $this->vouchers->nextVoucherNo('SLV');
        $date = $payroll->paid_at ? $payroll->paid_at->format('Y-m-d') : now()->format('Y-m-d');

        $voucher = $this->vouchers->createVoucher([
            'voucher_no' => $voucherNo,
            'date' => $date,
            'voucher_type_id' => $voucherType->id,
            'amount' => round($gross, 2),
            'type' => 'multiple',
            'description' => 'Monthly payroll for ' . $payroll->period->format('F Y'),
            'created_by' => auth()->user()->id,
        ]);
        $voucher->refresh();

        $line = 0;
        foreach ($payslips as $payslip) {
            $base = (float) $payslip->base_salary;
            $allowances = (float) $payslip->total_allowances;
            $deductions = (float) $payslip->total_deductions;
            $net = (float) $payslip->net_salary;

            $debit = round($base + $allowances, 2);
            if ($debit > 0) {
                $line++;
                $this->transactions->create([
                    'transaction_no' => $voucherNo . '-' . $line,
                    'voucher_id' => $voucher->id,
                    'account_id' => $salaryExpense->id,
                    'amount' => $debit,
                    'type' => 'debit',
                    'source' => 'payroll',
                    'description' => 'Salary expense',
                ]);
                $this->vouchers->applyBalance($salaryExpense->id, 'debit', $debit);
            }

            if ($deductions > 0) {
                $line++;
                $this->transactions->create([
                    'transaction_no' => $voucherNo . '-' . $line,
                    'voucher_id' => $voucher->id,
                    'account_id' => $deductionsPayable->id,
                    'amount' => round($deductions, 2),
                    'type' => 'credit',
                    'source' => 'payroll',
                    'description' => 'Salary deductions payable',
                ]);
                $this->vouchers->applyBalance($deductionsPayable->id, 'credit', round($deductions, 2));
            }

            if ($net > 0) {
                $line++;
                $this->transactions->create([
                    'transaction_no' => $voucherNo . '-' . $line,
                    'voucher_id' => $voucher->id,
                    'account_id' => $officeAccount->id,
                    'amount' => round($net, 2),
                    'type' => 'credit',
                    'source' => 'payroll',
                    'description' => 'Net salary paid',
                ]);
                $this->vouchers->applyBalance($officeAccount->id, 'credit', round($net, 2));
            }
        }

        $this->payrolls->update($payroll->uid, ['voucher_id' => $voucher->id]);

        return $voucher->refresh();
    }
}