<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('city_name', 160);
            $table->string('city_key', 180)->unique();
            $table->decimal('tax_rate', 6, 2);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'city_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_tax_rates');
    }
};
