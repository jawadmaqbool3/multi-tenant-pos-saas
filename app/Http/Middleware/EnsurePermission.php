<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePermission
{
    /**
     * Allow the request only when the authenticated user has one of the
     * given permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        if (method_exists($user, 'hasPermission')) {
            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'You are not authorized to access this resource.');
    }
}
