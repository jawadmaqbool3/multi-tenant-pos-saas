@extends('layouts.basic')

@php
    $money = function ($amount, $currency) {
        return number_format((float) $amount / 100, 2) . ' ' . strtoupper($currency ?: 'usd');
    };

    $statusTone = function ($status) {
        switch ($status) {
            case 'active':
            case 'trialing':
                return 'success';
            case 'past_due':
            case 'incomplete':
                return 'warning';
            case 'canceled':
            case 'unpaid':
            case 'incomplete_expired':
                return 'danger';
            default:
                return 'secondary';
        }
    };
@endphp

@section('content')
    <div class="d-flex flex-wrap flex-stack mb-6">
        <div class="d-flex align-items-center">
            <a href="{{ route('billing.plans.index') }}"
                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-4">
                <x-icon name="arrow-left" :size="18" />
            </a>
            <div>
                <h3 class="fw-bold m-0">{{ $plan->name }}</h3>
                <div class="text-muted fw-semibold fs-7">{{ $plan->description ?: $plan->slug }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-light-{{ $plan->status == 'active' ? 'success' : 'danger' }} fs-7">
                {{ strtoupper($plan->status) }}
            </span>
            @if ($plan->is_public)
                <span class="badge badge-light-primary fs-7">PUBLIC</span>
            @endif
            @can('edit billing plan')
                <a href="{{ route('billing.plans.edit', $plan->uid) }}" class="btn btn-sm btn-primary">Edit plan</a>
            @endcan
        </div>
    </div>

    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body">
                    <div class="text-gray-400 fw-semibold fs-7 text-uppercase mb-2">Active companies</div>
                    <span class="fs-2 fw-bold text-gray-800">{{ $stats['companies'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body">
                    <div class="text-gray-400 fw-semibold fs-7 text-uppercase mb-2">Subscriptions</div>
                    <span class="fs-2 fw-bold text-gray-800">{{ $stats['subscriptions'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body">
                    <div class="text-gray-400 fw-semibold fs-7 text-uppercase mb-2">Est. MRR</div>
                    <span class="fs-2 fw-bold text-gray-800">
                        {{ number_format($stats['mrr'], 2) }} {{ $stats['currency'] }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body">
                    <div class="text-gray-400 fw-semibold fs-7 text-uppercase mb-2">Permissions</div>
                    <span class="fs-2 fw-bold text-gray-800">{{ $permissionsCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8">
        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Prices</h3>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-4">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">
                                    <th>Amount</th>
                                    <th>Interval</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                @forelse ($prices as $price)
                                    <tr>
                                        <td>{{ $money($price->unit_amount, $price->currency) }}</td>
                                        <td>Every {{ $price->interval_count ?: 1 }} {{ $price->interval }}</td>
                                        <td>
                                            <span class="badge badge-light-{{ $price->active ? 'success' : 'secondary' }}">
                                                {{ $price->active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted fs-7 py-5">No prices configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Plan limits</h3>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-4">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">
                                    <th>Resource</th>
                                    <th>Limit</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                @forelse ($limits as $limit)
                                    <tr>
                                        <td>{{ $limit['label'] }}</td>
                                        <td>{{ $limit['value'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-muted fs-7 py-5">No limits configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mt-0">
        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Modules included</h3>
                </div>
                <div class="card-body pt-2">
                    @forelse ($modules as $module)
                        <span class="badge badge-light-primary fs-7 me-2 mb-2">{{ $module }}</span>
                    @empty
                        <div class="text-muted fs-7 py-5">No modules assigned to this plan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">New subscriptions (last 6 months)</h3>
                </div>
                <div class="card-body pt-2">
                    <div id="plan_subs_chart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-flush mt-5 mt-xl-8">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold text-gray-800">Companies on this plan</h3>
        </div>
        <div class="card-body pt-2">
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle gs-0 gy-4">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">
                            <th>Company</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse ($companies as $row)
                            <tr>
                                <td>
                                    @if ($row['company'])
                                        <a href="{{ route('companies.show', $row['company']->uid) }}"
                                            class="text-gray-800 text-hover-primary">{{ $row['name'] }}</a>
                                    @else
                                        {{ $row['name'] }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $statusTone($row['status']) }}">
                                        {{ strtoupper($row['status']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-muted fs-7 py-5">No active companies on this plan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('jsSection')
    <script>
        $(document).ready(function() {
            var node = document.getElementById('plan_subs_chart');
            if (!node || typeof ApexCharts === 'undefined') {
                if (node) {
                    node.innerHTML = '<div class="text-muted fs-7 py-5">No subscription data yet.</div>';
                }
                return;
            }

            var palette = (window.UiTheme && UiTheme.palette) ? UiTheme.palette() : {};

            new ApexCharts(node, {
                series: [{
                    name: 'Subscriptions',
                    data: @json($chart)
                }],
                chart: {
                    type: 'area',
                    height: 260,
                    toolbar: {
                        show: false
                    }
                },
                xaxis: {
                    categories: @json($chartLabels),
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            colors: palette.label || undefined
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: palette.label || undefined
                        }
                    }
                },
                grid: {
                    borderColor: palette.grid || undefined
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: [palette.primary || '#009EF7']
            }).render();
        });
    </script>
@endsection
