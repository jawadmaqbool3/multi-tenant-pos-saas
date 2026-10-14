@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Stock Report</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Stocks' => false,
                    ]" />
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="search" :size="18" />
                        </span>
                        <input type="text" data-table-filter="search"
                            class="form-control form-control-solid w-250px ps-15" placeholder="Search Product">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="search" :size="18" />
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-3">
            <div class="col-sm-2 py-5">
                <div class="d-flex align-items-center fw-bold">
                    <div class="text-muted fs-7 me-2">Status</div>
                    <select name="stock"
                        class="form-select form-select-transparent text-dark fs-7 lh-1 fw-bold py-0 w-auto"
                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px" id="status"
                        data-placeholder="Select an option" data-kt-table-widget-5="filter_status">
                        <option></option>
                        <option value="all" selected="selected">Show All</option>
                        <option value="in_stock">In Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                        <option value="low_stock">Low Stock</option>
                    </select>
                    <button class="btn btn-secondary btn-sm"  id="print_stock_btn">
                        <x-icon name="printer" size="16" />
                    </button>
                </div>

            </div>
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="category_table">
                    <thead>
                        <tr class="border-0">
                            <th class="fw-bold p-0">SR#</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Item</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Purchase Price</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Status</th>
                            <th class="fw-bold p-0 min-w-150px text-start">QTY</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('jsSection')
    @include('inventory::reports.stocks._js')
@endsection
