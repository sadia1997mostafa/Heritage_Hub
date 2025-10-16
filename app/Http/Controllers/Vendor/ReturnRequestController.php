<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnRequest;

class ReturnRequestController extends Controller
{
    public function __construct()
    {
    // Ensure the vendor is authenticated via the vendor guard and approved
    $this->middleware(['auth:vendor', 'vendor.approved']);
    }

    public function index(Request $request)
    {
        $vendorId = $request->user()->vendorProfile->id;
        // fetch return requests where orderItem->product->vendor_id == vendorId
        $returns = ReturnRequest::whereHas('orderItem.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->latest()->paginate(20);

        return view('vendor.returns.index', ['returns' => $returns]);
    }

    public function show(ReturnRequest $returnRequest)
    {
        $user = request()->user('vendor');
        if (! $this->vendorCanAccess($user, $returnRequest)) {
            logger()->warning('vendor denied access in controller show', ['vendor_profile' => $user?->vendorProfile?->id ?? null, 'rr_id' => $returnRequest->id]);
            abort(403, 'Unauthorized action.');
        }

        return view('vendor.returns.show', ['r' => $returnRequest]);
    }

    public function approve(Request $request, ReturnRequest $returnRequest)
    {
        $user = $request->user('vendor');
        if (! $this->vendorCanAccess($user, $returnRequest)) abort(403);
        $returnRequest->update(['vendor_status' => 'approved','vendor_notes'=>$request->input('vendor_notes'), 'vendor_handled_at' => now(), 'vendor_id' => $user->vendorProfile->id]);
        return redirect()->route('vendor.returns.index')->with('success','Return approved');
    }

    public function decline(Request $request, ReturnRequest $returnRequest)
    {
        $user = $request->user('vendor');
        if (! $this->vendorCanAccess($user, $returnRequest)) abort(403);
        $returnRequest->update(['vendor_status' => 'declined','vendor_notes'=>$request->input('vendor_notes'), 'vendor_handled_at' => now(), 'vendor_id' => $user->vendorProfile->id]);
        return redirect()->route('vendor.returns.index')->with('success','Return declined');
    }

    private function vendorCanAccess($user, ReturnRequest $returnRequest): bool
    {
        if (! $user) return false;
        $vpId = $user->vendorProfile?->id ?? null;
        if (! $vpId) return false;

        // If return_request has vendor_id set, compare directly
        if ($returnRequest->vendor_id) {
            return $returnRequest->vendor_id === $vpId;
        }

        $oi = $returnRequest->orderItem;
        if (! $oi) return false;
        // Try loaded product first
        if ($oi->relationLoaded('product')) {
            return ($oi->product?->vendor_id ?? null) === $vpId;
        }

        // fallback lookup
        $prod = \App\Models\Product::find($oi->product_id);
        return ($prod?->vendor_id ?? null) === $vpId;
    }
}
