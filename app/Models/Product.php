<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'vendor_id','category_id','title','slug','description','stock','status','price'
    ];

    public function vendor() {
        return $this->belongsTo(VendorProfile::class,'vendor_id');
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function media() {
        return $this->hasMany(ProductMedia::class);
    }
}
