<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedule_assignments') || Schema::hasColumn('schedule_assignments', 'extra_chef_ids')) {
            return;
        }

        Schema::table('schedule_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_assignments', 'chef_4_id')) {
                $table->json('extra_chef_ids')->nullable()->after('chef_4_id');
            } else {
                $table->json('extra_chef_ids')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('schedule_assignments') || !Schema::hasColumn('schedule_assignments', 'extra_chef_ids')) {
            return;
        }

        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropColumn('extra_chef_ids');
        });
    }
};
