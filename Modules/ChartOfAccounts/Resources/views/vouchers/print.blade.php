<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.style')
    <title>Voucher | {{ $voucher->voucher_no ?? $voucher->uid }}</title>
</head>

<body class="bg-white" onload="window.print()">
    <div class="container-fluid">
        <h1>{{ $voucher->voucherType->name }}</h1>
        <p><strong>No:</strong> {{ $voucher->voucher_no ?? $voucher->uid }}</p>
        <p><strong>Date:</strong> {{ $voucher->date }}</p>
        <p><strong>Amount:</strong> {{ $voucher->amount }}</p>
        <p><strong>Description:</strong> {{ $voucher->description }}</p>
        <p><strong>Created By:</strong> {{ $voucher->creator->name ?? '-' }}</p>
    </div>
</body>

</html>
