<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vlog extends Model
{
    protected $fillable = ['user_id','title','body','published_at'];

    // Ensure published_at is cast to a DateTime/Carbon instance so
    // ->diffForHumans() works in views even if the raw value is a string.
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function images()
    {
        return $this->hasMany(VlogImage::class);
    }

    // Render body as simple HTML from Markdown (using Parsedown if available).
    public function getBodyHtmlAttribute()
    {
        // If Parsedown is installed, prefer it. Otherwise, do a simple nl2br + e().
        if (class_exists('\\Parsedown')) {
            $p = new \Parsedown();
            return $p->text($this->body);
        }

        return nl2br(e($this->body));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
