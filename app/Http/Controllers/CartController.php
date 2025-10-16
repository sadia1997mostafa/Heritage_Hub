<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\PaymentIntent;
use App\Models\VendorEarning;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartController extends Controller
{
    protected $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        $cart = $this->cart->all();
        // Load product details
        $productIds = [];
        foreach ($cart as $vendor => $lines) {
            $productIds = array_merge($productIds, array_keys($lines));
        }
        $products = Product::whereIn('id', $productIds)->with('media')->get()->keyBy('id');

        return view('cart.index', compact('cart','products'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'nullable|integer|min:1',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $qty = $data['qty'] ?? 1;
        $this->cart->add($product->vendor_id, $product->id, $product->price, $qty);

        return redirect()->route('cart')->with('success','Added to cart');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required|integer',
            'product_id' => 'required|integer',
            'qty' => 'required|integer|min:0',
        ]);
        $this->cart->update($data['vendor_id'], $data['product_id'], $data['qty']);
        return back();
    }

    public function removeVendor($vendorId)
    {
        $this->cart->removeVendor((int)$vendorId);
        return back();
    }

    public function checkoutForm()
    {
        $cart = $this->cart->all();
        if (empty($cart)) return redirect()->route('cart')->with('error','Cart empty');

        // Build a simple summary to show in the checkout sidebar (items, subtotal, shipping, total)
        $summary = ['items' => 0, 'subtotal' => 0, 'shipping' => 0, 'total' => 0];
        foreach ($cart as $vendorId => $lines) {
            foreach ($lines as $productId => $line) {
                $summary['items'] += $line['qty'] ?? 0;
                $summary['subtotal'] += ($line['price'] ?? 0) * ($line['qty'] ?? 1);
            }
            // per-vendor flat shipping (stub)
            $summary['shipping'] += 0; // shipping is computed later; keep 0 here for now
        }
        $summary['total'] = $summary['subtotal'] + $summary['shipping'];

        return view('cart.checkout', compact('cart','summary'));
    }

    public function checkoutSubmit(Request $request)
    {
            if (! Auth::check()) {
            return redirect()->route('login')->with('error','Please login to place an order');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'payment_method' => 'required|string|in:cod,online',
        ]);

        $cart = $this->cart->all();
        if (empty($cart)) return redirect()->route('cart')->with('error','Cart empty');

        // Branch by payment method
        if ($data['payment_method'] === 'cod') {
            DB::beginTransaction();
            try {
                // calculate totals
                $subtotal = 0; $shipping_total = 0;
                foreach ($cart as $vendorId => $lines) {
                    foreach ($lines as $productId => $line) {
                        $subtotal += $line['qty'] * (float)$line['price'];
                    }
                    // per-vendor flat shipping stub: 50.00
                    $shipping_total += 50.00;
                }
                $total = $subtotal + $shipping_total;

                $order = Order::create([
                        'user_id' => Auth::id(),
                    'subtotal' => $subtotal,
                    'shipping_total' => $shipping_total,
                    'total' => $total,
                    'shipping_address' => ['name'=>$data['name'],'phone'=>$data['phone'],'address'=>$data['address'],'notes'=>$data['notes'] ?? ''],
                    'payment_method' => 'cod',
                ]);

                // create items and shipments, decrement stock with for update
                foreach ($cart as $vendorId => $lines) {
                    $shipment = Shipment::create(['order_id'=>$order->id,'vendor_id'=>$vendorId,'shipping_amount'=>50.00]);
                    // accumulate gross for this shipment
                    $gross = 0;
                    foreach ($lines as $productId => $line) {
                        // lock the product row
                        $product = Product::where('id',$productId)->lockForUpdate()->first();
                        if (!$product || $product->stock < $line['qty']) {
                            throw new \Exception("Product out of stock: {$productId}");
                        }
                        $product->decrement('stock', $line['qty']);

                        OrderItem::create([
                            'order_id' => $order->id,
                            'vendor_id' => $vendorId,
                            'product_id' => $productId,
                            'qty' => $line['qty'],
                            'price' => $line['price'],
                            'shipping_share' => 50.00 / max(1, count($lines)),
                        ]);

                        $gross += $line['qty'] * (float)$line['price'];
                    }

                    // create vendor earning record (platform commission 10%)
                    $platformFee = round($gross * 0.10, 2);
                    $vendorShare = round($gross - $platformFee + 50.00, 2); // vendor receives shipping amount here
                    VendorEarning::create([
                        'order_id' => $order->id,
                        'shipment_id' => $shipment->id,
                        'vendor_id' => $vendorId,
                        'gross_amount' => $gross,
                        'platform_fee' => $platformFee,
                        'vendor_share' => $vendorShare,
                        'status' => 'pending',
                    ]);
                }

                DB::commit();
                $this->cart->clear();
                return redirect()->route('cart.confirm', $order->id)->with('success','Order placed');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error','Could not place order: '.$e->getMessage());
            }
        }

        // ONLINE payment: create order and order items but do NOT decrement stock or create shipments yet
        DB::beginTransaction();
        try {
            $subtotal = 0; $shipping_total = 0;
            foreach ($cart as $vendorId => $lines) {
                foreach ($lines as $productId => $line) {
                    $subtotal += $line['qty'] * (float)$line['price'];
                }
                $shipping_total += 50.00; // flat per-vendor
            }
            $total = $subtotal + $shipping_total;

            $order = Order::create([
                    'user_id' => Auth::id(),
                'subtotal' => $subtotal,
                'shipping_total' => $shipping_total,
                'total' => $total,
                'shipping_address' => ['name'=>$data['name'],'phone'=>$data['phone'],'address'=>$data['address'],'notes'=>$data['notes'] ?? ''],
                'payment_method' => 'online',
                'status' => 'pending',
            ]);

            // persist order items only (no stock changes)
            foreach ($cart as $vendorId => $lines) {
                foreach ($lines as $productId => $line) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'vendor_id' => $vendorId,
                        'product_id' => $productId,
                        'qty' => $line['qty'],
                        'price' => $line['price'],
                        'shipping_share' => 50.00 / max(1, count($lines)),
                    ]);
                }
            }

            DB::commit();

            // create a payment intent linked to this order
            // choose gateway: if SSLCommerz is configured, use it; otherwise use mock
            $gateway = env('SSLCOMMERZ_STORE_ID') ? 'sslcommerz' : 'mock';

            $intent = PaymentIntent::create([
                'order_id' => $order->id,
                'amount' => (int)round($total * 100),
                'currency' => 'BDT',
                'gateway' => $gateway,
                'status' => 'pending',
                'external_id' => \Illuminate\Support\Str::random(20),
            ]);

            $redirect = $gateway === 'sslcommerz'
                ? route('sslcommerz.checkout', ['intent' => $intent->id])
                : route('payment.mock.redirect', ['id' => $intent->id]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => true, 'redirect' => $redirect]);
            }

            return redirect($redirect);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error','Could not create order: '.$e->getMessage());
        }
    }

    public function confirm(Order $order)
    {
        return view('cart.confirm', compact('order'));
    }
}
