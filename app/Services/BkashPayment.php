<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\PaymentIntent;

class BkashPayment
{
    protected $auth;
    protected $sandbox;

    public function __construct(BkashAuth $auth)
    {
        $this->auth = $auth;
        $this->sandbox = env('BKASH_SANDBOX', true);
    }

    /**
     * Create a payment and return an approval URL (mocked when no credentials).
     */
    public function createPayment(PaymentIntent $intent)
    {
        $token = $this->auth->getToken();
        if (! $token) return null;

        // If token is mocked (starts with mock-), build a local approval URL
        if (is_string($token['id_token']) && strpos($token['id_token'],'mock-') === 0) {
            $approveUrl = route('bkash.approve',['intent' => $intent->id]);
            return ['paymentID' => 'mockpay-'.substr(md5($intent->external_id),0,8), 'approveUrl' => $approveUrl, 'token' => $token['id_token']];
        }

        // Real implementation: call bkash create payment endpoint with token
        // Real implementation: call bkash create payment endpoint with token
        $createBase = env('BKASH_CREATE_URL') ?: ($this->sandbox ? 'https://checkout.sandbox.bkash.com' : 'https://checkout.bkash.com');
        try {
            $resp = Http::withToken($token['id_token'])->post(rtrim($createBase,'/').'/checkout/payment/create', [
                'amount' => number_format($intent->amount/100, 2, '.', ''),
                'trxID' => $intent->external_id,
            ]);
            if ($resp->ok()) return $resp->json();
            logger()->warning('bkash create payment failed: '.$resp->body());
        } catch (\Throwable $e) {
            logger()->warning('bkash create payment error: '.$e->getMessage());
        }

        return null;
    }

    public function executePayment($paymentID, $paymentToken)
    {
        // Mocked execution
        if ((is_string($paymentToken) && strpos($paymentToken,'mock-') === 0) || (is_string($paymentID) && strpos($paymentID,'mockpay-')===0)) {
            return ['status' => 'success', 'trxID' => 'trx-'.substr(md5($paymentID.microtime(true)),0,8)];
        }

        // Real implementation: call execute endpoint
        $executeBase = env('BKASH_EXECUTE_URL') ?: ($this->sandbox ? 'https://checkout.sandbox.bkash.com' : 'https://checkout.bkash.com');
        try {
            $resp = Http::withToken($paymentToken)->post(rtrim($executeBase,'/')."/checkout/payment/execute/".$paymentID);
            if ($resp->ok()) {
                $json = $resp->json();
                // example success shape may include transaction id
                return ['status' => ($json['status'] ?? 'success'), 'trxID' => $json['trxID'] ?? ($json['transactionId'] ?? null), 'raw' => $json];
            }
            logger()->warning('bkash execute failed: '.$resp->body());
        } catch (\Throwable $e) {
            logger()->warning('bkash execute error: '.$e->getMessage());
        }

        return null;
    }
}
