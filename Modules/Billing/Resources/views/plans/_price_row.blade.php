<div class="row price-row mb-3 align-items-center g-2">
    <div class="col-md-3">
        <select class="form-select" name="prices[{{ $priceIndex ?? 0 }}][interval]">
            <option value="month" {{ isset($price) && $price->interval == 'month' ? 'selected' : '' }}>Monthly</option>
            <option value="year" {{ isset($price) && $price->interval == 'year' ? 'selected' : '' }}>Yearly</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="number" min="0" step="1" class="form-control"
            name="prices[{{ $priceIndex ?? 0 }}][unit_amount]"
            value="{{ isset($price) ? $price->unit_amount : '' }}" placeholder="Amount (cents)">
    </div>
    <div class="col-md-2">
        <input type="text" maxlength="3" class="form-control text-uppercase"
            name="prices[{{ $priceIndex ?? 0 }}][currency]"
            value="{{ isset($price) ? $price->currency : ($currency ?? 'usd') }}">
    </div>
    <div class="col-md-2">
        <div class="form-check form-switch form-check-custom form-check-solid mt-2">
            <input class="form-check-input" type="checkbox" name="prices[{{ $priceIndex ?? 0 }}][active]"
                value="1" {{ !isset($price) || $price->active ? 'checked' : '' }}
                id="price_active_{{ $priceIndex ?? 0 }}">
            <label class="form-check-label" for="price_active_{{ $priceIndex ?? 0 }}">Active</label>
        </div>
    </div>
    <div class="col-md-2 text-end">
        <button type="button" class="btn btn-sm btn-icon btn-light remove-price-row" title="Remove price">
            <span class="svg-icon svg-icon-3">
                <x-icon name="x-circle" :size="18" />
            </span>
        </button>
    </div>
</div>