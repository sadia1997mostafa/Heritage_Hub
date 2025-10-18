<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueToPaymentEvents extends Migration
{
    public function up()
    {
        Schema::table('payment_events', function (Blueprint $table) {
            $table->unique(['payment_intent_id','external_event_id'], 'payment_event_unique');
        });
    }

    public function down()
    {
        Schema::table('payment_events', function (Blueprint $table) {
            $table->dropUnique('payment_event_unique');
        });
    }
}
