@php /* @var array $widgets */ @endphp
<div class="row g-5 g-xl-10 mb-10 mb-xl-10">
    @forelse ($widgets as $widget)
        <div class="{{ $widget->size() }}">
            @include($widget->view(), ['widget' => $widget, 'key' => $widget->key()])
        </div>
    @empty
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-body d-flex flex-column justify-content-center align-items-center py-15">
                    <span class="fs-2hx fw-bold text-gray-300 mb-3">No widgets available</span>
                    <span class="text-gray-400 fw-semibold">Ask an administrator to grant your role dashboard
                        access.</span>
                </div>
            </div>
        </div>
    @endforelse
</div>
