@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Ledger</span>
            </h3>
            <div class="row w-100 g-3 align-items-end">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Ledger' => false,
                    ]" />
                </div>
                <div class="col-md-5">
                    <label class="form-label">Account</label>
                    <x-account-selector accountLevel="all" selectLevel="all" selected="all" :account="$accounts"
                        id="ledger_account" name="account" class="account-selector" isRequired="true" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date Range</label>
                    <div class="d-flex align-items-center position-relative">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="calendar" :size="18" />
                        </span>
                        <input type="text" id="ledger_date_range" class="form-control form-control-solid ps-15"
                            placeholder="All dates" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive" id="ledger_wrapper"></div>
        </div>
    </div>
@endsection

@section('jsSection')
    @include('chartofaccounts::reports.ledger._js')
@endsection
