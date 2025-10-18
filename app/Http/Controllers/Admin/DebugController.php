<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnRequest;

class DebugController extends Controller
{
    /**
     * Inspect a return request and related relations for debugging vendor authorization.
     */
    public function returnRequest(ReturnRequest $returnRequest)
    {
        $orderItem = $returnRequest->orderItem;
        $product = $orderItem?->product;

        $data = [
            'return_request_id' => $returnRequest->id,
            'return_request_vendor_id' => $returnRequest->vendor_id,
            'order_item_id' => $orderItem?->id,
            'order_item_product_id' => $orderItem?->product_id ?? ($product?->id ?? null),
            'product_vendor_id' => $product?->vendor_id ?? null,
            'order_item_relation_loaded' => $returnRequest->relationLoaded('orderItem'),
            'product_relation_loaded_on_order_item' => $orderItem?->relationLoaded('product') ?? false,
        ];

        // Also include the vendor profile id for the product vendor (if any)
        if ($product && $product->vendor_id) {
            $data['product_vendor_profile'] = \App\Models\VendorProfile::find($product->vendor_id)?->only(['id','user_id','shop_name']);
        }
        if ($returnRequest->vendor_id) {
            $data['rr_vendor_profile'] = \App\Models\VendorProfile::find($returnRequest->vendor_id)?->only(['id','user_id','shop_name']);
        }

        return response()->json($data);
    }
}
