<?php

namespace Modules\Billing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('billing_plans', 'slug')->ignore($this->plan)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort' => ['nullable', 'integer'],
            'prices' => ['nullable', 'array'],
            'prices.*.unit_amount' => ['nullable', 'integer', 'min:0'],
            'prices.*.currency' => ['required_with:prices.*.unit_amount', 'string', 'size:3'],
            'prices.*.interval' => ['nullable', Rule::in(['month', 'year'])],
            'limits' => ['nullable', 'array'],
        ];
    }
}