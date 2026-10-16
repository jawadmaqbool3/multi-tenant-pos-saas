<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Http\Requests\StoreAllowanceRequest;
use Modules\Payroll\Http\Requests\UpdateAllowanceRequest;
use Modules\Payroll\Models\Allowance;
use Modules\Payroll\Services\AllowanceService;

class AllowanceController extends Controller
{
    protected $service;

    public function __construct(AllowanceService $service = null)
    {
        $this->service = $service ?: app(AllowanceService::class);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getList();
        }
        return view('payroll::allowances.index');
    }

    public function create()
    {
        return view('payroll::allowances.create');
    }

    public function store(StoreAllowanceRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->service->store($request);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'New Allowance Added',
                'table_reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(Allowance $allowance)
    {
        return view('payroll::allowances.edit', compact('allowance'));
    }

    public function update(UpdateAllowanceRequest $request, Allowance $allowance)
    {
        DB::beginTransaction();
        try {
            $this->service->update($request, $allowance);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Allowance Updated',
                'table_reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Allowance $allowance)
    {
        $this->service->delete($allowance);
        return response([
            'success' => true,
            'message' => 'Allowance Deleted',
            'table_reload' => true,
        ]);
    }
}