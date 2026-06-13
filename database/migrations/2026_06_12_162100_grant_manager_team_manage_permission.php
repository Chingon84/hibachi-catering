<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        $managerPermissionCount = DB::table('role_permissions')
            ->where('role', 'manager')
            ->count();

        if ($managerPermissionCount === 0) {
            return;
        }

        DB::table('role_permissions')->insertOrIgnore([
            'role' => 'manager',
            'permission' => 'team.manage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        DB::table('role_permissions')
            ->where('role', 'manager')
            ->where('permission', 'team.manage')
            ->delete();
    }
};
