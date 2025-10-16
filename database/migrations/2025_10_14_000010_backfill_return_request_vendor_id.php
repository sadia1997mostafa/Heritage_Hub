<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Backfill vendor_id on existing return_requests using order_items -> products
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('return_requests')) {
            return;
        }

        // Update in chunks to avoid locking large tables
        DB::table('return_requests')
            ->whereNull('vendor_id')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $oi = DB::table('order_items')->where('id', $row->order_item_id)->first();
                    if (! $oi || ! isset($oi->product_id)) continue;
                    $prod = DB::table('products')->where('id', $oi->product_id)->first();
                    if (! $prod || ! isset($prod->vendor_id)) continue;
                    DB::table('return_requests')->where('id', $row->id)->update(['vendor_id' => $prod->vendor_id]);
                }
            });
    }

    /**
     * Reverse the migrations.
     * (We won't null them again.)
     * @return void
     */
    public function down()
    {
        // intentionally left blank
    }
};
