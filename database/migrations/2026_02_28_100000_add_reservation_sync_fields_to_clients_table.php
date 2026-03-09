<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'source')) {
                $table->string('source', 50)->nullable()->after('website');
                $table->index('source');
            }

            if (!Schema::hasColumn('clients', 'created_from_reservation_id')) {
                $table->unsignedBigInteger('created_from_reservation_id')->nullable()->after('source');
                $table->index('created_from_reservation_id');
            }

            if (!Schema::hasColumn('clients', 'total_events_count')) {
                $table->unsignedInteger('total_events_count')->default(0)->after('last_guests');
            }

            if (!Schema::hasColumn('clients', 'lifetime_value')) {
                $table->decimal('lifetime_value', 12, 2)->default(0)->after('total_events_count');
            }

            if (!Schema::hasColumn('clients', 'last_event_total')) {
                $table->decimal('last_event_total', 12, 2)->nullable()->after('last_event_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'source')) {
                $table->dropIndex(['source']);
                $table->dropColumn('source');
            }

            if (Schema::hasColumn('clients', 'created_from_reservation_id')) {
                $table->dropIndex(['created_from_reservation_id']);
                $table->dropColumn('created_from_reservation_id');
            }

            if (Schema::hasColumn('clients', 'total_events_count')) {
                $table->dropColumn('total_events_count');
            }

            if (Schema::hasColumn('clients', 'lifetime_value')) {
                $table->dropColumn('lifetime_value');
            }

            if (Schema::hasColumn('clients', 'last_event_total')) {
                $table->dropColumn('last_event_total');
            }
        });
    }
};
