<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices') || Schema::hasColumn('invoices', 'travel_fee')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('travel_fee', 10, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices') || !Schema::hasColumn('invoices', 'travel_fee')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('travel_fee');
        });
    }
};
