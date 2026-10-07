<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'status' => 'required',
            'thumbnail' => 'sometimes|required|mimes:jpeg,jpg,bmp,png|max:2000'
        ];
    }
}
