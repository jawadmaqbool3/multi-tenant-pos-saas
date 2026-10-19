<script>
    $(document).ready(function() {
        $('._select2').select2();

        function renumberRows() {
            $('#lines_wrapper tr').each(function(i) {
                $(this).find('[name^="lines["]').each(function() {
                    $(this).attr('name', $(this).attr('name').replace(/^lines\[\d+\]/, 'lines[' + i + ']'));
                });
            });
        }

        function updateTotals() {
            var debit = 0;
            var credit = 0;
            $('#lines_wrapper .line-debit').each(function() {
                debit += parseFloat($(this).val()) || 0;
            });
            $('#lines_wrapper .line-credit').each(function() {
                credit += parseFloat($(this).val()) || 0;
            });
            $('#total_debit').text(debit.toFixed(2));
            $('#total_credit').text(credit.toFixed(2));

            var balanced = debit > 0 && Math.abs(debit - credit) < 0.005;
            var badge = $('#balance_badge');
            if (balanced) {
                badge.removeClass('badge-light-danger').addClass('badge-light-success').text('Balanced');
            } else {
                badge.removeClass('badge-light-success').addClass('badge-light-danger').text('Not balanced');
            }
            $('#voucher_submit').prop('disabled', !balanced);
        }

        $('#btn_add_row').click(function(e) {
            e.preventDefault();
            $('#lines_wrapper').append($('#line_template').html());
            renumberRows();
            $('#lines_wrapper tr').last().find('._select2').select2();
        });

        $(document).on('click', '.btn-delete', function() {
            if ($('#lines_wrapper tr').length <= 1) {
                toastr.warning('A voucher needs at least one line.', 'Warning');
                return;
            }
            $(this).closest('tr').remove();
            renumberRows();
            updateTotals();
        });

        $(document).on('input', '.line-debit', function() {
            if ($(this).val()) {
                $(this).closest('tr').find('.line-credit').val('');
            }
            updateTotals();
        });

        $(document).on('input', '.line-credit', function() {
            if ($(this).val()) {
                $(this).closest('tr').find('.line-debit').val('');
            }
            updateTotals();
        });

        $(document).on('change', '.line-account', function() {
            updateTotals();
        });

        $('#voucher_form').on('submit', function(e) {
            $('#lines_wrapper tr').each(function() {
                var $row = $(this);
                var filled = $row.find('.line-account').val() || $row.find('.line-debit').val() ||
                    $row.find('.line-credit').val() || $row.find('.line-description').val();
                if (!filled) {
                    $row.remove();
                }
            });
            renumberRows();

            if ($('#lines_wrapper tr').length < 2) {
                e.preventDefault();
                e.stopImmediatePropagation();
                toastr.error('A voucher needs at least two journal lines.', 'Error', {
                    timeOut: 5000,
                    extendedTimeOut: 0,
                    closeButton: true,
                    closeDuration: 0
                });
            }
        });

        updateTotals();
    });
</script>
