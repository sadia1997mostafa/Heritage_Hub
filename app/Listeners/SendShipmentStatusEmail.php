<?php
namespace App\Listeners;

use App\Events\ShipmentStatusUpdated;
use App\Mail\CustomerShipmentStatusChanged;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendShipmentStatusEmail
{
    public function handle(ShipmentStatusUpdated $event)
    {
        $shipment = $event->shipment;
        $userEmail = $shipment->order->user->email ?? null;
        if (! $userEmail) return;

        try {
            // Send immediately so customers receive email even if queue worker isn't running
            Mail::to($userEmail)->send(new CustomerShipmentStatusChanged($shipment, $event->oldStatus, $event->newStatus));
            Log::info("Shipment status email sent to {$userEmail} for shipment {$shipment->id}");
        } catch (\Exception $e) {
            Log::error('Failed to send shipment status email: ' . $e->getMessage());
        }
    }
}
