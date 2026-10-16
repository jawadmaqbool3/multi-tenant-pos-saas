<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Employee\Models\AttendanceHistory;
use Modules\Employee\Services\AttendanceHistoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceHistoryController extends Controller
{
    protected $service;

    public function __construct(AttendanceHistoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $canViewAll = auth()->user()->hasPermission('view employee attendance report');

        if ($request->ajax()) {
            $month = $request->month ?? Carbon::now()->format('Y-m');
            // Users without the tenant-wide report only ever see their own row.
            $user = $canViewAll
                ? $this->service->getUserByUid($request->id)
                : auth()->user();
            $view = view('employee::reports.employee._ajax_attendance', compact('user', 'month'))->render();
            return response([
                'success' => true,
                'view' => $view
            ]);
        }

        // Employees without the tenant-wide report only have their own history.
        if (!$canViewAll) {
            return redirect()->route('attendance.me');
        }

        $employees = $this->service->getEmployees();
        return view('employee::reports.employee.index', compact('employees'));
    }

    /**
     * Personal attendance history (own-only report screen).
     *
     * @return \Illuminate\Http\Response
     */
    public function me()
    {
        return view('employee::reports.employee.mine');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->attendance_enabled) {
            return response([
                'success' => false,
                'message' => 'Attendance is not enabled for your account',
            ]);
        }

        $type = $this->service->store($request);
        return response([
            'success' => true,
            'message' => 'You are clocked ' . ($type == 'clockin' ? ' in ' : ' out ') . ' successfuly',
            'event' => ['name' => $type]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Modules\Employee\Models\AttendanceHistory  $attendanceHistory
     * @return \Illuminate\Http\Response
     */
    public function show(AttendanceHistory $attendanceHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Modules\Employee\Models\AttendanceHistory  $attendanceHistory
     * @return \Illuminate\Http\Response
     */
    public function edit(AttendanceHistory $attendanceHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\Employee\Models\AttendanceHistory  $attendanceHistory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttendanceHistory $attendanceHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Modules\Employee\Models\AttendanceHistory  $attendanceHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceHistory $attendanceHistory)
    {
        //
    }
}
