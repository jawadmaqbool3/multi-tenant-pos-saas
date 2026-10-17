@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Voucher Types</span>
                <x-breadcrums :links="[
                    'Home' => route('dashboard'),
                    'Voucher Types' => false,
                ]" />
            </h3>
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1 me-3">
                    <span class="svg-icon svg-icon-1 position-absolute ms-6">
                        <x-icon name="search" :size="18" />
                    </span>
                    <input type="text" data-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="Search Voucher Types">
                </div>
                @can('create voucher type')
                    <a href="#" class="btn btn-primary" data-bs-target="#create_modal" data-bs-toggle="modal">
                        <x-icon name="plus" :size="18" class="me-1" />
                        Create Voucher Type
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="voucher_types_table">
                    <thead>
                        <tr class="border-0">
                            <th class="fw-bold p-0">SR#</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Name</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Short</th>
                            <th class="fw-bold p-0 min-w-100px text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('chartofaccounts::voucher_types.create_modal')
    @include('chartofaccounts::voucher_types.edit_modal')
    <x-delete url="{{ url('voucher-types/') }}/" />
@endsection

@section('jsSection')
    <script>
        $(document).ready(function() {
            var table = $('#voucher_types_table').DataTable({
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
                        "data": "name"
                    },
                    {
                        "data": "short"
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
