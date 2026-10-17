<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'thumbnail' => 'sometimes|required|mimes:jpeg,jpg,bmp,png|max:2000',
            'status' => ['sometimes', Rule::in(['published', 'scheduled', 'unpublished'])],
        ];
    }
}
