<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('heritage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['site','craft','festival','cuisine']);
            $table->string('title');
            $table->string('location')->nullable(); // ex: "Sylhet Sadar"
            $table->text('summary')->nullable();
            $table->string('hero_image')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();

            $table->index(['district_id','category','order_index']);
        });
    }
    public function down(): void { Schema::dropIfExists('heritage_items'); }
};
