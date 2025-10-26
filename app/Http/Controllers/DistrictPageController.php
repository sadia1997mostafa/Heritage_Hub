<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            
        }

        
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

      
        foreach ($district->items as $it) {
            $img = $it->hero_image ?? null;
            if (!$img) {
                // pick first image media if present
                $m = $it->media->firstWhere('type', 'image');
                $img = $m->url ?? null;
            }

            $resolved = null;
            if ($img) {
                // already a full URL
                if (preg_match('/^https?:\/\//i', $img)) {
                    $resolved = $img;
                } else {
                    
                    $candidate = ltrim($img, '/');
                
                    if (preg_match('/^storage\//i', $candidate)) {
                        $resolved = asset($candidate);
                    } else {
                        
                        try {
                            if (Storage::disk('public')->exists($candidate)) {
                                $resolved = asset('storage/' . $candidate);
                            } else {
                                
                                if (Storage::disk('public')->exists('images/' . $candidate)) {
                                    $resolved = asset('storage/images/' . $candidate);
                                } else {
                                    
                                    $resolved = asset('storage/' . $candidate);
                                }
                            }
                        } catch (\Throwable $e) {
                        
                            $resolved = asset('storage/' . $candidate);
                        }
                    }
                }
            }

            $it->first_image_url = $resolved;
        }

        return view('heritage.district', [
            'district' => $district,
            'division' => $district->division,
            'itemsByCat' => $byCat,
            'gallery' => $gallery,
        ]);
    }
}
