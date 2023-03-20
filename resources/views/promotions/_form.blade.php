<div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>General</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="mb-10 row">
                <div class="col-md-6">
                    <label class="required form-label">Name</label>
                    <input type="text" name="name" value="{{ @$promotion->name }}" class="form-control mb-2"
                        placeholder="Product name" />

                </div>
                <div class="col-md-3">
                    <label class="required form-label">Start Date</label>
                    <input type="date" name="start_date" value="{{ @$promotion->start_date }}"
                        class="form-control mb-2" placeholder="Product name" />

                </div>
                <div class="col-md-3">
                    <label class="required form-label">End Date</label>
                    <input type="date" name="end_date" value="{{ @$promotion->end_date }}" class="form-control mb-2"
                        placeholder="Product name" />
                </div>
            </div>
            <div class="mb-10 fv-row table-responsive">
                <table class="table table-border">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Percentage</th>
                            <th>Amount</th>
                            <th><button class="btn btn-success float-end" id="btn_add_row"><i
                                        class="bi bi-plus fs-3"></i></button></th>
                        </tr>
                    </thead>
                    <tbody id="products_wrapper">
                        @if (@$promotion)
                            @foreach ($promotion->productPromotions as $promotion)
                                <tr>
                                    <td>
                                        <select name="product[]" id=""
                                            class="form-control-solid form-control product _select2">
                                            <option value="" selected disabled>Select Product</option>
                                            @foreach ($products as $product)
                                                <option @if ($promotion->product_id == $product->id) selected @endif
                                                    data-price="{{ $product->unit_price }}" value="{{ $product->uid }}">
                                                    {{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input value="{{ $promotion->percentage }}" type="number" step="0.01"
                                            name="percentage[]" placeholder="Discount percentage"
                                            class="form-control form-control-solid percentage">
                                    </td>
                                    <td>
                                        <input value="{{ $promotion->amount }}" type="number" step="0.01"
                                            name="amount[]" placeholder="Discount amount"
                                            class="form-control form-control-solid amount">
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-danger btn-delete"><i class="fs-3 bi bi-x"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        <tr>
                            <td>
                                <select name="product[]" id=""
                                    class="form-control-solid form-control product _select2">
                                    <option value="" selected disabled>Select Product</option>
                                    @foreach ($products as $product)
                                        <option data-price="{{ $product->unit_price }}" value="{{ $product->uid }}">
                                            {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="percentage[]"
                                    placeholder="Discount percentage"
                                    class="form-control form-control-solid percentage">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="amount[]" placeholder="Discount amount"
                                    class="form-control form-control-solid amount">
                            </td>
                            <td class="text-end">
                                <button class="btn btn-danger btn-delete" disabled><i class="fs-3 bi bi-x"></i></button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('promotions.index') }}" id="kt_ecommerce_add_product_cancel"
            class="btn btn-light me-5">Cancel</a>
        <button type="submit" id="kt_ecommerce_add_category_submit" class="btn btn-primary">
            <span class="indicator-label">Save Changes</span>
            <span class="indicator-progress">Please wait...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </div>
</div>
