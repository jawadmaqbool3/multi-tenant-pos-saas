<div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>Thumbnail</h2>
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
            <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3"
                data-kt-image-input="true">
                <div class="image-input-wrapper w-150px h-150px"
                    style="background-image: url('{{ @$supplier ? asset('/assets/media/suppliers/thumbs/') . '/' . $supplier->thumbnail : '' }}')">
                </div>
                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                    <x-icon name="pencil" size="14" />
                    <input type="file" name="thumbnail" accept=".png, .jpg, .jpeg" />
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
            <div class="text-muted fs-7">Set the category thumbnail image. Only *.png, *.jpg and *.jpeg
                image files are accepted</div>
        </div>
    </div>
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>Status</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <select name="status" class="form-select mb-2" data-control="select2" data-hide-search="true"
                data-placeholder="Select an option" id="status" onchange="">
                <option></option>
                <option data-status-sign="bg-success" {{ @$supplier->status == 'active' ? 'selected' : '' }}
                    value="active" selected="selected">
                    Active</option>
                <option data-status-sign="bg-danger" {{ @$supplier->status == 'inactive' ? 'selected' : '' }}
                    value="inactive">Inactive
                </option>
            </select>
            <div class="text-muted fs-7">Set the supplier status.</div>
        </div>
    </div>
</div>
<div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>General</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="mb-10 fv-row">
                <label class="required form-label">Supplier Name</label>
                <input type="text" name="name" value="{{ @$supplier->name }}" class="form-control mb-2"
                    placeholder="Supplier name" />
                <div class="text-muted fs-7">A Supplier name is required and recommended to be unique.
                </div>
            </div>
        </div>
    </div>

    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>Contact Information</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="mb-10 fv-row">
                <label class="required form-label">Phone#</label>
                <input type="text" name="phone_no" value="{{ @$supplier->phone_no }}" class="form-control mb-2"
                    placeholder="Phone#" />
            </div>
            <div class="mb-10 fv-row">
                <label class="required form-label">WhatsApp#</label>
                <input type="text" name="whatsapp_no" value="{{ @$supplier->whatsapp_no }}" class="form-control mb-2"
                    placeholder="WhatsApp#" />
            </div>
            <div class="mb-10 fv-row">
                <label class="required form-label">Cell#</label>
                <input type="text" name="cell_no" value="{{ @$supplier->cell_no }}" class="form-control mb-2"
                    placeholder="Cell#" />
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('suppliers.index') }}" id="kt_ecommerce_add_supplier_cancel"
            class="btn btn-light me-5">Cancel</a>
        <button type="submit" id="kt_ecommerce_add_category_submit" class="btn btn-primary">
            <span class="indicator-label">Save Changes</span>
            <span class="indicator-progress">Please wait...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </div>
</div>
