<script>
    $(document).ready(function() {
        var productID = '';

        $(document).bind("keydown", (event) => {
            if ($('#add_stock_modal').hasClass('show') ||
                $('#place_order_modal').hasClass('show')) {
                if (event.code == "Enter") {
                    selectLastProduct(productID);
                    productID = '';
                } else {
                    productID += event.key;
                }
            } else {
                productID = '';
            }
        });


        function selectLastProduct(productID) {
            var shouldContinue = false;
            for (var i = 0, len = products.length; i < len; i++) {
                if (products[i]["code"] == productID) {
                    shouldContinue = true;
                    break;
                }
            }
            if (!shouldContinue) {
                toastr.warning(
                    "Product not found.",
                    "warning", {
                        timeOut: 5000,
                        extendedTimeOut: 0,
                        closeButton: true,
                        closeDuration: 0
                    }
                );
                return;
            }
            let modal = null;
            let alreadyRegistered = [];
            @can('add stock')
                if ($('#add_stock_modal').hasClass('show')) {

                    modal = '#add_stock_modal';
                    alreadyRegistered = $('#add_stock_modal #product_wrapper .product').find(
                        `[data-product-no=${productID}]:selected`);
                }
            @endcan
            @can('place order')
                if ($('#place_order_modal').hasClass('show')) {
                    modal = '#place_order_modal';
                    alreadyRegistered = $('#place_order_modal #product_wrapper .product').find(
                        `[data-product-no=${productID}]:selected`);
                }
            @endcan
            alreadyRegistered.each(function() {
                let previousQuantity = $(this).parents('tr').find('.quantity');
                previousQuantity.val(Number(previousQuantity.val()) + 1);
                previousQuantity.change();
            });

            if (alreadyRegistered.length == 0) {
                @can('place order')
                    if (modal == '#place_order_modal') {
                        addOrderRow();
                    }
                @endcan
                @can('add stock')
                    if (modal == '#add_stock_modal') {
                        addStockRow();
                    }
                @endcan
                let lastChild = $(modal).find('#product_wrapper tr:last-child');
                lastChild.find(`.product option[data-product-no=${productID}]`).attr('selected', true);
                lastChild.find('.quantity').val(1);
                lastChild.find(`.product`).change();
            }
            productID = '';
            calculateTotal();
        }


        $('#add_stock_modal ._select2').select2({
            dropdownParent: $('#add_stock_modal #dropdown_parent')
        });
        $('#place_order_modal ._select2').select2({
            dropdownParent: $('#place_order_modal #dropdown_parent')
        });
        var products = {};

        $(document).on('click', '.btn-add-stock, .btn-place-order', function() {
            $('#add_stock_modal #product_wrapper, #place_order_modal #product_wrapper').empty();
            $('#add_stock_modal #total, #place_order_modal #total').val(0);
            getInitialParams();
        });

        function getInitialParams() {
            $.ajax({
                url: window.location.href + "?ajax=true",
                method: "GET",
                success: function(response) {
                    if (response.success) {
                        products = response.products;
                    }
                },
                error: function(response) {
                    toastr.error(
                        response.message,
                        "Error", {
                            timeOut: 5000,
                            extendedTimeOut: 0,
                            closeButton: true,
                            closeDuration: 0
                        }
                    );

                },
                complete: function() {}
            });
        }

        @can('add stock')
            @include('dashboard.add_stock_js')
        @endcan

        @can('place order')
            @include('dashboard.place_order_js')

            $(document).on('change', '.payment-check', function() {
                if ($(this).is(":checked")) {
                    $(this).parents('.modal').find('#payment_method').removeClass('d-none');
                } else {
                    $(this).parents('.modal').find('#payment_method').addClass('d-none');
                }
            });
        @endcan

        document.addEventListener("table_reload", function() {
            $('#add_stock_modal_cancel').click();
            $('#place_order_modal_cancel').click();
        });

        @if (collect($widgets)->contains(fn($w) => $w->key() === 'attendance.mine'))
            @include('employee::reports.employee.attendance_js')

            getAttendance("{{ auth()->user()->uid }}");
            $(document).on('change', '#attendance_month', function() {
                getAttendance("{{ auth()->user()->uid }}", $('#attendance_month').val());
            });
            document.addEventListener("clockin", function() {
                $('#attendance_form').html(`
                                        <input type="submit" name="clockout" class="btn-sm btn btn-danger"
                                            value="Clock out" />`);
                getAttendance("{{ auth()->user()->uid }}");

            });
            document.addEventListener("clockout", function() {
                $('#attendance_form').html(`
                                        <input type="submit" name="clockin" class="btn-sm btn btn-success"
                                            value="Clock in" />`);
                getAttendance("{{ auth()->user()->uid }}");

            });
        @endif
    });
</script>
