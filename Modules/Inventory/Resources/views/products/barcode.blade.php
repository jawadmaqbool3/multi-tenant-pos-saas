</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="@logo(icon)" />
    @include('layouts.style')
    <title>BARCODE | {{ $product->name }}</title>
    
    <style>
        @page {
            /* size: 2.6in 3in; */
            margin: 3mm;
            /* padding: 3mm; */
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-white">
    <div class="container-fluid">
        <div class="row">

            <div class="col-12 text-center fs-4 fw-bold mt-1">
                @setting(name)
            </div>
            <div class="col-12 text-start">
                <h5>{{ $product->name }}</h5>
            </div>
            <div class="col-12 text-start">
                <h5>{{ $product->unit_price }}
                {{ config('app.currency') }}</h5>
            </div>
            <div class="col-12">
                @php
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                @endphp
                <img class="w-100"
                    src="data:image/png;base64,{{ base64_encode($generator->getBarcode($product->code, $generator::TYPE_CODE_128)) }}">
            </div>
            <div class="col-12"
                style="display: flex;
            align-items: center;
            justify-content: center; letter-spacing: 12px;">
                {{ $product->code }}
            </div>
            
        </div>
    </div>

</body>
<script type="text/javascript">
    window.print();
    window.onafterprint = function() {
        window.close();
        history.go(-1);
    }
</script>

</html>
