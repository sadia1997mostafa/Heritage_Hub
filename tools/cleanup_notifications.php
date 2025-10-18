<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LocalNotification;

echo "Scanning for duplicate notifications...\n";

$all = LocalNotification::orderBy('user_id')->orderByDesc('id')->get();
$groups = [];
$toDelete = [];

foreach ($all as $n) {
    $key = null;
    $data = (array) ($n->data ?? []);
    switch ($n->type) {
        case 'shipment_status':
            $sid = $data['shipment_id'] ?? null;
            $status = $data['status'] ?? null;
            $key = "shipment:{$n->user_id}:{$sid}:{$status}";
            break;
        case 'return_approved':
            $rid = $data['return_request_id'] ?? null;
            $key = "return:{$n->user_id}:{$rid}";
            break;
        default:
            // generic fallback: use type + user + serialized data
            $key = "other:{$n->user_id}:{$n->type}:" . md5(json_encode($data));
    }

    if (isset($groups[$key])) {
        // we keep the first seen (which is the latest by id due to ordering) and mark others for deletion
        $toDelete[] = $n->id;
    } else {
        $groups[$key] = $n->id;
    }
}

if (empty($toDelete)) {
    echo "No duplicates found.\n";
    exit;
}

echo "Found " . count($toDelete) . " duplicate notifications. Deleting...\n";
LocalNotification::whereIn('id', $toDelete)->delete();
echo "Deleted.\n";
