<?php
namespace App\Listeners;

use App\Events\ShipmentStatusUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

class SendShipmentStatusSms implements ShouldQueue
{
    use Queueable;

    public function handle(ShipmentStatusUpdated $event)
    {
        $shipment = $event->shipment;
        // try order shipping phone, then user's phone
        $phone = $shipment->order->shipping_address['phone'] ?? $shipment->order->user->phone ?? null;
        if (!$phone) return;

        $message = "Your order #{$shipment->order->id} status changed to {$event->newStatus}.";

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_FROM');

        if ($sid && $token && $from) {
            try {
                // Requires twilio/sdk via composer
                $client = new \Twilio\Rest\Client($sid, $token);
                $client->messages->create($phone, [
                    'from' => $from,
                    'body' => $message,
                ]);
                Log::info("Sent SMS to {$phone} for shipment {$shipment->id}");
            } catch (\Exception $e) {
                Log::error('Twilio SMS failed: ' . $e->getMessage());
            }
            return;
        }

        // Fallback: log the SMS (no external provider configured)
        Log::info("SMS to {$phone}: {$message}");
    }
}
