<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorProfile extends Model
{
    protected $fillable = [
        'user_id','shop_name','slug','status','approved_at','rejected_at','rejection_reason',
        'phone','support_email','support_phone','district_id','address','description',
        'heritage_story','shop_logo_path','banner_path','vendor_category'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }

    public function images() {
        return $this->hasMany(VendorProfileImage::class,'vendor_profile_id')->orderBy('ordering','asc');
    }

    // helpers (optional)
    public function getLogoUrlAttribute(): string
    {
        return $this->shop_logo_path ? asset('storage/'.$this->shop_logo_path) : asset('images/default-shop.png');
    }
    public function getBannerUrlAttribute(): string
    {
        return $this->banner_path ? asset('storage/'.$this->banner_path) : asset('images/default-banner.png');
    }
    public function getGalleryAttribute()
    {
        return $this->images->map(fn($i)=> asset('storage/'.$i->path))->toArray();
    }
    public function products() {
    return $this->hasMany(\App\Models\Product::class, 'vendor_id');
}
}
