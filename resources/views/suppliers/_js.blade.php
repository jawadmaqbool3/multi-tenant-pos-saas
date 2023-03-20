<script>
    $(document).ready(function() {
        $('._select2').select2();
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
                    "data": "thumbnail",
                    "orderable": false,
                    "searchable": false
                },
                {
                    "data": "name"
                },
                {
                    "data": "balance"
                },
                {
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
        $(document).on('change', '#pay_supplier_modal #modal_balance', function() {
            let max = $('#pay_supplier_modal #modal_max').val();
            if ($(this).val() < 0 || $(this).val() > max) {
                $(this).val(max);   
                toastr.warning(
                    "Cannot exceed than pending amount",
                    "warning", {
                        timeOut: 5000,
                        extendedTimeOut: 0,
                        closeButton: true,
                        closeDuration: 0
                    }
                );
            }
        });
    });
</script>
