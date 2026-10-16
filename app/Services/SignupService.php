<?php

namespace App\Services;

use Modules\Employee\Repositories\UserRepository;
use Modules\Billing\Repositories\PlanRepository;
use Modules\Billing\Repositories\PriceRepository;
use Modules\Billing\Repositories\SignupRepository;

class SignupService
{
    protected $companyService;
    protected $users;
    protected $signups;
    protected $plans;
    protected $prices;

    public function __construct(
        CompanyService $companyService,
        UserRepository $users,
        SignupRepository $signups,
        PlanRepository $plans,
        PriceRepository $prices
    ) {
        $this->companyService = $companyService;
        $this->users = $users;
        $this->signups = $signups;
        $this->plans = $plans;
        $this->prices = $prices;
    }

    /**
     * Provision a brand new tenant from a public self-signup.
     *
     * @param  \App\Http\Requests\StoreSignupRequest  $request
     * @return \App\Models\Company
     */
    public function register($request)
    {
        $request->merge(['status' => 'active']);

        $company = $this->companyService->store($request);

        if ($request->filled('plan')) {
            $this->recordSignup($company, $request);
        }

        return $company;
    }

    /**
     * Find a registered user for a company by email.
     *
     * @param  int|string  $companyId
     * @param  string  $email
     * @return \Modules\Employee\Models\User|null
     */
    public function findUser($companyId, string $email)
    {
        return $this->users->query()
            ->where('email', $email)
            ->where('company_id', $companyId)
            ->first();
    }

    /**
     * Persist the signup record so pricing/checkout can pre-select the plan.
     *
     * @param  \App\Models\Company  $company
     * @param  \App\Http\Requests\StoreSignupRequest  $request
     * @return void
     */
    protected function recordSignup($company, $request)
    {
        $user = $this->users->query()
            ->where('company_id', $company->id)
            ->where('email', $request->email)
            ->first();

        $plan = $this->plans->query()->where('uid', $request->plan)->first();

        if (!$user || !$plan) {
            return;
        }

        $price = $this->prices->query()
            ->where('plan_id', $plan->id)
            ->where('active', true)
            ->orderBy('unit_amount')
            ->first();

        $this->signups->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'price_id' => $price ? $price->id : null,
        ]);
    }
}
