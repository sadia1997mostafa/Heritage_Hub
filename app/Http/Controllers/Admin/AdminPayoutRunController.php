<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorEarning;
use App\Models\VendorProfile;
use App\Models\PayoutRun;
use App\Models\PayoutTransaction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdminPayoutRunController extends Controller
{
    public function index()
    {
        // compute available balance per vendor (sum of vendor_share where status='pending')
        $balances = VendorEarning::selectRaw('vendor_id, SUM(vendor_share) as balance')
            ->where('status','pending')
            ->groupBy('vendor_id')
            ->get()
            ->mapWithKeys(function($r){ return [$r->vendor_id => $r->balance]; });

        $vendors = VendorProfile::whereIn('id', $balances->keys()->toArray())->with('user')->get();

        return view('admin.payouts.runs.index', compact('vendors','balances'));
    }

    public function generate(Request $req)
    {
        $date = now()->toDateString();
        $balances = VendorEarning::selectRaw('vendor_id, SUM(vendor_share) as balance')
            ->where('status','pending')
            ->groupBy('vendor_id')
            ->get();

        if ($balances->isEmpty()) {
            return back()->with('status','No pending vendor earnings to payout.');
        }

        $total = $balances->sum('balance');

        $payout = PayoutRun::create([ 'run_date'=>$date, 'total_amount'=>$total, 'status'=>'pending', 'admin_user_id'=>Auth::id() ]);

        // Build CSV
        $rows = [['vendor_id','vendor','email','account_method','account_no','amount']];
        foreach ($balances as $b) {
            $vendor = VendorProfile::with('user')->find($b->vendor_id);
            $account = $vendor->user && $vendor->user->payoutAccount ? $vendor->user->payoutAccount : null;
            PayoutTransaction::create([
                'payout_run_id' => $payout->id,
                'vendor_id' => $vendor->id,
                'amount' => $b->balance,
                'payout_account_id' => $account->id ?? null,
                'status' => 'pending'
            ]);

            $rows[] = [ $vendor->id, $vendor->shop_name ?? ($vendor->user->name ?? 'Vendor'), $vendor->user->email ?? '', $account->method ?? '', $account->account_no ?? '', number_format($b->balance,2) ];
        }

        $csv = '';
        foreach ($rows as $r) { $csv .= implode(',', array_map(function($c){ return '"'.str_replace('"','""',$c).'"'; }, $r)) . "\n"; }

        $path = 'payout_runs/payout_run_'.$payout->id.'_'.time().'.csv';
        Storage::disk('public')->put($path, $csv);
        $payout->update(['csv_path'=>$path]);

        return redirect()->route('admin.payouts.runs.index')->with('status','Payout run generated.');
    }

    public function download(PayoutRun $payout)
    {
        if (!$payout->csv_path || !Storage::disk('public')->exists($payout->csv_path)) {
            return back()->with('status','CSV not found');
        }
        return response()->download(storage_path('app/public/'.$payout->csv_path));
    }
}
