<div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>General</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="mb-10 fv-row">
                <label class="required form-label">Deduction Name</label>
                <input type="text" name="name" value="{{ @$deduction->name }}" class="form-control mb-2"
                    placeholder="Deduction name" required />
                <div class="text-muted fs-7">A deduction name is required and must be unique within your company.</div>
            </div>
            <div class="mb-10 fv-row">
                <label class="required form-label">Status</label>
                <select name="status" class="form-select mb-2" data-control="select2" data-hide-search="true"
                    data-placeholder="Select an option" id="status">
                    <option></option>
                    <option data-status-sign="bg-success" value="active"
                        {{ @$deduction->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option data-status-sign="bg-warning" value="inactive"
                        {{ @$deduction->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <div class="text-muted fs-7">Only active deductions are available when editing payslips.</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('deductions.index') }}" class="btn btn-light me-5">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span class="indicator-label">Save Changes</span>
            <span class="indicator-progress">Please wait...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </div>
</div>
