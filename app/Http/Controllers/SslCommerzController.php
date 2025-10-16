<?php

namespace App\Http\Controllers;

use App\Models\PaymentIntent;
use App\Models\PaymentEvent;
use Illuminate\Http\Request;
use App\Services\SslCommerzService;
use App\Services\SslCommerzVerifier;
use App\Http\Controllers\PaymentController as BasePaymentController;
use Illuminate\Support\Str;

class SslCommerzController extends Controller
{
    protected $svc;
    public function __construct(SslCommerzService $svc)
    {
        $this->svc = $svc;
    }

    public function checkout(Request $request, PaymentIntent $intent)
    {
        $success = route('payment.mock.return', ['id'=>$intent->id, 'action'=>'success']);
        $fail = route('payment.mock.return', ['id'=>$intent->id, 'action'=>'cancel']);
        $cancel = $fail;

        $payload = $this->svc->init($intent, $success, $fail, $cancel);

        // render a form that auto-posts to the provider endpoint
        return view('payments.sslcommerz-redirect', ['endpoint' => $payload['endpoint'], 'payload' => $payload['payload']]);
    }

    public function ipn(Request $request)
    {
        $payload = $request->all();
        logger()->info('SSLCommerz IPN received', $payload);

        // Attempt to find the intent by tran_id (external_id) or by cart/order identifiers
        $tran = $payload['tran_id'] ?? $payload['tran_id'] ?? null;
        $external = $payload['tran_id'] ?? $payload['val_id'] ?? ($payload['buy_order'] ?? null);
        if (!$external) {
            return response('ignored', 200);
        }

        $intent = PaymentIntent::where('external_id', $external)->first();
        if (! $intent) {
            // attempt fuzzy: if payload has amount+order id, try matching by amount
            $intent = PaymentIntent::where('amount', (int)round(($payload['amount'] ?? $payload['total_amount'] ?? 0) * 100))->first();
        }

        if (! $intent) return response('not found', 404);

        // verify payload with provider
        $verifier = app(SslCommerzVerifier::class);
        $valid = $verifier->verify($payload);
        if (! $valid) {
            logger()->warning('SSLCommerz IPN verification failed for intent '.$intent->id);
            return response('failed verification', 400);
        }

        // Idempotency: ensure external_event_id not previously processed
        $extEventId = $payload['val_id'] ?? $payload['tran_id'] ?? ($payload['id'] ?? null) ?: Str::random(12);
        $exists = PaymentEvent::where('payment_intent_id', $intent->id)
            ->where('external_event_id', $extEventId)->exists();
        if ($exists) return response('already processed', 200);

        $evt = PaymentEvent::create([
            'payment_intent_id' => $intent->id,
            'external_event_id' => $extEventId,
            'type' => $payload['status'] ?? 'sslcommerz.ipn',
            'payload' => $payload,
        ]);

        // mark intent as succeeded and finalize the order
        if ($intent->status !== 'succeeded') {
            $intent->update(['status' => 'succeeded']);
            if ($intent->order_id) {
                // use PaymentController's finalizeOrder
                $pc = app(BasePaymentController::class);
                try {
                    $order = $pc->finalizeOrder(\App\Models\Order::find($intent->order_id));
                } catch (\Exception $e) {
                    logger()->error('Finalize order via IPN failed: '.$e->getMessage());
                }
            }
        }

        return response('OK',200);
    }
}
