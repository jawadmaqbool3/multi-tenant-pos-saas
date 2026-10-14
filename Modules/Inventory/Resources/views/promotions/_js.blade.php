<script>
    $(document).ready(function() {
        $('._select2').select2();
        $('#btn_add_row').click(function(e) {
            e.preventDefault();
            $('#products_wrapper').append(`
            <tr>
                            <td>
                                <select name="product[]" id=""
                                    class="form-control-solid form-control product _select2">
                                    <option value="" selected disabled>Select Product</option>
                                    @foreach ($products as $product)
                                        <option data-price="{{$product->unit_price}}" value="{{ $product->uid }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="percentage[]"
                                    placeholder="Discount percentage"
                                    class="form-control form-control-solid percentage">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="amount[]" placeholder="Discount amount"
                                    class="form-control form-control-solid amount">
                            </td>
                            <td class="text-end">
                                <button class="btn btn-danger btn-delete"><x-icon name="x" size="16" /></button>
                            </td>
                        </tr>
            `);

            $('.product').each(function(){
                if($(this).hasClass('select2-hidden-accessible') == false)
                {
                    $(this).parents('tr').find('._select2').select2();
                }
            });
        });

        $(document).on('click', '.btn-delete', function () {  
            $(this).parents('tr').remove();
        });

        $(document).on('change', '.product, .percentage, .amount', function(){

            let row  = $(this).parents('tr');
            let price = row.find('.product option:selected').data('price');
            let percentage = row.find('.percentage').val();
            let amount = row.find('.amount').val();
           if($(this).hasClass('product') && price)
           {
                if(amount)
                {
                    percentage = price / $amount;
                    row.find('.percentage').val(percentage);
                }
                else if(percentage)
                {
                   amount = (price / 100) *  percentage;
                   row.find('.amount').val(amount);
                }
           }
           else if($(this).hasClass('percentage') && price){
                amount  = (price / 100) *  percentage;
                row.find('.amount').val(amount);
           }
           else if($(this).hasClass('amount') && price){
                percentage = (amount / price) * 100;
                row.find('.percentage').val(percentage);
           }
           else if(price == false)
           {
                toastr.warning(
                    'Product price is not available add percentage and amount manually',
                    "Warning", {
                        timeOut: 5000,
                        extendedTimeOut: 0,
                        closeButton: true,
                        closeDuration: 0
                    }
                );
           }
        });
    });
</script>
