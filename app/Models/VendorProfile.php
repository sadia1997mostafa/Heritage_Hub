<?php
// app/Models/VendorProfile.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    protected $fillable = [
        'user_id','shop_name','description','heritage_story','address','phone','district','shop_logo_path'
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function getLogoUrlAttribute(): string
    {
        return $this->shop_logo_path
            ? asset('storage/'.$this->shop_logo_path)
            : asset('images/default-shop.png'); // optional default
    }
}
