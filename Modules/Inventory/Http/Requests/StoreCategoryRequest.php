<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'thumbnail' => 'mimes:jpeg,jpg,bmp,png|max:2000',
            'status' => 'required'
        ];
    }
}
