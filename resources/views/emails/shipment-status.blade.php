<p>Dear {{ $shipment->order->shipping_address['name'] ?? 'Customer' }},</p>
<p>Your shipment #{{ $shipment->id }} status has been updated to <strong>{{ $newStatus }}</strong>.</p>
<p>Order #{{ $shipment->order->id }} — thank you for shopping with {{ config('app.name') }}.</p>
