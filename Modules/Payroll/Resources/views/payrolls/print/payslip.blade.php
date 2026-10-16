<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="@logo(icon)" />
    @include('layouts.style')
    <title>Payslip | {{ @$payslip->employee->name }}</title>
    <style>
        .payslip-table td,
        .payslip-table th {
            padding: 6px 10px;
        }
    </style>
</head>

<body class="bg-white">
    <div class="container-fluid">
        <div class="row border-bottom border-bottom-2 border-dark pb-3 mb-3">
            <div class="col-6">
                <h1 class="fw-bold mb-0">@setting(name)</h1>
                <div class="text-muted">{{ @$payslip->company->name }}</div>
            </div>
            <div class="col-6 text-end">
                <h3 class="fw-bold mb-0">Payslip</h3>
                <div>
                    {{ \Illuminate\Support\Carbon::parse(@$payslip->payroll->period)->format('F Y') }}
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-1">Employee</h4>
                <div>{{ @$payslip->employee->name }}</div>
                <div class="text-muted">{{ @$payslip->employee->email }}</div>
            </div>
            <div class="col-md-6 text-end">
                <h4 class="fw-bold mb-1">Status</h4>
                <div>{{ ucwords(@$payslip->payroll->status) }}</div>
                <div class="text-muted">
                    Generated {{ \Illuminate\Support\Carbon::parse($payslip->created_at)->format('d M, Y') }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped border-bottom border-dark payslip-table">
                    <thead>
                        <tr>
                            <th class="border px-3 border-dark fw-bold">Description</th>
                            <th class="border px-3 border-dark fw-bold text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-3 border-dark">Base Salary</td>
                            <td class="border px-3 border-dark text-end">
                                {{ number_format($payslip->base_salary, 2) }}</td>
                        </tr>
                        @foreach ($payslip->allowanceItems as $item)
                            <tr>
                                <td class="border px-3 border-dark">{{ @$item->allowance->name }}</td>
                                <td class="border px-3 border-dark text-end">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        @foreach ($payslip->deductionItems as $item)
                            <tr>
                                <td class="border px-3 border-dark">{{ @$item->deduction->name }}</td>
                                <td class="border px-3 border-dark text-end">-{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="border px-3 border-dark fw-bold">Total Allowances</th>
                            <th class="border px-3 border-dark text-end fw-bold">
                                {{ number_format($payslip->total_allowances, 2) }}</th>
                        </tr>
                        <tr>
                            <th class="border px-3 border-dark fw-bold">Total Deductions</th>
                            <th class="border px-3 border-dark text-end fw-bold">
                                {{ number_format($payslip->total_deductions, 2) }}</th>
                        </tr>
                        <tr>
                            <th class="border px-3 border-dark fw-bold">Net Payable</th>
                            <th class="border px-3 border-dark text-end fw-bold">
                                {{ number_format($payslip->net_salary, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if ($payslip->notes)
            <div class="mt-3">
                <h5 class="fw-bold">Notes</h5>
                <div>{{ $payslip->notes }}</div>
            </div>
        @endif
    </div>
</body>
<script type="text/javascript">
    // window.print();
</script>

</html>
