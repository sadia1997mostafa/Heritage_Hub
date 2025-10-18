<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$shipments = \App\Models\Shipment::where('status','delivered')->orderBy('id','desc')->take(10)->get(['id','vendor_id','order_id','status'])->toArray();
echo json_encode($shipments, JSON_PRETTY_PRINT);
