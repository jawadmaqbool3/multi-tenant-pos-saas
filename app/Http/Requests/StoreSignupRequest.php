<?php

namespace App\Http\Requests;

use App\Exceptions\EmailAlreadyTakenException;
use App\Exceptions\InvalidFieldException;
use App\Exceptions\MissingFieldException;
use App\Exceptions\PasswordMismatchException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Billing\Models\Plan;

class StoreSignupRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'company_name' => 'required|min:3|max:255',
            'company_email' => 'required|email|unique:settings,email',
            'company_address' => 'nullable|string|max:255',
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'plan' => ['required', 'exists:billing_plans,uid', function ($attribute, $value, $fail) {
                $plan = Plan::where('uid', $value)->where('status', 'active')->first();

                if (!$plan) {
                    $fail('The selected plan is not available.');
                    return;
                }

                $hasPaidPrice = $plan->prices()
                    ->where('active', true)
                    ->where('unit_amount', '>', 0)
                    ->exists();

                if (!$hasPaidPrice) {
                    $fail('The selected plan does not have an active paid price.');
                }
            }],
        ];
    }

    /**
     * Surface the first validation failure to the frontend as a domain
     * exception that carries the offending field, instead of the generic
     * "Something went wrong" envelope.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \App\Exceptions\CustomException
     */
    protected function failedValidation(Validator $validator)
    {
        $failed = $validator->failed();
        $field = array_key_first($failed) ?: 'signup';
        $rules = array_change_key_case($failed[$field] ?? [], CASE_LOWER);
        $message = $validator->errors()->first($field);

        if (array_key_exists('required', $rules)) {
            throw new MissingFieldException($field, $message);
        }

        if (array_key_exists('unique', $rules)) {
            throw new EmailAlreadyTakenException($field, $message);
        }

        if (array_key_exists('confirmed', $rules)) {
            throw new PasswordMismatchException($field . '_confirmation', $message);
        }

        throw new InvalidFieldException($field, $message);
    }
}
