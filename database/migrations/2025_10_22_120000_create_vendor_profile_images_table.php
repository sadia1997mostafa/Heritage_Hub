<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vendor_profile_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
            $t->string('path');
            $t->integer('ordering')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vendor_profile_images'); }
};
