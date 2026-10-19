<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'status' => ['required', Rule::in(['published', 'scheduled', 'unpublished'])],
            'thumbnail' => 'sometimes|required|mimes:jpeg,jpg,bmp,png|max:2000'
        ];
    }
}
