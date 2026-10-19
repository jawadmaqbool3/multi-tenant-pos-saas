@php
    $present = array_sum($presence);
    $tracked = count($presence);
    $absent = $tracked - $present;
    $rate = $tracked ? round($present / $tracked * 100) : 0;
    $rateTone = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
@endphp

<div class="row g-5 mb-5">
    <div class="col-md-4">
        <div class="card card-flush py-4 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold fs-7 mb-1">Days Present</div>
                <div class="fs-3 fw-bold text-success">{{ $present }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-flush py-4 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold fs-7 mb-1">Days Absent</div>
                <div class="fs-3 fw-bold text-danger">{{ $absent }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-flush py-4 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold fs-7 mb-1">Attendance</div>
                <div class="fs-3 fw-bold text-{{ $rateTone }}">{{ $rate }}%</div>
            </div>
        </div>
    </div>
</div>

<div id="employee_attendance_chart" class="min-h-250px"></div>
