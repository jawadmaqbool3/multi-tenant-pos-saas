<script>
    $(document).ready(function() {
        $('#create_modal ._select2').select2({
            dropdownParent: $('#create_modal #dropdown_parent')
        });
        $('#edit_modal ._select2').select2({
            dropdownParent: $('#edit_modal #dropdown_parent')
        });

        $('.account-selector').change(function() {
            let level = $(this).find(':selected').data('level');
            $(this).parents('.modal').find('input.level').val(level);
        });

        const fetchAccounts = () => {
            $.ajax({
                url: window.location.href,
                type: 'GET',
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.success) {
                        $('#tree_wrapper').html(response.tree);
                    } else {
                        toastr.error(
                            "Failed to load accounts", {
                                timeOut: 5000,
                                extendedTimeOut: 0,
                                closeButton: true,
                                closeDuration: 0
                            }
                        );
                    }
                },
                error: function(response) {
                    toastr.error(
                        "Failed to load accounts", {
                            timeOut: 5000,
                            extendedTimeOut: 0,
                            closeButton: true,
                            closeDuration: 0
                        }
                    );
                },

            });
        }
        fetchAccounts();
        document.addEventListener("table_reload", function() {
            fetchAccounts();
        });
    });
</script>
