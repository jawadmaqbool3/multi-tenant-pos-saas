@extends('layouts.basic')

@section('content')
    <div class="d-flex flex-column flex-center py-10">
        <div class="text-center mb-10" style="max-width: 640px;">
            <div class="eyebrow d-inline-flex mb-2">Almost there</div>
            <h1 class="text-dark fw-bolder mb-3">Choose a plan to continue</h1>
            <p class="text-gray-500 fw-semibold mb-0">
                Your account is active, but you need an active subscription to use the workspace.
                Pick a plan below to complete checkout.
            </p>
        </div>

        @if (!$configured)
            <div class="alert alert-warning d-flex align-items-center mb-8" style="max-width: 720px;">
                <span class="svg-icon svg-icon-2 svg-icon-warning me-3">
                    <x-icon name="alert-triangle" :size="20" />
                </span>
                <span class="fw-semibold">Billing is not configured yet. Please contact the administrator.</span>
            </div>
        @endif

        @if ($plans->isEmpty())
            <div class="card card-flush" style="max-width: 720px;">
                <div class="card-body text-center py-10">
                    <h3 class="fw-bold text-dark mb-2">No plans available</h3>
                    <p class="text-muted mb-0">There are no public plans to subscribe to yet. Please contact support.</p>
                </div>
            </div>
        @else
            <div class="row g-6 justify-content-center w-100" style="max-width: 980px;">
                @foreach ($plans as $plan)
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-flush h-100">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title fw-bolder text-dark fs-2">{{ $plan->name }}</h3>
                            </div>
                            <div class="card-body pt-2">
                                @if ($plan->description)
                                    <p class="text-muted fs-7 mb-4">{{ $plan->description }}</p>
                                @endif

                                @php $firstPrice = $plan->prices->first(); @endphp
                                <div class="d-flex align-items-end mb-5">
                                    <span class="fs-2hx fw-bolder text-dark">
                                        {{ number_format(($firstPrice->unit_amount ?? 0) / 100, 2) }}
                                    </span>
                                    <span class="text-muted fw-semibold ms-1">{{ strtoupper($firstPrice->currency ?? $currency) }}</span>
                                    <span class="text-muted fs-7 ms-1">
                                        /{{ ($firstPrice->interval_count ?? 1) > 1 ? ($firstPrice->interval_count . ' ') : '' }}{{ $firstPrice->interval ?? 'month' }}
                                    </span>
                                </div>

                                <form data-ajax="true" method="POST" action="{{ route('billing.subscribe.checkout') }}">
                                    @csrf
                                    <input type="hidden" name="price" value="{{ $firstPrice->uid }}" />
                                    <button type="submit" class="btn btn-primary w-100"
                                        {{ $configured ? '' : 'disabled' }}>
                                        Subscribe
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="mt-10">
            @csrf
            <button type="submit" class="btn btn-link text-muted">Sign out</button>
        </form>
    </div>
@endsection
