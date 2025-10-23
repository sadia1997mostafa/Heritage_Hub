<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use Illuminate\Http\Request;

class MakerController extends Controller
{
    public function verify(Request $req, VendorProfile $profile)
    {
        // toggle verify
        if ($profile->approved_at) {
            $profile->approved_at = null;
        } else {
            $profile->approved_at = now();
        }
        $profile->save();
        return back();
    }
}
