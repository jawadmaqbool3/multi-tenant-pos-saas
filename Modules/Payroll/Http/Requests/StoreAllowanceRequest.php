<?php

namespace Modules\Payroll\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAllowanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|min:2',
            'status' => 'required|in:active,inactive',
        ];
    }
}