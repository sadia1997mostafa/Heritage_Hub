<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_profile_photo_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->string('profile_photo_path')->nullable()->after('password');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn('profile_photo_path');
        });
    }
};
