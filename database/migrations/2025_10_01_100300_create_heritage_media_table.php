<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('heritage_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('heritage_item_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image','video'])->default('image');
            $table->string('url');
            $table->string('caption')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->index(['heritage_item_id','type','order_index']);
        });
    }
    public function down(): void { Schema::dropIfExists('heritage_media'); }
};
