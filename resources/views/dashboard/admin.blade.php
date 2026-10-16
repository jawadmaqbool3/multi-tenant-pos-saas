@extends('layouts.basic')
@section('content')
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="page-title">
                            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                                SaaS Dashboard</h1>
                            <x-breadcrums :links="[
                                'Home' => route('dashboard'),
                                'Dashboard' => false,
                            ]" />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="">
                            <a href="{{ route('companies.create') }}"
                                class="mt-2 ms-2 float-end btn btn-sm fw-bold btn-primary">Create Company</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                @include('dashboard._grid')
            </div>
        </div>
    </div>
@endsection
@section('jsSection')
    @include('dashboard._widgets_js')
@endsection
