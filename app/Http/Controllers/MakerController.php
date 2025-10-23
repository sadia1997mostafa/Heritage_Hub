<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use Illuminate\Http\Request;

class MakerController extends Controller
{
    public function show(Request $req, string $slug)
    {
        $profile = VendorProfile::where('slug', $slug)->firstOrFail();

    // Use approved() scope on Product model instead of non-existent `visible` column
    $products = $profile->products()->approved()->orderBy('created_at','desc')->paginate(12);

        return view('makers.show', ['profile' => $profile, 'products' => $products]);
    }
}
