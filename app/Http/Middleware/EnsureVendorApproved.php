<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\VendorProfile;

class EnsureVendorApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the authenticated vendor from the vendor guard
        $vendorUser = $request->user('vendor');

        if (!$vendorUser) {
            // If not authenticated as vendor, send them to login
            return redirect()->route('login')->withErrors([
                'login' => 'Please log in as vendor first.',
            ]);
        }

        // Fetch vendor profile
        $profile = VendorProfile::where('user_id', $vendorUser->id)->first();

        if (!$profile || $profile->status !== 'approved') {
            // If no profile or not yet approved, block access
            abort(403, 'Your vendor account is not approved yet.');
        }

        return $next($request);
    }
}

