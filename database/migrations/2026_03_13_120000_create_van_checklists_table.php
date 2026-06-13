<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_checklists', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_time');
            $table->string('user');
            $table->string('van_number');
            $table->string('gas_level');
            $table->unsignedInteger('grills')->default(0);
            $table->string('grills_numbers')->nullable();
            $table->unsignedInteger('propane')->default(0);
            $table->unsignedInteger('tables')->default(0);
            $table->unsignedInteger('chairs')->default(0);
            $table->unsignedInteger('chairs_covers')->default(0);
            $table->string('color')->nullable();
            $table->unsignedInteger('dolly')->default(0);
            $table->unsignedInteger('ramps')->default(0);
            $table->unsignedInteger('mats')->default(0);
            $table->text('falla')->nullable();
            $table->string('clean')->nullable();
            $table->string('signature')->nullable();
            $table->text('notes')->nullable();
            $table->string('picture1')->nullable();
            $table->string('picture2')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_checklists');
    }
};
