<div class="row">
    <div class="col-md-4">
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 mb-7 me-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Details</h2>
                    </div>
                </div>
                <div class="card-body text-left pt-0">
                    <div class="mb-5">
                        <label class="form-label">Name</label>
                        <input value="{{ @$plan->name }}" type="text" name="name" id=""
                            class="form-control" placeholder="Plan Name">
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Slug</label>
                        <input value="{{ @$plan->slug }}" type="text" name="slug" id=""
                            class="form-control" placeholder="plan-slug">
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                            placeholder="Short description">{{ @$plan->description }}</textarea>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Sort</label>
                        <input value="{{ @$plan->sort }}" type="number" step="1" name="sort" id=""
                            class="form-control" placeholder="100">
                    </div>
                </div>
            </div>
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                    <div class="card-toolbar">
                        <div class="rounded-circle
                        @if (@$plan->status == 'active') bg-success
                        @elseif(@$plan->status == 'inactive') bg-warning
                        @else bg-success @endif
                         w-15px h-15px" id="status_sign"></div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <select name="status" class="form-select mb-2" data-control="select2" data-hide-search="true"
                        data-placeholder="Select an option" id="status">
                        <option></option>
                        <option data-status-sign="bg-success" {{ @$plan->status != 'inactive' ? 'selected' : '' }}
                            value="active">Active</option>
                        <option data-status-sign="bg-warning" {{ @$plan->status == 'inactive' ? 'selected' : '' }}
                            value="inactive">Inactive</option>
                    </select>
                    <div class="form-check form-switch form-check-custom form-check-solid mt-3">
                        <input name="is_public" class="form-check-input" type="checkbox" value="1"
                            {{ @$plan->is_public ? 'checked' : '' }} id="is_public_switch">
                        <label class="form-check-label" for="is_public_switch">Public (shown on signup)</label>
                    </div>
                    <div class="text-muted fs-7 mt-2">Inactive or hidden plans are not offered at signup.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 fw-semibold border-0"
                        id="plan_tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-active-primary pb-2 active" data-bs-toggle="tab"
                                data-bs-target="#tab_prices" type="button" role="tab">Prices</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-active-primary pb-2" data-bs-toggle="tab"
                                data-bs-target="#tab_modules" type="button" role="tab">Modules</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-active-primary pb-2" data-bs-toggle="tab"
                                data-bs-target="#tab_permissions" type="button" role="tab">Permissions</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-active-primary pb-2" data-bs-toggle="tab"
                                data-bs-target="#tab_limits" type="button" role="tab">Limits</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body pt-0 tab-content" id="plan_tabs_content">

                    <div class="tab-pane fade show active" id="tab_prices" role="tabpanel">
                        <div class="fw-bold fs-7 text-muted mb-3">Amounts are in minor units (cents).</div>
                        <div id="price_rows">
                            @php $priceIndex = 0; @endphp
                            @forelse ($selectedPrices as $price)
                                @include('billing::plans._price_row', compact('price', 'priceIndex'))
                                @php $priceIndex++; @endphp
                            @empty
                                @include('billing::plans._price_row', compact('currency'))
                            @endforelse
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-light-primary" id="add_price_row">Add price</button>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab_modules" role="tabpanel">
                        <div class="row">
                            @foreach ($modules as $module)
                                <div class="col-sm-6 col-md-4 mt-2">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input name="modules[]" class="form-check-input module-switch"
                                            data-module-id="{{ $module->id }}" type="checkbox"
                                            value="{{ $module->id }}"
                                            @if (in_array($module->id, $selectedModules)) checked="checked" @endif
                                            id="module_{{ $module->id }}">
                                        <label class="form-check-label"
                                            for="module_{{ $module->id }}">{{ $module->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab_permissions" role="tabpanel">
                        <div class="row">
                            @php $previous = null; @endphp
                            @foreach ($permissions as $permission)
                                @if ($permission->module_id != $previous)
                                    @php $previous = $permission->module_id; @endphp
                                    <div class="col-md-12">
                                        <h5 class="mt-8 border-bottom mb-5">{{ @$permission->module->name }}
                                            <div class="float-end d-inline-flex align-items-center">
                                                <span class="text-muted fw-normal fs-8 me-3">Check all</span>
                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                    <input class="form-check-input check-all"
                                                        type="checkbox" data-target=".module{{ $permission->module_id }}">
                                                </div>
                                            </div>
                                        </h5>
                                    </div>
                                @endif
                                <div class="col-sm-6 col-md-4 mt-2">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input name="permissions[]"
                                            class="form-check-input permission-check module{{ $permission->module_id }}"
                                            type="checkbox" id="plan_permission_{{ $permission->id }}"
                                            value="{{ $permission->uid }}"
                                            @if (in_array($permission->id, $selectedPermissions)) checked="checked" @endif />
                                        <label class="form-check-label"
                                            for="plan_permission_{{ $permission->id }}">{{ $permission->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab_limits" role="tabpanel">
                        <div class="row">
                            @foreach ($limitKeys as $key => $label)
                                <div class="col-sm-6 col-md-4 mt-4">
                                    <div class="mb-1">
                                        <label class="form-label">{{ $label }}</label>
                                    </div>
                                    <input type="number" step="1" min="-1"
                                        name="limits[{{ $key }}]" class="form-control"
                                        value="{{ $limits[$key] ?? '' }}"
                                        placeholder="Unlimited">
                                </div>
                            @endforeach
                        </div>
                        <div class="text-muted fs-7 mt-3">Leave blank for unlimited. Use <code>-1</code> to disable
                            creation entirely.</div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <a href="{{ route('billing.plans.index') }}" id="kt_ecommerce_add_product_cancel"
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

<script>
    $(document).ready(function() {

        $('#add_price_row').on('click', function() {
            var index = $('#price_rows .price-row').length;
            var last = $('#price_rows .price-row').last();
            var clone = last.clone(true);
            clone.find('input,select')
                .val('')
                .prop('checked', false)
                .removeAttr('checked')
                .each(function() {
                    var $el = $(this);
                    var name = $el.attr('name');
                    if (name) {
                        $el.attr('name', name.replace(/prices\[\d+\]/, 'prices[' + index + ']'));
                    }
                });
            clone.find('select option[value="month"]').prop('selected', true);
            $('#price_rows').append(clone);
        });

        $(document).on('click', '.remove-price-row', function() {
            if ($('#price_rows .price-row').length > 1) {
                $(this).closest('.price-row').remove();
            }
        });

        $('.module-switch').on('change', function() {
            var moduleId = $(this).data('module-id');
            $('.permission-check.module' + moduleId).prop('checked', $(this).prop('checked'));
        });

        $('.check-all').on('change', function() {
            var target = $(this).data('target');
            $(target + '.permission-check').prop('checked', $(this).prop('checked'));
        });

        $('#status').on('change', function() {
            var sign = $(this).find(':selected').data('status-sign');
            $('#status_sign').removeClass('bg-success bg-warning').addClass(sign);
        });
    });
</script>