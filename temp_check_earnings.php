<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$earnings = \App\Models\VendorEarning::where('shipment_id',15)->get()->map(function($e){
    return [
        'id'=>$e->id,
        'order_id'=>$e->order_id,
        'vendor_id'=>$e->vendor_id,
        'gross_amount'=>$e->gross_amount,
        'platform_fee'=>$e->platform_fee,
        'vendor_share'=>$e->vendor_share,
        'status'=>$e->status,
    ];
})->toArray();
echo json_encode($earnings, JSON_PRETTY_PRINT);
