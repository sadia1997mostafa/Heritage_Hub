<?php
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) { echo json_encode(['error'=>'vendor autoload missing']); exit(1); }
require $autoload;
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = \App\Models\VendorProfileImage::limit(50)->get()->map(function($r){ return ['id'=>$r->id,'path'=>$r->path,'vendor_profile_id'=>$r->vendor_profile_id,'created_at'=>(string)$r->created_at]; });
echo json_encode($rows,JSON_PRETTY_PRINT);
