<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorProfileImage extends Model
{
    protected $fillable = ['vendor_profile_id','path','ordering'];

    public function profile(): BelongsTo { return $this->belongsTo(VendorProfile::class,'vendor_profile_id'); }
}
