@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">

            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                <div class="me-7 mb-4">
                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                        <img src="{{ asset('assets/media/suppliers/thumbs/' . $supplier->thumbnail) }}" alt="image" />
                        <div
                            class="position-absolute translate-middle bottom-0 start-100 mb-6 
                            @if ($supplier->status == 'active') bg-success 
                            @else
                            bg-warning @endif
                            rounded-circle border border-4 border-body h-20px w-20px">
                        </div>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <a
                                    class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{ ucwords($supplier->name) }}</a>
                                <a href="#">
                                    <span class="svg-icon svg-icon-1 svg-icon-primary">
                                        <x-icon name="check-circle" :size="18" />
                                    </span>
                                </a>

                            </div>
                            <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">

                                @if ($supplier->cell_no)
                                    <a href="#"
                                        class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                        <span class="svg-icon svg-icon-4 me-1">
                                            <x-icon name="phone" size="16" /> {{ $supplier->cell_no }}
                                        </span>

                                    </a>
                                @endif
                                @if ($supplier->whatsapp_no)
                                    <a href="#"
                                        class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                        <span class="svg-icon svg-icon-4 me-1">
                                            <x-icon name="message-circle" size="16" /> {{ $supplier->whatsapp_no }}
                                        </span>
                                    </a>
                                @endif
                                @if ($supplier->phone_no)
                                    <a href="#"
                                        class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                        <span class="svg-icon svg-icon-4 me-1">
                                            <x-icon name="phone" size="16" /> {{ $supplier->phone_no }}
                                        </span>
                                    </a>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap flex-stack">
                        <div class="d-flex flex-column flex-grow-1 pe-8">
                            <div class="d-flex flex-wrap">
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-2 fw-bold" data-kt-countup="true"
                                            data-kt-countup-value="{{ $supplier->balance }}"
                                            data-kt-countup-prefix="{{ config('app.currency') }} ">0</div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-400">Balance</div>
                                </div>
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-2 fw-bold" data-kt-countup="true"
                                            data-kt-countup-value="{{ $supplier->purchaseOrders->count() }}">0
                                        </div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-400">Orders</div>
                                </div>
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-2 fw-bold" data-kt-countup="true"
                                            data-kt-countup-value="{{ $supplierStockHistory }}"
                                            data-kt-countup-prefix="">0</div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-400">Products</div>
                                </div>


                                @php
                                    if ($supplierStockHistory) {
                                        $percentageShare = round(($totalStockProducts / $supplierStockHistory) * 100, 2);
                                    } else {
                                        $percentageShare = 0;
                                    }
                                @endphp

                                <div class="d-flex align-items-center w-200px w-sm-300px flex-column mt-3">
                                    <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                        <span class="fw-semibold fs-6 text-gray-400">Share in Stock Products</span>
                                        <span class="fw-bold fs-6">{{ $percentageShare }}%</span>
                                    </div>
                                    <div class="h-5px mx-3 w-100 bg-light mb-3">
                                        <div class="bg-success rounded h-5px" role="progressbar"
                                            style="width: {{ $percentageShare }}%;" aria-valuenow="{{ $percentageShare }}"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-10 py-5 active" data-bs-toggle="tab"
                        href="#kt_tab_orders">Orders</a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-10 py-5 " data-bs-toggle="tab"
                        href="#kt_tab_products">Products</a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-10 py-5 " data-bs-toggle="tab"
                        href="#kt_tab_ledger">Ledger</a>
                </li>

            </ul>
        </div>
    </div>
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="kt_tab_orders" role="tabpanel">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                    <x-icon name="search" :size="18" />
                                </span>
                                <input type="text" data-table-filter="search"
                                    class="form-control form-control-solid w-250px ps-15" placeholder="Search Order">
                                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                    <x-icon name="search" :size="18" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive mt-6">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4"
                            id="category_table">
                            <thead>
                                <tr class="border-0">
                                    <th class="fw-bold p-0">SR#</th>
                                    <th class="fw-bold p-0">Order#</th>
                                    <th class="fw-bold p-0 min-w-150px text-start">Amount</th>
                                    <th class="fw-bold p-0 min-w-150px text-start">Generated at</th>
                                    <th class="fw-bold p-0 min-w-150px text-start">Status</th>
                                    <th class="fw-bold p-0 min-w-100px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="kt_tab_products" role="tabpanel">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                    <x-icon name="search" :size="18" />
                                </span>
                                <input type="text" data-table-filter="search"
                                    class="form-control form-control-solid w-250px ps-15" placeholder="Search Order">
                                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                    <x-icon name="search" :size="18" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive mt-6">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4"
                            id="product_table">
                            <thead>
                                <tr class="border-0">
                                    <th class="fw-bold p-0">SR#</th>
                                    <th class="fw-bold p-0">Thumb</th>
                                    <th class="fw-bold p-0 min-w-150px text-start">Name</th>
                                    <th class="fw-bold p-0 min-w-150px text-start">Stock</th>
                                    <th class="text-center"></th>
                                    <th class="fw-bold p-0 min-w-100px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="kt_tab_ledger" role="tabpanel">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                    <x-icon name="search" :size="18" />
                                </span>
                                <input type="text" data-table-filter="search"
                                    class="form-control form-control-solid w-250px ps-15" placeholder="Search Order">
                                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                    <x-icon name="search" :size="18" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive mt-6">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="ledger_table">
                            <thead>
                                <tr class="border-0">
                                    <th class="fw-bold p-0">SR#</th>
                                    <th class="fw-bold p-0">Date</th>
                                    <th class="fw-bold p-0">Amount</th>
                                    <th class="fw-bold p-0">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <x-order-refund-modal />
    </div>
@endsection

@section('jsSection')
    <script>
        $(document).ready(function() {
            var ordersTable = $('#category_table').DataTable({
                "processing": true,
                "serverSide": true,
                "searchDelay": 500,
                "ajax": "{{ route('purchase-orders.index') }}?supplier={{ $supplier->uid }}",
                "columns": [{
                        "data": 'DT_RowIndex',
                        "name": '',
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "order_no"
                    },

                    {
                        "data": "amount"
                    },
                    {
                        "data": "created_at"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "action",
                        "orderable": false,
                        "searchable": false
                    },
                ],
            });
            var table = $('#product_table').DataTable({
                "processing": true,
                "serverSide": true,
                "searchDelay": 500,
                "ajax": "{{ route('products.index') }}?supplier={{ $supplier->uid }}",
                "columns": [{
                        "data": 'DT_RowIndex',
                        "name": '',
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "thumbnail",
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "stock"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "action",
                        "orderable": false,
                        "searchable": false
                    },
                ],
            });
            var table = $('#ledger_table').DataTable({
                "processing": true,
                "serverSide": true,
                "searchDelay": 500,
                "ajax": "{{ route('supplier.ledger', $supplier->uid) }}",
                "columns": [{
                        "data": 'DT_RowIndex',
                        "name": '',
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": 'date',
                    },
                    {
                        "data": 'amount',
                    },
                    {
                        "data": 'description',
                    },
                ],
            });
            document.addEventListener("table_reload", function() {
                table.ajax.reload();
            });
            $(document).on('keyup', '[data-table-filter="search"]', function() {
                table.search($(this).val()).draw();
            })
            document.addEventListener("table_reload", function() {
                ordersTable.ajax.reload();
                productsTable.ajax.reload();
            });
        });
    </script>
@endsection
