<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE clients MODIFY status VARCHAR(20) NOT NULL DEFAULT 'regular'");
            DB::statement("UPDATE clients SET status='regular' WHERE status IS NULL OR status='' OR status='active'");
        } catch (\Throwable $e) {
            // silently ignore if the DBMS does not support this exact statement
        }
    }

    public function down(): void
    {
        // No rollback provided (enum -> string irreversible without DBAL)
    }
};

