<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$routes = app('router')->getRoutes();
foreach ($routes as $r) {
    $uri = $r->uri();
    if (strpos($uri, 'districts') !== false) echo $uri . PHP_EOL;
}
