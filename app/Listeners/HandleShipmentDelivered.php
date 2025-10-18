<?php

namespace App\Listeners;

use App\Events\ShipmentStatusUpdated;
use App\Models\VendorEarning;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HandleShipmentDelivered
{
    /**
     * Handle the event.
     */
    public function handle(ShipmentStatusUpdated $event): void
    {
        try {
            if ($event->newStatus !== 'delivered') return;

            $shipment = $event->shipment->loadMissing('order');

            DB::transaction(function() use ($shipment) {
                // 1) Mark existing vendor_earnings for this shipment as available
                $earnings = VendorEarning::where('shipment_id', $shipment->id)->get();
                foreach ($earnings as $e) {
                    if ($e->status === 'pending') {
                        $e->update(['status' => 'available']);
                    }
                }

                // 2) Create missing vendor_earnings for order items belonging to this shipment's vendor
                $order = $shipment->order;
                if (!$order) return;

                $items = $order->items()->with('product')->get();
                foreach ($items as $item) {
                    $product = $item->product;
                    if (!$product) continue;
                    if ($product->vendor_id != $shipment->vendor_id) continue;

                    $itemGross = ($item->unit_price ?? $item->price ?? 0) * ($item->quantity ?? 1);

                    // If an earning already exists for this shipment & approx amount, skip
                    $exists = VendorEarning::where('shipment_id', $shipment->id)
                                ->where('vendor_id', $shipment->vendor_id)
                                ->whereRaw('ABS(gross_amount - ?) < 0.02', [$itemGross])
                                ->exists();
                    if ($exists) continue;

                    $platformFee = round($itemGross * 0.10, 2);
                    $vendorShare = round($itemGross - $platformFee, 2);

                    VendorEarning::create([
                        'order_id' => $order->id,
                        'shipment_id' => $shipment->id,
                        'vendor_id' => $shipment->vendor_id,
                        'gross_amount' => $itemGross,
                        'platform_fee' => $platformFee,
                        'vendor_share' => $vendorShare,
                        'status' => 'available',
                    ]);
                }
            });
        } catch (\Throwable $ex) {
            // swallow errors to not break main flow; log for debugging
            Log::error('HandleShipmentDelivered error: '.$ex->getMessage());
        }
    }
}
