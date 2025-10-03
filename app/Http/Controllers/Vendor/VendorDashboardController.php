<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;

class VendorDashboardController extends Controller
{
    public function index()
    {
        $user = auth('vendor')->user(); // the User model (logged in via vendor guard)
        $profile = $user?->vendorProfile; // optional
        return view('vendor.dashboard', compact('user','profile'));
    }
}
