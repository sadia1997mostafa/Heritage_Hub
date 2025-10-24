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

        // Also include VendorProfile rows that match this district so newer storefront entries
        // appear on the district page. We merge them into the `vendors` relation so views
        // referencing $district->vendors continue to work.
        try {
            if (class_exists('\App\\Models\\VendorProfile')) {
                $vp = \App\Models\VendorProfile::where(function($q) use ($district) {
                    $q->where('district_id', $district->id)
                      ->orWhere('shop_name', 'like', '%' . $district->name . '%')
                      ->orWhere('address', 'like', '%' . $district->name . '%');
                })->get();

                if ($vp && $vp->isNotEmpty()) {
                    // Merge the vendor profiles into the existing vendors relation
                    $current = $district->getRelation('vendors') ?? collect();
                    $district->setRelation('vendors', $current->merge($vp));
                }
            }
        } catch (\Throwable $e) {
            // If anything goes wrong (missing table/columns), ignore and continue with legacy vendors only
        }

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
