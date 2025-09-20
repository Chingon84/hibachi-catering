<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->decimal('distance_miles', 8, 2)->nullable();
            $table->string('event_type')->nullable();
            $table->string('setup_color')->nullable();
            $table->boolean('stairs')->default(false);
            $table->string('heard_about')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'company','city','zip_code','distance_miles',
                'event_type','setup_color','stairs','heard_about'
            ]);
        });
    }
};
