@extends('layouts.basic')

@php
    $statusTone = function ($status) {
        switch ($status) {
            case 'active':
            case 'trialing':
            case 'paid':
                return 'success';
            case 'past_due':
            case 'open':
                return 'warning';
            case 'canceled':
            case 'unpaid':
            case 'uncollectible':
                return 'danger';
            default:
                return 'secondary';
        }
    };

    $money = function ($amount, $currency) {
        return number_format((float) $amount / 100, 2) . ' ' . strtoupper($currency ?: 'usd');
    };
@endphp

@section('content')
    <div class="d-flex flex-wrap flex-stack mb-6">
        <div class="d-flex align-items-center">
            <a href="{{ route('companies.index') }}"
                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-4">
                <x-icon name="arrow-left" :size="18" />
            </a>
            <div>
                <h3 class="fw-bold m-0">{{ optional($setting)->name ?: 'Company #' . $company->id }}</h3>
                <div class="text-muted fw-semibold fs-7">
                    {{ optional($setting)->email }}
                    @if (optional($setting)->address)
                        &middot; {{ $setting->address }}
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-light-{{ $company->status == 'active' ? 'success' : 'danger' }} fs-7">
                {{ strtoupper($company->status) }}
            </span>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body">
                    <div class="text-gray-400 fw-semibold fs-7 text-uppercase mb-2">Current plan</div>
                    @if ($plan)
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fs-2 fw-bold text-gray-800">{{ $plan->name }}</span>
                            <a href="{{ route('billing.plans.show', $plan->uid) }}"
                                class="btn btn-sm btn-light">View</a>
                        </div>
                        <div class="text-muted fw-semibold fs-7 mt-1">
                            {{ $plan->prices->where('active', true)->first()
                                ? $money($plan->prices->where('active', true)->first()->unit_amount, $plan->prices->where('active', true)->first()->currency) .
                                    ' / ' .
                                    $plan->prices->where('active', true)->first()->interval
                                : 'No active price' }}
                        </div>
                    @else
                        <span class="fs-2 fw-bold text-gray-400">No plan</span>
                    @endif
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
                    <div class="text-gray-400 fw-semibold fs-7 text-uppercase mb-2">Invoices</div>
                    <span class="fs-2 fw-bold text-gray-800">{{ $stats['invoices'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body">
                    <div class="text-gray-400 fw-semibold fs-7 text-uppercase mb-2">Total paid</div>
                    <span class="fs-2 fw-bold text-gray-800">
                        {{ number_format($stats['paid'], 2) }} {{ $stats['currency'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8">
        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Usage vs plan limits</h3>
                </div>
                <div class="card-body pt-2">
                    @if (!$plan)
                        <div class="text-muted fs-7 py-5">This company has no active plan.</div>
                    @elseif (empty($usage))
                        <div class="text-muted fs-7 py-5">No limits configured for this plan.</div>
                    @else
                        @foreach ($usage as $row)
                            <div class="mb-5">
                                <div class="d-flex justify-content-between fw-semibold fs-7 mb-2">
                                    <span class="text-gray-600">{{ $row['label'] }}</span>
                                    <span class="text-gray-800">
                                        {{ $row['used'] }}
                                        @if ($row['limit'] === null)
                                            / Unlimited
                                        @else
                                            / {{ $row['limit'] }}
                                        @endif
                                    </span>
                                </div>
                                @if ($row['limit'] !== null)
                                    <div class="progress h-6px w-100 bg-light-primary">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ $row['percent'] }}%"></div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Paid revenue (last 6 months)</h3>
                </div>
                <div class="card-body pt-2">
                    <div id="company_revenue_chart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-flush mt-5 mt-xl-8">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold text-gray-800">Subscription history</h3>
        </div>
        <div class="card-body pt-2">
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle gs-0 gy-4">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">
                            <th>Plan</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Period start</th>
                            <th>Period end</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse ($subscriptions as $subscription)
                            <tr>
                                <td>{{ optional($subscription->plan)->name ?: '-' }}</td>
                                <td>
                                    {{ $subscription->price
                                        ? $money($subscription->price->unit_amount, $subscription->price->currency) .
                                            ' / ' .
                                            $subscription->price->interval
                                        : '-' }}
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $statusTone($subscription->status) }}">
                                        {{ strtoupper($subscription->status) }}
                                    </span>
                                </td>
                                <td>{{ $subscription->current_period_start ? \Illuminate\Support\Carbon::parse($subscription->current_period_start)->format('M j, Y') : '-' }}
                                </td>
                                <td>{{ $subscription->current_period_end ? \Illuminate\Support\Carbon::parse($subscription->current_period_end)->format('M j, Y') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted fs-7 py-5">No subscriptions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-flush mt-5 mt-xl-8">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold text-gray-800">Recent invoices</h3>
        </div>
        <div class="card-body pt-2">
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle gs-0 gy-4">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th class="text-end">Links</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>{{ strtoupper(substr($invoice->stripe_invoice_id, 0, 12)) }}</td>
                                <td>{{ $money($invoice->amount_due, $invoice->currency) }}</td>
                                <td>{{ $invoice->due_date ? \Illuminate\Support\Carbon::parse($invoice->due_date)->format('M j, Y') : '-' }}
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $statusTone($invoice->status) }}">
                                        {{ strtoupper($invoice->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if ($invoice->hosted_invoice_url)
                                        <a href="{{ $invoice->hosted_invoice_url }}" target="_blank"
                                            class="btn btn-sm btn-light me-1">View</a>
                                    @endif
                                    @if ($invoice->invoice_pdf)
                                        <a href="{{ $invoice->invoice_pdf }}" target="_blank"
                                            class="btn btn-sm btn-light">PDF</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted fs-7 py-5">No invoices yet.</td>
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
            var node = document.getElementById('company_revenue_chart');
            if (!node || typeof ApexCharts === 'undefined') {
                if (node) {
                    node.innerHTML = '<div class="text-muted fs-7 py-5">No revenue data yet.</div>';
                }
                return;
            }

            var palette = (window.UiTheme && UiTheme.palette) ? UiTheme.palette() : {};

            new ApexCharts(node, {
                series: [{
                    name: 'Paid',
                    data: @json($revenue)
                }],
                chart: {
                    type: 'bar',
                    height: 260,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '45%'
                    }
                },
                xaxis: {
                    categories: @json($revenueLabels),
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
                colors: [palette.primary || '#009EF7']
            }).render();
        });
    </script>
@endsection
