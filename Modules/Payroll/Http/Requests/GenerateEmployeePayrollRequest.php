<?php

namespace Modules\Payroll\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateEmployeePayrollRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'period' => ['required', 'date_format:Y-m'],
        ];
    }

    public function messages()
    {
        return [
            'period.date_format' => 'The period must be a valid month (YYYY-MM).',
        ];
    }
}
