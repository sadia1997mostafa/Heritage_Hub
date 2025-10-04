<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use Illuminate\Http\Request;

class AdminVendorController extends Controller
{
    public function index()
    {
        $pending  = VendorProfile::with('user','district')->where('status','pending')->latest()->paginate(20);
        $approved = VendorProfile::with('user','district')->where('status','approved')->latest()->paginate(20);
        $rejected = VendorProfile::with('user','district')->where('status','rejected')->latest()->paginate(20);
        return view('admin.vendors.index', compact('pending','approved','rejected'));
    }

    public function show(VendorProfile $profile)
    {
        $profile->load('user','district');
        return view('admin.vendors.show', compact('profile'));
    }

    public function approve(VendorProfile $profile)
    {
        $profile->update(['status'=>'approved','approved_at'=>now(),'rejection_reason'=>null,'rejected_at'=>null]);
        return back()->with('status','Approved');
    }

    public function reject(Request $req, VendorProfile $profile)
    {
        $req->validate(['reason'=>'required|max:255']);
        $profile->update(['status'=>'rejected','rejected_at'=>now(),'rejection_reason'=>$req->reason]);
        return back()->with('status','Rejected');
    }
}
