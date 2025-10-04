<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\VendorProfile;

class EnsureVendorApproved
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (!$u) abort(401);

        $p = VendorProfile::where('user_id',$u->id)->first();
        if (!$p || $p->status !== 'approved') {
            abort(403, 'Vendor not approved yet.');
        }
        return $next($request);
    }
}
