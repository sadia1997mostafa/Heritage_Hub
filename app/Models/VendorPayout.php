<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    protected $fillable = ['vendor_id','amount','status','paid_at','note'];

    public function vendor() { return $this->belongsTo(VendorProfile::class,'vendor_id'); }
}
