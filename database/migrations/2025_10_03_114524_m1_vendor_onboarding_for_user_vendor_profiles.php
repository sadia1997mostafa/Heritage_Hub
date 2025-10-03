<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // --- vendor_profiles: add workflow + slug + district_id + contacts + banner ---
        Schema::table('vendor_profiles', function (Blueprint $t) {
            if (!Schema::hasColumn('vendor_profiles', 'slug')) {
                $t->string('slug')->unique()->after('shop_name');
            }

            if (!Schema::hasColumn('vendor_profiles', 'status')) {
                $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('user_id');
                $t->timestamp('approved_at')->nullable()->after('status');
                $t->timestamp('rejected_at')->nullable()->after('approved_at');
                $t->string('rejection_reason')->nullable()->after('rejected_at');
            }

            if (!Schema::hasColumn('vendor_profiles', 'district_id')) {
                $t->unsignedBigInteger('district_id')->nullable()->after('user_id');
                $t->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
            }

            if (!Schema::hasColumn('vendor_profiles', 'support_email')) {
                $t->string('support_email')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('vendor_profiles', 'support_phone')) {
                $t->string('support_phone', 32)->nullable()->after('support_email');
            }

            if (!Schema::hasColumn('vendor_profiles', 'banner_path')) {
                $t->string('banner_path')->nullable()->after('shop_logo_path');
            }
        });

        // --- payout accounts tied to user_id (no separate vendors table) ---
        if (!Schema::hasTable('vendor_payout_accounts')) {
            Schema::create('vendor_payout_accounts', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->enum('method', ['bkash', 'nagad', 'bank']);
                $t->string('account_no')->nullable();
                $t->string('account_name')->nullable();
                $t->string('bank_name')->nullable();
                $t->string('branch')->nullable();
                $t->string('routing_no')->nullable();
                $t->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
                $t->string('doc_path')->nullable();
                $t->timestamps();

                $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // --- KYC docs tied to user_id ---
        if (!Schema::hasTable('vendor_kyc_docs')) {
            Schema::create('vendor_kyc_docs', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->string('type');        // nid, trade_license, etc.
                $t->string('file_path');
                $t->timestamps();

                $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vendor_kyc_docs')) {
            Schema::drop('vendor_kyc_docs');
        }

        if (Schema::hasTable('vendor_payout_accounts')) {
            Schema::drop('vendor_payout_accounts');
        }

        Schema::table('vendor_profiles', function (Blueprint $t) {
            if (Schema::hasColumn('vendor_profiles', 'district_id')) {
                // drop FK first, then column
                $t->dropForeign(['district_id']);
                $t->dropColumn('district_id');
            }

            foreach (['banner_path', 'support_phone', 'support_email'] as $c) {
                if (Schema::hasColumn('vendor_profiles', $c)) {
                    $t->dropColumn($c);
                }
            }

            foreach (['rejection_reason', 'rejected_at', 'approved_at', 'status', 'slug'] as $c) {
                if (Schema::hasColumn('vendor_profiles', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
