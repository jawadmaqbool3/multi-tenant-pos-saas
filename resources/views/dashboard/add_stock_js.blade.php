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
        <td width="40%" class="p-0"><select name="product[]" id=""
                class="select2_ form-control product  rounded-0">${html}</select></td>
        <td class="rounded-0 p-0"><input type="number" name="quantity[]" placeholder="Quantity" id=""
                class="rounded-0 form-control  quantity"></td>
        <td class="p-0"><input type="number" placeholder="Purchase" name="purchase[]" id=""
                class="form-control rounded-0 purchase"></td>
        <td class="p-0"><input type="number" placeholder="Retail" name="retail[]" id=""
                class="form-control rounded-0 retail"></td>
        <td class="p-0"><button type="button" class="btn-danger btn-delete btn px-1 rounded-0"><i
                    class="bi bi-trash fs-2 fw-bolder"></i></button></td>
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
