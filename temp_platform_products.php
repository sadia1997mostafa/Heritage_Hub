<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('vendor_earnings as ve')
    ->join('order_items as oi','ve.order_id','=','oi.order_id')
    ->join('products as p','oi.product_id','=','p.id')
    ->select('p.id as product_id','p.title as product_title', DB::raw('SUM(ve.gross_amount) as total_gross'), DB::raw('SUM(COALESCE(ve.platform_fee, ve.gross_amount * 0.10)) as total_platform_fee'), DB::raw('COUNT(DISTINCT ve.id) as earnings_count'))
    ->groupBy('p.id','p.title')
    ->orderByDesc('total_platform_fee')
    ->limit(20)
    ->get()
    ->toArray();

echo json_encode($rows, JSON_PRETTY_PRINT), PHP_EOL;
