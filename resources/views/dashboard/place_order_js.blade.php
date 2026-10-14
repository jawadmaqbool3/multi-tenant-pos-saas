   

    function addOrderRow() {
        let html = '<option>Select Option</option>';

        for (const key in products) {
            let product = products[key];
            html += `<option value="${product.uid}" data-product-no="${product.code}" data-unit-price="${product.unit_price}"
            data-max-quantity="${product.stock}" data-purchase-price="${product.purchase_price}">
            ${product.name}
        </option>`;
        }
        $('#place_order_modal #product_wrapper').append(
            `<tr>
            <td style="min-width: 220px;"><select name="product[]" id=""
                    class="select2_ form-control product">${html}</select></td>
            <td><input type="number" name="quantity[]" placeholder="Quantity" id=""
                    class="form-control quantity"></td>
            <td><input type="number" placeholder="Price" name="price[]" id=""
                    class="form-control price"></td>
            <td><input type="number" placeholder="Total" name="total[]" id=""
                    class="form-control total"></td>
            <td class="text-end"><button type="button" title="Remove" id=""
                    class="btn btn-icon btn-sm btn-light-danger btn-delete"><x-icon name="trash" size="16" /></button></td>
        </tr>`
        );
        $('#place_order_modal #product_wrapper .product').each(function() {
            if ($(this).hasClass('.select2-hidden-accessible') == false) {
                $(this).select2({
                    dropdownParent: $('#place_order_modal #dropdown_parent')
                });
            }
        });
    }
    $(document).on('click', '#place_order_modal .btn-add-product', function() {
        addOrderRow(false);
    });
    $(document).on('click', '#place_order_modal .btn-delete', function() {
        $(this).parents('tr').remove();
    });
    $(document).on('change', '#place_order_modal .product, #place_order_modal .quantity, #place_order_modal .price',
        function() {
            let unitPrice = 0;
            let quantity = 0;
            if ($(this).hasClass('product')) {
                unitPrice = $(this).parents('tr').find('.product option:selected').data('unitPrice');
                $(this).parents('tr').find('.price').val(unitPrice);
            } else {
                if ($(this).hasClass('quantity')) {
                    quantity = $(this).parents('tr').find('.quantity').val();
                    let maxQuantity = $(this).parents('tr').find('.product option:selected').data('maxQuantity');
                    if (quantity > maxQuantity) {
                        $(this).parents('tr').find('.quantity').val(maxQuantity);
                        toastr.warning(
                            'Cannot Exceed then available stock',
                            "Warning", {
                                timeOut: 5000,
                                extendedTimeOut: 0,
                                closeButton: true,
                                closeDuration: 0
                            }
                        );
                    }
                }
                unitPrice = $(this).parents('tr').find('.price').val();
            }
            quantity = $(this).parents('tr').find('.quantity').val();
            let total = quantity * unitPrice;
            $(this).parents('tr').find('.total').val(total ? total : 0);
            calculateTotal();
        });

   

    function calculateTotal() {
        let total = 0;
        $('#place_order_modal .total').each(function() {
            total += Number($(this).val());
        });
        $('#total').val(total);
    }
