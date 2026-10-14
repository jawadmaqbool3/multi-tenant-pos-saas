    <form data-ajax="true"  class="form" action="{{ route('orders.store') }}" method="POST">
        <div class="row g-9 mb-8" id="place_order_modal">
            <div class="col-sm-8">
                <div class="mb-3">
                    <label for="" class="form-label">Reference</label>
                    <input name="reference" placeholder="some reference (optional)" type="text" id="reference"
                        class="form-control form-control-solid">
                </div>
            </div>
            <div class="col-sm-4">
                <button type="button" class="mt-9 float-end btn  btn-primary btn-add-product"><x-icon
                        name="plus" size="16" /></button>
            </div>
            <div class="col-md-12 fv-row" id="dropdown_parent">
                <table class="table">
                    <tr>
                        <thead>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </thead>
                        <tbody id="product_wrapper">

                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><input disabled type="number" class="form-control form-control-solid"
                                        value="0" id="total"></td>
                            </tr>
                        </tfoot>
                    </tr>
                </table>
            </div>
            <div class="col-md-12">
                <div class="form-check form-switch form-check-custom form-check-solid ">
                    <input name="isPaid" class="form-check-input payment-check" type="checkbox" value="true"
                        id="place_order_switch">
                    <label class="form-check-label" for="place_order_switch">
                        Mark Paid
                    </label>
                </div>
            </div>
            <div class="col-md-12 d-none" id="payment_method">
                <x-payment-method-selector />
            </div>
            <div class="col-md-12">
                <div class="text-end">
                    <a href="{{ route('dashboard') }}" class="btn btn-light mx-3">Cancel</a>
                    <button type="submit" id="kt_modal_new_target_submit"
                        onclick="$(`input[name='saveAndPrint']`).val(false)" class="btn btn-primary">
                        <span class="indicator-label">Save</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                    <input type="submit" id="print_and_submit" name="printAndSave"
                        onclick="$(`input[name='saveAndPrint']`).val(true)" class="btn btn-primary mx-3"
                        value="Save & Print">
                    <input type="hidden" name="saveAndPrint">

                </div>
            </div>
        </div>
    </form>
