<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
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
            'company_name' => 'required|min:3',
            'company_email' => 'required|unique:settings,email',
            'status' => 'required'
        ];
    }
}
