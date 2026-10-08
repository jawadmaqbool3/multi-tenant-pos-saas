<?php

namespace Modules\Employee\Http\Traits;

use Modules\Employee\Models\AttendanceHistory;
use Modules\Employee\Models\EmployeePersonalInformation;
use Carbon\Carbon;

/**
 * 
 */
trait EmployeeTrait
{



    public function empInfo()
    {
        return $this->hasOne(
            EmployeePersonalInformation::class,
            'employee_id',
            'id',
            'order_id',
        );
    }

    public function lastAttendanceActivity()
    {
        $today = Carbon::now();
        return $this->hasOne(AttendanceHistory::class, 'user_id', 'id')
            ->whereDay('time', $today->format('d'))
            ->whereMonth('time', $today->format('m'))
            ->whereYear('time', $today->format('Y'))
            ->orderBy('id', 'DESC');
    }


    public function clockOut($date)
    {
        $date = new Carbon($date);
        return AttendanceHistory::where('user_id', $this->id)
            ->whereDay('time', $date->format('d'))
            ->whereMonth('time', $date->format('m'))
            ->whereYear('time', $date->format('Y'))
            ->where('type', 'clockout')
            ->orderBy('id', 'DESC')
            ->first();
    }
    public function clockin($date)
    {
        $date = new Carbon($date);
        return AttendanceHistory::where('user_id', $this->id)
            ->whereDay('time', $date->format('d'))
            ->whereMonth('time', $date->format('m'))
            ->whereYear('time', $date->format('Y'))
            ->where('type', 'clockin')
            ->first();
    }
}
