<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReturnRequest;
use App\Models\User;
use App\Http\Controllers\Vendor\ReturnRequestController;
use Illuminate\Http\Request as HttpRequest;

// take the first ReturnRequest with NULL or empty vendor_status
$rr = ReturnRequest::whereNull('vendor_status')->orWhere('vendor_status','')->first();
if (! $rr) {
    echo "No pending return requests found.\n";
    exit;
}

// find vendor id from related product
$vendorId = $rr->vendor_id ?: ($rr->orderItem->product->vendor_id ?? null);
$vendorUser = null;
if ($vendorId) {
    $vp = App\Models\VendorProfile::find($vendorId);
    if ($vp) $vendorUser = App\Models\User::find($vp->user_id);
}
if (! $vendorUser) {
    echo "No vendor user found for vendor_id={$vendorId}\n";
    // as fallback, pick first vendor user
    $vendorUser = App\Models\User::whereHas('vendorProfile')->first();
}

echo "Before: RR id={$rr->id}, vendor_status={$rr->vendor_status}, vendor_id={$rr->vendor_id}\n";

$controller = new ReturnRequestController();
// create a request with vendor user returned by user('vendor')
$req = new HttpRequest();
$req->setMethod('POST');
$req->server->set('HTTP_ACCEPT','application/json');
$req->request->set('vendor_notes', 'CLI test approve');
// hack: add a user resolver
$req->setUserResolver(function() use ($vendorUser) { return $vendorUser; });

// call approve
$response = $controller->approve($req, $rr);

// print response (JSON)
if ($response instanceof Illuminate\Http\JsonResponse) {
    echo "JSON Response: ".json_encode($response->getData())."\n";
} else {
    echo "Non-JSON response: ".get_class($response)."\n";
}

$rr->refresh();
echo "After: RR id={$rr->id}, vendor_status={$rr->vendor_status}, vendor_id={$rr->vendor_id}, vendor_handled_at={$rr->vendor_handled_at}\n";

// show notification created
$notif = \App\Models\LocalNotification::where('type','return_approved')->where('data->return_request_id',$rr->id)->first();
if ($notif) {
    echo "LocalNotification created id={$notif->id}, user_id={$notif->user_id}\n";
} else {
    echo "No local notification created.\n";
}
