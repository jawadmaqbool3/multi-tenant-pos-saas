<?php

namespace Modules\Billing\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => [
                'required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('billing_coupons', 'code'),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['required', Rule::in(['percent', 'amount'])],
            'discount_value' => ['required', 'numeric', 'gt:0'],
            'currency' => ['nullable', 'string', 'size:3', 'required_if:discount_type,amount'],
            'duration' => ['required', Rule::in(['once', 'repeating', 'forever'])],
            'duration_in_months' => ['nullable', 'integer', 'min:1', 'required_if:duration,repeating'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->discount_type === 'percent' && (float) $this->discount_value > 100) {
                $validator->errors()->add('discount_value', 'A percentage discount cannot exceed 100%.');
            }
        });
    }
}
