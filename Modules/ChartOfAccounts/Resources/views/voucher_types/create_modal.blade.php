<div class="modal fade" id="create_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form data-ajax="true" class="form" method="post" action="{{ route('voucher-types.store') }}">
                <div class="modal-header">
                    <h2 id="modal_title">Create Voucher Type</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <x-icon name="x" :size="18" />
                        </span>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="voucher_type_name" class="form-label required">Name</label>
                                <input type="text" name="name" id="modal_name"
                                    class="form-control form-control-solid" placeholder="e.g. Bank Payment Voucher"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="voucher_type_short" class="form-label required">Short</label>
                                <input type="text" name="short" id="modal_short"
                                    class="form-control form-control-solid" placeholder="e.g. BPV" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Create</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
