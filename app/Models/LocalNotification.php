<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalNotification extends Model
{
    protected $table = 'local_notifications';
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
