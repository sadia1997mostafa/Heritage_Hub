<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionPolicy extends Model
{
    protected $fillable = ['name','percent','vendor_id','active'];

    public function vendor() { return $this->belongsTo(VendorProfile::class,'vendor_id'); }
}
