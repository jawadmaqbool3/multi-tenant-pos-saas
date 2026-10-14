@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Accounts</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Accounts' => false,
                    ]" />
                </div>

            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary"
                    data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <span class="svg-icon svg-icon-2">
                        <x-icon name="more-horizontal" :size="20" />
                    </span>
                </button>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px"
                    data-kt-menu="true" style="">
                    <div class="menu-item px-3">
                        <div class="menu-content fs-6 text-dark fw-bold px-3 py-4">Quick Actions</div>
                    </div>
                    <div class="separator mb-3 opacity-75"></div>
                    <div class="menu-item px-3 mb-3">
                        @can('create account')
                            <a class="menu-link px-3" data-bs-target="#create_modal" data-bs-toggle="modal">New Account</a>
                        @endcan
                    </div>

                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive" id="tree_wrapper">
            </div>
        </div>
    </div>
    @include('chartofaccounts::accounts.create_modal')
    @include('chartofaccounts::accounts.delete_modal')
    @include('chartofaccounts::accounts.edit_modal')
@endsection

@section('jsSection')
    @include('chartofaccounts::accounts._js');
@endsection
