<div class="row">
    <div class="col-md-4 ">
        <div class="card card-flush py-4 mb-8">
            <div class="card-header">
                <div class="card-title">
                    <h2>Logo</h2>
                </div>
            </div>
            <div class="card-body text-center pt-0">
                <style>
                    .image-input-placeholder {
                        background-image: url('{{ asset('assets/media/svg/files/blank-image.svg') }}');
                    }

                    [data-theme="dark"] .image-input-placeholder {
                        background-image: url('{{ asset('assets/media/svg/files/blank-image-dark.svg') }}');
                    }
                </style>
                <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-8"
                    data-kt-image-input="true">
                    <div class="image-input-wrapper w-150px h-150px"
                        style="background-image: url('{{ @$company ? asset('assets/media/users/profile/') . '/' . $company->setting->image : '' }}')">
                    </div>
                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                        data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                        <x-icon name="pencil" size="14" />
                        <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                        <input type="hidden" name="avatar_remove" />
                    </label>
                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                        data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                        <x-icon name="x" size="16" />
                    </span>
                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                        data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                        <x-icon name="x" size="16" />
                    </span>
                </div>
                <div class="text-muted fs-7">Set the user profile image. Only *.png, *.jpg and *.jpeg
                    image files are accepted</div>
            </div>
        </div>
        <div class="card card-flush py-4 mb-8">
            <div class="card-header">
                <div class="card-title">
                    <h2>Status</h2>
                </div>
                <div class="card-toolbar">
                    <div class="rounded-circle 
                    @if (@$company->setting->status == 'active') bg-success
                    @elseif(@$company->setting->status == 'inactive')
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
                    <option data-status-sign="bg-success" {{ @$company->setting->status == 'active' ? 'selected' : '' }}
                        value="active" selected="selected">
                        Active</option>
                    <option data-status-sign="bg-warning"
                        {{ @$company->setting->status == 'inactive' ? 'selected' : '' }} value="inactive">Inactive
                    </option>

                </select>
                <div class="text-muted fs-7">Set the role status.</div>

            </div>
        </div>
        @if (auth()->user()->hasRole('admin'))
            <div class="card card-flush p-4 my-5">
                <div class="col-md-12">
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input name="isManager" class="form-check-input" type="checkbox" value="1" id="isManager">
                        <label class="form-check-label" for="isManager">
                            Is Manager?
                        </label>
                    </div>
                </div>
            </div>
        @endif
        <div class="card card-flush py-4 mb-8">
            <div class="card-header">
                <div class="card-title">
                    <h2>Roles</h2>
                </div>
            </div>
            <div class="card-body pt-0 w-100">
                <x-role-selector :company="@$company" />
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-flush py-4 mb-8">
            <div class="card-header">
                <div class="card-title">
                    <h2>Comapany Information</h2>
                </div>
            </div>
            <div class="card-body">
                <div class="fv-row">
                    <label class="required form-label">Name</label>
                    <input type="text" name="company_name" value="{{ @$company->setting->name }}"
                        class="form-control mb-2" placeholder="Company Name" required />
                    <div class="text-muted fs-7">A user name is required.
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="fv-row">
                    <label class="required form-label">Email</label>
                    <input type="email" name="company_email" value="{{ @$company->setting->email }}"
                        class="form-control mb-2" placeholder="Company Email" />
                    <div class="text-muted fs-7">An email is required.
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="fv-row">
                    <label class="form-label">Address</label>
                    <input type="text" name="company_address" value="{{ @$company->setting->email }}"
                        class="form-control mb-2" placeholder="Company Address" />
                </div>
            </div>
        </div>

        <div class="card card-flush py-4 mb-8">
            <div class="card-header">
                <div class="card-title">
                    <h2>Admin Information</h2>
                </div>
            </div>
            <div class="card-body my-3">
                <div class="fv-row">
                    <label class="required form-label">Name</label>
                    <input type="text" name="name" value="{{ @$ad->name }}" class="form-control mb-2"
                        placeholder="User name" required />
                    <div class="text-muted fs-7">A user name is required.
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="fv-row">
                    <label class="required form-label">Email</label>
                    <input type="email" name="email" value="{{ @$company->setting->email }}"
                        class="form-control mb-2" placeholder="User Email" required />
                    <div class="text-muted fs-7">An email is required.
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="row">
                    <div class="col-md-6 my-3"> <label class="required form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control mb-2"
                            placeholder="Password" @if (@$company == false) required @endif />
                        <div class="text-muted fs-7">A password is required.
                        </div>
                    </div>
                    <div class="col-md-6 my-3"> <label class="required form-label">Confirm Password</label>
                        <input type="password" id="co_password" name="co_password" class="form-control mb-2"
                            placeholder="Confirm Password" @if (@$company == false) required @endif />
                        <div class="text-muted fs-7">Confirm your password.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-flush py-4 mb-8">
            <div class="card-header">
                <div class="card-title">
                    <h2>Attach Permissions</h2>
                </div>
            </div>
            <div class="card-body my-3">
                <x-permission-selector :company="@$company" />
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <a href="{{ route('companies.index') }}"
                id="kt_ecommerce_add_product_cancel" class="btn btn-light me-5">Cancel</a>
            <button type="submit" id="kt_ecommerce_add_category_submit" class="btn btn-primary">
                <span class="indicator-label">Save Changes</span>
                <span class="indicator-progress">Please wait...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
    </div>
</div>
