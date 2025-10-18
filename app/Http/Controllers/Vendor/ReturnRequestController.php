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
        logger()->info('Vendor approve called', [
            'vendor_user_id' => $user?->id ?? null,
            'vendor_profile_id' => $user?->vendorProfile?->id ?? null,
            'return_request_id' => $returnRequest->id,
            'current_vendor_status' => $returnRequest->vendor_status,
        ]);
        if (! $this->vendorCanAccess($user, $returnRequest)) {
            logger()->warning('vendor denied access in approve', ['vendor_profile' => $user?->vendorProfile?->id ?? null, 'rr_id' => $returnRequest->id]);
            abort(403);
        }

        $returnRequest->update([
            'vendor_status' => 'approved',
            'vendor_notes' => $request->input('vendor_notes'),
            'vendor_handled_at' => now(),
            'vendor_id' => $user->vendorProfile->id,
        ]);
        logger()->info('ReturnRequest updated by vendor', ['rr_id' => $returnRequest->id, 'vendor_status' => $returnRequest->vendor_status]);

        try {
            // notify the buyer (order owner)
            $orderUser = $returnRequest->orderItem->order->user ?? null;
            if ($orderUser) {
                $exists = \App\Models\LocalNotification::where('user_id', $orderUser->id)
                    ->where('type','return_approved')
                    ->where('data->return_request_id', $returnRequest->id)
                    ->exists();
                if (! $exists) {
                    \App\Models\LocalNotification::create([
                        'user_id' => $orderUser->id,
                        'type' => 'return_approved',
                        'data' => [
                            'return_request_id' => $returnRequest->id,
                            'order_id' => $returnRequest->orderItem->order_id,
                            'message' => sprintf('Your return request #%d has been approved by the vendor.', $returnRequest->id),
                        ],
                    ]);
                }
            }
        } catch (\Throwable $ex) {
            logger()->error('Notify buyer on return approve failed: '.$ex->getMessage());
        }
        if ($request->wantsJson()) {
            return response()->json(['status' => 'approved', 'message' => 'Return approved', 'return_request_id' => $returnRequest->id]);
        }
        return redirect()->route('vendor.returns.index')->with('success','Return approved');
    }

    public function decline(Request $request, ReturnRequest $returnRequest)
    {
        $user = $request->user('vendor');
        logger()->info('Vendor decline called', [
            'vendor_user_id' => $user?->id ?? null,
            'vendor_profile_id' => $user?->vendorProfile?->id ?? null,
            'return_request_id' => $returnRequest->id,
            'current_vendor_status' => $returnRequest->vendor_status,
        ]);
        if (! $this->vendorCanAccess($user, $returnRequest)) {
            logger()->warning('vendor denied access in decline', ['vendor_profile' => $user?->vendorProfile?->id ?? null, 'rr_id' => $returnRequest->id]);
            abort(403);
        }

        $returnRequest->update([
            'vendor_status' => 'declined',
            'vendor_notes' => $request->input('vendor_notes'),
            'vendor_handled_at' => now(),
            'vendor_id' => $user->vendorProfile->id,
        ]);
        logger()->info('ReturnRequest declined by vendor', ['rr_id' => $returnRequest->id, 'vendor_status' => $returnRequest->vendor_status]);
        if ($request->wantsJson()) {
            return response()->json(['status' => 'declined', 'message' => 'Return declined', 'return_request_id' => $returnRequest->id]);
        }
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
