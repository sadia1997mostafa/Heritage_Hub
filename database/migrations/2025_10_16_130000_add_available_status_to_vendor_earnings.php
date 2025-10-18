<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'available' to enum so listener can set it.
        // Uses raw DB statement for MySQL. Adjust if using another driver.
        DB::statement("ALTER TABLE `vendor_earnings` MODIFY COLUMN `status` ENUM('pending','available','paid') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `vendor_earnings` MODIFY COLUMN `status` ENUM('pending','paid') NOT NULL DEFAULT 'pending'");
    }
};
