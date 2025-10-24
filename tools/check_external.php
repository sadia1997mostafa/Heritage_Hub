<?php
$url = 'http://127.0.0.1:8000/api/districts/sylhet';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
if (!$res) {
    echo "NO_RESPONSE\n";
    exit(1);
}
$json = json_decode($res, true);
if (json_last_error() !== JSON_ERROR_NONE) { echo "INVALID_JSON\n"; echo $res; exit(1); }
$external = $json['data']['external'] ?? $json['external'] ?? null;
if (!$external) { echo "NO_EXTERNAL\n"; var_export($json); exit(0); }
print_r($external);
