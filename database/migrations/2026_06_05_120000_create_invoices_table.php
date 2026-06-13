<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable()->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_phone', 40)->nullable();
            $table->string('customer_address')->nullable();
            $table->date('event_date')->nullable();
            $table->string('event_time', 20)->nullable();
            $table->unsignedInteger('event_guests')->nullable();
            $table->string('event_type', 80)->nullable();
            $table->string('setup_color', 80)->nullable();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->string('status', 24)->default('draft')->index();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('payment_collection', 32)->default('request_payment');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->boolean('tax_enabled')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->boolean('gratuity_enabled')->default(false);
            $table->decimal('gratuity', 10, 2)->default(0);
            $table->boolean('deposit_enabled')->default(false);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->boolean('service_charge_enabled')->default(false);
            $table->decimal('service_charge_rate', 5, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->boolean('discount_enabled')->default(false);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->text('memo')->nullable();
            $table->text('footer_note')->nullable();
            $table->text('internal_note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
