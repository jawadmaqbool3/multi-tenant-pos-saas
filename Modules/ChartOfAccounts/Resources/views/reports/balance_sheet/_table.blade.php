<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <div>
        <span class="fs-4 fw-bold d-block">Balance Sheet</span>
        <span class="text-muted fs-7">As of {{ $balanceSheet['as_of'] }}</span>
    </div>
    <div class="text-end">
        @if (round($balanceSheet['difference'], 2) == 0)
            <span class="badge badge-light-success">Balanced</span>
        @else
            <span class="badge badge-light-warning">
                Out of balance: {{ number_format($balanceSheet['difference'], 2) }}
            </span>
        @endif
    </div>
</div>

<table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
    <thead>
        <tr class="border-0">
            <th class="fw-bold p-0 min-w-250px text-start">Account</th>
            <th class="fw-bold p-0 min-w-150px text-end">Amount</th>
        </tr>
    </thead>
    <tbody>
        {{-- Assets --}}
        <tr class="bg-light">
            <td colspan="2" class="fw-bold text-uppercase fs-7">Assets</td>
        </tr>
        @foreach ($balanceSheet['assets'] as $level1)
            <tr>
                <td class="fw-bold text-start">{{ $level1->name }}</td>
                <td class="fw-bold text-end">{{ number_format($level1->report_balance, 2) }}</td>
            </tr>
            @foreach ($level1->level2 as $level2)
                @if (round($level2->report_balance, 2) != 0)
                    <tr>
                        <td class="ps-5 text-start">{{ $level2->name }}</td>
                        <td class="text-end">{{ number_format($level2->report_balance, 2) }}</td>
                    </tr>
                    @foreach ($level2->level3 as $level3)
                        @if (round($level3->report_balance, 2) != 0)
                            <tr>
                                <td class="ps-8 text-start text-muted">{{ $level3->name }}</td>
                                <td class="text-end text-muted">{{ number_format($level3->report_balance, 2) }}</td>
                            </tr>
                            @foreach ($level3->level4 as $level4)
                                @if (round($level4->report_balance, 2) != 0)
                                    <tr>
                                        <td class="ps-11 text-start text-muted fs-7">{{ $level4->name }}</td>
                                        <td class="text-end text-muted fs-7">{{ number_format($level4->report_balance, 2) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endforeach
        <tr class="border-top border-gray-300">
            <td class="fw-bold text-start">Total Assets</td>
            <td class="fw-bold text-end">{{ number_format($balanceSheet['assets_total'], 2) }}</td>
        </tr>

        {{-- Liabilities --}}
        <tr class="bg-light">
            <td colspan="2" class="fw-bold text-uppercase fs-7">Liabilities</td>
        </tr>
        @forelse ($balanceSheet['liabilities'] as $level1)
            <tr>
                <td class="fw-bold text-start">{{ $level1->name }}</td>
                <td class="fw-bold text-end">{{ number_format($level1->report_balance, 2) }}</td>
            </tr>
            @foreach ($level1->level2 as $level2)
                @if (round($level2->report_balance, 2) != 0)
                    <tr>
                        <td class="ps-5 text-start">{{ $level2->name }}</td>
                        <td class="text-end">{{ number_format($level2->report_balance, 2) }}</td>
                    </tr>
                    @foreach ($level2->level3 as $level3)
                        @if (round($level3->report_balance, 2) != 0)
                            <tr>
                                <td class="ps-8 text-start text-muted">{{ $level3->name }}</td>
                                <td class="text-end text-muted">{{ number_format($level3->report_balance, 2) }}</td>
                            </tr>
                            @foreach ($level3->level4 as $level4)
                                @if (round($level4->report_balance, 2) != 0)
                                    <tr>
                                        <td class="ps-11 text-start text-muted fs-7">{{ $level4->name }}</td>
                                        <td class="text-end text-muted fs-7">{{ number_format($level4->report_balance, 2) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endforeach
        @empty
            <tr>
                <td colspan="2" class="text-muted py-3">No liabilities.</td>
            </tr>
        @endforelse
        <tr class="border-top border-gray-300">
            <td class="fw-bold text-start">Total Liabilities</td>
            <td class="fw-bold text-end">{{ number_format($balanceSheet['liabilities_total'], 2) }}</td>
        </tr>

        {{-- Equity --}}
        <tr class="bg-light">
            <td colspan="2" class="fw-bold text-uppercase fs-7">Equity</td>
        </tr>
        @forelse ($balanceSheet['equity'] as $level1)
            <tr>
                <td class="fw-bold text-start">{{ $level1->name }}</td>
                <td class="fw-bold text-end">{{ number_format($level1->report_balance, 2) }}</td>
            </tr>
            @foreach ($level1->level2 as $level2)
                @if (round($level2->report_balance, 2) != 0)
                    <tr>
                        <td class="ps-5 text-start">{{ $level2->name }}</td>
                        <td class="text-end">{{ number_format($level2->report_balance, 2) }}</td>
                    </tr>
                    @foreach ($level2->level3 as $level3)
                        @if (round($level3->report_balance, 2) != 0)
                            <tr>
                                <td class="ps-8 text-start text-muted">{{ $level3->name }}</td>
                                <td class="text-end text-muted">{{ number_format($level3->report_balance, 2) }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @empty
            <tr>
                <td colspan="2" class="text-muted py-3">No equity accounts.</td>
            </tr>
        @endforelse
        <tr>
            <td class="ps-5 text-start">Net Profit (Revenue - Expenses)</td>
            <td class="text-end">{{ number_format($balanceSheet['net_profit'], 2) }}</td>
        </tr>
        <tr class="border-top border-gray-300">
            <td class="fw-bold text-start">Total Equity</td>
            <td class="fw-bold text-end">
                {{ number_format($balanceSheet['equity_total'] + $balanceSheet['net_profit'], 2) }}
            </td>
        </tr>

        <tr class="bg-light-primary">
            <td class="fw-bold text-primary text-start">Total Liabilities &amp; Equity</td>
            <td class="fw-bold text-primary text-end">
                {{ number_format($balanceSheet['liabilities_and_equity_total'], 2) }}
            </td>
        </tr>
    </tbody>
</table>
