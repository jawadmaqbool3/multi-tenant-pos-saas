@extends('layouts.basic')
@section('content')
    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Vouchers</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Vouchers' => false,
                    ]" />
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="search" :size="18" />
                        </span>
                        <input type="text" data-table-filter="search"
                            class="form-control form-control-solid w-250px ps-15" placeholder="Search Vouchers">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <x-icon name="search" :size="18" />
                        </span>
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
                            <th class="fw-bold p-0 min-w-150px text-start">Voucher#</th>
                            <th class="fw-bold p-0 min-w-100px text-start">Date</th>
                            <th class="fw-bold p-0 min-w-100px text-start">Amount</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Type</th>
                            <th class="fw-bold p-0 min-w-150px text-start">Created By</th>
                            <th class="text-right"></th>
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
                        "data": "voucher_no"
                    },
                    {
                        "data": "date"
                    },
                    {
                        "data": "amount"
                    },
                    {
                        "data": "creator.name"
                    },
                    
                    {
                        "data": "type"
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
