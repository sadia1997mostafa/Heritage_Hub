<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'approved')) {
                $table->boolean('approved')->default(false)->after('is_public');
            }
            if (!Schema::hasColumn('events', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved');
            }
            if (!Schema::hasColumn('events', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('events', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('approved_at');
            }
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'cover_image')) {
                $table->dropColumn('cover_image');
            }
            if (Schema::hasColumn('events', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('events', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('events', 'approved')) {
                $table->dropColumn('approved');
            }
        });
    }
};
