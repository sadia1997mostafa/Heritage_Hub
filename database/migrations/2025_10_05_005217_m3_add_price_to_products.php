<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            if (!Schema::hasColumn('products','price')) {
                $t->decimal('price', 10, 2)->default(0)->after('description');
                $t->index(['status','category_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            if (Schema::hasColumn('products','price')) {
                $t->dropColumn('price');
            }
        });
    }
};
