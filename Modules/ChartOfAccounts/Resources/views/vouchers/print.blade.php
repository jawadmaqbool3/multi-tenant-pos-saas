<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.style')
    <title>Voucher | {{ $voucher->voucher_no }}</title>
</head>

<body class="bg-white" onload="window.print()">
    <div class="container-fluid">
        <h1>{{ $voucher->voucherType->name }}</h1>
        <p><strong>No:</strong> {{ $voucher->voucher_no }}</p>
        <p><strong>Date:</strong> {{ $voucher->date }}</p>
        <p><strong>Description:</strong> {{ $voucher->description ?? '-' }}</p>
        <p><strong>Created By:</strong> {{ $voucher->creator->name ?? '-' }}</p>

        @if ($voucher->type === 'void')
            <p><span class="badge bg-warning text-dark">VOIDED</span></p>
        @endif

        <table class="table table-bordered" style="margin-top: 20px">
            <thead>
                <tr>
                    <th>Account</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($voucher->transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->account->name ?? '-' }}</td>
                        <td class="text-end">{{ $transaction->type == 'debit' ? number_format($transaction->amount, 2) : '' }}
                        </td>
                        <td class="text-end">{{ $transaction->type == 'credit' ? number_format($transaction->amount, 2) : '' }}
                        </td>
                        <td>{{ $transaction->description ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td class="text-end">Total</td>
                    <td class="text-end">
                        {{ number_format($voucher->transactions->where('type', 'debit')->sum('amount'), 2) }}
                    </td>
                    <td class="text-end">
                        {{ number_format($voucher->transactions->where('type', 'credit')->sum('amount'), 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>
