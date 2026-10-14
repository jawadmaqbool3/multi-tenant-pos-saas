@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Sale Report</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Sales' => false,
                    ]" />
                </div>
                <div class="col-sm-5">
                    <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="search" :size="18" />
                        </span>
                        <input type="text" data-table-filter="search" class="form-control form-control-solid  ps-15"
                            placeholder="Search Order">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="search" :size="18" />
                        </span>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                        <span class="svg-icon svg-icon-2 position-absolute ms-6">
                            <x-icon name="calendar" :size="18" />
                        </span>
                        <input type="text" id="filter_date_range" class="form-control form-control-solid  ps-15"
                            placeholder="Date Range">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                        <button class=" btn btn-secondary" id="print_stock_btn"><x-icon name="printer" size="16" /></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-3">

            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="category_table">
                    <thead>
                        <tr class="border-0">
                            <th class="fw-bold p-0">SR#</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Order#</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Amount</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Date</th>
                            <th class="fw-bold p-0 min-w-150px text-start"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr class="border-0">
                            <th colspan="5" class="fw-bold p-0 min-w-150px text-start"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('jsSection')
    @include('order::reports.sales._js')
@endsection
