<?php

namespace Modules\Payroll\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'required|in:processed,paid',
        ];
    }
}