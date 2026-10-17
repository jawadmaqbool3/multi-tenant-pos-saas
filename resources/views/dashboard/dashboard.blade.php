@extends('layouts.basic')
@section('content')
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl ">
                <div class="row">
                    <div class="col-lg-6">

                        <div class="page-title ">
                            <div class="eyebrow">Overview</div>
                            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
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
                                    class="mt-2 ms-2 float-end btn btn-sm fw-bold btn-primary">
                                    <x-icon name="plus" size="14" /> Create
                                    Product</a>
                            @endcan
                            @can('add stock')
                                <a data-bs-target="#add_stock_modal" data-bs-toggle="modal"
                                    class="mt-2 ms-2 float-end btn btn-sm fw-bold btn-outline btn-add-stock">Create Purchase
                                    Order</a>
                            @endcan
                            @can('place order')
                                <a data-bs-target="#place_order_modal" data-bs-toggle="modal"
                                    class="mt-2 ms-2 float-end btn btn-sm fw-bold btn-outline btn-place-order">Create
                                    Sale Order</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div class="app-container container-xxl">
                @include('dashboard._grid')
            </div>
        </div>
    </div>
    <x-add-stock-modal />
    <x-place-order-modal />
@endsection
@section('jsSection')
    @include('dashboard._widgets_js')
    @include('dashboard._js')
    @if (collect($widgets)->contains(fn($w) => $w->key() === 'stock.report'))
        @include('inventory::reports.stocks._js')
    @endif
@endsection
