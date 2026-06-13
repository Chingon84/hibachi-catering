<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_priority_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start_date');
            $table->string('status', 20)->default('Active');
            $table->string('availability_status', 20)->default('Available');
            $table->unsignedInteger('events_assigned')->default(0);
            $table->unsignedTinyInteger('requested_days_off')->default(0);
            $table->unsignedTinyInteger('missed_days')->default(0);
            $table->unsignedTinyInteger('late_cancellations')->default(0);
            $table->decimal('reliability_score', 5, 2)->default(82);
            $table->decimal('fairness_adjustment', 5, 2)->default(0);
            $table->decimal('penalty_points', 6, 2)->default(0);
            $table->decimal('final_priority_score', 6, 2)->default(0);
            $table->string('priority_tier', 2)->default('C');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'week_start_date']);
            $table->index(['week_start_date', 'priority_tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_priority_logs');
    }
};
