<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="@logo(icon)" />
    @include('layouts.style')
    <title>Purchase Order | {{ $purchaseOrder->order_no }}</title>
    @php
        $setting = \App\Models\Setting::first();
    @endphp
    <style>

    </style>
</head>

<body class="bg-white">
    <div class="container-fluid">
        <div id="pageHeader">

            <div class="row">
                <div class="col-12 mb-3">
                    <h1 class="fw-bold">{{ $purchaseOrder->supplier->name }}</h1>
                </div>
            </div>
            <div class="row border-bottom border-bottom-2 border-dark">
                @if ($purchaseOrder->supplier->phone_no)
                    <div class="col-4 text-start">
                        <h4><x-icon name="phone" size="18" class="text-dark fw-bold" />
                            {{ $purchaseOrder->supplier->phone_no }}
                        </h4>
                    </div>
                @endif
                @if ($purchaseOrder->supplier->whatsapp_no)
                    <div class="col-4 text-center">
                        <h4><x-icon name="message-circle" size="18" class="text-dark fw-bold" />
                            {{ $purchaseOrder->supplier->whatsapp_no }}
                        </h4>
                    </div>
                @endif
                @if ($purchaseOrder->order_no)
                    <div class="col-4 text-end">
                        <h4></i> {{ $purchaseOrder->order_no }}</h4>
                    </div>
                @endif
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12 text-end">
                <h5><x-icon name="calendar" size="18" class="text-dark fw-bold" />
                    {{ Carbon\Carbon::create($purchaseOrder->created_at)->format('d M, Y') }}
                </h5>
            </div>
            <div class="col-12 text-end">
                <h5><x-icon name="clock" size="18" class="text-dark fw-bold" />
                    {{ Carbon\Carbon::create($purchaseOrder->created_at)->format('h:i A') }}
                </h5>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table  table-striped border-bottom border-dark">
                    <thead>
                        <tr scope="row">
                            <th scope="col" class="border px-3 border-dark fw-bold">SR</th>
                            <th scope="col" class="border px-3 border-dark fw-bold">Product</th>
                            <th scope="col" class="border px-3 border-dark fw-bold">Qty</th>
                            <th scope="col" class="border px-3 border-dark fw-bold">Price</th>
                            <th scope="col" class="border px-3 border-dark fw-bold">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = 0;
                        @endphp
                        @foreach ($stockHistories as $purchaseOrderProduct)
                            <tr scope="row">
                                <td scope="col" class="border px-3 border-dark ">{{ $loop->iteration }}</td>
                                <td scope="col" class="border px-3 border-dark ">
                                    {{ $purchaseOrderProduct->product->name }}</td>
                                <td scope="col" class="border px-3 border-dark text-end">
                                    {{ $purchaseOrderProduct->quantity }}</td>
                                <td scope="col" class="border px-3 border-dark text-end">
                                    {{ $purchaseOrderProduct->purchase }}</td>
                                <td scope="col" class="border px-3 border-dark text-end">
                                    {{ $purchaseOrderProduct->purchase * $purchaseOrderProduct->quantity }}</td>
                                @php
                                    $total += $purchaseOrderProduct->purchase * $purchaseOrderProduct->quantity;
                                @endphp
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="col" class="border px-3 border-dark fw-bold" colspan="4">Total</th>
                            <th scope="col" class="border px-3 border-dark text-end fw-bold">{{ $total }}
                                {{ config('app.currency') }}</th>
                        </tr>
                    </tfoot>
                </table>
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
