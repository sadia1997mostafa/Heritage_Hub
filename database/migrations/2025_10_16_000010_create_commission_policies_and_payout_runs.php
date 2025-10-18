<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commission_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('percent',5,2)->default(10.00);
            $table->foreignId('vendor_id')->nullable()->constrained('vendor_profiles')->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('payout_runs', function (Blueprint $table) {
            $table->id();
            $table->date('run_date');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('csv_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payout_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_run_id')->constrained('payout_runs')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendor_profiles')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->foreignId('payout_account_id')->nullable()->constrained('vendor_payout_accounts')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_transactions');
        Schema::dropIfExists('payout_runs');
        Schema::dropIfExists('commission_policies');
    }
};
