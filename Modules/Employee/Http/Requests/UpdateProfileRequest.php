<?php

namespace Modules\Employee\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'nullable|min:8|same:co_password',
            'co_password' => 'nullable|min:8',
            'image' => 'nullable|image|mimes:png,jpg,jpeg',
        ];
    }
}
