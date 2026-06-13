<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'employee_number')) {
                $table->string('employee_number', 40)->nullable()->after('phone');
            }

            if (!Schema::hasColumn('users', 'employee_type')) {
                $table->string('employee_type', 40)->nullable()->after('employee_number');
            }

            if (!Schema::hasColumn('users', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('employee_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['hire_date', 'employee_type', 'employee_number'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
