<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'gratuity')) {
                $table->decimal('gratuity', 10, 2)->default(0)->after('tax');
            }
            if (!Schema::hasColumn('reservations', 'balance')) {
                $table->decimal('balance', 10, 2)->default(0)->after('deposit_paid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'gratuity')) {
                $table->dropColumn('gratuity');
            }
            if (Schema::hasColumn('reservations', 'balance')) {
                $table->dropColumn('balance');
            }
        });
    }
};

