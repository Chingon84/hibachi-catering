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
    Schema::create('reservation_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
        $table->unsignedBigInteger('menu_id')->nullable();
        $table->string('name_snapshot');
        $table->decimal('unit_price_snapshot',10,2);
        $table->integer('qty');
        $table->decimal('line_total',10,2);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
    }
};
