<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductApprovalController extends Controller
{
    public function index()
    {
        $pending = Product::where('status','submitted')->get();
        $approved = Product::where('status','approved')->get();
        return view('admin.products.index', compact('pending','approved'));
    }

    public function approve(Product $product)
    {
        $product->update(['status'=>'approved']);
        return back()->with('status','Approved');
    }

    public function reject(Product $product)
    {
        $product->update(['status'=>'draft']);
        return back()->with('status','Rejected');
    }
}
