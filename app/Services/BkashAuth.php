<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BkashAuth
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

    public function getToken()
    {
        // If no credentials, return a mocked token for local development
        if (! $this->appKey || ! $this->appSecret) {
            return ['id_token' => 'mock-token-'.substr(md5(time()),0,8), 'expires_in' => 3600];
        }

        // Real implementation (scaffold): request token from bkash
        // Allow overriding endpoints with env for testing / provider updates
        $tokenBase = env('BKASH_TOKEN_URL') ?: ($this->sandbox ? 'https://tokenized.sandbox.bkash.com' : 'https://tokenized.bkash.com');
        try {
            $resp = Http::withHeaders(['Accept' => 'application/json'])->post(rtrim($tokenBase,'/').'/token/grant', [
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
            ]);
            if ($resp->ok()) {
                // expected shape: { "id_token": "...", "expires_in": 3600 }
                return $resp->json();
            }
            logger()->warning('bkash token grant failed: '.$resp->body());
        } catch (\Throwable $e) {
            logger()->warning('bkash token error: '.$e->getMessage());
        }

        return null;
    }
}
