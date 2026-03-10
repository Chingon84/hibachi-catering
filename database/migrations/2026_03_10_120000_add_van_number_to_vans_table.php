<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vans', function (Blueprint $table) {
            $table->unsignedTinyInteger('van_number')->nullable()->after('id')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('vans', function (Blueprint $table) {
            $table->dropUnique(['van_number']);
            $table->dropColumn('van_number');
        });
    }
};
