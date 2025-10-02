<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->string('website_url')->nullable();
            $table->string('shop_url')->nullable(); // internal /shop?id=... or external
            $table->string('tags')->nullable(); // e.g., "cane,bamboo,handicraft"
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['district_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('vendors'); }
};
