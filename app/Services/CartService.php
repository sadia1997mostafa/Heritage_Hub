<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

/**
 * Cart structure stored in session as 'cart'
 * [
 *   vendor_id => [
 *     product_id => ['qty'=>int,'price'=>string]
 *   ]
 * ]
 */
class CartService
{
    protected $key = 'cart';

    public function all(): array
    {
        return Session::get($this->key, []);
    }

    public function add(int $vendorId, int $productId, float $price, int $qty = 1)
    {
        $cart = $this->all();
        $vendor = $cart[$vendorId] ?? [];
        $line = $vendor[$productId] ?? ['qty' => 0, 'price' => (string)$price];
        $line['qty'] += $qty;
        $line['price'] = (string)$price; // snapshot
        $vendor[$productId] = $line;
        $cart[$vendorId] = $vendor;
        Session::put($this->key, $cart);
        Session::save();
    }

    public function update(int $vendorId, int $productId, int $qty)
    {
        $cart = $this->all();
        if (isset($cart[$vendorId][$productId])) {
            if ($qty <= 0) {
                unset($cart[$vendorId][$productId]);
            } else {
                $cart[$vendorId][$productId]['qty'] = $qty;
            }
            if (empty($cart[$vendorId])) unset($cart[$vendorId]);
            Session::put($this->key, $cart);
            Session::save();
        }
    }

    public function removeVendor(int $vendorId)
    {
        $cart = $this->all();
        unset($cart[$vendorId]);
        Session::put($this->key, $cart);
        Session::save();
    }

    public function clear()
    {
        Session::forget($this->key);
    }
}
