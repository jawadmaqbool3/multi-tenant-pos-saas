<?php

namespace Modules\Payroll\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayrollRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'period' => [
                'required',
                'date_format:Y-m',
                Rule::unique('payrolls', 'period')->where('company_id', auth()->user()->company_id),
            ],
            'notes' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'period.date_format' => 'The period must be a valid month (YYYY-MM).',
            'period.unique' => 'A payroll run already exists for that month.',
        ];
    }
}