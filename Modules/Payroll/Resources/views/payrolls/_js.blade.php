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
                    "data": "period"
                },
                {
                    "data": "status",
                    "orderable": false
                },
                {
                    "data": "total_employees",
                    "searchable": false
                },
                {
                    "data": "total_net",
                    "searchable": false
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
        });
    });
</script>
