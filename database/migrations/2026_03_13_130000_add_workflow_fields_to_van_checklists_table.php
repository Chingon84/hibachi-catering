<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('van_checklists', function (Blueprint $table) {
            $table->string('checklist_type', 40)->nullable()->after('van_number');
            $table->string('trip_status', 60)->nullable()->after('checklist_type');
        });

        DB::table('van_checklists')
            ->whereNull('checklist_type')
            ->update(['checklist_type' => 'Dispatch']);

        DB::table('van_checklists')
            ->whereNull('trip_status')
            ->update(['trip_status' => 'Complete']);
    }

    public function down(): void
    {
        Schema::table('van_checklists', function (Blueprint $table) {
            $table->dropColumn(['checklist_type', 'trip_status']);
        });
    }
};
