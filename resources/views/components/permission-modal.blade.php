@if ($operation == 'update')
    <div class="modal fade" id="edit_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <form data-ajax="true" class="form" id="kt_modal_new_address_form" method="post"
                    data-base-url="{{ url('permissions/update') }}/">
                    @method('PUT')
                    <div class="modal-header" id="kt_modal_new_address_header">
                        <h2 id="modal_title">Edit Permission</h2>
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <span class="svg-icon svg-icon-1">
                                <x-icon name="x" :size="18" />
                            </span>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17"  id="dropdown_parent">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="" class="form-label">Name</label>
                                    <input type="text" name="name" id="modal_name"
                                        class="form-control form-control-solid" placeholder="Permission Name"
                                        aria-describedby="helpId" required>
                                </div>
                            </div>
                            <div class="col-md-12 mt-5">
                                <div class="mb-3">
                                    <label for="" class="form-label">Module</label>
                                    <select type="text" name="module" id="modal_module"
                                        class="form-control form-control-solid _select2" required>
                                        @foreach ($modules as $module)
                                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                                        @endforeach
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

                            <span class="indicator-label">Update</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@else
    <div class="modal fade" id="create_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <form data-ajax="true" class="form" id="kt_modal_new_address_form" method="post"
                    data-base-url="{{ route('permissions.store') }}">
                    <div class="modal-header" id="kt_modal_new_address_header">
                        <h2 id="modal_title">Create Permission</h2>
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <span class="svg-icon svg-icon-1">
                                <x-icon name="x" :size="18" />
                            </span>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17"  id="dropdown_parent">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="" class="form-label">Name</label>
                                    <input type="text" name="name" id="" class="form-control"
                                        placeholder="Permission Name" aria-describedby="helpId" required>
                                </div>
                            </div>
                            <div class="col-md-12 mt-5">
                                <div class="mb-3">
                                    <label for="" class="form-label">Module</label>
                                    <select type="text" name="module" id=""
                                        class="form-control form-control-solid _select2" required>
                                        @foreach ($modules as $module)
                                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                                        @endforeach
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
@endif
