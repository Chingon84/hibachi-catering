<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('position')->nullable()->after('name');
            $table->string('role')->default('staff')->after('position');
            $table->boolean('can_access_admin')->default(false)->after('role');
            $table->boolean('is_active')->default(true)->after('can_access_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username','position','role','can_access_admin','is_active']);
        });
    }
};

