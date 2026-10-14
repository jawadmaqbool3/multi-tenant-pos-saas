@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Companies</span>
            </h3>

            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Companies' => false,
                    ]" />
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="search" :size="18" />
                        </span>
                        <input type="text" data-table-filter="search"
                            class="form-control form-control-solid w-250px ps-15" placeholder="Search Companies">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="search" :size="18" />
                        </span>
                    </div>
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
                        @can('create company')
                            <a href="{{ route('companies.create') }}" class="menu-link px-3">New Company</a>
                        @endcan
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
                            <th class="fw-bold p-0">Logo</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Name</th>
                            @if (auth()->user()->hasRole('admin'))
                                <th class="fw-bold p-0">Manager</th>
                            @endif
                            <th class="fw-bold p-0">Status</th>
                            <th class="fw-bold p-0 min-w-100px text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <x-delete url="{{ url('companies/') }}/" />
@endsection

@section('jsSection')
    <script>
        $(document).ready(function() {
            var table = $('#category_table').DataTable({
                "processing": true,
                "serverSide": true,
                "searchDelay": 500,
                "ajax": window.location.href,
                "columns": [{
                        "data": 'DT_RowIndex',
                        "name": '',
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "setting.logo",
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "setting.name"
                    },
                    @if (auth()->user()->hasRole('admin'))
                        {
                            "data": "manager.name",
                        },
                    @endif {
                        "data": "status"
                    },
                    {
                        "data": "action",
                        "orderable": false,
                        "searchable": false
                    },
                ],
            });

            document.addEventListener("table_reload", function() {
                table.ajax.reload();
            });

            $(document).on('keyup', '[data-table-filter="search"]', function() {
                table.search($(this).val()).draw();
            })


        });
    </script>
@endsection
