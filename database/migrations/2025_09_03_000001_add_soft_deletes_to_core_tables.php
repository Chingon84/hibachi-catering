<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['reservations','reservation_items','payments'] as $tbl) {
            if (Schema::hasTable($tbl) && !Schema::hasColumn($tbl, 'deleted_at')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['reservations','reservation_items','payments'] as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'deleted_at')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};

