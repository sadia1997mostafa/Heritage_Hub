<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentIntent;
use App\Services\BkashService;
use App\Models\PaymentEvent;
use App\Services\BkashPayment;
use Illuminate\Http\RedirectResponse;

class BkashController extends Controller
{
    protected $svc;

    public function __construct(BkashService $svc)
    {
        $this->svc = $svc;
    }

    public function checkout(Request $request, PaymentIntent $intent)
    {
        // For a richer flow we create a payment (or mock) and redirect user to approval page
        $paymentService = app(BkashPayment::class);
        $res = $paymentService->createPayment($intent);
        if (! $res) {
            // fallback to simple redirect view
            $success = route('payment.mock.return', ['id'=>$intent->id, 'action'=>'success']);
            $fail = route('payment.mock.return', ['id'=>$intent->id, 'action'=>'cancel']);
            $cancel = $fail;
            $payload = $this->svc->init($intent, $success, $fail, $cancel);
            return view('payments.bkash-redirect', ['endpoint' => $payload['endpoint'], 'payload' => $payload['payload']]);
        }

        // Redirect user to approval URL (mocked approve page or provider URL)
        return redirect($res['approveUrl']);
    }

    // endpoint to create payment (AJAX)
    public function create(Request $request)
    {
        $intent = PaymentIntent::findOrFail($request->input('intent'));
        $svc = app(BkashPayment::class);
        $res = $svc->createPayment($intent);
        if (! $res) return response()->json(['error'=>'could not create payment'], 500);
        return response()->json($res);
    }

    // simulated approval page shown to the user to approve payment (dev only)
    public function approve(Request $request, PaymentIntent $intent)
    {
        return view('payments.bkash-approve', ['intent' => $intent]);
    }

    // execute called after approval (simulated)
    public function execute(Request $request)
    {
        $paymentID = $request->input('paymentID');
        $paymentToken = $request->input('paymentToken');
        $intentId = $request->input('intent');
        $svc = app(BkashPayment::class);
        $res = $svc->executePayment($paymentID, $paymentToken);
        if ($res && $res['status'] === 'success') {
            $intent = PaymentIntent::find($intentId);
            if ($intent) {
                $intent->update(['status' => 'succeeded']);
                // call finalize
                try { app(\App\Http\Controllers\PaymentController::class)->finalizeOrder(\App\Models\Order::find($intent->order_id)); } catch (\Throwable $e) { logger()->error($e->getMessage()); }
            }
            return redirect()->route('cart')->with('success','Payment executed (mock)');
        }
        return redirect()->route('cart')->with('error','Payment failed (mock)');
    }

    public function ipn(Request $request)
    {
        $payload = $request->all();
        logger()->info('bKash IPN received', $payload);

        // naive idempotent persistence similar to other handlers
        $external = $payload['trx_id'] ?? ($payload['external_id'] ?? null);
        if (! $external) return response('ignored', 200);
        $intent = PaymentIntent::where('external_id', $external)->first();
        if (! $intent) return response('not found',404);

        $extEventId = $payload['event_id'] ?? ($payload['id'] ?? null) ?: null;
        if ($extEventId) {
            $exists = PaymentEvent::where('payment_intent_id', $intent->id)->where('external_event_id', $extEventId)->exists();
            if ($exists) return response('already processed',200);
        }

        PaymentEvent::create([ 'payment_intent_id' => $intent->id, 'external_event_id' => $extEventId, 'type' => $payload['event'] ?? 'bkash.ipn', 'payload' => $payload]);

        if (! in_array($intent->status, ['succeeded'])) {
            $intent->update(['status' => 'succeeded']);
            try { app(\App\Http\Controllers\PaymentController::class)->finalizeOrder(\App\Models\Order::find($intent->order_id)); } catch (\Throwable $e) { logger()->error($e->getMessage()); }
        }

        return response('OK',200);
    }
}
