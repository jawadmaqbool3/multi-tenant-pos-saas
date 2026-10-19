<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
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

        return $this->users->getUsers($filter);
    }
}
