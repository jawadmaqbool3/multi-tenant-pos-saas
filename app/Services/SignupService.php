<?php

namespace App\Services;

use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Signup;
use Modules\Employee\Models\User;

class SignupService
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Provision a brand new tenant from a public self-signup.
     *
     * Guests always create active companies; commercial state is tracked in
     * billing_signups + Stripe rather than gating access to the app.
     *
     * @param  \App\Http\Requests\StoreSignupRequest  $request
     * @return \App\Models\Company
     */
    public function register($request)
    {
        $request->merge(['status' => 'active']);

        $company = $this->companyService->store($request);

        $user = User::query()
            ->where('email', $request->email)
            ->where('company_id', $company->id)
            ->first();

        [$plan, $price] = $this->resolvePlan($request->input('plan'));

        if ($user) {
            Signup::create([
                'user_id' => $user->id,
                'plan_id' => $plan ? $plan->id : null,
                'price_id' => $price ? $price->id : null,
                'email' => $user->email,
                'status' => 'provisioned',
                'metadata' => json_encode([
                    'company_id' => $company->id,
                    'company_name' => $request->company_name,
                ]),
            ]);
        }

        return $company;
    }

    /**
     * @param  string|null  $uid
     * @return array{0: \Modules\Billing\Models\Plan|null, 1: \Modules\Billing\Models\Price|null}
     */
    protected function resolvePlan($uid)
    {
        if (!$uid) {
            return [null, null];
        }

        $plan = Plan::query()
            ->where('uid', $uid)
            ->where('status', 'active')
            ->first();

        if (!$plan) {
            return [null, null];
        }

        $price = $plan->prices()
            ->where('active', true)
            ->get()
            ->sortBy(function ($price) {
                return $price->interval === 'month' ? 0 : 1;
            })
            ->first();

        return [$plan, $price];
    }
}
