<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Services\SubscriptionService;
use Modules\Billing\Services\PlanFeaturePresenter;

class SubscribeController extends Controller
{
    protected $service;

    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    public function show(Request $request)
    {
        return $this->renderPricing();
    }

    public function pricing(Request $request)
    {
        return $this->renderPricing();
    }

    protected function renderPricing()
    {
        $user = auth()->user();

        if (!$user->company_id) {
            return redirect()->route('dashboard');
        }

        $company = $this->service->getCompany($user->company_id);

        if ($company && $company->hasActiveSubscription()) {
            return redirect()->route('dashboard');
        }

        $plans = $this->service->getPublicPlans();
        $presenter = app(PlanFeaturePresenter::class);
        $features = $plans->mapWithKeys(function ($plan) use ($presenter) {
            return [$plan->uid => $presenter->present($plan)];
        });

        $selectedPrice = $this->service->getSelectedPrice($user->email);

        return view('billing::pricing', [
            'plans' => $plans,
            'features' => $features,
            'currency' => config('billing.currency'),
            'configured' => $this->service->isConfigured(),
            'selectedPrice' => $selectedPrice,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'price' => 'required|exists:billing_prices,uid',
        ]);

        $user = auth()->user();

        if (!$user->company_id) {
            return response([
                'error' => true,
                'message' => 'Your account does not need a subscription.',
            ]);
        }

        if (!$this->service->isConfigured()) {
            return response([
                'error' => true,
                'message' => 'Billing is not configured yet. Please contact support.',
            ]);
        }

        $company = $this->service->getCompany($user->company_id);

        $price = $this->service->getActivePrice($request->price);

        if (!$price) {
            return response([
                'error' => true,
                'message' => 'The selected plan is no longer available.',
            ]);
        }

        $coupon = null;
        if ($request->filled('coupon')) {
            $code = strtoupper(trim((string) $request->coupon));
            $coupon = $this->service->getCoupon($code);

            if (!$coupon || !$coupon->isRedeemable()) {
                return response([
                    'error' => true,
                    'message' => 'That promo code is invalid or has expired.',
                ]);
            }
        }

        try {
            $url = $this->service->startCheckout($company, $price, $coupon);
        } catch (\Throwable $e) {
            return response([
                'error' => true,
                'message' => $e->getMessage() ?: 'Could not start checkout.',
            ]);
        }

        return response([
            'success' => true,
            'message' => 'Redirecting to checkout...',
            'redirect' => true,
            'url' => $url,
        ]);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId && $this->service->isConfigured()) {
            try {
                $this->service->activateFromCheckoutSession($sessionId);
            } catch (\Throwable $e) {
                //
            }
        }

        return redirect()->route('dashboard');
    }

    public function cancel(Request $request)
    {
        return redirect()->route('billing.subscribe.show');
    }
}
