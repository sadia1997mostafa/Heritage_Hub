<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('event_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->enum('status', ['interested','going'])->default('interested');
            $table->timestamps();
            $table->unique(['event_id','user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_user');
    }
};
