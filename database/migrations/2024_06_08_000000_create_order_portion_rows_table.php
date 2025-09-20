<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_portion_rows', function (Blueprint $table) {
            $table->id();
            $table->string('row_key')->unique();
            $table->string('label')->nullable();
            $table->decimal('qty', 10, 2)->default(0);
            $table->string('unit', 10)->default('oz');
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('ozs', 12, 2)->default(0);
            $table->decimal('lbs', 12, 4)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_portion_rows');
    }
};
