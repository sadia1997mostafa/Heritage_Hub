<?php

namespace App\Listeners;

use App\Events\ShipmentStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\VendorEarning;
use Illuminate\Support\Facades\Log;

class HandleShipmentDelivered
{
    /**
     * Handle the event.
     */
    public function handle(ShipmentStatusUpdated $event): void
    {
        try {
            if ($event->newStatus !== 'delivered') return;
            $shipment = $event->shipment;
            // Mark vendor earnings for this shipment as available (or create if missing)
            $earnings = VendorEarning::where('shipment_id', $shipment->id)->get();
            foreach ($earnings as $e) {
                // Only update pending entries
                if ($e->status === 'pending') $e->update(['status' => 'available']);
            }
        } catch (\Throwable $ex) {
            // swallow errors to not break main flow; log for debugging
            Log::error('HandleShipmentDelivered error: '.$ex->getMessage());
        }
    }
}
