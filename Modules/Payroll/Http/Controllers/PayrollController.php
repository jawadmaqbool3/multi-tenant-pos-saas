<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Http\Requests\StorePayrollRequest;
use Modules\Payroll\Http\Requests\UpdatePayrollStatusRequest;
use Modules\Payroll\Http\Requests\UpdatePayslipRequest;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Services\PayrollService;

class PayrollController extends Controller
{
    protected $service;

    public function __construct(PayrollService $service = null)
    {
        $this->service = $service ?: app(PayrollService::class);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getList();
        }
        return view('payroll::payrolls.index');
    }

    public function create()
    {
        return view('payroll::payrolls.create');
    }

    public function store(StorePayrollRequest $request)
    {
        DB::beginTransaction();
        try {
            $result = $this->service->generate($request->period, $request->notes);
            DB::commit();

            $skipped = $result['skipped'];
            $message = 'Payroll Run Generated';
            if (!empty($skipped)) {
                $message .= '. Skipped (no salary set): ' . implode(', ', $skipped);
            }

            return response([
                'success' => true,
                'message' => $message,
                'redirect' => true,
                'url' => route('payrolls.show', $result['payroll']->uid),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Payroll $payroll)
    {
        $payslips = $this->service->payslipsForPayroll($payroll);
        $allowances = $this->service->allowanceOptions();
        $deductions = $this->service->deductionOptions();
        return view('payroll::payrolls.show', compact('payroll', 'payslips', 'allowances', 'deductions'));
    }

    public function updatePayslip(UpdatePayslipRequest $request, Payroll $payroll, Payslip $payslip)
    {
        DB::beginTransaction();
        try {
            $this->service->syncPayslipItems(
                $payslip,
                $request->input('allowances', []),
                $request->input('deductions', []),
                $request->notes
            );
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Payslip Updated',
                'table_reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStatus(UpdatePayrollStatusRequest $request, Payroll $payroll)
    {
        DB::beginTransaction();
        try {
            $this->service->updateStatus($payroll, $request->status);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Payroll Marked ' . ucfirst($request->status),
                'table_reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Payroll $payroll)
    {
        DB::beginTransaction();
        try {
            $this->service->destroy($payroll);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Payroll Deleted',
                'redirect' => true,
                'url' => route('payrolls.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function printPayslip(Payslip $payslip)
    {
        $payslip = $this->service->payslipForPrint($payslip);
        return view('payroll::payrolls.print.payslip', compact('payslip'));
    }
}