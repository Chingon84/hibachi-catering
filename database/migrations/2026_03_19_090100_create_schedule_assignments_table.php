<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->date('week_start_date');
            $table->decimal('priority_snapshot', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('reservation_id');
            $table->index(['week_start_date', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_assignments');
    }
};
