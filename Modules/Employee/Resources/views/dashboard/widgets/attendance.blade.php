<div class="card card-flush h-xl-100" data-widget-key="{{ $key }}">
    <div class="card-header pt-7">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-dark">{{ $widget->title() }}</span>
        </h3>
        <form id="attendance_form" data-ajax="true" action="{{ route('attendance.store') }}" method="POST">
            @if (auth()->user()->lastAttendanceActivity == false || auth()->user()->lastAttendanceActivity->type == 'clockout')
                <input type="submit" name="clockin" class="btn-sm btn btn-success" value="Clock in" />
            @else
                <input type="submit" name="clockout" class="btn-sm btn btn-danger" value="Clock out" />
            @endif
        </form>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <input class="form-control form-control-solid ps-12 flatpickr-input active" placeholder="Select a month"
                    value="{{ Carbon\Carbon::create('now')->format('Y-m') }}" id="attendance_month" name="month"
                    type="month">
            </div>
        </div>
        <div class="table-responsive mt-5" id="attendance_report_wrapper">

        </div>
    </div>
</div>
