<script>
    $(document).ready(function() {
        $('#balance_sheet_as_of').daterangepicker({
            singleDatePicker: true,
            autoUpdateInput: true,
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        $('#balance_sheet_as_of').on('apply.daterangepicker', function(ev, picker) {
            loadBalanceSheet(picker.startDate.format('YYYY-MM-DD'));
        });

        function loadBalanceSheet(asOf) {
            $.ajax({
                url: window.location.href,
                type: 'GET',
                data: {
                    as_of: asOf,
                },
                success: function(response) {
                    if (response.success) {
                        $('#balance_sheet_wrapper').html(response.table);
                    }
                },
                error: function(xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) ?
                        xhr.responseJSON.message :
                        'Failed to load balance sheet';
                    toastr.error(message, "Error", {
                        timeOut: 5000,
                        extendedTimeOut: 0,
                        closeButton: true,
                        closeDuration: 0
                    });
                }
            });
        }
    });
</script>
