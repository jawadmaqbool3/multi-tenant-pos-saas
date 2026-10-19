@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Attendance Report</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Attendance Report' => false,
                    ]" />
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="row">
                <div class="col-md-4">
                    <input class="form-control form-control-solid ps-12 flatpickr-input active" placeholder="Select a month"
                        value="{{ Carbon\Carbon::create('now')->format('Y-m') }}" id="attendance_month" name="month"
                        type="month">
                </div>
                <div class="col-md-4">
                    <select name="" id="employee_selector" class="form-control-solid  form-control">
                        <option value="" disabled selected>Select Option</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->uid }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive mt-10" id="attendance_report_wrapper">

            </div>
        </div>
    </div>
@endsection

@section('jsSection')
    <script>
        $(document).ready(function() {
            $("#employee_selector").select2();
            @include('employee::reports.employee.attendance_js')
            $('#employee_selector, #attendance_month').change(function() {
                let month = $('#attendance_month').val();
                let employee = $('#employee_selector').val();
                if (month && employee) {
                    getAttendance(employee, month);
                }
            });
        });
    </script>
@endsection
