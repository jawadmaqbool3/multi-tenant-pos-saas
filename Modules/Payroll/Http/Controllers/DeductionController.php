<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Http\Requests\StoreDeductionRequest;
use Modules\Payroll\Http\Requests\UpdateDeductionRequest;
use Modules\Payroll\Models\Deduction;
use Modules\Payroll\Services\DeductionService;

class DeductionController extends Controller
{
    protected $service;

    public function __construct(DeductionService $service = null)
    {
        $this->service = $service ?: app(DeductionService::class);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getList();
        }
        return view('payroll::deductions.index');
    }

    public function create()
    {
        return view('payroll::deductions.create');
    }

    public function store(StoreDeductionRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->service->store($request);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'New Deduction Added',
                'table_reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(Deduction $deduction)
    {
        return view('payroll::deductions.edit', compact('deduction'));
    }

    public function update(UpdateDeductionRequest $request, Deduction $deduction)
    {
        DB::beginTransaction();
        try {
            $this->service->update($request, $deduction);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Deduction Updated',
                'table_reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Deduction $deduction)
    {
        $this->service->delete($deduction);
        return response([
            'success' => true,
            'message' => 'Deduction Deleted',
            'table_reload' => true,
        ]);
    }
}