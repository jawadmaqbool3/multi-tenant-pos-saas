<?php

namespace Modules\Employee\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'password' => 'required|same:co_password',
            'co_password' => 'required|min:8',
            'email' => 'required|unique:users,email',
            'name' => 'required|min:3',
            'role' => 'required',
            'status' => 'required',
            'payroll_enabled' => 'nullable|boolean',
            'attendance_enabled' => 'nullable|boolean',
            'salary' => 'required_if:payroll_enabled,1|nullable|numeric|min:0',
        ];
    }
}
