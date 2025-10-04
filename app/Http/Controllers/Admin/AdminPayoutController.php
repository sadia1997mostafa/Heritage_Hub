<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorPayoutAccount;
use Illuminate\Http\Request;

class AdminPayoutController extends Controller
{
    public function index()
    {
        $pending  = VendorPayoutAccount::with('user')->where('status','pending')->latest()->paginate(15);
        $verified = VendorPayoutAccount::with('user')->where('status','verified')->latest()->paginate(15);
        $rejected = VendorPayoutAccount::with('user')->where('status','rejected')->latest()->paginate(15);

        return view('admin.payouts.index', compact('pending','verified','rejected'));
    }

    public function approve(VendorPayoutAccount $payout)
    {
        $payout->update(['status' => 'verified']);
        return back()->with('status','✅ Payout account verified successfully.');
    }

    public function reject(Request $req, VendorPayoutAccount $payout)
    {
        $payout->update(['status' => 'rejected']);
        return back()->with('status','❌ Payout account rejected.');
    }
}
