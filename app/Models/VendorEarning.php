<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorEarning extends Model
{
    protected $fillable = ['order_id','shipment_id','vendor_id','gross_amount','platform_fee','vendor_share','status'];

    public function vendor() { return $this->belongsTo(VendorProfile::class,'vendor_id'); }
    public function shipment() { return $this->belongsTo(Shipment::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
