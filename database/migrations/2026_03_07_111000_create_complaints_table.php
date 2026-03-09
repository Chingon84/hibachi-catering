<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->date('event_date')->index();
            $table->date('date_received')->index();
            $table->string('chef')->index();
            $table->string('category');
            $table->text('description');
            $table->string('resolution_status')->index();
            $table->string('assistant')->nullable();
            $table->text('action_taken')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
