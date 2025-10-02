<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeritageItem extends Model
{
    protected $fillable = [
        'district_id','category','title','location','summary',
        'hero_image','lat','lon','order_index','visible'
    ];

    protected $casts = [
        'lat' => 'float', 'lon' => 'float', 'visible' => 'boolean',
    ];

    public function district() { return $this->belongsTo(District::class); }
    public function media()    { return $this->hasMany(HeritageMedia::class); }
}
