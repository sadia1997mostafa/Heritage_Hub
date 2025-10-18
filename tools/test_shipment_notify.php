<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Shipment;
use App\Events\ShipmentStatusUpdated;
use App\Models\LocalNotification;

$shipment = Shipment::where('status','processing')->orWhere('status','approved')->orderBy('id','desc')->first();
if (! $shipment) $shipment = Shipment::orderBy('id','desc')->first();
if (! $shipment) { echo "No shipments found\n"; exit;
}

$old = $shipment->status;
$shipment->update(['status'=>'shipped']);
event(new ShipmentStatusUpdated($shipment, $old, 'shipped'));

$notifications = LocalNotification::where('user_id', $shipment->order->user_id)->orderBy('id','desc')->take(5)->get();

echo "Shipment {$shipment->id} updated to shipped. Notifications for user {$shipment->order->user_id}:\n";
foreach ($notifications as $n) {
    echo "- [".($n->is_read? 'read' : 'unread')."] {$n->type} : ".($n->data['message'] ?? json_encode($n->data))."\n";
}
