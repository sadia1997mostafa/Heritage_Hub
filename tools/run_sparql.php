<?php
$name = 'sylhet';
$sparql = <<<'SPARQL'
SELECT ?item ?itemLabel ?image ?coord WHERE {
  ?item wdt:P131* ?region .
  ?region rdfs:label ?regionLabel .
  FILTER(CONTAINS(LCASE(STR(?regionLabel)), "%s"))
  OPTIONAL { ?item wdt:P18 ?image. }
  OPTIONAL { ?item wdt:P625 ?coord. }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
LIMIT 120
SPARQL;
$query = sprintf($sparql, addslashes($name));
$url = 'https://query.wikidata.org/sparql';
$ch = curl_init($url . '?query=' . rawurlencode($query));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/sparql-results+json','User-Agent: HeritageHub/1.0 (dev@heritagehub.local)']);
$res = curl_exec($ch);
$info = curl_getinfo($ch);
$err = curl_error($ch);
curl_close($ch);
if ($err) { echo "ERR: $err\n"; }
echo "HTTP:" . ($info['http_code'] ?? 'NO') . "\n";
file_put_contents('tools/sparql_out.json',$res);
echo substr($res,0,1000) . "\n";
?>