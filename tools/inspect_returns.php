<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReturnRequest;

$rows = ReturnRequest::with(['orderItem.product','orderItem.order'])->orderByDesc('id')->take(10)->get();
foreach ($rows as $r) {
    $oi = $r->orderItem;
    $prod = $oi?->product;
    $order = $oi?->order;
    echo "ReturnRequest {$r->id}: vendor_id={$r->vendor_id}, order_item_id={$r->order_item_id}, vendor_status={$r->vendor_status}\n";
    if ($oi) {
        echo "  OrderItem id={$oi->id}, order_id={$oi->order_id}, product_id={$oi->product_id}\n";
        echo "    Product vendor_id=".($prod?->vendor_id ?? 'NULL')." title=".($prod?->title ?? 'N/A')."\n";
        echo "    Order user_id=".($order?->user_id ?? 'NULL')."\n";
    } else {
        echo "  No orderItem relation\n";
    }
}
