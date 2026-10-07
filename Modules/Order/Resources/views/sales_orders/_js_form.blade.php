<script>
    $(document).ready(function() {
        $('._select2').select2();
        var products = {};
        getInitialParams();
       
        function getInitialParams() {
            $.ajax({
                url: "{{ route('dashboard') }}?ajax=true",
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
            });
        }

        document.addEventListener("reload", function() {
            window.location.reload();
        });
        @can('place order')
            @include('dashboard.place_order_js')
        @endcan


        $(document).on('change', '.payment-check', function() {
            if ($(this).is(":checked")) {
                $('#payment_method').removeClass('d-none');
            } else {
                $('#payment_method').addClass('d-none');
            }
        });




        var productID = '';
        $(document).bind("keydown", (event) => {
            if (event.code == "Enter") {
                selectLastProduct(productID);
                productID = '';
            } else {
                productID += event.key;
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

            modal = '#place_order_modal';
            alreadyRegistered = $('#place_order_modal #product_wrapper .product').find(
                `[data-product-no=${productID}]:selected`);

            alreadyRegistered.each(function() {
                let previousQuantity = $(this).parents('tr').find('.quantity');
                previousQuantity.val(Number(previousQuantity.val()) + 1);
                previousQuantity.change();
            });

            if (alreadyRegistered.length == 0) {
                addOrderRow();
                let lastChild = $(modal).find('#product_wrapper tr:last-child');
                lastChild.find(`.product option[data-product-no=${productID}]`).attr('selected',
                    true);
                lastChild.find('.quantity').val(1);
                lastChild.find(`.product`).change();
            }
            productID = '';
        }




    });
</script>
