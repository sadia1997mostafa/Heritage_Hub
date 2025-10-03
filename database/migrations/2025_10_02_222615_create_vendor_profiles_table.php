z<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_vendor_profiles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vendor_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('shop_name');
            $t->string('phone', 30)->index();
            $t->string('district', 100)->index();
            $t->string('address', 255)->nullable();
            $t->text('description')->nullable();
            $t->text('heritage_story')->nullable();
            $t->string('shop_logo_path')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vendor_profiles'); }
};
