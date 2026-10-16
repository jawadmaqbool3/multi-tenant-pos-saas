@extends('layouts.basic')
@section('content')
    @php
        $statusTone = ['draft' => 'secondary', 'processed' => 'warning', 'paid' => 'success'][$payroll->status] ?? 'secondary';
    @endphp

    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Payroll Run &mdash; {{ \Illuminate\Support\Carbon::parse($payroll->period)->format('F Y') }}
                </h1>
                <x-breadcrums :links="[
                    'Home' => route('dashboard'),
                    'Payroll Runs' => route('payrolls.index'),
                    'Detail' => false,
                ]" />
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('payrolls.index') }}" class="btn btn-light">Back</a>
                @if ($payroll->status === 'draft')
                    @can('edit payroll')
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                            data-bs-target="#process_modal">Mark as Processed</button>
                    @endcan
                    @can('delete payroll')
                        <a href="#" class="btn btn-danger modal-button" data-bs-toggle="modal"
                            data-bs-target="#delete_modal" data-modal_id="{{ $payroll->uid }}"
                            data-modal_name="{{ \Illuminate\Support\Carbon::parse($payroll->period)->format('F Y') }}"
                            data-modal_title="Delete payroll run?">Delete</a>
                    @endcan
                @elseif ($payroll->status === 'processed')
                    @can('edit payroll')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#pay_modal">Mark as Paid</button>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <div class="row g-5 g-xl-8 mb-5">
                <div class="col-md-4">
                    <div class="card card-flush py-4">
                        <div class="card-body">
                            <div class="text-muted fw-semibold fs-7 mb-1">Status</div>
                            <span
                                class="badge badge-light-{{ $statusTone }} fs-6">{{ ucwords($payroll->status) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-flush py-4">
                        <div class="card-body">
                            <div class="text-muted fw-semibold fs-7 mb-1">Employees</div>
                            <div class="fs-3 fw-bold">{{ $payroll->total_employees }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-flush py-4">
                        <div class="card-body">
                            <div class="text-muted fw-semibold fs-7 mb-1">Net Total</div>
                            <div class="fs-3 fw-bold">{{ number_format($payroll->total_net, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush py-4 mb-5">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 border-end">
                            <div class="text-muted fw-semibold fs-7 mb-1">Base Salary</div>
                            <div class="fs-4 fw-bold">{{ number_format($payroll->total_base, 2) }}</div>
                        </div>
                        <div class="col-md-3 border-end">
                            <div class="text-muted fw-semibold fs-7 mb-1">Allowances</div>
                            <div class="fs-4 fw-bold text-success">{{ number_format($payroll->total_allowances, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3 border-end">
                            <div class="text-muted fw-semibold fs-7 mb-1">Deductions</div>
                            <div class="fs-4 fw-bold text-danger">{{ number_format($payroll->total_deductions, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted fw-semibold fs-7 mb-1">Net Payable</div>
                            <div class="fs-4 fw-bold">{{ number_format($payroll->total_net, 2) }}</div>
                        </div>
                    </div>
                    @if ($payroll->notes)
                        <div class="separator my-5"></div>
                        <div class="text-muted fw-semibold fs-7 mb-1">Notes</div>
                        <div>{{ $payroll->notes }}</div>
                    @endif
                </div>
            </div>

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Payslips</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="border-0">
                                    <th class="fw-bold p-0">SR#</th>
                                    <th class="fw-bold p-0 text-start">Employee</th>
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
                                        <td class="text-start">{{ @$payslip->employee->name ?? '—' }}</td>
                                        <td class="text-end">{{ number_format($payslip->base_salary, 2) }}</td>
                                        <td class="text-end text-success">
                                            {{ number_format($payslip->total_allowances, 2) }}</td>
                                        <td class="text-end text-danger">
                                            {{ number_format($payslip->total_deductions, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($payslip->net_salary, 2) }}</td>
                                        <td class="text-end">
                                            @if ($payroll->status === 'draft')
                                                @can('edit payroll')
                                                    <button type="button"
                                                        class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm edit-payslip"
                                                        data-bs-toggle="modal" data-bs-target="#payslip_modal"
                                                        data-url="{{ route('payrolls.payslips.update', ['payroll' => $payroll->uid, 'payslip' => $payslip->uid]) }}"
                                                        data-employee="{{ @$payslip->employee->name }}"
                                                        data-base="{{ $payslip->base_salary }}"
                                                        data-allowances="{{ json_encode($payslip->allowanceItems->map(fn($i) => ['allowance' => @$i->allowance->uid, 'amount' => $i->amount])->values()) }}"
                                                        data-deductions="{{ json_encode($payslip->deductionItems->map(fn($i) => ['deduction' => @$i->deduction->uid, 'amount' => $i->amount])->values()) }}"
                                                        data-notes="{{ $payslip->notes }}">
                                                        <x-icon name="pencil" :size="16" />
                                                    </button>
                                                @endcan
                                            @endif
                                            <a href="{{ route('payslip.print', $payslip->uid) }}" target="_blank"
                                                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                                <x-icon name="printer" :size="16" />
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">No payslips in this run.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-delete url="{{ url('payrolls/') }}/" />

    @can('edit payroll')
        <div class="modal fade" id="process_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form data-ajax="true" method="post" action="{{ route('payrolls.status.update', $payroll->uid) }}">
                        @method('PATCH')
                        <input type="hidden" name="status" value="processed">
                        <div class="modal-header">
                            <h2>Mark as Processed</h2>
                            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                <x-icon name="x" :size="18" />
                            </div>
                        </div>
                        <div class="modal-body py-10 px-lg-17">
                            Once processed the payroll can no longer be edited. Continue?
                        </div>
                        <div class="modal-footer flex-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">
                                <span class="indicator-label">Mark Processed</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="pay_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form data-ajax="true" method="post" action="{{ route('payrolls.status.update', $payroll->uid) }}">
                        @method('PATCH')
                        <input type="hidden" name="status" value="paid">
                        <div class="modal-header">
                            <h2>Mark as Paid</h2>
                            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                <x-icon name="x" :size="18" />
                            </div>
                        </div>
                        <div class="modal-body py-10 px-lg-17">
                            Marking this run as paid will post the salary voucher to the ledger. Continue?
                        </div>
                        <div class="modal-footer flex-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <span class="indicator-label">Mark Paid</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <div class="modal fade" id="payslip_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-800px">
            <div class="modal-content">
                <form data-ajax="true" method="post" action="" id="payslip_form">
                    @method('PATCH')
                    <div class="modal-header">
                        <h2 id="payslip_modal_title">Edit Payslip</h2>
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <x-icon name="x" :size="18" />
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <input type="hidden" id="payslip_base" value="">
                        <div class="d-flex flex-stack mb-2">
                            <h4 class="fw-bold mb-0">Allowances</h4>
                            <button type="button" class="btn btn-sm btn-light-primary" id="add_allowance">Add
                                Allowance</button>
                        </div>
                        <div id="allowance_rows" class="mb-8"></div>

                        <div class="d-flex flex-stack mb-2">
                            <h4 class="fw-bold mb-0">Deductions</h4>
                            <button type="button" class="btn btn-sm btn-light-primary" id="add_deduction">Add
                                Deduction</button>
                        </div>
                        <div id="deduction_rows" class="mb-8"></div>

                        <div class="fv-row">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="payslip_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="application/json" id="allowance_catalog">@json($allowances)</script>
    <script type="application/json" id="deduction_catalog">@json($deductions)</script>
@endsection

@section('jsSection')
    @include('payroll::payrolls._show_js')
@endsection
