<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReturnRequest;
use App\Models\OrderItem;

$oi = OrderItem::first();
if (! $oi) { echo "No order items in DB to attach return to\n"; exit; }
$r = ReturnRequest::create([
    'order_item_id' => $oi->id,
    'user_id' => $oi->order->user_id ?? 1,
    'reason' => 'Test pending return',
]);

echo "Created ReturnRequest id={$r->id}\n";
