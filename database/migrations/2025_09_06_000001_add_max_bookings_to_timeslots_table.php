<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timeslots', function (Blueprint $table) {
            $table->integer('max_bookings')->nullable()->after('capacity');
        });
    }

    public function down(): void
    {
        Schema::table('timeslots', function (Blueprint $table) {
            $table->dropColumn('max_bookings');
        });
    }
};

