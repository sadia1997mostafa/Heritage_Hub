<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name','email','password','profile_photo_path'];
    protected $hidden   = ['password','remember_token'];

    public function vendorProfile() { return $this->hasOne(VendorProfile::class); }

    public function isVendor(): bool
    {
        return isset($this->vendorProfile) && $this->vendorProfile->status === 'approved';
    }

    public function isAdmin(): bool
    {
        return $this->is_admin ?? false;
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : asset('images/default-avatar.png');
    }
}
