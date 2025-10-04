<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user(); // the Admin model
        return view('admin.dashboard', compact('admin'));
        $pendingProducts = Product::where('status','submitted')->count();

        return view('admin.dashboard', [
            'pendingProducts' => $pendingProducts,
        ]);
    }
}
