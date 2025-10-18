<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStockLog extends Model
{
    protected $fillable = ['product_id','delta','before','after','vendor_id','reason'];

    public function product() { return $this->belongsTo(Product::class); }
}
