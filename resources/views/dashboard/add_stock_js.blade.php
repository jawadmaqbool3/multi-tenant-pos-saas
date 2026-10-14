    function addStockRow() {
    let html = '<option>Select Option</option>';
    for (const key in products) {
    let product = products[key];
    html += `<option value="${product.uid}" data-product-no="${product.code}" data-unit-price="${product.unit_price}"
        data-purchase-price="${product.purchase_price}">
        ${product.name}
    </option>`;
    }


    $('#add_stock_modal #product_wrapper').append(
    `<tr>
        <td style="min-width: 220px;"><select name="product[]" id=""
                class="select2_ form-control product">${html}</select></td>
        <td><input type="number" name="quantity[]" placeholder="Quantity" id=""
                class="form-control quantity"></td>
        <td><input type="number" placeholder="Purchase" name="purchase[]" id=""
                class="form-control purchase"></td>
        <td><input type="number" placeholder="Retail" name="retail[]" id=""
                class="form-control retail"></td>
        <td class="text-end"><button type="button" title="Remove" id=""
                class="btn btn-icon btn-sm btn-light-danger btn-delete"><x-icon name="trash" size="16" /></button></td>
    </tr>`
    );
    $('#add_stock_modal #product_wrapper .product').each(function() {
    if ($(this).hasClass('.select2-hidden-accessible') == false) {
    $(this).select2({
    dropdownParent: $('#add_stock_modal #dropdown_parent')
    });
    }
    });
    }
    $(document).on('click', '#add_stock_modal .btn-add-product', function() {
    addStockRow(false);
    });
    $(document).on('click', '#add_stock_modal .btn-delete', function() {
    $(this).parents('tr').remove();
    });
    $(document).on('change', '#add_stock_modal .product', function() {
    let unitPrice = $(this).find('option:selected').data('unitPrice');
    let purchasePrice = $(this).find('option:selected').data('purchasePrice');
    if(purchasePrice || unitPrice)
    {
    $(this).parents('tr').find('.purchase').val(purchasePrice);
    $(this).parents('tr').find('.retail').val(unitPrice);
    }

    {{-- $(this).parents('tr').find('.total').val(total); --}}
    });
