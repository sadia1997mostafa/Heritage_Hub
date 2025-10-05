<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;

class StorefrontController extends Controller
{
    public function show(string $slug)
    {
        $store = VendorProfile::with(['district'])
            ->where('slug',$slug)
            ->where('status','approved')
            ->firstOrFail();

        $products = $store->products()
            ->with('media','category')
            ->approved()
            ->latest()
            ->paginate(12);

        return view('shop.store', compact('store','products'));
    }
}
