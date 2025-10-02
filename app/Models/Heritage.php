<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Heritage extends Model
{
    protected $fillable = [
        'division',
        'district',
        'title',
        'category',
        'summary',
        'image_url',
        'wiki_url',
        'lat',
        'lon',
        'source',
        'fetched_at',
    ];

    /**
     * Cast attributes to proper types.
     */
    protected $casts = [
        'fetched_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'lat'        => 'float',
        'lon'        => 'float',
    ];
}
