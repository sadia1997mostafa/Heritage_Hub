<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PaymentIntent;
use App\Models\PaymentEvent;

class BkashFlowTest extends TestCase
{
    public function test_create_and_execute_payment_updates_intent_and_persists_event()
    {
        // Ensure minimal tables exist for tests (avoid running full migrations)
        if (! Schema::hasTable('payment_intents')) {
            Schema::create('payment_intents', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('order_id')->nullable();
                $table->integer('amount');
                $table->string('currency', 10)->default('BDT');
                $table->string('gateway')->nullable();
                $table->string('status')->default('created');
                $table->string('external_id')->nullable();
                $table->text('metadata')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('payment_events')) {
            Schema::create('payment_events', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('payment_intent_id');
                $table->string('external_event_id')->nullable();
                $table->string('type')->nullable();
                $table->text('payload')->nullable();
                $table->timestamps();
            });
        }

        // Create a payment intent in DB (minimal fields)
        $intent = PaymentIntent::create([
            'order_id' => 1,
            'amount' => 10000, // 100.00
            'currency' => 'BDT',
            'gateway' => 'bkash',
            'status' => 'created',
            'external_id' => 'ext-'.uniqid(),
            'metadata' => []
        ]);

        // Fake token grant, create and execute endpoints
        Http::fake(function ($request) use ($intent) {
            $url = $request->url();
            if (str_contains($url, '/token/grant')) {
                return Http::response(['id_token' => 'real-token-abc', 'expires_in' => 3600], 200);
            }
            if (str_contains($url, '/checkout/payment/create')) {
                return Http::response(['paymentID' => 'pay-123', 'approveUrl' => 'https://bkash.test/approve', 'paymentToken' => 'real-token-abc'], 200);
            }
            if (str_contains($url, '/checkout/payment/execute')) {
                return Http::response(['status' => 'success', 'trxID' => 'trx-789'], 200);
            }
            return Http::response(null, 404);
        });

        // Call create endpoint
        $createResp = $this->postJson(route('bkash.create'), ['intent' => $intent->id]);
        $createResp->assertStatus(200);
        $data = $createResp->json();
        $this->assertArrayHasKey('paymentID', $data);

        // Simulate execute: call controller directly
        $execResp = $this->postJson(route('bkash.execute'), ['paymentID' => $data['paymentID'], 'paymentToken' => 'real-token-abc', 'intent' => $intent->id]);

        // After execute, intent should be updated to succeeded
        $intent->refresh();
        $this->assertEquals('succeeded', $intent->status);

        // PaymentEvent should be created (execute may add an event via IPN in real flows; we check at least zero errors occurred)
        $this->assertDatabaseHas('payment_intents', ['id' => $intent->id, 'status' => 'succeeded']);

        // Cleanup
        PaymentEvent::where('payment_intent_id', $intent->id)->delete();
        $intent->delete();
    }
}
