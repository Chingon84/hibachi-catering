<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'tax_enabled')) {
                $table->boolean('tax_enabled')->default(false)->after('subtotal');
            }
            if (!Schema::hasColumn('invoices', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('tax_enabled');
            }
            if (!Schema::hasColumn('invoices', 'gratuity_enabled')) {
                $table->boolean('gratuity_enabled')->default(false)->after('tax');
            }
            if (!Schema::hasColumn('invoices', 'gratuity')) {
                $table->decimal('gratuity', 10, 2)->default(0)->after('gratuity_enabled');
            }
            if (!Schema::hasColumn('invoices', 'deposit_enabled')) {
                $table->boolean('deposit_enabled')->default(false)->after('gratuity');
            }
            if (!Schema::hasColumn('invoices', 'deposit_amount')) {
                $table->decimal('deposit_amount', 10, 2)->default(0)->after('deposit_enabled');
            }
            if (!Schema::hasColumn('invoices', 'service_charge_enabled')) {
                $table->boolean('service_charge_enabled')->default(false)->after('deposit_amount');
            }
            if (!Schema::hasColumn('invoices', 'service_charge_rate')) {
                $table->decimal('service_charge_rate', 5, 2)->default(0)->after('service_charge_enabled');
            }
            if (!Schema::hasColumn('invoices', 'service_charge')) {
                $table->decimal('service_charge', 10, 2)->default(0)->after('service_charge_rate');
            }
            if (!Schema::hasColumn('invoices', 'discount_enabled')) {
                $table->boolean('discount_enabled')->default(false)->after('service_charge');
            }
            if (!Schema::hasColumn('invoices', 'discount_rate')) {
                $table->decimal('discount_rate', 5, 2)->default(0)->after('discount_enabled');
            }
            if (!Schema::hasColumn('invoices', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('discount_rate');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            foreach ([
                'tax_enabled',
                'tax_rate',
                'gratuity_enabled',
                'gratuity',
                'deposit_enabled',
                'deposit_amount',
                'service_charge_enabled',
                'service_charge_rate',
                'service_charge',
                'discount_enabled',
                'discount_rate',
                'discount',
            ] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
