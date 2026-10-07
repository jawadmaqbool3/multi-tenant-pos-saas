<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'supplier' => 'required',
            'product' => 'required',
            'quantity' => 'required',
            'purchase' => 'required',
            'retail' => 'required'
        ];
    }
}
