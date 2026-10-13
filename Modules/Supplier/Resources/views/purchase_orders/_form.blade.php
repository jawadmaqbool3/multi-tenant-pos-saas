<div class="row g-9 mb-8" id="add_stock_modal">
    <div class="col-sm-5">
        <div class="mb-3">
            <label for="" class="form-label">Suppliers</label>
            <select name="supplier" id="supplier" class="_select2 form-control form-control-solid">
                <option value="">Select Supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->uid }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-sm-5">
        <div class="mb-3">
            <label for="" class="form-label">Reference</label>
            <input name="reference" placeholder="some reference (optional)" type="text" id="reference"
                class="form-control form-control-solid">
        </div>
    </div>
    <div class="col-sm-2">
        <label class="form-label d-none d-sm-block">&nbsp;</label>
        <button type="button" class="btn btn-light-primary w-100 btn-add-product">
            <i class="bi bi-plus-lg me-1"></i> Add
        </button>
    </div>
    <div class="col-md-12 fv-row" id="dropdown_parent">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Purchase</th>
                    <th>Retail</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody id="product_wrapper"></tbody>
        </table>
    </div>
    <div class="col-md-12">
        <div class="form-check form-switch form-check-custom form-check-solid">
            <input name="isPaid" class="form-check-input" type="checkbox" value="true" id="isSwitchPaid">
            <label class="form-check-label" for="isSwitchPaid">
                Mark Paid
            </label>
        </div>
    </div>
    <div class="col-md-12">
        <div class="d-flex justify-content-end">
            <a href="{{ route('products.index') }}" id="kt_ecommerce_add_product_cancel"
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
