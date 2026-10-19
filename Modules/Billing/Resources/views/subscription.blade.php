@extends('layouts.basic')

@section('title', 'My Subscription')

@section('content')
    @php
        $subscription = $subscription ?? null;
        $plan = $plan ?? null;
        $price = $price ?? null;
        $renewal = $renewal ?? null;
        $usage = $usage ?? [];
        $plans = $plans ?? collect();
        $features = $features ?? [];
        $configured = $configured ?? false;
    @endphp

    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">My Subscription</span>
            </h3>
            <div class="row w-100">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'My Subscription' => false,
                    ]" />
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        {{-- Current plan --}}
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold text-dark">Current Plan</h3>
                    @if ($renewal)
                        <span class="badge badge-light-{{ $renewal['status'] === 'active' ? 'success' : 'warning' }} py-3 px-4">
                            {{ strtoupper($renewal['status']) }}
                        </span>
                    @endif
                </div>
                <div class="card-body pt-2">
                    @if ($plan)
                        <div class="d-flex align-items-end mb-4">
                            <span class="fs-2hx fw-bold text-dark me-2">{{ $plan->name }}</span>
                            @if ($price)
                                <span class="fs-4 fw-bold text-gray-600 mb-2">
                                    {{ number_format($price->unit_amount / 100, 2) }}
                                    {{ strtoupper($price->currency) }}/{{ $price->interval_count > 1 ? $price->interval_count . ' ' : '' }}{{ $price->interval }}
                                </span>
                            @endif
                        </div>

                        @if ($plan->description)
                            <p class="text-gray-600 fw-semibold mb-5">{{ $plan->description }}</p>
                        @endif

                        <div class="separator separator-dashed my-4"></div>

                        <div class="d-flex justify-content-between py-2">
                            <span class="text-gray-600 fw-semibold">Status</span>
                            <span class="text-dark fw-bold text-capitalize">{{ $renewal['status'] ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-gray-600 fw-semibold">Current period</span>
                            <span class="text-dark fw-bold">
                                {{ $renewal['period_start'] ? \Carbon\Carbon::parse($renewal['period_start'])->format('M j, Y') : '-' }}
                                &ndash;
                                {{ $renewal['period_end'] ? $renewal['period_end']->format('M j, Y') : '-' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-gray-600 fw-semibold">Renews in</span>
                            <span class="text-dark fw-bold">
                                {{ $renewal['days_left'] === null ? 'N/A' : $renewal['days_left'] . ' days' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-gray-600 fw-semibold">Auto renew</span>
                            <span class="text-dark fw-bold">{{ $renewal['auto_renew'] ? 'Yes' : 'No' }}</span>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-600 fw-semibold mb-4">Your company has no active subscription.</p>
                            <a href="{{ route('billing.pricing') }}" class="btn btn-primary">Choose a plan</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Usage --}}
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold text-dark">Resource Usage</h3>
                </div>
                <div class="card-body pt-2">
                    @if (empty($usage))
                        <p class="text-gray-600 fw-semibold text-center py-8">No usage data available.</p>
                    @else
                        @foreach ($usage as $row)
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-gray-800 fw-semibold">{{ $row['label'] }}</span>
                                    <span class="text-gray-600 fw-semibold">
                                        {{ $row['used'] }} /
                                        {{ $row['unlimited'] ? 'Unlimited' : $row['limit'] }}
                                        @if (!$row['unlimited'])
                                             &middot; {{ $row['remaining'] }} left
                                        @endif
                                    </span>
                                </div>
                                <div class="progress h-8px bg-light-{{ $row['exceeded'] ? 'danger' : 'primary' }}">
                                    <div class="progress-bar bg-{{ $row['exceeded'] ? 'danger' : 'primary' }}"
                                        role="progressbar"
                                        style="width: {{ $row['unlimited'] ? 100 : $row['percentage'] }}%"
                                        aria-valuenow="{{ $row['used'] }}"
                                        aria-valuemin="0"
                                        aria-valuemax="{{ $row['limit'] ?? 0 }}"></div>
                                </div>
                                @if ($row['exceeded'])
                                    <span class="text-danger fs-8 fw-semibold">Limit reached</span>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Change plan --}}
    <div class="card">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title fw-bold text-dark">Change Plan</h3>
        </div>
        <div class="card-body">
            @if (!$configured)
                <div class="alert alert-warning d-flex align-items-center mb-6">
                    <span class="svg-icon svg-icon-2 svg-icon-warning me-3">
                        <x-icon name="alert-triangle" :size="20" />
                    </span>
                    <span class="fw-semibold">Billing is not configured yet. Plan changes may not be processed.</span>
                </div>
            @endif

            @if ($plans->isEmpty())
                <p class="text-gray-600 fw-semibold mb-0">No plans are available to switch to right now.</p>
            @else
                <div class="row g-4">
                    @foreach ($plans as $option)
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border {{ $option['is_current'] ? 'border-primary' : 'border-gray-300' }}">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h4 class="fw-bold text-dark mb-0">{{ $option['plan']->name }}</h4>
                                        @if ($option['is_current'])
                                            <span class="badge badge-primary">Current</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-end mb-4">
                                        <span class="fs-2x fw-bold text-dark me-1">
                                            {{ number_format($option['amount'], 2) }}
                                        </span>
                                        <span class="fs-7 fw-bold text-gray-600 mb-1">
                                            {{ $option['currency'] }}/{{ $option['interval'] }}
                                        </span>
                                    </div>

                                    @if ($option['plan']->description)
                                        <p class="text-gray-600 fw-semibold fs-7 mb-4">{{ $option['plan']->description }}</p>
                                    @endif

                                    <ul class="list-unstyled flex-grow-1 mb-4">
                                        @foreach (array_slice($option['features'], 0, 6) as $line)
                                            <li class="d-flex align-items-center mb-2">
                                                <span class="svg-icon svg-icon-4 me-2 text-{{ $line['included'] ? 'success' : 'muted' }}">
                                                    <x-icon :name="$line['included'] ? 'check' : 'minus'" :size="14" />
                                                </span>
                                                <span class="fs-7 fw-semibold text-gray-700 {{ $line['included'] ? '' : 'text-decoration-line-through text-muted' }}">
                                                    {{ $line['label'] }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if ($option['is_current'])
                                        <button type="button" class="btn btn-light w-100" disabled>Current plan</button>
                                    @else
                                        <form data-ajax="true" method="POST"
                                            action="{{ route('billing.subscription.change-plan') }}">
                                            @csrf
                                            <input type="hidden" name="price" value="{{ $option['uid'] }}" />
                                            <button type="submit" class="btn btn-primary w-100 change-plan-btn"
                                                data-plan="{{ $option['plan']->name }}">
                                                Switch to {{ $option['plan']->name }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@section('jsSection')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.change-plan-btn', function(e) {
                var plan = $(this).data('plan');
                if (!confirm('Switch to the ' + plan + ' plan? Any price difference will be prorated.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
