<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('van_feedback', function (Blueprint $table) {
            $table->id();
            $table->date('event_date')->index();
            $table->date('date_received')->index();
            $table->string('chef')->index();
            $table->string('van')->index();
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_feedback');
    }
};
