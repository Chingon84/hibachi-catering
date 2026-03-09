<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('client_reservations')) {
            return;
        }

        Schema::table('client_reservations', function (Blueprint $table) {
            try {
                $table->dropUnique('client_reservations_reservation_id_unique');
            } catch (\Throwable $e) {
                // ignore if index does not exist
            }

            try {
                $table->unique(['client_id', 'reservation_id'], 'client_reservations_client_reservation_unique');
            } catch (\Throwable $e) {
                // ignore if index already exists
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('client_reservations')) {
            return;
        }

        Schema::table('client_reservations', function (Blueprint $table) {
            try {
                $table->dropUnique('client_reservations_client_reservation_unique');
            } catch (\Throwable $e) {
                // ignore if index does not exist
            }

            try {
                $table->unique('reservation_id', 'client_reservations_reservation_id_unique');
            } catch (\Throwable $e) {
                // ignore if index already exists
            }
        });
    }
};

