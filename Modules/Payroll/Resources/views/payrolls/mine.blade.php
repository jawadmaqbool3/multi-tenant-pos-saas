@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">My Payslips</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'My Payslips' => false,
                    ]" />
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="border-0">
                            <th class="fw-bold p-0">SR#</th>
                            <th class="fw-bold p-0 min-w-125px text-start">Period</th>
                            <th class="fw-bold p-0 text-start">Status</th>
                            <th class="fw-bold p-0 text-end">Base</th>
                            <th class="fw-bold p-0 text-end">Allowances</th>
                            <th class="fw-bold p-0 text-end">Deductions</th>
                            <th class="fw-bold p-0 text-end">Net</th>
                            <th class="fw-bold p-0 min-w-100px text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payslips as $index => $payslip)
                            @php
                                $badge = [
                                    'draft' => 'btn-secondary',
                                    'processed' => 'btn-warning',
                                    'paid' => 'btn-success',
                                ][@$payslip->payroll->status] ?? 'btn-secondary';
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse(@$payslip->payroll->period)->format('F Y') }}
                                </td>
                                <td>
                                    <span class="btn btn-sm {{ $badge }} disabled">
                                        {{ ucwords(@$payslip->payroll->status) }}
                                    </span>
                                </td>
                                <td class="text-end">{{ number_format($payslip->base_salary, 2) }}</td>
                                <td class="text-end">{{ number_format($payslip->total_allowances, 2) }}</td>
                                <td class="text-end">{{ number_format($payslip->total_deductions, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($payslip->net_salary, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('payslip.print', $payslip->uid) }}" target="_blank"
                                        class="btn btn-sm btn-light-primary">
                                        <x-icon name="printer" :size="16" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-10">No payslips yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
