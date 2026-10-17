@php
    $c = $coupon ?? null;
    $synced = $c && $c->stripe_coupon_id;
    $discountType = old('discount_type');
    if (!$discountType && $c) {
        $discountType = $c->percent_off ? 'percent' : ($c->amount_off ? 'amount' : 'percent');
    }
    $discountType = $discountType ?: 'percent';
    $existingValue = $c ? ($c->percent_off ?: ($c->amount_off ? $c->amount_off / 100 : '')) : '';
    $discountValue = $synced ? $existingValue : old('discount_value', $existingValue);
@endphp

<div class="row">
    <div class="col-md-8 col-lg-7 col-xxl-8">
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 mb-7 me-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Coupon</h2>
                    </div>
                </div>
                <div class="card-body text-left pt-0">
                    <div class="mb-5">
                        <label class="form-label required">Code</label>
                        <input value="{{ old('code', $coupon->code ?? '') }}" type="text" name="code"
                            class="form-control text-uppercase" placeholder="e.g. LAUNCH20"
                            {{ $synced ? 'disabled' : '' }}>
                        @if ($synced)
                            <div class="text-muted fs-7 mt-2">A coupon's code cannot be changed once created in Stripe.</div>
                        @endif
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Name</label>
                        <input value="{{ old('name', $coupon->name ?? '') }}" type="text" name="name"
                            class="form-control" placeholder="Internal name (optional)">
                    </div>

                    @if ($synced)
                        <input type="hidden" name="discount_type" value="{{ $discountType }}">
                        <input type="hidden" name="discount_value" value="{{ $discountValue }}">
                        <input type="hidden" name="duration" value="{{ $coupon->duration }}">
                        @if ($coupon->currency)
                            <input type="hidden" name="currency" value="{{ $coupon->currency }}">
                        @endif
                        @if ($coupon->duration_in_months)
                            <input type="hidden" name="duration_in_months" value="{{ $coupon->duration_in_months }}">
                        @endif
                        <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-4">
                            <div class="fw-semibold fs-7 text-gray-700">
                                Stripe coupons are immutable. To change the discount, deactivate this coupon and create a
                                new one.
                            </div>
                        </div>
                    @else
                        <div class="mb-5">
                            <label class="form-label required">Discount type</label>
                            <select name="discount_type" id="coupon_discount_type" class="form-select"
                                data-control="select2" data-hide-search="true">
                                <option value="percent" {{ $discountType === 'percent' ? 'selected' : '' }}>Percentage off
                                </option>
                                <option value="amount" {{ $discountType === 'amount' ? 'selected' : '' }}>Fixed amount off
                                </option>
                            </select>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required">Discount value</label>
                            <input value="{{ $discountValue }}" type="number" min="0" step="0.01"
                                name="discount_value" class="form-control" placeholder="e.g. 20">
                            <div class="text-muted fs-7 mt-2" id="coupon_value_hint">
                                Percentage (0-100).
                            </div>
                        </div>
                        <div class="mb-5" id="coupon_currency_wrap">
                            <label class="form-label">Currency</label>
                            <input value="{{ old('currency', $coupon->currency ?? $currency) }}" type="text"
                                maxlength="3" name="currency" class="form-control text-uppercase">
                        </div>
                        <div class="mb-5">
                            <label class="form-label required">Duration</label>
                            <select name="duration" id="coupon_duration" class="form-select" data-control="select2"
                                data-hide-search="true">
                                <option value="once"
                                    {{ old('duration', $coupon->duration ?? 'once') === 'once' ? 'selected' : '' }}>Once
                                </option>
                                <option value="repeating"
                                    {{ old('duration', $coupon->duration ?? '') === 'repeating' ? 'selected' : '' }}>
                                    Repeating</option>
                                <option value="forever"
                                    {{ old('duration', $coupon->duration ?? '') === 'forever' ? 'selected' : '' }}>Forever
                                </option>
                            </select>
                        </div>
                        <div class="mb-5" id="coupon_months_wrap">
                            <label class="form-label required">Months</label>
                            <input value="{{ old('duration_in_months', $coupon->duration_in_months ?? '') }}"
                                type="number" min="1" step="1" name="duration_in_months" class="form-control"
                                placeholder="e.g. 3">
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Max redemptions</label>
                            <input value="{{ old('max_redemptions', $coupon->max_redemptions ?? '') }}" type="number"
                                min="1" step="1" name="max_redemptions" class="form-control"
                                placeholder="Unlimited">
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Expires at</label>
                            <input
                                value="{{ old('expires_at', isset($coupon) && $coupon && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '') }}"
                                type="date" name="expires_at" class="form-control">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-5 col-xxl-4">
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 mb-7">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                    <div class="card-toolbar">
                        <div class="rounded-circle
                        @if (old('status', $coupon->status ?? 'active') == 'active') bg-success
                        @else bg-warning @endif
                          w-15px h-15px" id="status_sign"></div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <select name="status" class="form-select mb-2" data-control="select2" data-hide-search="true"
                        id="status">
                        <option></option>
                        <option data-status-sign="bg-success"
                            {{ old('status', $coupon->status ?? 'active') === 'active' ? 'selected' : '' }}
                            value="active">Active</option>
                        <option data-status-sign="bg-warning"
                            {{ old('status', $coupon->status ?? '') === 'inactive' ? 'selected' : '' }}
                            value="inactive">Inactive</option>
                    </select>
                    <div class="text-muted fs-7 mt-2">Inactive coupons cannot be redeemed at checkout.</div>
                </div>
            </div>
            <div class="card card-flush py-4">
                <div class="card-body pt-5">
                    <h3 class="fw-bold text-gray-800 mb-3">How coupons work</h3>
                    <ul class="text-muted fw-semibold fs-7 ps-4 mb-0">
                        <li class="mb-2">Each coupon creates a <strong>Stripe Coupon</strong> (the discount) and a
                            <strong>Stripe Promotion Code</strong> (the code customers type).</li>
                        <li class="mb-2">Customers enter the code on the pricing page and it is applied to their
                            subscription at checkout.</li>
                        <li class="mb-2">Discounts apply to every billing cycle for the duration you choose.</li>
                        <li class="mb-2">Percentage discounts must be between 0 and 100; fixed discounts are entered in
                            your billing currency.</li>
                        <li>The coupon's code and discount are immutable in Stripe once created.</li>
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <a href="{{ route('billing.coupons.index') }}" class="btn btn-light me-5">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">Save Changes</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        function toggleDiscountFields() {
            var type = $('#coupon_discount_type').val();
            if (type === 'amount') {
                $('#coupon_currency_wrap').show();
                $('#coupon_value_hint').text('Amount in your billing currency (e.g. 5.00).');
            } else {
                $('#coupon_currency_wrap').hide();
                $('#coupon_value_hint').text('Percentage (0-100).');
            }
        }

        function toggleMonths() {
            if ($('#coupon_duration').val() === 'repeating') {
                $('#coupon_months_wrap').show();
            } else {
                $('#coupon_months_wrap').hide();
            }
        }

        $('#coupon_discount_type').on('change', toggleDiscountFields);
        $('#coupon_duration').on('change', toggleMonths);
        toggleDiscountFields();
        toggleMonths();

        $('#status').on('change', function() {
            var sign = $(this).find(':selected').data('status-sign');
            $('#status_sign').removeClass('bg-success bg-warning').addClass(sign);
        });
    });
</script>
