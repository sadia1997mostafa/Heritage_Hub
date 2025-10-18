<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Events\ShipmentStatusUpdated;
use Illuminate\Support\Facades\Auth;

class VendorOrderController extends Controller
{
    public function __construct()
    {
        // Route group already applies auth:vendor and vendor.approved middleware.
        // Ensure controller uses the vendor guard if invoked directly.
        $this->middleware('auth:vendor');
        // The route group already applies auth:vendor and vendor.approved middleware,
        // so avoid re-running the gate here (it may use a different default guard).
    }

    public function index()
    {

        // Use the vendor guard to get the authenticated vendor user
        $vendorUser = auth('vendor')->user();
        $vendor = $vendorUser?->vendorProfile ?? null;
        if (!$vendor) abort(403);

        $shipments = Shipment::with('order','order.items','order.items.product')
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(20);

        return view('vendor.orders.index', compact('shipments'));
    }

    public function show(Shipment $shipment)
    {
        $vendorUser = auth('vendor')->user();
        $vendor = $vendorUser?->vendorProfile ?? null;
        if (!$vendor || $shipment->vendor_id !== $vendor->id) abort(403);

        $shipment->load('order','order.items','order.items.product');
        return view('vendor.orders.show', compact('shipment'));
    }

    // Quick action to mark as shipped (simple stub)
    public function quickShip(Shipment $shipment)
    {
        $vendorUser = auth('vendor')->user();
        $vendor = $vendorUser?->vendorProfile ?? null;
        if (!$vendor || $shipment->vendor_id !== $vendor->id) abort(403);

        $old = $shipment->status;
        $shipment->update(['status' => 'shipped']);
        event(new ShipmentStatusUpdated($shipment, $old, 'shipped'));
        return back()->with('status', 'Marked shipped');
    }

    // Ledger stub
    public function ledger()
    {
        $vendorUser = auth('vendor')->user();
        $vendor = $vendorUser?->vendorProfile ?? null;
        if (!$vendor) abort(403);

        $entries = \App\Models\VendorEarning::where('vendor_id', $vendor->id)->latest()->get();

        // group entries by status
        $receivedEntries = $entries->whereIn('status', ['available','paid']);
        $pendingEntries = $entries->where('status', 'pending');

        // sums
        // Total received amount = total the buyers paid (we treat gross_amount as the buyer-paid item total)
        $received = $receivedEntries->sum('gross_amount');
        // Platform fee = 10% of the received gross (we prefer using stored platform_fee if available)
        $receivedPlatformFee = $receivedEntries->sum(function($e){
            return $e->platform_fee ?? round(($e->gross_amount * 0.10), 2);
        });
        // Vendor revenue = received - platform fee (or sum of vendor_share if that already includes shipping adjustments)
        $vendorRevenue = $received - $receivedPlatformFee;

        $pending = $pendingEntries->sum('gross_amount');
        $pendingPlatformFee = $pendingEntries->sum(function($e){
            return $e->platform_fee ?? round(($e->gross_amount * 0.10), 2);
        });
        $pendingVendorRevenue = $pending - $pendingPlatformFee;

        $totalGross = $entries->sum('gross_amount');
        $totalPlatformFee = $entries->sum('platform_fee');

        // pass computed platform/vendor totals so blade can display them
        return view('vendor.ledger', compact(
            'entries',
            'receivedEntries',
            'pendingEntries',
            'received',
            'receivedPlatformFee',
            'vendorRevenue',
            'pending',
            'pendingPlatformFee',
            'pendingVendorRevenue',
            'totalGross',
            'totalPlatformFee'
        ));
    }

    // Packing slip printable view
    public function packingSlip(Shipment $shipment)
    {
        $vendorUser = auth('vendor')->user();
        $vendor = $vendorUser?->vendorProfile ?? null;
        if (!$vendor || $shipment->vendor_id !== $vendor->id) abort(403);

        $shipment->load('order','order.items','order.items.product');
        return view('vendor.orders.packing-slip', compact('shipment'));
    }

    public function updateStatus(Request $req, Shipment $shipment)
    {
        $vendorUser = auth('vendor')->user();
        $vendor = $vendorUser?->vendorProfile ?? null;
        if (!$vendor || $shipment->vendor_id !== $vendor->id) abort(403);

    $req->validate(['status'=>'required|in:processing,approved,shipped,delivered']);
        $old = $shipment->status;
        $shipment->update(['status'=>$req->status,'tracking_number'=>$req->tracking_number]);
        event(new ShipmentStatusUpdated($shipment, $old, $req->status));
        return back()->with('status','Updated');
    }
}
