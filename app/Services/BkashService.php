<?php

namespace App\Services;

class BkashService
{
    protected $appKey;
    protected $appSecret;
    protected $sandbox;

    public function __construct()
    {
        $this->appKey = env('BKASH_APP_KEY');
        $this->appSecret = env('BKASH_APP_SECRET');
        $this->sandbox = env('BKASH_SANDBOX', true);
    }

    /**
     * Prepare a server-side payload for an in-page redirect or client-side token exchange.
     * This is a scaffold for development; real bkash integration requires OAuth and token exchanges.
     */
    public function init($intent, $successUrl, $failUrl, $cancelUrl)
    {
        // For sandbox this returns a fake payload the frontend could use to start a bkash flow
        $payload = [
            'app_key' => $this->appKey ?: 'sandbox_app_key',
            'amount' => number_format($intent->amount/100, 2, '.', ''),
            'currency' => $intent->currency ?: 'BDT',
            'trx_id' => $intent->external_id,
            'success_url' => $successUrl,
            'fail_url' => $failUrl,
            'cancel_url' => $cancelUrl,
        ];

        $endpoint = $this->sandbox ? 'https://checkout.sandbox.bkash.com' : 'https://checkout.bkash.com';
        return ['endpoint' => $endpoint, 'payload' => $payload];
    }
}
