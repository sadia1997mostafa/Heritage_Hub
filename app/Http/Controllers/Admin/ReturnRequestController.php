<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\Log;

class ReturnRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin-only');
    }

    public function index()
    {
        $returns = ReturnRequest::latest()->paginate(20);
        return view('admin.returns.index', ['returns' => $returns]);
    }

    public function show(ReturnRequest $returnRequest)
    {
        return view('admin.returns.show', ['r' => $returnRequest]);
    }

    public function approve(Request $request, ReturnRequest $returnRequest)
    {
        $returnRequest->update(['status' => 'approved', 'admin_status' => 'approved', 'admin_notes' => $request->input('admin_notes'), 'admin_handled_at' => now()]);

        // Create a reversal ledger entry for the vendor to adjust earnings.
        try {
            $oi = $returnRequest->orderItem ? $returnRequest->orderItem : null;
            if ($oi) {
                $order = $oi->order;
                $product = $oi->product;
                $vendorId = $product->vendor_id ?? null;
                // compute item gross (price * qty) if available
                $itemGross = ($oi->unit_price ?? $oi->price ?? 0) * ($oi->quantity ?? 1);

                // attempt to find a matching VendorEarning row
                $orig = \App\Models\VendorEarning::where('order_id', $order->id)
                        ->where('vendor_id', $vendorId)
                        ->whereRaw('ABS(gross_amount - ?) < 0.02', [$itemGross])
                        ->first();

                $status = $orig->status ?? 'pending';
                $platformFee = $orig->platform_fee ?? round($itemGross * 0.10, 2);
                $vendorShare = $orig->vendor_share ?? round($itemGross - $platformFee, 2);

                \App\Models\VendorEarning::create([
                    'order_id' => $order->id,
                    'shipment_id' => $orig->shipment_id ?? null,
                    'vendor_id' => $vendorId,
                    'gross_amount' => -1 * $itemGross,
                    'platform_fee' => -1 * $platformFee,
                    'vendor_share' => -1 * $vendorShare,
                    'status' => $status,
                ]);
            }
        } catch (\Exception $ex) {
            // swallow to avoid blocking admin flow; log for later inspection
            Log::error('Return approval reversal creation failed: '.$ex->getMessage());
        }

        return redirect()->route('admin.returns.index')->with('success','Return approved and ledger adjusted');
    }

    public function decline(Request $request, ReturnRequest $returnRequest)
    {
        $returnRequest->update(['status' => 'declined', 'admin_status' => 'declined', 'admin_notes' => $request->input('admin_notes'), 'admin_handled_at' => now()]);
        return redirect()->route('admin.returns.index')->with('success','Return declined');
    }
}
