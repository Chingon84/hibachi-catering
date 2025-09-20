<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservation_items') && !Schema::hasColumn('reservation_items', 'description')) {
            Schema::table('reservation_items', function (Blueprint $table) {
                $table->string('description', 255)->nullable()->after('name_snapshot');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reservation_items') && Schema::hasColumn('reservation_items', 'description')) {
            Schema::table('reservation_items', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};

