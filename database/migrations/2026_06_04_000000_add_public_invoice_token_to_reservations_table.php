<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'public_invoice_token')) {
                $table->string('public_invoice_token', 64)->nullable()->unique()->after('invoice_status');
            }
        });

        if (!Schema::hasColumn('reservations', 'public_invoice_token')) {
            return;
        }

        DB::table('reservations')
            ->whereNull('public_invoice_token')
            ->orderBy('id')
            ->select('id')
            ->chunkById(200, function ($reservations) {
                foreach ($reservations as $reservation) {
                    DB::table('reservations')
                        ->where('id', $reservation->id)
                        ->update(['public_invoice_token' => $this->generateToken()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'public_invoice_token')) {
                $table->dropUnique(['public_invoice_token']);
                $table->dropColumn('public_invoice_token');
            }
        });
    }

    private function generateToken(): string
    {
        do {
            $token = Str::random(48);
        } while (DB::table('reservations')->where('public_invoice_token', $token)->exists());

        return $token;
    }
};
