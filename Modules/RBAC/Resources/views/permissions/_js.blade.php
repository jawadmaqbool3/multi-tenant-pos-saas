<script>
    $(document).ready(function() {
       
        $('#create_modal ._select2').select2({
            dropdownParent: $('#create_modal #dropdown_parent')
        });
        $('#edit_modal ._select2').select2({
            dropdownParent: $('#edit_modal #dropdown_parent')
        });
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
                    "data": "name"
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
