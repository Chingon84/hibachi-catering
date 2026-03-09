<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('good_feedback', function (Blueprint $table) {
            $table->id();
            $table->date('event_date')->index();
            $table->date('date_received')->index();
            $table->string('chef')->index();
            $table->string('source')->nullable()->index();
            $table->text('compliment');
            $table->string('assistant')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('good_feedback');
    }
};
