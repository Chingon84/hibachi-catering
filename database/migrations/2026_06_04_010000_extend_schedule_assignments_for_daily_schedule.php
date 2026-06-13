<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedule_assignments')) {
            return;
        }

        $this->makeLegacyUserAssignmentNullable();

        Schema::table('schedule_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_assignments', 'chef_1_id')) {
                $table->foreignId('chef_1_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('schedule_assignments', 'chef_2_id')) {
                $table->foreignId('chef_2_id')->nullable()->after('chef_1_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('schedule_assignments', 'chef_3_id')) {
                $table->foreignId('chef_3_id')->nullable()->after('chef_2_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('schedule_assignments', 'chef_4_id')) {
                $table->foreignId('chef_4_id')->nullable()->after('chef_3_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('schedule_assignments', 'assistant_id')) {
                $table->foreignId('assistant_id')->nullable()->after('chef_4_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('schedule_assignments', 'confirm_by_id')) {
                $table->foreignId('confirm_by_id')->nullable()->after('assistant_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('schedule_assignments', 'van')) {
                $table->string('van', 80)->nullable()->after('confirm_by_id');
            }
            if (!Schema::hasColumn('schedule_assignments', 'leave_at')) {
                $table->time('leave_at')->nullable()->after('van');
            }
            if (!Schema::hasColumn('schedule_assignments', 'time_to_drive')) {
                $table->string('time_to_drive', 80)->nullable()->after('leave_at');
            }
            if (!Schema::hasColumn('schedule_assignments', 'chef_tip')) {
                $table->decimal('chef_tip', 10, 2)->nullable()->after('time_to_drive');
            }
            if (!Schema::hasColumn('schedule_assignments', 'schedule_notes')) {
                $table->text('schedule_notes')->nullable()->after('chef_tip');
            }
            if (!Schema::hasColumn('schedule_assignments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('schedule_notes')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('schedule_assignments', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('schedule_assignments')) {
            return;
        }

        Schema::table('schedule_assignments', function (Blueprint $table) {
            foreach (['chef_1_id', 'chef_2_id', 'chef_3_id', 'chef_4_id', 'assistant_id', 'confirm_by_id', 'created_by', 'updated_by'] as $column) {
                if (Schema::hasColumn('schedule_assignments', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['van', 'leave_at', 'time_to_drive', 'chef_tip', 'schedule_notes'] as $column) {
                if (Schema::hasColumn('schedule_assignments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function makeLegacyUserAssignmentNullable(): void
    {
        if (!Schema::hasColumn('schedule_assignments', 'user_id')) {
            return;
        }

        try {
            Schema::table('schedule_assignments', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });

            return;
        } catch (Throwable $e) {
            // Fall through to driver-specific SQL for environments where column change support is limited.
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE schedule_assignments MODIFY user_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE schedule_assignments ALTER COLUMN user_id DROP NOT NULL');
        } elseif ($driver === 'sqlsrv') {
            DB::statement('ALTER TABLE schedule_assignments ALTER COLUMN user_id BIGINT NULL');
        }
    }
};
