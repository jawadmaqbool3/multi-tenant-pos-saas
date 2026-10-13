<div class="modal fade" id="place_order_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px" tabindex="-1">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="6" y="17.3137" width="16" height="2"
                                rx="1" transform="rotate(-45 6 17.3137)" fill="currentColor" />
                            <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                transform="rotate(45 7.41422 6)" fill="currentColor" />
                        </svg>
                    </span>
                </div>
            </div>
            <div class="modal-body px-10 px-lg-15 pt-0 pb-15" id="dropdown_parent">
                <form data-ajax="true" id="kt_modal_new_target_form" class="form" action="{{ route('orders.store') }}"
                    method="POST">
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Place Order</h1>
                    </div>
                    <div class="row g-9 mb-8">
                        <div class="col-sm-8">
                            <div class="mb-3">
                                <label for="" class="form-label">Reference</label>
                                <input name="reference" placeholder="some reference (optional)" type="text"
                                    id="reference" class="form-control form-control-solid">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label d-none d-sm-block">&nbsp;</label>
                            <button type="button" class="btn btn-light-primary w-100 btn-add-product">
                                <i class="bi bi-plus-lg me-1"></i> Add
                            </button>
                        </div>
                        <div class="col-md-12 fv-row">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="product_wrapper"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Grand Total</td>
                                        <td colspan="2"><input disabled type="number"
                                                class="form-control form-control-solid" value="0" id="total"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch form-check-custom form-check-solid ">
                                <input name="isPaid" class="form-check-input payment-check" type="checkbox"
                                    value="true" id="place_order_switch">
                                <label class="form-check-label" for="place_order_switch">
                                    Mark Paid
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12 d-none" id="payment_method">
                            <x-payment-method-selector />
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="reset" id="place_order_modal_cancel" data-bs-dismiss="modal"
                            class="btn btn-light mx-3">Cancel</button>
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
                </form>
            </div>
        </div>
    </div>
</div>
