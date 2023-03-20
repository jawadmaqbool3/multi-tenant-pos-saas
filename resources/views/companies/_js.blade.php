<script>
    $('#status').change(function() {
        let option = $(this).find('option:selected');
        $('#status_sign').removeClass('bg-success').removeClass('bg-danger').removeClass('bg-warning').addClass(
            option.data('statusSign'));
        if ($(this).val() == 'scheduled') {
            $('.date-selector').removeClass('d-none');
        } else {
            $('.date-selector').addClass('d-none');
        }
    });

    $('#co_password').change(function() {
        if ($('#password').val() != $(this).val()) {
            $(this).val('');
            toastr.error(
                'Password Mismatch',
                "Reset Preview!", {
                    timeOut: 5000,
                    extendedTimeOut: 0,
                    closeButton: true,
                    closeDuration: 0
                }
            );
        }
    });

    $('.check-all').change(function() {
        let target = $(this).data('target');
        if ($(this).is(':checked')) {
            $(target).prop('checked', true);
        } else {
            $(target).prop('checked', false);
        }
    });

    $('.permission-check').change(function() {
        let target = $(this).data('target');
        let check = true;
        $(`.${target}`).each(function() {
            if ($(this).is(':checked') == false) {
                check = false;
                return;
            }
        });
        if (check) {
            $(`[data-target=".${target}"]`).prop('checked', true);
        } else {
            $(`[data-target=".${target}"]`).prop('checked', false);
        }
    });
</script>
