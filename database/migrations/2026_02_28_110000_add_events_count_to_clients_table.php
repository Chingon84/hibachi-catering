<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'events_count')) {
                $table->unsignedInteger('events_count')->default(0)->after('last_guests');
                $table->index('events_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'events_count')) {
                $table->dropIndex(['events_count']);
                $table->dropColumn('events_count');
            }
        });
    }
};

