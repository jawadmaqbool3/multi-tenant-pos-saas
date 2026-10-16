<div class="card card-flush h-xl-100" data-widget-key="{{ $key }}">
    <div class="card-header pt-7">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-dark">{{ $widget->title() }}</span>
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center fw-bold w-md-250px">
            <div class="text-muted fs-7 me-2">Status</div>
            <select name="stock"
                class="form-select form-select-transparent text-dark fs-7 lh-1 fw-bold py-0 w-auto"
                data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px" id="status"
                data-placeholder="Select an option" data-kt-table-widget-5="filter_status">
                <option></option>
                <option value="all" selected="selected">Show All</option>
                <option value="in_stock">In Stock</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="low_stock">Low Stock</option>
            </select>
            <button class="btn btn-secondary btn-sm" id="print_stock_btn">
                <x-icon name="printer" size="16" />
            </button>
        </div>
        <div class="table-responsive mt-5">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="category_table">
                <thead>
                    <tr class="border-0">
                        <th class="fw-bold p-0">SR#</th>
                        <th class="fw-bold p-0 min-w-150px text-start">Item</th>
                        <th class="fw-bold p-0 min-w-150px text-start">Purchase Price</th>
                        <th class="fw-bold p-0 min-w-150px text-start">Status</th>
                        <th class="fw-bold p-0 min-w-150px text-start">QTY</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
