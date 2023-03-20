@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">

        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Purchase Orders</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Purchase Orders' => false,
                    ]" />
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="p-0">
                    <form id="" data-ajax="true" action="{{ route('purchase-orders.store') }}"
                        class="form d-flex flex-column flex-lg-row" data-kt-redirect="categories.html" method="POST"
                        enctype="multipart/form-data">
                        @include('purchase_orders._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('jsSection')
    @include('purchase_orders._js')
@endsection
