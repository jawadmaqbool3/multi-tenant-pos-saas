@php $currency = config('app.currency'); @endphp
<div class="card card-flush h-100" data-widget-key="{{ $key }}">
    <div class="card-header pt-7">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-dark">{{ $widget->title() }}</span>
        </h3>
        <div class="card-toolbar" data-role="link"></div>
    </div>
    <div class="card-body pt-2">
        <div class="table-responsive" data-role="table">
            <div class="text-muted fs-7 py-5">Loading…</div>
        </div>
    </div>
</div>
