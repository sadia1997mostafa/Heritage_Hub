<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentIntent;
use App\Models\PaymentEvent;
use App\Models\Order;

class PaymentWebhookTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Create minimal tables needed for this test to avoid running full migrations
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('shipping_total', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);
                $table->json('shipping_address')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_intents')) {
            Schema::create('payment_intents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->integer('amount')->default(0);
                $table->string('currency', 10)->default('BDT');
                $table->string('gateway')->nullable();
                $table->string('status')->nullable();
                $table->string('external_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_events')) {
            Schema::create('payment_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payment_intent_id');
                $table->string('external_event_id')->nullable();
                $table->string('type')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_webhook_idempotent_processing()
    {
        // create a payment intent without an order to avoid finalizeOrder running
        $intent = PaymentIntent::create([
            'order_id' => null,
            'amount' => 1000,
            'currency' => 'BDT',
            'gateway' => 'mock',
            'status' => 'pending',
            'external_id' => 'evt123'
        ]);

        $payload = ['external_id' => 'evt123', 'event' => 'payment.succeeded', 'event_id' => 'evt123-1'];

    $this->postJson(route('payment.webhook'), $payload)->assertStatus(200);
    $this->assertDatabaseCount('payment_events', 1);
    $this->assertDatabaseHas('payment_intents', ['id' => $intent->id, 'status' => 'succeeded']);

    // send again (duplicate)
    $this->postJson(route('payment.webhook'), $payload)->assertStatus(200);
    $this->assertDatabaseCount('payment_events', 1);
    }
}
