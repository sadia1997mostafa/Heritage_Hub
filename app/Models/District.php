<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    // include commonly used fields from the existing table
    protected $fillable = [
        'division_id', 'name', 'slug', 'banner_url', 'intro_html', 'description',
        'hero_image', 'gallery', 'lat', 'lng', 'meta', 'featured', 'order'
    ];

    protected $casts = [
        'gallery' => 'array',
        'meta' => 'array',
        'featured' => 'boolean',
    ];

    public function division() { return $this->belongsTo(Division::class); }
    public function items()   { return $this->hasMany(HeritageItem::class); }
    public function sources() { return $this->hasMany(HeritageSource::class); }
    public function vendors() { return $this->hasMany(Vendor::class); }

    // convenience accessor for banner/hero url — uses banner_url first, then hero_image
    public function getHeroUrlAttribute()
    {
        if ($this->banner_url) return $this->banner_url;
        if ($this->hero_image) return $this->hero_image;
        return null;
    }
}
