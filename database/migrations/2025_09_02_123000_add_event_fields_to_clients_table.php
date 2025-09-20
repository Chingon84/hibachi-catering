<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'last_event_date')) {
                $table->date('last_event_date')->nullable()->after('website');
            }
            if (!Schema::hasColumn('clients', 'last_guests')) {
                $table->integer('last_guests')->nullable()->after('last_event_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'last_event_date')) {
                $table->dropColumn('last_event_date');
            }
            if (Schema::hasColumn('clients', 'last_guests')) {
                $table->dropColumn('last_guests');
            }
        });
    }
};

