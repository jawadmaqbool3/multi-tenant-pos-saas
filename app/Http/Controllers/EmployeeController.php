<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    protected $service;

    public function __construct(EmployeeService $service = null)
    {
        $this->service = $service ?? app(EmployeeService::class);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getEmployees();
        }
        return view('employees.index');
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(StoreEmployeeRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->service->store($request);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Record Added',
                'redirect' => true,
                'url' => route('employees.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        //
    }

    public function edit(User $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, User $employee)
    {
        DB::beginTransaction();
        try {
            $this->service->update($request, $employee);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Record Updated',
                'redirect' => true,
                'url' => route('employees.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(User $employee)
    {
        $this->service->delete($employee);
        return response([
            'success' => true,
            'message' => 'Record Deleted',
            'table_reload' => true,
        ]);
    }
}
