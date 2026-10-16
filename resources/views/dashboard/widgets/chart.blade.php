@php $currency = config('app.currency'); @endphp
<div class="card card-flush h-100" data-widget-key="{{ $key }}">
    <div class="card-header pt-5">
        <div class="card-title d-flex flex-column">
            <div class="d-flex align-items-center">
                <span class="text-gray-400 fs-4 fw-semibold me-1 align-self-start">{{ $currency }}</span>
                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2" data-role="value">—</span>
                <span data-role="direction"></span>
            </div>
            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ $widget->title() }}</span>
        </div>
    </div>
    <div class="card-body d-flex flex-column">
        <div class="d-flex flex-column w-100 mb-4" data-role="legend"></div>
        <div class="d-flex align-items-center flex-grow-1">
            <div class="w-100" style="min-height: 180px" data-role="chart"></div>
        </div>
    </div>
</div>
