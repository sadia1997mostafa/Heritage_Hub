<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductPublicController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::with(['media','vendor.district','category'])
            ->approved()
            ->where('slug',$slug)
            ->firstOrFail();

        // Related (same category) for a small carousel/grid
        $related = Product::with('media')
            ->approved()
            ->where('category_id',$product->category_id)
            ->where('id','!=',$product->id)
            ->latest()->take(8)->get();

        return view('shop.product', compact('product','related'));
    }
}
