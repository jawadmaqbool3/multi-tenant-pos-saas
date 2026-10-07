<?php

namespace App\Services;

use App\Repositories\AttendanceHistoryRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;

class AttendanceHistoryService
{
    protected $attendanceHistories;
    protected $users;

    public function __construct(AttendanceHistoryRepository $attendanceHistories, UserRepository $users)
    {
        $this->attendanceHistories = $attendanceHistories;
        $this->users = $users;
    }

    public function getUserByUid($uid)
    {
        return $this->users->query()->where('uid', $uid)->firstOrFail();
    }

    public function getEmployees()
    {
        return $this->users->query()->whereHas('role', function ($query) {
            $query->where('name', 'employee');
        })->get();
    }

    public function store($request)
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
        $this->attendanceHistories->create([
            'time' => $current,
            'user_id' => auth()->user()->id,
            'type' => $type
        ]);
        return $type;
    }
}
