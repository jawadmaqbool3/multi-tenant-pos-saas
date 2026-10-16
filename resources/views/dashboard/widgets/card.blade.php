@php $currency = config('app.currency'); @endphp
<div class="card card-flush h-100" data-widget-key="{{ $key }}">
    <div class="card-body d-flex flex-column justify-content-between">
        <div class="card-title d-flex flex-column">
            <div class="d-flex align-items-center">
                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2" data-role="value">—</span>
                <span data-role="direction"></span>
            </div>
            <div data-role="badge" class="pt-1"></div>
            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ $widget->title() }}</span>
        </div>
        <div class="d-flex flex-stack mt-2" data-role="secondary"></div>
        <div class="d-flex flex-column mt-3" data-role="rows"></div>
        <div class="symbol-group symbol-hover flex-nowrap mt-3" data-role="heroes"></div>
        <div class="mt-3" data-role="link"></div>
    </div>
</div>
