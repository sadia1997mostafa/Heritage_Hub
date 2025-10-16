<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutRun extends Model
{
    protected $fillable = ['run_date','total_amount','status','admin_user_id','csv_path','notes'];

    public function transactions() { return $this->hasMany(PayoutTransaction::class,'payout_run_id'); }
}
