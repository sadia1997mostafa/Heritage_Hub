<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = ['name','slug','banner_url','intro_html'];

    public function districts() { return $this->hasMany(District::class); }
}
