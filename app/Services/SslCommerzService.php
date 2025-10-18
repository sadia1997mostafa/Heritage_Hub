<?php

namespace App\Services;

class SslCommerzService
{
    protected $storeId;
    protected $storePass;
    protected $sandbox;

    public function __construct()
    {
        $this->storeId = env('SSLCOMMERZ_STORE_ID');
        $this->storePass = env('SSLCOMMERZ_STORE_PASS');
        $this->sandbox = env('SSLCOMMERZ_SANDBOX', true);
    }

    public function init($intent, $successUrl, $failUrl, $cancelUrl)
    {
        // Build payload according to SSLCommerz simple checkout
        $post = [
            'store_id' => $this->storeId ?: 'testbox',
            'store_passwd' => $this->storePass ?: 'qwerty',
            'total_amount' => number_format($intent->amount/100,2, '.', ''),
            'currency' => $intent->currency ?: 'BDT',
            'tran_id' => $intent->external_id,
            'success_url' => $successUrl,
            'fail_url' => $failUrl,
            'cancel_url' => $cancelUrl,
        ];

        // In sandbox we can redirect to the sandbox endpoint via a form
        $endpoint = $this->sandbox ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';

        return ['endpoint'=>$endpoint,'payload'=>$post];
    }
}
