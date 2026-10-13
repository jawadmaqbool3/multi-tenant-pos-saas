<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
<script src="{{ asset('assets/js/ui-charts.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/vis-timeline/vis-timeline.bundle.js') }}"></script>
<script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
<script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/users-search.js') }}"></script>

<script>
    function convertToAbbreviation(num) {
        if (num >= 1e9) {
            return (num / 1e9).toFixed(1) + "B";
        } else if (num >= 1e6) {
            return (num / 1e6).toFixed(1) + "M";
        } else if (num >= 1e3) {
            return (num / 1e3).toFixed(1) + "k";
        }
        return num;
    }



    $(document).ready(function() {


        function formatAMPM(date) {
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0' + minutes : minutes;
            var strTime = hours + ':' + minutes + ' ' + ampm;
            return strTime;
        }



        $(document).on('submit', 'form', function(e) {
            if ($(this).data('ajax')) {
                e.preventDefault();
                var selector = $(this).find('button[type="submit"]');
                selector.attr('disabled', true);
                let url = $(this).prop('action');
                let method = $(this).prop('method') ?? 'GET';
                let _token = "{{ csrf_token() }}"
                var formData = new FormData($(this)[0]);
                formData.append('_token', _token);
                $(this).find('.quill-input').each(function() {
                    let name = $(this).data('name');
                    let description = $(this).find('.ql-editor').html();
                    formData.append(name, description);
                });
                $.ajax({
                    url: url,
                    data: formData,
                    type: method,
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {

                            toastr.success(
                                response.message,
                                "Success", {
                                    timeOut: 5000,
                                    extendedTimeOut: 0,
                                    closeButton: true,
                                    closeDuration: 0
                                }
                            );
                        } else if (response.error) {

                            toastr.error(
                                response.message,
                                "Error", {
                                    timeOut: 5000,
                                    extendedTimeOut: 0,
                                    closeButton: true,
                                    closeDuration: 0
                                }
                            );
                        } else if (response.warning) {
                            toastr.warning(
                                response.message,
                                "warning", {
                                    timeOut: 5000,
                                    extendedTimeOut: 0,
                                    closeButton: true,
                                    closeDuration: 0
                                }
                            );
                        }
                        if (response.reload) {
                            window.location.reload();
                        }
                        if (response.redirect) {
                            window.location.href = response.url;
                        }
                        if (response.table_reload) {
                            const tableReload = new Event("table_reload");
                            document.dispatchEvent(tableReload)
                        }
                        if (response.event) {
                            const event = new Event(response.event.name);
                            document.dispatchEvent(event)
                        }
                    },
                    error: function(response) {
                        if (response.status == 422) {
                            const errors = response.responseJSON.errors;
                            for (const key in errors) {
                                $(`[name="${key}"]`).addClass('border-danger');
                                if (errors.hasOwnProperty.call(errors, key)) {
                                    const error = errors[key];
                                    toastr.error(
                                        error[0],
                                        key.toUpperCase(), {
                                            timeOut: 5000,
                                            extendedTimeOut: 0,
                                            closeButton: true,
                                            closeDuration: 0
                                        }
                                    );
                                }
                            }
                        } else {
                            toastr.error(
                                response.message,
                                "Error", {
                                    timeOut: 5000,
                                    extendedTimeOut: 0,
                                    closeButton: true,
                                    closeDuration: 0
                                }
                            );
                        }

                    },
                    complete: function() {
                        selector.removeAttr('disabled');
                    }
                });
            }
        });

        $(document).on('click', '.modal-button', function() {

            var attributes = $(this).data();
            let modal = $($(this).data('bsTarget'));
            let url = $(this).data('baseUrl');
            let currentURL = modal.find('form').data('baseUrl');
            if (!url) {
                url = currentURL + $(this).data('modal_id');
            }
            modal.find('form').attr('action', url);
            for (const key in attributes) {
                if (Object.hasOwnProperty.call(attributes, key)) {
                    const element = attributes[key];
                    const target = modal.find('#' + key);
                    if (target.prop('tagName') == 'INPUT' || target.prop('tagName') == 'SELECT') {
                        target.val(element).change();
                    } else {
                        target.html(element);
                    }
                }
            }
        });
    });
</script>
