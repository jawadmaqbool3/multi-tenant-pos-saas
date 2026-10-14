<div class="modal fade" id="pay_supplier_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px" tabindex="-1">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <x-icon name="x" :size="18" />
                    </span>
                </div>
            </div>
            <div class="modal-body px-10 px-lg-15 pt-0 pb-15" id="dropdown_parent">
                <form data-ajax="true" id="kt_modal_new_target_form" class="form"
                    data-base-url="{{ url('supplier/pay') }}/" method="POST">
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Supplier Payment</h1>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="modal_balance" class="form-label">Amount</label>
                                <input type="number" step="0.1" value=""
                                    class="form-control form-control-solid" id="modal_balance" name="amount" required>
                                <input type="hidden" id="modal_max">
                                <input type="hidden" id="modal_id">
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="reset" id="pay_supplier_modal_cancel" data-bs-dismiss="modal"
                            class="btn btn-light me-3">Cancel</button>
                        <button type="submit" id="pay_supplier_modal_submit" data-bs-dismiss="modal" class="btn btn-primary">
                            <span class="indicator-label">Pay</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
