<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vlog extends Model
{
    protected $fillable = ['user_id','title','body','published_at'];

    
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function images()
    {
        return $this->hasMany(VlogImage::class);
    }

    public function getBodyHtmlAttribute()
    {
        
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
