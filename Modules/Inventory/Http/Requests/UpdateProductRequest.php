<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'thumbnail' => 'sometimes|required|mimes:jpeg,jpg,bmp,png|max:2000'
        ];
    }
}
