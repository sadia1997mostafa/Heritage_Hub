<?php

namespace App\Http\Controllers;

use App\Models\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\PaymentEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\VendorEarning;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    // Create a payment intent for an order (simple mock)
    public function create(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'nullable|integer',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:8'
        ]);

        // choose gateway: prefer explicit request param, then provider envs (bkash/sslcommerz), fall back to mock
        $reqGateway = $request->query('gateway') ?? $request->input('gateway');
        if ($reqGateway) {
            $gateway = $reqGateway;
        } else if (env('BKASH_APP_KEY')) {
            $gateway = 'bkash';
        } else if (env('SSLCOMMERZ_STORE_ID')) {
            $gateway = 'sslcommerz';
        } else {
            $gateway = 'mock';
        }

        $intent = PaymentIntent::create([
            'order_id' => $data['order_id'] ?? null,
            'amount' => (int)round($data['amount'] * 100),
            'currency' => $data['currency'] ?? 'BDT',
            'gateway' => $gateway,
            'status' => 'pending',
            'external_id' => Str::random(20),
            'metadata' => []
        ]);

        if ($gateway === 'sslcommerz') {
            return redirect()->route('sslcommerz.checkout', ['intent' => $intent->id]);
        }
        if ($gateway === 'bkash') {
            return redirect()->route('bkash.checkout', ['intent' => $intent->id]);
        }

        // For mock gateway, redirect to a local return page with success simulation
        return redirect()->route("payment.mock.redirect", ['id' => $intent->id]);
    }

    // Simulated gateway redirect page (user 'pays' and returns)
    public function mockRedirect($id)
    {
        $intent = PaymentIntent::findOrFail($id);
        return view('payments.mock-redirect', ['intent' => $intent]);
    }

    // Simulated return from gateway (success/cancel)
    public function mockReturn(Request $request, $id)
    {
        $intent = PaymentIntent::findOrFail($id);
        $action = $request->query('action','success');
        $chosen = $request->query('gateway', null);
        if ($chosen) {
            $intent->update(['gateway' => $chosen]);
        }

        if ($action === 'success') {
            $intent->update(["status" => "succeeded"]);
            // finalize the order: create shipments, decrement stock, create vendor earnings
            if ($intent->order_id) {
                $order = Order::find($intent->order_id);
                if ($order) {
                    try {
                        $this->finalizeOrder($order);
                    } catch (\Exception $e) {
                        // log and continue
                        logger()->error('Finalize order failed: '.$e->getMessage());
                    }
                }
            }
            return redirect("/")->with("status","Payment succeeded (mock)");
        }

        $intent->update(["status" => "failed"]);
        return redirect("/")->with("status","Payment failed (mock)");
    }

    // Webhook handler for gateway events (idempotent by external_id)
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $external = $payload['external_id'] ?? null;
        $extEventId = $payload['event_id'] ?? ($payload['id'] ?? null);
        if (!$external) return response('ignored', 200);

        $intent = PaymentIntent::where('external_id',$external)->first();
        if (!$intent) return response('not found', 404);

        // Idempotency: if we already processed this external event id for this intent, ignore
        if ($extEventId) {
            $exists = PaymentEvent::where('payment_intent_id', $intent->id)
                ->where('external_event_id', $extEventId)->exists();
            if ($exists) return response('already processed', 200);
        }

        // Persist the incoming event
        $evt = PaymentEvent::create([
            'payment_intent_id' => $intent->id,
            'external_event_id' => $extEventId,
            'type' => $payload['event'] ?? ($payload['type'] ?? 'unknown'),
            'payload' => $payload,
        ]);

        $event = $payload['event'] ?? $payload['type'] ?? 'unknown';
        if ($event === 'payment.succeeded' || $event === 'charge.succeeded') {
            if ($intent->status !== 'succeeded') {
                $intent->update(["status" => "succeeded"]);
                // finalize order
                if ($intent->order_id) {
                    $order = Order::find($intent->order_id);
                    if ($order) $this->finalizeOrder($order);
                }
            }
        }

        return response('ok',200);
    }

    // finalize an order after successful payment
    public function finalizeOrder(Order $order)
    {
        DB::beginTransaction();
        try {
            // lock items and decrement stock, create shipments and vendor earnings
            $items = $order->items; // assume Order has items relation

            // group by vendor
            $byVendor = [];
            foreach ($items as $it) {
                $byVendor[$it->vendor_id][] = $it;
            }

            foreach ($byVendor as $vendorId => $items) {
                $shipment = Shipment::create(['order_id'=>$order->id,'vendor_id'=>$vendorId,'shipping_amount'=>50.00]);
                $gross = 0;
                foreach ($items as $it) {
                    $product = Product::where('id',$it->product_id)->lockForUpdate()->first();
                    if (!$product || $product->stock < $it->qty) {
                        throw new \Exception('Out of stock on finalize: '.$it->product_id);
                    }
                    $product->decrement('stock', $it->qty);

                    // update item shipment id if column exists
                    if (property_exists($it,'shipment_id')) {
                        $it->shipment_id = $shipment->id;
                        $it->save();
                    }

                    $gross += $it->qty * (float)$it->price;
                }

                $platformFee = round($gross * 0.10, 2);
                $vendorShare = round($gross - $platformFee + 50.00, 2);
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

            $order->status = 'paid';
            $order->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
