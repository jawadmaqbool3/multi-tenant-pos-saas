<script>
    $(document).ready(function() {
        $('#ledger_account').select2({
            placeholder: 'Select Account',
            allowClear: true
        });

        var dateRange = {
            start: @json($from ?? ''),
            end: @json($to ?? '')
        };

        $('#ledger_date_range').daterangepicker({
            autoUpdateInput: false,
            opens: 'left',
            startDate: dateRange.start || undefined,
            endDate: dateRange.end || undefined,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#ledger_date_range').on('apply.daterangepicker', function(ev, picker) {
            dateRange.start = picker.startDate.format('YYYY-MM-DD');
            dateRange.end = picker.endDate.format('YYYY-MM-DD');
            $(this).val(dateRange.start + ' - ' + dateRange.end);
            loadLedger();
        });

        $('#ledger_date_range').on('cancel.daterangepicker', function() {
            dateRange.start = '';
            dateRange.end = '';
            $(this).val('');
            loadLedger();
        });

        function loadLedger() {
            $.ajax({
                url: window.location.href,
                type: 'GET',
                data: {
                    account: $('#ledger_account').val(),
                    from: dateRange.start,
                    to: dateRange.end,
                },
                success: function(response) {
                    if (response.success) {
                        $('#ledger_wrapper').html(response.table);
                    }
                },
                error: function(xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) ?
                        xhr.responseJSON.message :
                        'Failed to load ledger';
                    toastr.error(message, "Error", {
                        timeOut: 5000,
                        extendedTimeOut: 0,
                        closeButton: true,
                        closeDuration: 0
                    });
                }
            });
        }

        $('#ledger_account').on('change', loadLedger);
        loadLedger();
    });
</script>
