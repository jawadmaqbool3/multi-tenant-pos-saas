@if (!$ledger)
    <div class="d-flex flex-column align-items-center justify-content-center py-10 text-muted">
        <span class="fs-6">Select an account to view its ledger.</span>
    </div>
@else
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <span class="fs-4 fw-bold d-block">{{ $ledger['account']->name }}</span>
            <span class="text-muted fs-7">
                {{ $ledger['account']->code ?? '' }}
                &middot; {{ $ledger['account_type'] }}
                @if ($ledger['from'] || $ledger['to'])
                    &middot; {{ $ledger['from'] ?: 'start' }} to {{ $ledger['to'] ?: 'today' }}
                @else
                    &middot; All dates
                @endif
            </span>
        </div>
    </div>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
        <thead>
            <tr class="border-0">
                <th class="fw-bold p-0 min-w-100px text-start">Date</th>
                <th class="fw-bold p-0 min-w-100px text-start">Voucher#</th>
                <th class="fw-bold p-0 min-w-150px text-start">Description</th>
                <th class="fw-bold p-0 min-w-100px text-end">Debit</th>
                <th class="fw-bold p-0 min-w-100px text-end">Credit</th>
                <th class="fw-bold p-0 min-w-100px text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-light">
                <td colspan="5" class="fw-bold text-start">Opening Balance</td>
                <td class="fw-bold text-end">{{ number_format($ledger['opening'], 2) }}</td>
            </tr>
            @forelse ($ledger['entries'] as $entry)
                <tr>
                    <td class="text-start">{{ $entry['date'] }}</td>
                    <td class="text-start">
                        {{ $entry['voucher_no'] }}
                        @if ($entry['voucher_type'])
                            <span class="text-muted fs-8">({{ $entry['voucher_type'] }})</span>
                        @endif
                    </td>
                    <td class="text-start">{{ $entry['description'] ?: '-' }}</td>
                    <td class="text-end">{{ $entry['debit'] ? number_format($entry['debit'], 2) : '-' }}</td>
                    <td class="text-end">{{ $entry['credit'] ? number_format($entry['credit'], 2) : '-' }}</td>
                    <td class="text-end">{{ number_format($entry['balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="border-0 bg-light">
                <td colspan="5" class="fw-bold text-start">Closing Balance</td>
                <td class="fw-bold text-end">{{ number_format($ledger['closing'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endif
