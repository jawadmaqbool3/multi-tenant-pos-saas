<script>
    $(document).ready(function() {
        var table = $('#category_table').DataTable({
            "processing": true,
            "serverSide": true,
            "searchDelay": 500,
            "ajax": "{{ route('stocks.index') }}",
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
                    "data": "purchase_price"
                },
                {
                    "data": "stock"
                },
                {
                    "data": "quantity",
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
        $(document).on('change', '#status', function() {
            table.ajax.url("{{ route('stocks.index') }}" + `?status=${$(this).val()}`).load();
        });

        $(document).on('click', '#print_stock_btn', function(e) {
            e.preventDefault();
            window.location.href = "{{ url('print/stock/') }}/" + $('#status').val();
        });


    });
</script>
