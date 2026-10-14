<div class="modal fade" id="add_stock_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px" tabindex="-1">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <x-icon name="x" :size="18" />
                    </span>
                </div>
            </div>
            <div class="modal-body px-10 px-lg-15 pt-0 pb-15" id="dropdown_parent">
                <form data-ajax="true" id="kt_modal_new_target_form" class="form"
                    action="{{ route('purchase-orders.store') }}" method="POST">
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Purchase Order</h1>
                    </div>
                    <div class="row g-9 mb-8">
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
                                <x-icon name="plus" size="14" class="me-1" /> Add
                            </button>
                        </div>
                        <div class="col-md-12 fv-row">
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
                        <div class="col-sm-12">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input name="isPaid" class="form-check-input" type="checkbox" value="true"
                                    id="add_stock_switch">
                                <label class="form-check-label" for="add_stock_switch">
                                    Mark Paid
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="reset" id="add_stock_modal_cancel" data-bs-dismiss="modal"
                            class="btn btn-light me-3">Cancel</button>
                        <button type="submit" id="kt_modal_new_target_submit" class="btn btn-primary">
                            <span class="indicator-label">Save</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
