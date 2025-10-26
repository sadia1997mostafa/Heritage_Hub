<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
  
    public function index(Request $request)
    {
        // get the vendor profile linked to the logged-in user
        $vendor = VendorProfile::with('user', 'district')
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Fetch recent shipments/orders for this vendor (show last 6)
        $shipments = \App\Models\Shipment::with('order')
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->take(6)
            ->get();

        // Pending return requests for this vendor
        $returnRequests = \App\Models\ReturnRequest::where('vendor_id', $vendor->id)
            ->where(function($q){ $q->whereNull('vendor_status')->orWhere('vendor_status','pending'); })
            ->latest()->take(6)->get();

        return view('vendor.dashboard', compact('vendor','shipments','returnRequests'));
    }
}

