<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vlog;
use App\Models\Product;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user(); // the Admin model

        $pendingProducts = Product::where('status','submitted')->count();
        $pendingVlogs = Vlog::where('approved', false)->count();

        return view('admin.dashboard', [
            'admin' => $admin,
            'pendingProducts' => $pendingProducts,
            'pendingVlogs' => $pendingVlogs,
        ]);
    }
}
