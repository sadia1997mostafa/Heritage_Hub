<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ReturnRequest;

class ReturnRequestPolicy
{
    public function reviewByVendor(User $user, ReturnRequest $returnRequest)
    {
        if (! $user->isVendor()) return false;
        // Fast path: if return_request has vendor_id set, compare directly
        if ($returnRequest->vendor_id) {
            $ok = $returnRequest->vendor_id === $user->vendorProfile->id;
            if (! $ok) logger()->info('return review denied vendor mismatch', ['user_vendor'=> $user->vendorProfile->id ?? null, 'rr_vendor' => $returnRequest->vendor_id, 'rr_id' => $returnRequest->id]);
            return $ok;
        }

        // Fallback: ensure the vendor owns the product for the order item
        $oi = $returnRequest->orderItem;
        if (! $oi || ! $oi->product) {
            logger()->info('return review denied missing relations', ['rr_id'=>$returnRequest->id, 'oi'=> $oi?->id ?? null]);
            return false;
        }
        $ok = $oi->product->vendor_id === $user->vendorProfile->id;
        if (! $ok) logger()->info('return review denied product vendor mismatch', ['user_vendor'=> $user->vendorProfile->id ?? null, 'product_vendor' => $oi->product->vendor_id, 'rr_id' => $returnRequest->id]);
        return $ok;
    }
}
