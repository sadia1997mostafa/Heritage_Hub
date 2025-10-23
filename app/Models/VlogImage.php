<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VlogImage extends Model
{
    protected $fillable = ['vlog_id','path','caption'];

    public function vlog()
    {
        return $this->belongsTo(Vlog::class);
    }
}
