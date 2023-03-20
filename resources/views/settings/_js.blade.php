<script>
    $('._select2').select2();
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
