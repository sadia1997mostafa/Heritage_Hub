<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PaymentIntent;
use App\Models\PaymentEvent;

class SslCommerzIpnTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
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

    public function test_sslcommerz_ipn_verifies_and_processes()
    {
        // Mock verifier to always return true
        $mock = $this->createMock(\App\Services\SslCommerzVerifier::class);
        $mock->method('verify')->willReturn(true);
        $this->app->instance(\App\Services\SslCommerzVerifier::class, $mock);

        $intent = PaymentIntent::create(['order_id'=>null,'amount'=>500,'currency'=>'BDT','gateway'=>'sslcommerz','status'=>'pending','external_id'=>'trx-abc']);

        $payload = ['tran_id'=>'trx-abc','val_id'=>'val-1','status'=>'VALID','amount'=>5.00];

        $this->postJson(route('sslcommerz.ipn'), $payload)->assertStatus(200);

        $this->assertDatabaseCount('payment_events', 1);
        $this->assertDatabaseHas('payment_intents', ['id'=>$intent->id,'status'=>'succeeded']);
    }
}
