<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentIntentsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('gateway')->default('mock')->index();
            $table->string('status')->default('pending')->index();
            $table->bigInteger('amount')->default(0); // cents
            $table->string('currency')->default('BDT');
            $table->string('external_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_intents');
    }
}
