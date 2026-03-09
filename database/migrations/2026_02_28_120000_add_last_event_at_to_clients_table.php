<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'last_event_at')) {
                $table->timestamp('last_event_at')->nullable()->after('last_event_date');
                $table->index('last_event_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'last_event_at')) {
                $table->dropIndex(['last_event_at']);
                $table->dropColumn('last_event_at');
            }
        });
    }
};

