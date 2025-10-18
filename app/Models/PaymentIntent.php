<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentIntent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
