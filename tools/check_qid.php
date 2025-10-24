<?php
$name = 'Sylhet';
$url = 'https://www.wikidata.org/w/api.php';
$ch = curl_init($url . '?action=wbsearchentities&search=' . rawurlencode($name) . '&language=en&format=json&type=item&limit=5');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'HeritageHub-check/1.0');
$res = curl_exec($ch);
$info = curl_getinfo($ch);
$err = curl_error($ch);
curl_close($ch);
if ($err) echo "ERR: $err\n";
echo "HTTP: " . ($info['http_code'] ?? 'NO') . "\n";
echo $res . "\n";
