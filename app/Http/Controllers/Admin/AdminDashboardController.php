<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user(); // the Admin model
        return view('admin.dashboard', compact('admin'));
    }
}
