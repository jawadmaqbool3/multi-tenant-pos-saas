<?php

namespace App\Http\Controllers;

use App\Models\AttendanceHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $month = $request->month ?? Carbon::now()->format('Y-m');
            $user = User::where('uid', $request->id)->firstOrFail();
            $view = view('reports.employee._ajax_attendance', compact('user', 'month'))->render();
            return response([
                'success' => true,
                'view' => $view
            ]);
        }
        $employees = User::whereHas('role', function ($query) {
            $query->where('name', 'employee');
        })->get();
        return view('reports.employee.index', compact('employees'));
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
        $current = Carbon::now()->format('Y-m-d H:i:s');
        $lastAction = auth()->user()->lastAttendanceActivity;
        if ($lastAction) {
            if ($lastAction->type == 'clockin') {
                $type = 'clockout';
            } else {
                $type = 'clockin';
            }
        } else {
            $type = 'clockin';
        }
        AttendanceHistory::create([
            'time' => $current,
            'user_id' => auth()->user()->id,
            'type' => $type
        ]);
        return response([
            'success' => true,
            'message' => 'You are clocked ' . ($type == 'clockin' ? ' in ' : ' out ') . ' successfuly',
            'event' => ['name' => $type]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AttendanceHistory  $attendanceHistory
     * @return \Illuminate\Http\Response
     */
    public function show(AttendanceHistory $attendanceHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AttendanceHistory  $attendanceHistory
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
     * @param  \App\Models\AttendanceHistory  $attendanceHistory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttendanceHistory $attendanceHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AttendanceHistory  $attendanceHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceHistory $attendanceHistory)
    {
        //
    }
}
