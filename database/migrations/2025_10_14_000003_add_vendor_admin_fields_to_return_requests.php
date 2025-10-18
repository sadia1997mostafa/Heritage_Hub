<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('return_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->after('user_id');
            $table->string('vendor_status')->nullable()->after('status');
            $table->text('vendor_notes')->nullable()->after('photos');
            $table->timestamp('vendor_handled_at')->nullable()->after('vendor_notes');

            $table->string('admin_status')->nullable()->after('vendor_handled_at');
            $table->text('admin_notes')->nullable()->after('admin_status');
            $table->timestamp('admin_handled_at')->nullable()->after('admin_notes');
        });
    }

    public function down()
    {
        Schema::table('return_requests', function (Blueprint $table) {
            $table->dropColumn(['vendor_id','vendor_status','vendor_notes','vendor_handled_at','admin_status','admin_notes','admin_handled_at']);
        });
    }
};
