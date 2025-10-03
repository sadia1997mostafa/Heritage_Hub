<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $t) {
            if (Schema::hasColumn('vendor_profiles','district')) {
                $t->dropColumn('district');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $t) {
            if (!Schema::hasColumn('vendor_profiles','district')) {
                $t->string('district')->nullable();
            }
        });
    }
};
