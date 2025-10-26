<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\VendorProfile;
use App\Models\District;

class SearchController extends Controller
{
   
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $cat = (string) $request->query('cat', '');

       
    $searchProducts = null;
    $searchVendors = null;
    $districtMatches = null;

        if ($q !== '' || $cat !== '') {
            
            if ($q !== '') {
                $districtMatches = District::query()
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', $q)
                    ->limit(10)
                    ->get();
            }
            $products = Product::with(['media','vendor','category'])
                ->approved()
                ->inStock();

            if ($q !== '') {
                $products->where(function($wb) use ($q) {
                    $wb->where('title', 'like', "%{$q}%")
                       ->orWhere('description', 'like', "%{$q}%")
                       ->orWhereHas('category', function($c) use ($q) {
                           $c->where('name', 'like', "%{$q}%");
                       })
                       ->orWhereHas('vendor', function($v) use ($q) {
                           $v->where('shop_name', 'like', "%{$q}%")->orWhere('description','like',"%{$q}%");
                       });
                });
            }

            if ($cat !== '') {
                if (is_numeric($cat)) {
                    $products->where('category_id', (int)$cat);
                } else {
                    $products->whereHas('category', function($c) use ($cat) {
                        $c->where('slug', $cat)->orWhere('name', 'like', "%{$cat}%");
                    });
                }
            }

  
            if (!empty($districtMatches) && $districtMatches->count()) {
                $districtIds = $districtMatches->pluck('id')->toArray();
                $products->whereHas('vendor', function($v) use ($districtIds) {
                    $v->whereIn('district_id', $districtIds);
                });
            }

            $searchProducts = $products->latest()->paginate(12)->withQueryString();

         
            $vquery = VendorProfile::query()->where('status','approved');
            if ($q !== '') {
                $vquery->where(function($w) use ($q) {
                    $w->where('shop_name','like',"%{$q}%")
                      ->orWhere('description','like',"%{$q}%");
                });
            }
            $searchVendors = $vquery->limit(20)->get();
        }

        
        if ($q === '' && $cat === '') {
            return redirect()->route('shop');
        }

        return view('search.results', [
            'searchProducts' => $searchProducts,
            'searchVendors' => $searchVendors,
            'districtMatches' => $districtMatches,
            'q' => $q,
            'cat' => $cat,
        ]);
    }
}
