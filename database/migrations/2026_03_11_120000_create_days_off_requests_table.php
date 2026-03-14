<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('days_off_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->string('chef')->index();
            $table->string('request_type')->default('Other')->index();
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->string('status')->default('Pending')->index();
            $table->unsignedInteger('days')->default(1);
            $table->string('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('unauthorized_days')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('days_off_requests');
    }
};
