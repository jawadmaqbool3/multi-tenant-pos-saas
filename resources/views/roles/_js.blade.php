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
    $('.check-all').change(function() {
        let target = $($(this).data('target'));
        if ($(this).prop('checked')) {
            $(target).find('.form-check-input').each(function() {
                $(this).prop('checked', true);
            });
        } else {
            $(target).find('.form-check-input').each(function() {
                $(this).prop('checked', false);
            });
        }

    })
</script>
