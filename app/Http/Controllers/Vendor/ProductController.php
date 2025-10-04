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
    /**
     * Show all products belonging to the current vendor.
     */
    public function index()
    {
        $vendorProfile = auth()->user()->vendorProfile;

        if (!$vendorProfile) {
            return redirect()->route('vendor.store.setup')
                ->withErrors(['You need to complete your vendor profile first.']);
        }

        $products = Product::where('vendor_id', $vendorProfile->id)
            ->with('category')
            ->latest()
            ->get();

        return view('vendor.products.index', compact('products'));
    }

    /**
     * Show the form to create a new product.
     */
    public function create()
    {
        $vendorProfile = auth()->user()->vendorProfile;

        if (!$vendorProfile) {
            return redirect()->route('vendor.store.setup')
                ->withErrors(['You need to complete your vendor profile first.']);
        }

        $categories = Category::orderBy('name')->get();

        return view('vendor.products.create', compact('categories'));
    }

    /**
     * Store the newly created product.
     */
    public function store(Request $request)
    {
        $vendorProfile = auth()->user()->vendorProfile;

        if (!$vendorProfile) {
            return redirect()->route('vendor.store.setup')
                ->withErrors(['You need to complete your vendor profile first.']);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'stock'       => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // ✅ Create the product
        $product = Product::create([
            'vendor_id'   => $vendorProfile->id,
            'category_id' => $validated['category_id'],
            'title'       => $validated['title'],
            'slug'        => Str::slug($validated['title']) . '-' . uniqid(),
            'description' => $validated['description'] ?? null,
            'stock'       => $validated['stock'],
            'price'       => $validated['price'],
            'status'      => 'draft',
        ]);

        // ✅ Upload product images if any
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('products', 'public');
                ProductMedia::create([
                    'product_id' => $product->id,
                    'path'       => $path,
                ]);
            }
        }

        return redirect()
            ->route('vendor.products.index')
            ->with('status', '✅ Product saved successfully!');
    }

    /**
     * Submit a product for admin approval.
     */
    public function submit(Product $product)
    {
        $vendorProfile = auth()->user()->vendorProfile;

        // Check if this product belongs to the current vendor
        if ($product->vendor_id !== $vendorProfile->id) {
            abort(403, 'Unauthorized action.');
        }

        $product->update(['status' => 'submitted']);

        return back()->with('status', '📤 Product submitted for admin approval.');
    }
}
