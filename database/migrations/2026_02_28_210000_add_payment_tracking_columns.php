<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'type')) {
                $table->string('type', 20)->nullable()->after('provider');
            }
            if (!Schema::hasColumn('payments', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            try {
                $table->index(['reservation_id', 'type', 'status'], 'payments_res_type_status_idx');
            } catch (\Throwable $e) {
            }
            try {
                $table->index('stripe_session_id', 'payments_stripe_session_idx');
            } catch (\Throwable $e) {
            }
            try {
                $table->index('stripe_payment_intent_id', 'payments_stripe_pi_idx');
            } catch (\Throwable $e) {
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'amount_paid_total')) {
                $table->decimal('amount_paid_total', 12, 2)->default(0)->after('deposit_paid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'amount_paid_total')) {
                $table->dropColumn('amount_paid_total');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            try {
                $table->dropIndex('payments_res_type_status_idx');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('payments_stripe_session_idx');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('payments_stripe_pi_idx');
            } catch (\Throwable $e) {
            }

            if (Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
                $table->dropColumn('stripe_payment_intent_id');
            }
            if (Schema::hasColumn('payments', 'stripe_session_id')) {
                $table->dropColumn('stripe_session_id');
            }
            if (Schema::hasColumn('payments', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};

