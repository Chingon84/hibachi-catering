<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('card_brand', 20)->nullable()->after('payload_json');
            $table->string('card_last4', 4)->nullable()->after('card_brand');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'card_last4')) $table->dropColumn('card_last4');
            if (Schema::hasColumn('payments', 'card_brand')) $table->dropColumn('card_brand');
        });
    }
};

