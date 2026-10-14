<div class="modal fade" id="mark_paid_order_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form data-ajax="true" class="form" id="kt_modal_new_address_form" method="post" data-base-url="">
                <div class="modal-header" id="kt_modal_new_address_header">
                    <h2 id="">Mark Paid</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <x-icon name="x" :size="18" />
                        </span>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="scroll-y me-n7 pe-7" id="kt_modal_new_address_scroll" data-kt-scroll="true"
                        data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                        data-kt-scroll-dependencies="#kt_modal_new_address_header"
                        data-kt-scroll-wrappers="#kt_modal_new_address_scroll" data-kt-scroll-offset="300px">
                        <div
                            class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                            <span class="svg-icon svg-icon-2tx svg-icon-warning me-4">
                                <x-icon name="alert-triangle" :size="26" />
                            </span>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">Warning</h4>

                                    <div class="fs-6 text-gray-700">Are you sure you want to mark the order <strong
                                            id="modal_name"></strong> as paid?</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="modal_id" name="id">
                        <x-payment-method-selector/>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" id="cancel_order_modal_cancel" class="btn btn-light me-3"
                        data-bs-dismiss="modal">Cancel</button>
                    <button data-bs-dismiss="modal" type="submit" id="kt_modal_mark_paid_order"
                        class="btn btn-primary">

                        <span class="indicator-label">Mark Paid</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
