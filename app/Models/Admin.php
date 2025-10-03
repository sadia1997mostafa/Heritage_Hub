<?php
// app/Models/Admin.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name','email','password','profile_photo_path'];
    protected $hidden   = ['password','remember_token'];

    public function getAvatarUrlAttribute(): string
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : asset('images/default-avatar.png');
    }
}
