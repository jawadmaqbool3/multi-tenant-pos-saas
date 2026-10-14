<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Price;
use Modules\Billing\Models\Signup;
use Modules\Billing\Services\PlanFeaturePresenter;
use Modules\Billing\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscribeController extends Controller
{
    protected $service;

    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    /**
     * The paywall shown to logged-in but unsubscribed tenants.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return $this->renderPricing();
    }

    /**
     * The dedicated public plans/pricing page (visible after registration).
     *
     * @return \Illuminate\Http\Response
     */
    public function pricing(Request $request)
    {
        return $this->renderPricing();
    }

    /**
     * Build and render the plans/pricing page for the current tenant.
     *
     * @return \Illuminate\Http\Response
     */
    protected function renderPricing()
    {
        $user = auth()->user();

        // Platform owners never need a plan.
        if (!$user->company_id) {
            return redirect()->route('dashboard');
        }

        $company = Company::where('id', $user->company_id)->first();

        if ($company && $company->hasActiveSubscription()) {
            return redirect()->route('dashboard');
        }

        $plans = Plan::query()
            ->where('status', 'active')
            ->where('is_public', true)
            ->with([
                'prices' => function ($query) {
                    $query->where('active', true)->orderBy('unit_amount');
                },
                'limits',
            ])
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->filter(function ($plan) {
                return $plan->prices->isNotEmpty();
            })
            ->values();

        $presenter = app(PlanFeaturePresenter::class);
        $features = $plans->mapWithKeys(function ($plan) use ($presenter) {
            return [$plan->uid => $presenter->present($plan)];
        });

        $selectedPrice = null;
        $signup = Signup::where('email', $user->email)->latest('id')->first();
        if ($signup && $signup->price_id) {
            $selectedPrice = $signup->price_id;
        }

        return view('billing::pricing', [
            'plans' => $plans,
            'features' => $features,
            'currency' => config('billing.currency'),
            'configured' => $this->service->isConfigured(),
            'selectedPrice' => $selectedPrice,
        ]);
    }

    /**
     * Start a Stripe Checkout Session for the chosen price.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

        $company = Company::where('id', $user->company_id)->firstOrFail();

        $price = Price::where('uid', $request->price)->where('active', true)->first();

        if (!$price) {
            return response([
                'error' => true,
                'message' => 'The selected plan is no longer available.',
            ]);
        }

        try {
            $url = $this->service->startCheckout($company, $price);
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

    /**
     * Handle the successful checkout redirect (webhook fallback).
     *
     * @return \Illuminate\Http\Response
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId && $this->service->isConfigured()) {
            try {
                $this->service->activateFromCheckoutSession($sessionId);
            } catch (\Throwable $e) {
                // The webhook will reconcile; don't block the redirect.
            }
        }

        return redirect()->route('dashboard')->with('message', 'Your subscription is active. Welcome aboard!');
    }

    /**
     * Handle a cancelled checkout.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        return redirect()->route('billing.pricing')->with('message', 'Checkout cancelled. Your plan was not changed.');
    }
}
