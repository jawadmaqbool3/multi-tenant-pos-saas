@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">My Attendance</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'My Attendance' => false,
                    ]" />
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="row">
                <div class="col-md-4">
                    <input class="form-control form-control-solid ps-12 flatpickr-input" placeholder="Select a month"
                        value="{{ Carbon\Carbon::now()->format('Y-m') }}" id="my_attendance_month" name="month"
                        type="month">
                </div>
            </div>
            <div class="table-responsive mt-10" id="my_attendance_report_wrapper"></div>
        </div>
    </div>
@endsection

@section('jsSection')
    <script>
        $(document).ready(function() {
            function loadOwnAttendance(month) {
                $.ajax({
                    url: "{{ route('attendance.index') }}",
                    type: "GET",
                    data: {
                        id: "{{ auth()->user()->uid }}",
                        month: month
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#my_attendance_report_wrapper').html(response.view);
                        }
                    },
                    error: function(response) {
                        console.error('failed to fetch attendance history');
                    }
                });
            }

            loadOwnAttendance($('#my_attendance_month').val());

            $('#my_attendance_month').change(function() {
                loadOwnAttendance($(this).val());
            });
        });
    </script>
@endsection
