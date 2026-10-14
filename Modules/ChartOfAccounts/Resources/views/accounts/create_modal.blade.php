<div class="modal fade" id="create_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form data-ajax="true" class="form" id="kt_modal_new_address_form" method="post"
                action="{{ route('accounts.store') }}">
                <div class="modal-header" id="kt_modal_new_address_header">
                    <h2 id="modal_title">Create Account</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <x-icon name="x" :size="18" />
                        </span>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17" id="dropdown_parent">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <input type="hidden" class="level" name="level">
                                <label for="" class="form-label">Name</label>
                                <input type="text" name="name" id="modal_name"
                                    class="form-control form-control-solid" placeholder="Account Name"
                                    aria-describedby="helpId" required>
                            </div>
                        </div>
                        <div class="col-sm-8 mt-5">
                            <div class="mb-3">
                                <label for="" class="form-label">Parent Account</label>
                                <x-account-selector selectLevel="level3" selected="all" :account="$accounts"
                                    isRequired="true" accountLevel="all" class="account-selector" id=""
                                    name="account" />
                            </div>
                        </div>
                        <div class="col-sm-4 mt-5">
                            <div class="mb-3">
                                <label for="" class="form-label">Status</label>
                                <select name="status" class="_select2 form-control form-control-solid">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" id="kt_modal_new_target_cancel" class="btn btn-light me-3"
                        data-bs-dismiss="modal">Cancel</button>
                    <button data-bs-dismiss="modal" type="submit" id="kt_modal_new_address_submit"
                        class="btn btn-primary">

                        <span class="indicator-label">Create</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
