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
    Schema::create('reservations', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique()->nullable();
        $table->enum('status',['draft','pending_payment','confirmed','canceled'])->default('draft');
        $table->integer('guests');
        $table->date('date');
        $table->time('time');
        $table->string('customer_name')->nullable();
        $table->string('phone',40)->nullable();
        $table->string('email')->nullable();
        $table->string('address')->nullable();
        $table->text('notes')->nullable();
        $table->decimal('subtotal',10,2)->default(0);
        $table->decimal('tax',10,2)->default(0);
        $table->decimal('travel_fee',10,2)->default(0);
        $table->decimal('discount',10,2)->default(0);
        $table->decimal('total',10,2)->default(0);
        $table->decimal('deposit_due',10,2)->default(0);
        $table->decimal('deposit_paid',10,2)->default(0);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
