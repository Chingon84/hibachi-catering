<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('timeslots', function (Blueprint $table) {
        $table->id();
        $table->date('date');
        $table->time('time');
        $table->integer('capacity')->default(999);
        $table->boolean('is_open')->default(true);
        $table->string('notes')->nullable();
        $table->timestamps();
        $table->unique(['date','time']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeslots');
    }
};
