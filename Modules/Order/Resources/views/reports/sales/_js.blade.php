<script>
    $(document).ready(function() {
        var dateRange = {
            start: "{{ date('Y-m-d') }}",
            end: "{{ date('Y-m-d') }}",
        }
        var table = $('#category_table').DataTable({
                "processing": true,
                "serverSide": true,
                "searchDelay": 500,
                "ajax": window.location.href + "?from={{ date('Y-m-d') }}",
                "columns": [{
                        "data": 'DT_RowIndex',
                        "name": '',
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "order_no"
                    },
                    {
                        "data": "amount",
                    },
                    {
                        "data": "date"
                    },
                    {
                        "data": "action",
                        "orderable": false,
                        "searchable": false
                    },

                ],

                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    // Remove the formatting to get integer data for summation
                    var intVal = function(i) {
                        return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i ===
                            'number' ? i : 0;
                    };

                    // Total over all pages
                    total = api
                        .column(2)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(1).footer()).html(`Total <span class="float-end">${total}</span>`);
                },
            },

        );
        $('#filter_date_range').daterangepicker({
            opens: 'left'
        }, function(start, end, label) {
            dateRange.start = start.format('YYYY-MM-DD') ? start.format('YYYY-MM-DD') : null;
            dateRange.end = end.format('YYYY-MM-DD') ? end.format('YYYY-MM-DD') : null;
            table.ajax.url(window.location.href + `?from=${start.format('YYYY-MM-DD')}&to=${end
                .format('YYYY-MM-DD')}`).load();
        });
        document.addEventListener("table_reload", function() {
            table.ajax.reload();
        });

        $(document).on('keyup', '[data-table-filter="search"]', function() {
            table.search($(this).val()).draw();
        })



        $(document).on('click', '#print_stock_btn', function(e) {
            e.preventDefault();
            window.location.href = "{{ url('print/sales/') }}/" + dateRange.start + "/" + dateRange.end;
        });


    });
</script>
