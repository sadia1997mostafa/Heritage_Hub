<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutTransaction extends Model
{
    protected $fillable = ['payout_run_id','vendor_id','amount','payout_account_id','status','reference'];

    public function payoutRun() { return $this->belongsTo(PayoutRun::class,'payout_run_id'); }
    public function vendor() { return $this->belongsTo(VendorProfile::class,'vendor_id'); }
    public function payoutAccount() { return $this->belongsTo(VendorPayoutAccount::class,'payout_account_id'); }
}
