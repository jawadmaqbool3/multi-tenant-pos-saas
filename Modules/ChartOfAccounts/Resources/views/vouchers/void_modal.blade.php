<div class="modal fade" id="void_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form data-ajax="true" class="form" method="post" data-base-url="{{ url('vouchers/void') }}/">
                <div class="modal-header">
                    <h2 id="modal_title">Void Voucher</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <x-icon name="x" :size="18" />
                        </span>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                        <span class="svg-icon svg-icon-2tx svg-icon-warning me-4">
                            <x-icon name="alert-triangle" :size="26" />
                        </span>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Warning</h4>
                                <div class="fs-6 text-gray-700">Are you sure you want to void <strong
                                        id="modal_name"></strong>? This will reverse its balance impact and exclude
                                    it from all reports. This cannot be undone.</div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="modal_id">
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button data-bs-dismiss="modal" type="submit" class="btn btn-danger">
                        <span class="indicator-label">Void Voucher</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
