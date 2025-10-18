<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$vendorId = 3;
use Illuminate\Support\Facades\DB;
$received = \App\Models\VendorEarning::where('vendor_id',$vendorId)->whereIn('status',['available','paid'])->sum('gross_amount');
$platform = \App\Models\VendorEarning::where('vendor_id',$vendorId)->whereIn('status',['available','paid'])->sum(DB::raw('COALESCE(platform_fee, gross_amount * 0.10)'));
$pending = \App\Models\VendorEarning::where('vendor_id',$vendorId)->where('status','pending')->sum('gross_amount');
echo json_encode(['received'=>$received,'platform'=>$platform,'vendor_revenue'=>$received - $platform,'pending'=>$pending], JSON_PRETTY_PRINT), PHP_EOL;
