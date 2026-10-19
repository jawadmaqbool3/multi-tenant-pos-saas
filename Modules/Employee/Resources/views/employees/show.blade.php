@extends('layouts.basic')
@section('content')
    @php
        $statusTone = ['draft' => 'secondary', 'processed' => 'warning', 'paid' => 'success'];
        $existingPayslip = $run ? $payslips->firstWhere('payroll_id', $run->id) : null;
        $nextPeriod = now()->format('Y-m');
    @endphp

    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ $user->name }}
                </h1>
                <x-breadcrums :links="[
                    'Home' => route('dashboard'),
                    'Employees' => route('employee.index'),
                    'Detail' => false,
                ]" />
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('employee.index') }}" class="btn btn-light">Back</a>
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div class="app-container container-xxl">

            <div class="card card-flush py-4 mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Employee Details</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex align-items-center mb-8">
                        <div class="me-5">
                            {!! app(\App\DataTables\DataTableBuilder::class)->thumbnail('assets/media/users/profile', $user->image) !!}
                        </div>
                        <div>
                            <div class="fs-3 fw-bold">{{ $user->name }}</div>
                            <div class="text-muted fw-semibold fs-6">{{ $user->email }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <tbody>
                                    <tr>
                                        <td class="text-muted fw-semibold fs-7">Role</td>
                                        <td class="fw-bold">{{ $role->name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold fs-7">Status</td>
                                        <td>
                                            {!! app(\App\DataTables\DataTableBuilder::class)->statusBadge(ucwords($user->status), $user->status == 'active' ? 'btn-success' : 'btn-danger') !!}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold fs-7">Payroll Enabled</td>
                                        <td>{{ $user->payroll_enabled ? 'Yes' : 'No' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold fs-7">Attendance Enabled</td>
                                        <td>{{ $user->attendance_enabled ? 'Yes' : 'No' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <tbody>
                                    <tr>
                                        <td class="text-muted fw-semibold fs-7">Monthly Salary</td>
                                        <td class="fw-bold fs-5">
                                            {{ $salary ? number_format($salary, 2) : '—' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold fs-7">Father Name</td>
                                        <td>{{ $info->father_name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold fs-7">Contact</td>
                                        <td>{{ $info->contact ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold fs-7">Address</td>
                                        <td>{{ $info->address ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush py-4 mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Attendance</h2>
                    </div>
                    <div class="card-toolbar">
                        <input type="month" id="attendance_month" name="month"
                            value="{{ $month }}" class="form-control form-control-sm w-200px" />
                    </div>
                </div>
                <div class="card-body pt-0" id="attendance_chart_wrapper">
                    @include('employee::employees.partials._attendance_chart')
                </div>
            </div>

            @if ($canViewPayroll)
                <div class="card card-flush py-4 mb-5">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>Payrolls</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form data-ajax="true" method="POST"
                            action="{{ route('payrolls.employees.generate', $user->uid) }}"
                            class="d-flex flex-wrap align-items-center gap-3 mb-8">
                            @csrf
                            <div>
                                <label class="required form-label mb-1">Period</label>
                                <input type="month" name="period" value="{{ $nextPeriod }}"
                                    class="form-control form-control-sm w-200px" required />
                            </div>
                            <div class="align-self-end">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <span class="indicator-label">
                                        @if ($existingPayslip)
                                            Regenerate Entry
                                        @elseif ($run)
                                            Add to {{ \Illuminate\Support\Carbon::parse($run->period)->format('F Y') }} Run
                                        @else
                                            Generate Payroll
                                        @endif
                                    </span>
                                    <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="border-0">
                                        <th class="fw-bold p-0">SR#</th>
                                        <th class="fw-bold p-0 text-start">Period</th>
                                        <th class="fw-bold p-0 text-start">Run Status</th>
                                        <th class="fw-bold p-0 text-end">Base</th>
                                        <th class="fw-bold p-0 text-end">Allowances</th>
                                        <th class="fw-bold p-0 text-end">Deductions</th>
                                        <th class="fw-bold p-0 text-end">Net</th>
                                        <th class="fw-bold p-0 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payslips as $payslip)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="text-start">
                                                {{ \Illuminate\Support\Carbon::parse($payslip->payroll->period)->format('F Y') }}
                                            </td>
                                            <td class="text-start">
                                                {!! app(\App\DataTables\DataTableBuilder::class)->statusBadge(ucwords($payslip->payroll->status), $statusTone[$payslip->payroll->status] ?? 'secondary') !!}
                                            </td>
                                            <td class="text-end">{{ number_format($payslip->base_salary, 2) }}</td>
                                            <td class="text-end text-success">
                                                {{ number_format($payslip->total_allowances, 2) }}</td>
                                            <td class="text-end text-danger">
                                                {{ number_format($payslip->total_deductions, 2) }}</td>
                                            <td class="text-end fw-bold">{{ number_format($payslip->net_salary, 2) }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('payrolls.show', $payslip->payroll->uid) }}"
                                                    title="View run"
                                                    class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                                    <x-icon name="eye" :size="16" />
                                                </a>
                                                <a href="{{ route('payslip.print', $payslip->uid) }}" target="_blank"
                                                    title="Print payslip"
                                                    class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                                    <x-icon name="printer" :size="16" />
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">No payslips yet for this
                                                employee.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

@section('jsSection')
    <script>
        // ApexCharts ships in `widgets.bundle.js`, which loads after the content
        // section, so the chart is rendered from here rather than inline.
        function renderEmployeeAttendanceChart(present) {
            var node = document.getElementById('employee_attendance_chart');
            if (!node || typeof ApexCharts === 'undefined') {
                return;
            }

            if (node._chart) {
                node._chart.destroy();
                node._chart = null;
            }

            node._chart = new ApexCharts(node, {
                series: [{
                    name: 'Present',
                    data: present
                }],
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 3,
                        columnWidth: '70%',
                        colors: {
                            ranges: [{
                                from: 0,
                                to: 0,
                                color: '#F1416C'
                            }, {
                                from: 1,
                                to: 1,
                                color: '#50CD89'
                            }]
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: present.map(function(_, index) {
                        return index + 1;
                    }),
                    title: {
                        text: 'Day of Month'
                    },
                    tickAmount: 15
                },
                yaxis: {
                    min: 0,
                    max: 1,
                    tickAmount: 1,
                    labels: {
                        formatter: function(value) {
                            return value >= 1 ? 'Present' : 'Absent';
                        }
                    }
                },
                legend: {
                    show: false
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return value >= 1 ? 'Present' : 'Absent';
                        }
                    }
                }
            });

            node._chart.render();
        }

        $(document).ready(function() {
            renderEmployeeAttendanceChart(@json(array_values($presence)));

            $(document).on('change', '#attendance_month', function() {
                var month = $(this).val();
                if (!month) {
                    return;
                }
                $.ajax({
                    url: "{{ route('employee.attendance', $user->uid) }}",
                    type: "GET",
                    data: {
                        month: month
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#attendance_chart_wrapper').html(response.view);
                            renderEmployeeAttendanceChart(response.presence);
                        }
                    },
                    error: function() {
                        console.error('failed to fetch attendance history');
                    }
                });
            });
        });
    </script>
@endsection
