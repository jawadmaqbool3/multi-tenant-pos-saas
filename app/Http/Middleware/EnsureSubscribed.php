<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;

class EnsureSubscribed
{
    /**
     * Allow the request only when the authenticated user's company has an
     * active subscription. Platform owners (no company) bypass the wall.
     *
     * Unsubscribed tenants are funnelled to the subscribe/paywall page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Auth, checkout and webhook endpoints must stay reachable while the
        // wall is up (otherwise a blocked tenant could never subscribe).
        if ($request->is(
            'login',
            'logout',
            'signup',
            'subscribe',
            'subscribe/*',
            'stripe/webhook',
            'clear-cache'
        )) {
            return $next($request);
        }

        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Platform owners and internal accounts are never gated.
        if (!$user->company_id) {
            return $next($request);
        }

        $company = Company::where('id', $user->company_id)->first();

        if ($company && $company->hasActiveSubscription()) {
            return $next($request);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response([
                'warning' => true,
                'message' => 'Please choose a plan to continue.',
                'redirect' => true,
                'url' => route('billing.subscribe'),
            ]);
        }

        return redirect()->route('billing.subscribe');
    }
}
