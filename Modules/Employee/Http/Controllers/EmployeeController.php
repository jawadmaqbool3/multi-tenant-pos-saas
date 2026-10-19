<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Employee\Models\User;
use Modules\Employee\Services\UserService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected $users;

    public function __construct(UserService $users)
    {
        $this->users = $users;
    }

    /**
     * Employees screen. The payroll roster is loaded from the frontend through
     * the employee data endpoint using the payroll flag.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('employee::employees.index');
    }

    /**
     * Data endpoint consumed by the frontend. The `filter` query selects the
     * employee flag to narrow by (payroll|attendance). `format=list` returns a
     * lightweight uid/name list for pickers; otherwise DataTables JSON is
     * returned for the employees table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $filter = $request->query('filter', 'payroll');

        if ($request->query('format') === 'list') {
            return response()->json([
                'data' => $this->users->employeeList($filter),
            ]);
        }

        return $this->users->employeeTable($filter);
    }

    /**
     * Employee detail: personal information and salary, a monthly attendance
     * presence chart, and (when the viewer may see payroll) the employee's
     * payslips plus a Generate Payroll action.
     *
     * @param  \Modules\Employee\Models\User  $user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(User $user, Request $request)
    {
        $user = $this->ownedUserOrFail($user);

        $detail = $this->users->employeeDetail($user, $request->query('month'));

        return view('employee::employees.show', $detail);
    }

    /**
     * Attendance presence series for one employee and month, as the partial the
     * detail screen injects when the month picker changes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\Employee\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function attendance(Request $request, User $user)
    {
        $user = $this->ownedUserOrFail($user);

        $detail = $this->users->employeeDetail($user, $request->query('month'));

        return response()->json([
            'success' => true,
            'view' => view('employee::employees.partials._attendance_chart', $detail)->render(),
            'presence' => array_values($detail['presence']),
        ]);
    }

    /**
     * Resolve a bound user, rejecting rows outside the acting company.
     *
     * @param  \Modules\Employee\Models\User  $user
     * @return \Modules\Employee\Models\User
     */
    protected function ownedUserOrFail($user)
    {
        $companyId = auth()->user()->company_id;

        if (!$companyId || (int) $user->company_id !== (int) $companyId) {
            abort(404);
        }

        return $user;
    }
}
