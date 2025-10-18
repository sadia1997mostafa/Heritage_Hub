<?php

namespace App\Listeners;

use App\Events\ShipmentStatusUpdated;
use App\Models\LocalNotification;

class CreateShipmentNotification
{
    public function handle(ShipmentStatusUpdated $event)
    {
        try {
            // only create notification when status actually changed
            if (($event->oldStatus ?? null) === ($event->newStatus ?? null)) return;

            $shipment = $event->shipment->loadMissing('order.user');
            $order = $shipment->order;
            if (! $order || ! $order->user) return;

            $message = sprintf('Your order #%s status changed to %s', $order->id, $event->newStatus);

            // strict dedupe: avoid duplicate notifications for same shipment+status (regardless of read)
            $exists = LocalNotification::where('user_id', $order->user->id)
                ->where('type', 'shipment_status')
                ->where('data->shipment_id', $shipment->id)
                ->where('data->status', $event->newStatus)
                ->exists();

            if (! $exists) {
                LocalNotification::create([
                    'user_id' => $order->user->id,
                    'type' => 'shipment_status',
                    'data' => ['shipment_id' => $shipment->id, 'order_id' => $order->id, 'status' => $event->newStatus, 'message' => $message],
                ]);
            }

        } catch (\Throwable $e) {
            logger()->error('CreateShipmentNotification error: '.$e->getMessage());
        }
    }
}
