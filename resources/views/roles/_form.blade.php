<div class="row">
    <div class="col-md-4">
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 mb-7 me-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Name</h2>
                    </div>
                </div>
                <div class="card-body text-left pt-0">
                    <div>
                        <div class="mb-3">
                            <input value="{{ @$role->name }}" type="text" name="name" id=""
                                class="form-control" placeholder="Role Name">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                    <div class="card-toolbar">
                        <div class="rounded-circle 
                        @if (@$role->status == 'active') bg-success
                        @elseif(@$role->status == 'inactive')
                        bg-warning
                        @else
                        bg-success @endif          
                         w-15px h-15px"
                            id="status_sign">
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <select name="status" class="form-select mb-2" data-control="select2" data-hide-search="true"
                        data-placeholder="Select an option" id="status" onchange="">
                        <option></option>
                        <option data-status-sign="bg-success" {{ @$role->status == 'active' ? 'selected' : '' }}
                            value="active" selected="selected">
                            Active</option>
                        <option data-status-sign="bg-warning" {{ @$role->status == 'inactive' ? 'selected' : '' }}
                            value="inactive">Inactive</option>

                    </select>
                    <div class="text-muted fs-7">Set the role status.</div>

                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Permissions</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <x-permission-selector :role="@$role" />
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <a href="{{ route('roles.index') }}" id="kt_ecommerce_add_product_cancel"
                    class="btn btn-light me-5">Cancel</a>
                <button type="submit" id="kt_ecommerce_add_category_submit" class="btn btn-primary">
                    <span class="indicator-label">Save Changes</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>
