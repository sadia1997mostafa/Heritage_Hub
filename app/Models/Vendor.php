<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = ['district_id','name','logo_url','website_url','shop_url','tags','description'];

    public function district() { return $this->belongsTo(District::class); }
}
