<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\Http\Request;

class DistrictPageController extends Controller
{
    public function show(string $slug)
    {
        $district = District::with([
            'division',
            'items' => function ($q) { $q->where('visible', true)->orderBy('category')->orderBy('order_index'); },
            'items.media' => function ($q) { $q->orderBy('order_index'); },
            'sources',
            'vendors'
        ])->where('slug', $slug)->firstOrFail();

        // Split items by category for tabs
        $byCat = [
            'site'     => [],
            'craft'    => [],
            'festival' => [],
            'cuisine'  => [],
        ];
        foreach ($district->items as $it) {
            $byCat[$it->category][] = $it;
        }

        // Gallery: flatten all media (images first)
        $gallery = [];
        foreach ($district->items as $it) {
            foreach ($it->media as $m) {
                if ($m->type === 'image') {
                    $gallery[] = ['url' => $m->url, 'caption' => $m->caption];
                }
            }
        }

        return view('heritage.district', [
            'district' => $district,
            'division' => $district->division,
            'itemsByCat' => $byCat,
            'gallery' => $gallery,
        ]);
    }
}
