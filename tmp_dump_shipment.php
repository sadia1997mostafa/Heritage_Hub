<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$shipment = \App\Models\Shipment::find(1);
if (!$shipment) { echo "no-shipment\n"; exit; }
print_r($shipment->toArray());
