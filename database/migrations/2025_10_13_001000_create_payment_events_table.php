<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentEventsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_intent_id')->nullable()->index();
            $table->string('external_event_id')->nullable()->index();
            $table->string('type')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_events');
    }
}
