<?php

namespace Modules\Payroll\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayslipRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'allowances' => 'nullable|array',
            'allowances.*.allowance' => 'nullable|exists:allowances,uid',
            'allowances.*.amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*.deduction' => 'nullable|exists:deductions,uid',
            'deductions.*.amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable',
        ];
    }
}