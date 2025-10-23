<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\MakerController;
use Illuminate\Http\Request;

$slug = $argv[1] ?? 'mula-FrOl';
try {
    $c = new MakerController();
    $req = Request::create("/makers/{$slug}", 'GET');
    $res = $c->show($req, $slug);
    echo "OK\n";
} catch (\Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
