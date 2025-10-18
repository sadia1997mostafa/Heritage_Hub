<?php
// tools/backfill_delivered_earnings.php
// Usage: php tools/backfill_delivered_earnings.php [--apply]
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apply = in_array('--apply', $argv);

use App\Models\Shipment;
use App\Models\VendorEarning;
use Illuminate\Support\Facades\DB;

$shipments = Shipment::where('status','delivered')->orderBy('id','desc')->get();

$report = [
    'shipments_checked' => 0,
    'shipments_with_missing_earnings' => 0,
    'shipments_with_pending_earnings' => 0,
    'earnings_to_create' => 0,
    'earnings_to_mark_available' => 0,
    'examples' => [],
];

foreach ($shipments as $shipment) {
    $report['shipments_checked']++;
    $order = $shipment->order;
    if (!$order) continue;

    $items = $order->items()->with('product')->get();

    $hasMissing = false;
    $hasPending = false;
    foreach ($items as $item) {
        $product = $item->product;
        if (!$product) continue;
        if ($product->vendor_id != $shipment->vendor_id) continue;

        $itemGross = round(($item->unit_price ?? $item->price ?? 0) * ($item->quantity ?? 1), 2);

        // Check for existing earning approx equal to itemGross
        $exists = VendorEarning::where('shipment_id', $shipment->id)
                    ->where('vendor_id', $shipment->vendor_id)
                    ->whereRaw('ABS(gross_amount - ?) < 0.02', [$itemGross])
                    ->exists();
        if (!$exists) {
            $hasMissing = true;
            $report['earnings_to_create']++;
            if (count($report['examples']['create'] ?? []) < 5) {
                $report['examples']['create'][] = [
                    'shipment_id' => $shipment->id,
                    'order_id' => $order->id,
                    'vendor_id' => $shipment->vendor_id,
                    'item_gross' => $itemGross,
                ];
            }
        }
    }

    $earnings = VendorEarning::where('shipment_id', $shipment->id)->get();
    foreach ($earnings as $e) {
        if ($e->status === 'pending') {
            $hasPending = true;
            $report['earnings_to_mark_available']++;
            if (count($report['examples']['mark'] ?? []) < 5) {
                $report['examples']['mark'][] = [
                    'earning_id' => $e->id,
                    'shipment_id' => $shipment->id,
                    'order_id' => $e->order_id,
                    'vendor_id' => $e->vendor_id,
                    'gross_amount' => $e->gross_amount,
                    'platform_fee' => $e->platform_fee,
                    'vendor_share' => $e->vendor_share,
                ];
            }
        }
    }

    if ($hasMissing) $report['shipments_with_missing_earnings']++;
    if ($hasPending) $report['shipments_with_pending_earnings']++;

    // If apply, perform the work in a transaction
    if ($apply && ($hasMissing || $hasPending)) {
        DB::transaction(function() use ($shipment, $order, &$report) {
            $items = $order->items()->with('product')->get();
            foreach ($items as $item) {
                $product = $item->product;
                if (!$product) continue;
                if ($product->vendor_id != $shipment->vendor_id) continue;

                $itemGross = round(($item->unit_price ?? $item->price ?? 0) * ($item->quantity ?? 1), 2);

                $exists = VendorEarning::where('shipment_id', $shipment->id)
                            ->where('vendor_id', $shipment->vendor_id)
                            ->whereRaw('ABS(gross_amount - ?) < 0.02', [$itemGross])
                            ->exists();
                if (!$exists) {
                    $platformFee = round($itemGross * 0.10, 2);
                    $vendorShare = round($itemGross - $platformFee, 2);

                    VendorEarning::create([
                        'order_id' => $order->id,
                        'shipment_id' => $shipment->id,
                        'vendor_id' => $shipment->vendor_id,
                        'gross_amount' => $itemGross,
                        'platform_fee' => $platformFee,
                        'vendor_share' => $vendorShare,
                        'status' => 'available',
                    ]);
                    $report['applied_created'] = ($report['applied_created'] ?? 0) + 1;
                }
            }

            // mark pending earnings available
            $pending = VendorEarning::where('shipment_id', $shipment->id)->where('status','pending')->get();
            foreach ($pending as $pe) {
                $pe->update(['status'=>'available']);
                $report['applied_marked'] = ($report['applied_marked'] ?? 0) + 1;
            }
        });
    }
}

echo json_encode($report, JSON_PRETTY_PRINT), PHP_EOL;

if (!$apply) {
    echo "\nDry-run complete. To apply changes run: php tools/backfill_delivered_earnings.php --apply\n";
}
