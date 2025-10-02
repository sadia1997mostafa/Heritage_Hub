<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('divisions', function (Blueprint $table) {
            $table->engine = 'InnoDB';        // ensure InnoDB for FKs
            $table->id();                      // BIGINT UNSIGNED
            $table->string('name')->unique();
            $table->string('slug')->unique(); // e.g. 'sylhet'
            $table->string('banner_url')->nullable();
            $table->text('intro_html')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('divisions');
    }
};
