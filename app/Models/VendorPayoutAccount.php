<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPayoutAccount extends Model
{
    protected $fillable = [
        'user_id','method','account_no','account_name','bank_name','branch','routing_no','status','doc_path'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
