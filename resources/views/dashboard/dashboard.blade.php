@extends('layouts.basic')
@section('content')
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl ">
                <div class="row">
                    <div class="col-lg-6">

                        <div class="page-title ">
                            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                                eCommerce
                                Dashboard</h1>
                            <x-breadcrums :links="[
                                'Home' => route('dashboard'),
                                'Dashboard' => false,
                            ]" />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="">
                            @can('create product')
                                <a href="{{ route('products.create') }}"
                                    class="mt-2 ms-2 float-end btn btn-sm fw-bold btn-primary">Create
                                    Product</a>
                            @endcan
                            @can('add stock')
                                <a data-bs-target="#add_stock_modal" data-bs-toggle="modal"
                                    class="mt-2 ms-2 float-end btn btn-sm fw-bold btn-primary btn-add-stock">Create Purchase
                                    Order</a>
                            @endcan
                            @can('place order')
                                <a data-bs-target="#place_order_modal" data-bs-toggle="modal"
                                    class="mt-2 ms-2 float-end btn btn-sm fw-bold bg-body btn-color-gray-700 btn-active-color-primary btn-place-order">Create
                                    Sale Order</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="row g-5 g-xl-10 mb-xl-10 mb-10">
                    <div class="col-md-6 col-lg-6 col-xl-6  mb-md-5 mb-xl-10">
                        @can('dashboard profit loss report')
                            <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">{{ config('app.currency') }}</span>
                                            <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2" id="profit_wrapper">0</span>
                                            <span id="profit_direction"></span>
                                        </div>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Today Profit</span>
                                    </div>
                                </div>

                                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                                    <div class="d-flex flex-center me-5 pt-2">
                                        <div id="profit_chart" style="min-width: 70px; min-height: 70px"></div>
                                    </div>
                                    <div class="d-flex flex-column content-justify-center w-100">
                                        <div class="d-flex fs-6 fw-semibold align-items-center">
                                            <div class="bullet w-8px h-6px rounded-2 bg-danger me-3"></div>
                                            <div class="text-gray-500 flex-grow-1 me-4">Expense</div>
                                            <div class="fw-bolder text-gray-700 text-xxl-end" id="expense_wrapper">0</div>
                                        </div>
                                        <div class="d-flex fs-6 fw-semibold align-items-center my-3">
                                            <div class="bullet w-8px h-6px rounded-2 bg-primary me-3"></div>
                                            <div class="text-gray-500 flex-grow-1 me-4">Revenue</div>
                                            <div class="fw-bolder text-gray-700 text-xxl-end" id="revenue_wrapper">0</div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endcan
                        <div class="row">
                            @can('dashboard monthly sales')
                                <div class="col-md-6 pb-5">
                                    <div class="card text-start" id="sales_this_month">
                                        <div class="card-body">
                                            <h4 class="card-title"><span class="amount">0</span>{{ config('app.currency') }}
                                                <span class="direction"></span>
                                            </h4>
                                            <p class="card-text"><span class="count float-end fs-3">0</span></p>
                                            <p class="pt-8">
                                                <small class="float-end">Sales this month</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                            @can('dashboard monthly orders')
                                <div class="col-md-6 pb-5">
                                    <div class="card text-start" id="orders_this_month">
                                        <div class="card-body">
                                            <h4 class="card-title"><span class="amount">0</span>{{ config('app.currency') }}
                                                <span class="direction"></span>
                                            </h4>
                                            <p class="card-text"><span class="count float-end fs-3">0</span></p>
                                            <p class="pt-8">
                                                <small class="float-end">Orders this month</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                            @can('dashboard monthly cancelled orders')
                                <div class="col-md-6 pb-5">
                                    <div class="card text-start" id="cancelled_this_month">
                                        <div class="card-body">
                                            <h4 class="card-title"><span class="amount">0</span>{{ config('app.currency') }}
                                                <span class="direction"></span>
                                            </h4>
                                            <p class="card-text"><span class="count float-end fs-3">0</span></p>
                                            <p class="pt-8">
                                                <small class="float-end">Cancelled Orders this month</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                            @can('dashboard monthly refunded orders')
                                <div class="col-md-6 pb-5">
                                    <div class="card text-start" id="refunded_this_month">
                                        <div class="card-body">
                                            <h4 class="card-title"><span class="amount">0</span>{{ config('app.currency') }}
                                                <span class="direction"></span>
                                            </h4>
                                            <p class="card-text"><span class="count float-end fs-3">0</span></p>
                                            <p class="pt-8">
                                                <small class="float-end">Refunded Orders this month</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-6  mb-md-5 mb-xl-10">
                        @can('dashboard daily average sales')
                            <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">{{ config('app.currency') }}</span>
                                            <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2"
                                                id="average_sale_wrapper">0</span>
                                            <span id="average_sale_direction"></span>
                                        </div>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Average Daily Sales</span>
                                    </div>
                                </div>
                                <div class="card-body d-flex align-items-end px-0 pb-0">
                                    <div id="average_sales_chart" class="w-100" style="height: 80px"></div>
                                </div>
                            </div>
                        @endcan
                        @can('dashboard monthly customers')
                            <div class="card card-flush h-md-50 mb-5" id="customer_card">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2 count"></span>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">New Customers This Month</span>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-end pe-0">
                                    <span class="fs-6 fw-bolder text-gray-800 d-block mb-2">Today’s Heroes</span>
                                    <div class="symbol-group symbol-hover flex-nowrap hero-list"></div>
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="row gy-5 g-xl-10">
                    @can('dashboard recent orders')
                        <div class="col-xl-4">
                            <div class="card card-flush h-xl-100">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-dark">Recent Orders</span>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="hover-scroll-overlay-y pe-6 me-n6" style="height: 415px"
                                        id="recent_order_card">

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                    @can('dashboard stock report')
                        <div class="col-xl-8">
                            <div class="card card-flush h-xl-100">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-dark">Stock Report</span>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center fw-bold w-md-250px">
                                        <div class="text-muted fs-7 me-2">Status</div>
                                        <select name="stock"
                                            class="form-select form-select-transparent text-dark fs-7 lh-1 fw-bold py-0 w-auto"
                                            data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
                                            id="status" data-placeholder="Select an option"
                                            data-kt-table-widget-5="filter_status">
                                            <option></option>
                                            <option value="all" selected="selected">Show All</option>
                                            <option value="in_stock">In Stock</option>
                                            <option value="out_of_stock">Out of Stock</option>
                                            <option value="low_stock">Low Stock</option>
                                        </select>
                                        <button class="btn btn-secondary btn-sm" id="print_stock_btn">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    </div>
                                    <div class="table-responsive mt-5">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4"
                                            id="category_table">
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
                        </div>
                    @endcan
                </div>
                @can('dashboard employee mark attendance')
                    <div class="row gy-5 g-xl-10 mt-0">

                        <div class="col-xl-12">
                            <div class="card card-flush h-xl-100">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-dark">Attendance Report</span>
                                    </h3>
                                    <form id="attendance_form" data-ajax="true" action="{{ route('attendance.store') }}"
                                        method="POST">
                                        @if (auth()->user()->lastAttendanceActivity == false || auth()->user()->lastAttendanceActivity->type == 'clockout')
                                            <input type="submit" name="clockin" class="btn-sm btn btn-success"
                                                value="Clock in" />
                                        @else
                                            <input type="submit" name="clockout" class="btn-sm btn btn-danger"
                                                value="Clock out" />
                                        @endif
                                    </form>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input class="form-control form-control-solid ps-12 flatpickr-input active"
                                                placeholder="Select a month"
                                                value="{{ Carbon\Carbon::create('now')->format('Y-m') }}"
                                                id="attendance_month" name="month" type="month">
                                        </div>
                                    </div>
                                    <div class="table-responsive mt-5" id="attendance_report_wrapper">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

            </div>

        </div>
    </div>
    <x-add-stock-modal />
    <x-place-order-modal />
@endsection
@section('jsSection')
    @include('dashboard._js')   
    @can('dashboard stock report')
        @include('inventory::reports.stocks._js')
    @endcan
@endsection
