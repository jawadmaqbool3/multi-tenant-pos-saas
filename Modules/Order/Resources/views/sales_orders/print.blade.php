<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="@logo(icon)" />
    @include('layouts.style')
    <title>INVOICE | {{ $order->order_no }}</title>
    @php
        $setting = \App\Models\Setting::first();
    @endphp
    <style>
        @page {
            orientation: portrait;
            size: 3in auto;
            margin: 2mm;
        }

        body {
            width: 100% !important;
        }

        td,
        th {
            border: 2px solid #222 !important;
            padding: 3px !important;
        }

        table {
            width: 100% !important;
        }
    </style>
</head>

<body class="bg-white">
    <div class="container-fluid">
        <div class="row">

            <div class="col-8">
                <h1>@setting(name)</h1>
                <h4>@setting(address)</h4>
                <h4>@setting(contact)</h4>
            </div>
            <div class="col-4 text-end pt-5">
                {!! QrCode::size(50)->generate(Request::url()) !!}
            </div>
            <div class="col-md-12">
                <p class="m-0 mt-1 p-0"><strong>Invoice#: {{ $order->order_no }}</strong></p>
                <p class="m-0 mt-1 p-0"><strong>Date:
                        {{ Carbon\Carbon::create($order->date)->format('d M, Y') }}</strong></p>
                @if ($order->status == 'paid')
                    <p class="m-0 mt-1 p-0"><strong>Method:
                            {{ strtoupper($order->payment_method ?? 'unpaid') }}</strong></p>
                @endif
            </div>
            <div>
                <table class="">
                    <thead>
                        <tr>
                            <th class="fw-bold">SR</th>
                            <th class="fw-bold">Product</th>
                            <th class="fw-bold">Qty</th>
                            <th class="fw-bold">Price</th>
                            <th class="fw-bold">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = 0;
                        @endphp
                        @foreach ($order->orderProducts as $orderProduct)
                            <tr>
                                <td class="">{{ $loop->iteration }}</td>
                                <td class="">{{ $orderProduct->product->name }}</td>
                                <td class="text-end">{{ $orderProduct->quantity }}</td>
                                <td class="text-end">{{ $orderProduct->price }}</td>
                                <td class="text-end">
                                    {{ $orderProduct->price * $orderProduct->quantity }}</td>
                                @php
                                    $total += $orderProduct->price * $orderProduct->quantity;
                                @endphp
                            </tr>
                        @endforeach

                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="fw-bold" colspan="4">Total</th>
                            <th class="text-end fw-bold">{{ $total }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-center">
                                {{ ucwords(\App\Core\Helper::convert_number_to_words($total)) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-6 mt-5">
                Find more on<br>
                <strong>@setting(url)</strong>
            </div>
        </div>
    </div>

</body>
<script type="text/javascript">
    // window.print();
    window.onafterprint = function() {
        // window.close();
        // history.go(-1);
    }
</script>

</html>
