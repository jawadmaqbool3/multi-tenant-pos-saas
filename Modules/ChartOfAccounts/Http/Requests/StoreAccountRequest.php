<?php

namespace Modules\ChartOfAccounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'account' => 'required',
            'status' => 'required'
        ];
    }
}
