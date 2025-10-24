<?php
$ch = curl_init('https://query.wikidata.org/sparql?query=SELECT%20%3Fs%20WHERE%20%7B%20%3Fs%20%3Fp%20%3Fo%20%7D%20LIMIT%201');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'HeritageHub-check/1.0');
$res = curl_exec($ch);
$info = curl_getinfo($ch);
$err = curl_error($ch);
curl_close($ch);
echo "HTTP_CODE:" . ($info['http_code'] ?? 'NO') . "\n";
if ($err) echo "ERR:" . $err . "\n";
if ($res) echo substr($res,0,200) . "\n";
