<?php

namespace Modules\ChartOfAccounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoucherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'voucher_type_id' => [
                'required',
                Rule::exists('voucher_types', 'id')->where('company_id', auth()->user()->company_id),
            ],
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:2',
            'lines.*.account' => [
                'required',
                Rule::exists('account_level4s', 'uid')->where('company_id', auth()->user()->company_id),
            ],
            'lines.*.debit' => 'nullable|numeric|min:0.01',
            'lines.*.credit' => 'nullable|numeric|min:0.01',
            'lines.*.description' => 'nullable|string|max:500',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);

            $debitTotal = 0;
            $creditTotal = 0;

            foreach ($lines as $key => $line) {
                $hasDebit = !empty($line['debit']);
                $hasCredit = !empty($line['credit']);

                if ($hasDebit && $hasCredit) {
                    $validator->errors()->add("lines.$key", 'Line ' . ($key + 1) . ' cannot have both a debit and a credit amount.');
                    continue;
                }
                if (!$hasDebit && !$hasCredit) {
                    $validator->errors()->add("lines.$key", 'Line ' . ($key + 1) . ' needs either a debit or a credit amount.');
                    continue;
                }

                $debitTotal += $hasDebit ? (float) $line['debit'] : 0;
                $creditTotal += $hasCredit ? (float) $line['credit'] : 0;
            }

            if (abs($debitTotal - $creditTotal) > 0.005) {
                $validator->errors()->add('lines', 'Total debits (' . number_format($debitTotal, 2) . ') must equal total credits (' . number_format($creditTotal, 2) . ').');
            }
        });
    }
}
