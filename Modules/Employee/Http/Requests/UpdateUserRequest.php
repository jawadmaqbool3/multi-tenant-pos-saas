<?php

namespace Modules\Employee\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|min:3',
            'role' => 'required',
            'status' => 'required',
            'payroll_enabled' => 'nullable|boolean',
            'attendance_enabled' => 'nullable|boolean',
            'salary' => 'required_if:payroll_enabled,1|nullable|numeric|min:0',
        ];
    }
}
