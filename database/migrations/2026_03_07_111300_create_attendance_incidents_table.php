<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_incidents', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('chef')->index();
            $table->string('incident_type')->index();
            $table->unsignedInteger('units')->default(0);
            $table->boolean('authorized')->default(false)->index();
            $table->string('manager')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_incidents');
    }
};
