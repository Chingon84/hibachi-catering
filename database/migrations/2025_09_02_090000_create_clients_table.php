<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();

            $table->string('phone_primary', 30)->nullable();
            $table->string('phone_alt', 30)->nullable();

            $table->string('email_primary')->nullable()->index();
            $table->string('email_alt')->nullable();

            $table->string('address1_street')->nullable();
            $table->string('address1_city')->nullable();
            $table->string('address1_state', 64)->nullable();
            $table->string('address1_zip', 16)->nullable();

            $table->string('address2_street')->nullable();
            $table->string('address2_city')->nullable();
            $table->string('address2_state', 64)->nullable();
            $table->string('address2_zip', 16)->nullable();

            $table->json('social_links')->nullable(); // e.g. {instagram:"...", tiktok:"...", other:"..."}
            $table->string('referral_source')->nullable();
            $table->text('internal_notes')->nullable();

            $table->enum('status', ['active','inactive','blocked'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

