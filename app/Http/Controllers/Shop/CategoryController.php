<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $req, string $slug)
    {
        $category = Category::where('slug',$slug)->firstOrFail();

        $q = Product::with(['media','vendor'])
            ->approved()
            ->where('category_id',$category->id);

        // Filters
        if ($req->boolean('in_stock')) $q->inStock();
        if ($req->filled('min')) $q->where('price','>=',(float)$req->min);
        if ($req->filled('max')) $q->where('price','<=',(float)$req->max);

        // Sort
        $sort = $req->get('sort','new');
        match ($sort) {
            'price_asc'  => $q->orderBy('price','asc'),
            'price_desc' => $q->orderBy('price','desc'),
            'title'      => $q->orderBy('title'),
            default      => $q->latest()
        };

        $products = $q->paginate(12)->withQueryString();

        return view('shop.category', compact('category','products'));
    }
}

