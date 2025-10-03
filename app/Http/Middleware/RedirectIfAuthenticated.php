<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * If someone hits a guest route (like /login) but is already authenticated,
     * redirect them based on the active guard:
     *  - admin  -> admin.dashboard
     *  - vendor -> vendor.dashboard
     *  - web    -> home
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        // Always prefer explicit guards first
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.dashboard');
        }

        // Fallback to the provided/default guard (web)
        foreach ($guards as $guard) {
            if (Auth::guard($guard ?: 'web')->check()) {
                return redirect()->route('home'); // normal users go to home
            }
        }

        return $next($request);
    }
}
