<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_event_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 24)->default('not_viewed');
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['reservation_id', 'user_id'], 'staff_event_confirmations_unique');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_event_confirmations');
    }
};
