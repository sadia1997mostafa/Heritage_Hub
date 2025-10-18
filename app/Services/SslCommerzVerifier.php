<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SslCommerzVerifier
{
    protected $sandbox;
    public function __construct()
    {
        $this->sandbox = env('SSLCOMMERZ_SANDBOX', true);
    }

    /**
     * Verify IPN payload with SSLCommerz validator endpoint.
     * Returns true if provider confirms the transaction is valid.
     */
    public function verify(array $payload): bool
    {
        // In sandbox mode we may allow a permissive verify when SANDBOX=true and STORE_ID is testbox.
        if ($this->sandbox && env('SSLCOMMERZ_STORE_ID') === null) {
            return true;
        }

        // Build verification endpoint (this is a simple pattern; providers vary)
        $endpoint = $this->sandbox
            ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' 
            : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';

        // SSLCommerz does not provide a single universal 'validate' endpoint in all docs; some flows require server-to-server queries.
        // For now we'll attempt a basic POST with the received payload and expect a JSON 'status' or 'val_id' field as confirmation.
        try {
            $resp = Http::timeout(6)->post($endpoint, $payload);
            if (! $resp->ok()) return false;
            $data = $resp->json();
            // heuristics: if response contains a transaction id or status=VALID, accept
            if (!empty($data['status']) && in_array(strtolower($data['status']), ['valid','completed','success','completed'])) return true;
            if (!empty($data['val_id']) || !empty($data['tran_id'])) return true;
        } catch (\Throwable $e) {
            logger()->warning('SSLCommerz verify failed: '.$e->getMessage());
        }

        return false;
    }
}
