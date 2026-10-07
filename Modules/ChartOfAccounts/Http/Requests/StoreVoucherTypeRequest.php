<?php

namespace Modules\ChartOfAccounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'short' => 'required'
        ];
    }
}
