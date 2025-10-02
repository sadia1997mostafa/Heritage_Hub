<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['division_id','name','slug','banner_url','intro_html'];

    public function division() { return $this->belongsTo(Division::class); }
    public function items()   { return $this->hasMany(HeritageItem::class); }
    public function sources() { return $this->hasMany(HeritageSource::class); }
    public function vendors() { return $this->hasMany(Vendor::class); }
}
