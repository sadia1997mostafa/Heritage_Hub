<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class WikipediaHeritageService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => 'https://en.wikipedia.org/',
            'timeout'  => 12,
            'headers'  => [
                // REQUIRED by Wikipedia robots policy
                'User-Agent'      => 'HeritageHub/1.0 (http://127.0.0.1; nasimac300@gmail.com)',
                'Accept'          => 'application/json',
                'Accept-Language' => 'en',
            ],
        ]);
    }

    /**
     * Fetch a mixed list for a Division or (optionally) a District.
     * Only returns items in these buckets:
     *  - historical-site, natural-place, landmark, craft, famous-food, famous-thing
     */
    public function fetchFor(string $division, ?string $district = null, int $limit = 8): array
    {
        $place = $district ?: $division;

        // Queries focused ONLY on places/crafts/foods/“famous for”
        $queries = [
            "{$place} Bangladesh historic places",
            "{$place} Bangladesh landmarks",
            "{$place} Bangladesh tourist attractions",
            "{$place} Bangladesh natural places",
            "{$place} traditional crafts",
            "{$place} handloom weaving textiles crafts",
            "{$place} famous food",
            "{$place} famous for",
            "{$place} specialty",
        ];

        $seen  = [];
        $items = [];

        foreach ($queries as $q) {
            foreach ($this->search($q) as $hit) {
                $title = $hit['title'] ?? null;
                if (!$title) continue;

                $key = Str::lower($title);
                if (isset($seen[$key])) continue;

                $summary = $this->summary($title);
                if (!$summary) continue;

                // classify & normalize
                $norm = $this->normalizeItem($summary);
                if (!$norm) continue; // skip disambiguations or empty

                // FILTER strictly to allowed categories
                $allowed = ['historical-site','natural-place','landmark','craft','famous-food','famous-thing'];
                if (!in_array($norm['category'], $allowed, true)) {
                    continue;
                }

                $items[]      = $norm;
                $seen[$key]   = true;

                if (count($items) >= $limit) break 2;
            }
        }

        return $items;
    }

    /** MediaWiki search (defensive) */
    protected function search(string $query): array
    {
        try {
            $res = $this->http->get('w/api.php', [
                'query' => [
                    'action'   => 'query',
                    'list'     => 'search',
                    'srsearch' => $query,
                    'format'   => 'json',
                    'srlimit'  => 10,
                    'utf8'     => 1,
                ]
            ]);
            $json = json_decode($res->getBody()->getContents(), true);
            return $json['query']['search'] ?? [];
        } catch (\Throwable $e) {
            // swallow & continue; return empty so the page never 500s
            return [];
        }
    }

    /** REST summary + media (skips disambiguation) */
    protected function summary(string $title): ?array
    {
        try {
            // Main summary
            $sumRes = $this->http->get("api/rest_v1/page/summary/" . rawurlencode($title));
            $sum    = json_decode($sumRes->getBody()->getContents(), true);

            // Skip disambiguation / invalid
            if (!isset($sum['title'])) return null;
            if (($sum['type'] ?? '') === 'disambiguation') return null;

            // Try to get a large image
            $img = $sum['thumbnail']['source'] ?? null;
            try {
                $mediaRes = $this->http->get("api/rest_v1/page/media-list/" . rawurlencode($title));
                $media    = json_decode($mediaRes->getBody()->getContents(), true);
                if (!empty($media['items'])) {
                    foreach ($media['items'] as $m) {
                        if (($m['type'] ?? '') === 'image' && isset($m['srcset']) && is_array($m['srcset'])) {
                            $last = end($m['srcset']);
                            if (is_array($last) && isset($last['src'])) {
                                $img = $last['src'];
                                break;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore media errors; keep summary thumbnail if any
            }

            $lat = $sum['coordinates']['lat'] ?? null;
            $lon = $sum['coordinates']['lon'] ?? null;

            return [
                'title'   => $sum['title'],
                'extract' => $sum['extract'] ?? null,
                'url'     => $sum['content_urls']['desktop']['page'] ?? null,
                'image'   => $img,
                'lat'     => $lat,
                'lon'     => $lon,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Categorize into EXACTLY one of:
     *   historical-site | natural-place | landmark | craft | famous-food | famous-thing
     * Return null to skip the item completely.
     */
    protected function normalizeItem(array $s): ?array
    {
        $title = $s['title'] ?? '';
        $text  = Str::lower(trim(($s['extract'] ?? '') . ' ' . $title));
        if ($title === '' || $text === '') {
            return null;
        }

        $category = $this->classify($text);

        // If we couldn't classify into allowed buckets, skip
        if ($category === null) return null;

        return [
            'title'     => $s['title'] ?? '',
            'category'  => $category,
            'summary'   => $s['extract'] ?? null,
            'image_url' => $s['image'] ?? null,
            'wiki_url'  => $s['url'] ?? null,
            'lat'       => $s['lat'] ?? null,
            'lon'       => $s['lon'] ?? null,
        ];
    }

    /** Keyword-based classifier limited to your requested buckets */
    protected function classify(string $text): ?string
    {
        // Order matters: first match wins.
        $buckets = [
            // Historical / popular places
            'historical-site' => [
                'mosque','temple','monastery','pagoda','church','shrine',
                'fort','palace','citadel','museum','zamindar','ruins','tomb','mausoleum','mazar','imambara',
            ],
            'natural-place' => [
                'park','garden','tea','tea garden','forest','sanctuary','wildlife',
                'beach','hill','hills','waterfall','lake','swamp','haor','river','canyon',
            ],
            'landmark' => [
                'landmark','market','bazar','ghat','bridge','tower','stadium','arena','gate','university','research institute',
            ],

            // Crafts
            'craft' => [
                'craft','weaving','handloom','textile','jamdani','silk','nakshi kantha',
                'pottery','terracotta','cane','bamboo','woodcarv','wood craft','metalwork','brass','copper','inlay',
            ],

            // Foods
            'famous-food' => [
                'food','sweet','dessert','pitha','biryani','kacchi','mishti','sandesh',
                'mango','litchi','lychee','tea','hilsa','fish','doi','yogurt','pitha','cha',
            ],

            // Generic famous things (not a place/craft/food but a “famous for”)
            'famous-thing' => [
                'famous for','renowned for','well known for','specialty','speciality','signature',
                'mango capital','tea capital','silk','rajshahi silk','jute','shipbreaking',
            ],
        ];

        foreach ($buckets as $cat => $words) {
            foreach ($words as $w) {
                if ($w !== '' && str_contains($text, $w)) {
                    return $cat;
                }
            }
        }

        // Heuristics: if it looks like a notable “place” by structure
        if (preg_match('/(mosque|temple|palace|fort|museum|park|garden|beach|hill|waterfall|lake|ghat|bridge|tower)/', $text)) {
            return 'historical-site';
        }

        // Otherwise: we don't include it
        return null;
    }
}
