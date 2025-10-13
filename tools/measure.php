<?php
$s = microtime(true);
$c = @file_get_contents('http://localhost:88/cart');
$e = microtime(true);
echo 'elapsed_ms: '.round(($e-$s)*1000,2)."\n";
if ($c === false) {
    echo "request_failed\n";
}
