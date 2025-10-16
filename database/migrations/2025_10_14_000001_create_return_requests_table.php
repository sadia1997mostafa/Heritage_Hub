<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status')->default('pending'); // pending, approved, declined, received
            $table->text('reason')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('return_requests');
    }
};
