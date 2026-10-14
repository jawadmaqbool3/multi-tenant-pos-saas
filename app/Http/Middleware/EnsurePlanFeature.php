<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Modules\Billing\Services\PlanFeatureService;

class EnsurePlanFeature
{
    /**
     * @var \Modules\Billing\Services\PlanFeatureService
     */
    protected $features;

    public function __construct(PlanFeatureService $features)
    {
        $this->features = $features;
    }

    /**
     * Deny access to modules that are not part of the company's plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$required
     * @return mixed
     */
    public function handle($request, Closure $next, ...$required)
    {
        $user = auth()->user();

        if (!$user || !$user->company_id) {
            return $next($request);
        }

        $company = Company::where('id', $user->company_id)->first();

        if (!$company) {
            return $next($request);
        }

        foreach ($required as $feature) {
            if (!$this->features->companyAllowsFeature($company, $feature)) {
                abort(403, 'This module is not included in your current plan.');
            }
        }

        return $next($request);
    }
}
