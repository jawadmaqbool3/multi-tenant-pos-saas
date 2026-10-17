@extends('layouts.basic')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Edit
                    Coupon</h1>
                <x-breadcrums :links="[
                    'Home' => route('dashboard'),
                    'Promotions & Coupons' => route('billing.coupons.index'),
                    'Edit' => false,
                ]" />
            </div>
        </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div class="app-container container-xxl">
            <form data-ajax="true" action="{{ route('billing.coupons.update', $coupon->uid) }}"
                class="form d-flex flex-column flex-lg-row" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @include('billing::coupons._form')
            </form>
        </div>
    </div>
@endsection
