<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Http\Requests\ChangePlanRequest;
use Modules\Billing\Services\CompanySubscriptionService;

/**
 * Tenant self-service subscription page: current plan, live resource usage,
 * and in-place upgrade/downgrade.
 */
class CompanySubscriptionController extends Controller
{
    protected $service;

    public function __construct(CompanySubscriptionService $service)
    {
        $this->service = $service;
    }

    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $company = $this->service->company();

        if (!$company) {
            return redirect()->route('dashboard');
        }

        return view('billing::subscription', $this->service->overview($company));
    }

    /**
     * Change the company's plan (upgrade/downgrade).
     *
     * @param  \Modules\Billing\Http\Requests\ChangePlanRequest  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function changePlan(ChangePlanRequest $request)
    {
        $company = $this->service->company();

        if (!$company) {
            return response([
                'error' => true,
                'message' => 'Your account does not need a subscription.',
            ]);
        }

        $result = $this->service->changePlan($company, $request->input('price'));

        $message = $result['plan']->name . ' plan is now active.';

        if ($result['stripe_updated']) {
            $message .= ' Your next invoice has been prorated.';
        }

        return response([
            'success' => true,
            'message' => $message,
            'reload' => true,
        ]);
    }
}
