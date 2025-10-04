<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductMedia;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendorProfile;
        $products = Product::where('vendor_id', $vendor->id)->latest()->get();
        return view('vendor.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('vendor.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;

        $validated = $request->validate([
            'title'=>'required|string|max:255',
            'category_id'=>'required|exists:categories,id',
            'description'=>'nullable|string',
            'stock'=>'required|integer|min:0',
            'images.*'=>'nullable|image|max:2048'
        ]);

        $product = Product::create([
            ...$validated,
            'vendor_id'=>$vendor->id,
            'slug'=>Str::slug($validated['title']).'-'.uniqid(),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('products','public');
                ProductMedia::create(['product_id'=>$product->id,'path'=>$path]);
            }
        }

        return redirect()->route('vendor.products.index')->with('status','Product saved!');
    }

    public function submit(Product $product)
    {
        $product->update(['status'=>'submitted']);
        return back()->with('status','Product submitted for approval.');
    }
}
