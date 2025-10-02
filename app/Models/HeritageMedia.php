<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeritageMedia extends Model
{
    protected $fillable = ['heritage_item_id','type','url','caption','order_index'];

    public function item() { return $this->belongsTo(HeritageItem::class, 'heritage_item_id'); }
}
