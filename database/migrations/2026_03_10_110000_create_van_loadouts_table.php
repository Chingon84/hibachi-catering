<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('van_loadouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_id')->constrained('vans')->cascadeOnDelete();
            $table->string('van_status', 20)->default('neutral');
            $table->json('grills')->nullable();
            $table->unsignedInteger('tables_count')->default(0);
            $table->unsignedInteger('chairs_count')->default(0);
            $table->unsignedInteger('propane_tanks_count')->default(0);
            $table->unsignedInteger('dolly_count')->default(0);
            $table->unsignedInteger('straps_count')->default(0);
            $table->unsignedInteger('floor_mats_count')->default(0);
            $table->unsignedInteger('trash_cans_count')->default(0);
            $table->unsignedInteger('heaters_count')->default(0);
            $table->unsignedInteger('buffet_warmers_count')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('loaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->date('event_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_loadouts');
    }
};
