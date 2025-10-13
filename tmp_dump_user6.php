<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u = \App\Models\User::find(6);
if (!$u) { echo "no-user\n"; exit; }
echo "User id=".$u->id." email=".$u->email."\n";
$vp = $u->vendorProfile;
if (!$vp) { echo "no vendor profile\n"; exit; }
print_r($vp->toArray());
