<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeritageSource extends Model
{
    protected $fillable = ['district_id','title','url'];

    public function district() { return $this->belongsTo(District::class); }
}
