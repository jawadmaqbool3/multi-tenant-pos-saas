@extends('layouts.basic')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Create
                    Voucher
                </h1>
                <x-breadcrums :links="[
                    'Home' => route('dashboard'),
                    'Vouchers' => route('vouchers.index'),
                    'Create' => false,
                ]" />
            </div>
        </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div class="app-container container-xxl">
            <form id="voucher_form" data-ajax="true" action="{{ route('vouchers.store') }}" method="POST">
                <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Voucher Details</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="mb-10 row">
                                <div class="col-md-4">
                                    <label class="required form-label">Voucher Type</label>
                                    <select name="voucher_type_id" class="form-control form-control-solid _select2"
                                        required>
                                        <option value="" selected disabled>Select Voucher Type</option>
                                        @foreach ($voucherTypes as $voucherType)
                                            <option value="{{ $voucherType->id }}">
                                                {{ $voucherType->name }} ({{ $voucherType->short }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="required form-label">Date</label>
                                    <input type="date" name="date" value="{{ now()->format('Y-m-d') }}"
                                        class="form-control form-control-solid" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control form-control-solid"
                                        placeholder="Voucher description (optional)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Journal Lines</h2>
                            </div>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-success btn-sm" id="btn_add_row">
                                    <x-icon name="plus" :size="16" class="me-1" /> Add Line
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table table-border align-middle" id="lines_table">
                                    <thead>
                                        <tr>
                                            <th class="required">Account</th>
                                            <th style="width: 160px">Debit</th>
                                            <th style="width: 160px">Credit</th>
                                            <th>Description</th>
                                            <th style="width: 50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="lines_wrapper">
                                        @for ($i = 0; $i < 2; $i++)
                                            <tr>
                                                <td>
                                                    <x-account-selector :account="$accounts" accountLevel="level4only"
                                                        name="lines[{{ $i }}][account]" :id="'line_account_' . $i"
                                                        class="line-account" />
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0"
                                                        name="lines[{{ $i }}][debit]"
                                                        class="form-control form-control-solid line-debit">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0"
                                                        name="lines[{{ $i }}][credit]"
                                                        class="form-control form-control-solid line-credit">
                                                </td>
                                                <td>
                                                    <input type="text" name="lines[{{ $i }}][description]"
                                                        class="form-control form-control-solid line-description"
                                                        placeholder="Optional">
                                                </td>
                                                <td class="text-end">
                                                    <button type="button"
                                                        class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete">
                                                        <x-icon name="trash" :size="16" />
                                                    </button>
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold fs-6">
                                            <td class="text-end">Totals</td>
                                            <td id="total_debit">0.00</td>
                                            <td id="total_credit">0.00</td>
                                            <td>
                                                <span id="balance_badge" class="badge badge-light-danger">Not
                                                    balanced</span>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('vouchers.index') }}" class="btn btn-light me-5">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="voucher_submit">
                            <span class="indicator-label">Save Voucher</span>
                            <span class="indicator-progress">Please wait...
                                <span
                                    class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <template id="line_template">
        <tr>
            <td>
                <x-account-selector :account="$accounts" accountLevel="level4only" name="lines[0][account]"
                    id="" class="line-account" :isRequired="false" />
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="lines[0][debit]"
                    class="form-control form-control-solid line-debit">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="lines[0][credit]"
                    class="form-control form-control-solid line-credit">
            </td>
            <td>
                <input type="text" name="lines[0][description]"
                    class="form-control form-control-solid line-description" placeholder="Optional">
            </td>
            <td class="text-end">
                <button type="button"
                    class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete">
                    <x-icon name="trash" :size="16" />
                </button>
            </td>
        </tr>
    </template>
@endsection

@section('jsSection')
    @include('chartofaccounts::vouchers._create_js')
@endsection
