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
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.3"
                        d="M12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22Z"
                        fill="currentColor"/>
                    <path d="M9.1 8.3L12 11.2L14.9 8.3C15.3 7.9 15.9 7.9 16.3 8.3C16.7 8.7 16.7 9.3 16.3 9.7L13.4 12.6L16.3 15.5C16.7 15.9 16.7 16.5 16.3 16.9C15.9 17.3 15.3 17.3 14.9 16.9L12 14L9.1 16.9C8.7 17.3 8.1 17.3 7.7 16.9C7.3 16.5 7.3 15.9 7.7 15.5L10.6 12.6L7.7 9.7C7.3 9.3 7.3 8.7 7.7 8.3C8.1 7.9 8.7 7.9 9.1 8.3Z"
                        fill="currentColor"/>
                </svg>
            </span>
        </button>
    </div>
</div>