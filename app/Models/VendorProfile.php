<?php
// app/Models/VendorProfile.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    protected $fillable = [
    'user_id','shop_name','slug','status','approved_at','rejected_at','rejection_reason',
    'description','heritage_story','address','phone',
    'support_email','support_phone',
    'district_id','shop_logo_path','banner_path',
    'vendor_category'   // ✅ added
];


    public function user() { return $this->belongsTo(User::class); }

    public function getLogoUrlAttribute(): string
    {
        return $this->shop_logo_path
            ? asset('storage/'.$this->shop_logo_path)
            : asset('images/default-shop.png'); // optional default
    }
}
