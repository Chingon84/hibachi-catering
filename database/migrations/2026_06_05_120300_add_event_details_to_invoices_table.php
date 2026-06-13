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
            if (!Schema::hasColumn('invoices', 'customer_address')) {
                $table->string('customer_address')->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('invoices', 'event_date')) {
                $table->date('event_date')->nullable()->after('customer_address');
            }
            if (!Schema::hasColumn('invoices', 'event_time')) {
                $table->string('event_time', 20)->nullable()->after('event_date');
            }
            if (!Schema::hasColumn('invoices', 'event_guests')) {
                $table->unsignedInteger('event_guests')->nullable()->after('event_time');
            }
            if (!Schema::hasColumn('invoices', 'event_type')) {
                $table->string('event_type', 80)->nullable()->after('event_guests');
            }
            if (!Schema::hasColumn('invoices', 'setup_color')) {
                $table->string('setup_color', 80)->nullable()->after('event_type');
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
                'customer_address',
                'event_date',
                'event_time',
                'event_guests',
                'event_type',
                'setup_color',
            ] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
