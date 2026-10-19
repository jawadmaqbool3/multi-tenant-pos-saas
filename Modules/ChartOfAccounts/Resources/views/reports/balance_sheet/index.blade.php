@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Balance Sheet</span>
            </h3>
            <div class="row w-100 g-3 align-items-end">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Balance Sheet' => false,
                    ]" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">As of</label>
                    <div class="d-flex align-items-center position-relative">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="calendar" :size="18" />
                        </span>
                        <input type="text" id="balance_sheet_as_of" class="form-control form-control-solid ps-15"
                            value="{{ $asOf }}" placeholder="As of date">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive" id="balance_sheet_wrapper">
                @include('chartofaccounts::reports.balance_sheet._table')
            </div>
        </div>
    </div>
@endsection

@section('jsSection')
    @include('chartofaccounts::reports.balance_sheet._js')
@endsection
