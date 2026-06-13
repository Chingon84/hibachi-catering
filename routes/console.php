<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Services\NotificationService;
use App\Services\ReservationPaymentSyncService;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:invoice-overdue', function () {
    if (!\Schema::hasTable('invoices') || !\Schema::hasTable('notifications')) {
        $this->warn('Invoices or notifications table not found.');
        return;
    }

    $service = app(NotificationService::class);
    $count = 0;

    Invoice::query()
        ->whereNotNull('due_date')
        ->whereDate('due_date', '<', now()->toDateString())
        ->whereNotIn('status', ['paid', 'void'])
        ->where('balance', '>', 0)
        ->orderBy('id')
        ->chunkById(100, function ($invoices) use ($service, &$count) {
            foreach ($invoices as $invoice) {
                $service->notifyInvoiceOverdue($invoice);
                $count++;
            }
        });

    $this->info("Overdue invoice notification scan completed. Invoices checked: {$count}");
})->purpose('Create internal notifications for overdue invoices');

Schedule::command('notifications:invoice-overdue')->dailyAt('08:00');

Artisan::command('clients:backfill-last-guests', function () {
    $updated = 0;

    Client::query()
        ->whereNotNull('created_from_reservation_id')
        ->orderBy('id')
        ->chunkById(200, function ($clients) use (&$updated) {
            foreach ($clients as $client) {
                $reservationId = (int) ($client->created_from_reservation_id ?? 0);
                if ($reservationId <= 0) {
                    continue;
                }

                $guests = Reservation::query()->where('id', $reservationId)->value('guests');
                if (is_null($guests)) {
                    continue;
                }

                $client->last_guests = (int) $guests;
                $client->save();
                $updated++;
            }
        });

    $this->info("Backfill completed. Updated clients: {$updated}");
})->purpose('Backfill clients.last_guests from linked reservations');

Artisan::command('clients:backfill-stats', function () {
    $updated = 0;

    if (!\Schema::hasTable('client_reservations')) {
        $this->warn('client_reservations table not found.');
        return;
    }

    Client::query()
        ->orderBy('id')
        ->chunkById(200, function ($clients) use (&$updated) {
            foreach ($clients as $client) {
                $reservationIds = \DB::table('client_reservations')
                    ->where('client_id', $client->id)
                    ->pluck('reservation_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                if (empty($reservationIds) && !empty($client->created_from_reservation_id)) {
                    $reservationIds = [(int) $client->created_from_reservation_id];
                }

                if (empty($reservationIds)) {
                    $client->events_count = 0;
                    $client->total_events_count = 0;
                    $client->last_event_at = null;
                    $client->save();
                    $updated++;
                    continue;
                }

                $events = Reservation::query()
                    ->whereIn('id', $reservationIds)
                    ->get(['date', 'time'])
                    ->map(function ($row) {
                        if (empty($row->date)) {
                            return null;
                        }
                        $date = $row->date instanceof Carbon
                            ? $row->date->toDateString()
                            : Carbon::parse((string) $row->date)->toDateString();
                        $time = !empty($row->time) ? (string) $row->time : '00:00:00';
                        return Carbon::parse($date . ' ' . $time);
                    })
                    ->filter()
                    ->sortDesc()
                    ->values();

                $client->events_count = count($reservationIds);
                $client->total_events_count = count($reservationIds);
                $client->last_event_at = $events->first();
                $client->save();
                $updated++;
            }
        });

    $this->info("Backfill completed. Updated clients: {$updated}");
})->purpose('Backfill clients.events_count and clients.last_event_at');

Artisan::command('reservations:backfill-payment-fields {--id=} {--limit=0}', function () {
    if (!\Schema::hasTable('payments')) {
        $this->warn('payments table not found.');
        return;
    }

    $service = app(ReservationPaymentSyncService::class);
    $updated = 0;

    $query = Reservation::query()->orderBy('id');

    if ($id = (int) $this->option('id')) {
        $query->where('id', $id);
    } else {
        $query->where(function ($q) {
            $q->where('deposit_paid', '>', 0)
              ->orWhere('balance', '<', 0)
              ->orWhereExists(function ($p) {
                  $p->selectRaw('1')
                    ->from('payments')
                    ->whereColumn('payments.reservation_id', 'reservations.id')
                    ->where('payments.status', 'succeeded');
              });
        });
    }

    $limit = max(0, (int) $this->option('limit'));
    if ($limit > 0) {
        $query->limit($limit);
    }

    $query->chunkById(200, function ($reservations) use ($service, &$updated) {
        foreach ($reservations as $reservation) {
            $service->recalculate($reservation);
            $updated++;
        }
    });

    $this->info("Backfill completed. Updated reservations: {$updated}");
})->purpose('Recalculate reservation payment fields from succeeded payments');

Artisan::command('migrations:reconcile-baseline {--apply : Insert missing migration rows when schema already reflects them}', function () {
    $schemaHasTables = fn (array $tables) => collect($tables)->every(fn (string $table) => \Schema::hasTable($table));
    $schemaHasColumns = fn (string $table, array $columns) => \Schema::hasTable($table)
        && collect($columns)->every(fn (string $column) => \Schema::hasColumn($table, $column));
    $schemaMissingColumns = fn (string $table, array $columns) => \Schema::hasTable($table)
        && collect($columns)->every(fn (string $column) => !\Schema::hasColumn($table, $column));
    $columnIsNullable = function (string $table, string $column): bool {
        if (!\Schema::hasTable($table) || !\Schema::hasColumn($table, $column)) {
            return false;
        }

        $result = DB::selectOne(
            "SELECT IS_NULLABLE as is_nullable
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND column_name = ?",
            [$table, $column]
        );

        return strtoupper((string) ($result->is_nullable ?? 'NO')) === 'YES';
    };

    $migrationFiles = collect(glob(database_path('migrations/*.php')))
        ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
        ->sort()
        ->values();

    $applied = DB::table('migrations')
        ->orderBy('migration')
        ->pluck('migration')
        ->values();

    $missing = $migrationFiles->diff($applied)->values();

    $checks = [
        '2026_03_10_100000_create_inventory_tables' => fn () => $schemaHasTables(['vans', 'inventory_items', 'inventory_movements', 'van_inventory_items']),
        '2026_03_10_110000_create_van_loadouts_table' => fn () => $schemaHasTables(['van_loadouts']),
        '2026_03_10_120000_add_van_number_to_vans_table' => fn () => $schemaHasColumns('vans', ['van_number']),
        '2026_03_11_120000_create_days_off_requests_table' => fn () => $schemaHasTables(['days_off_requests']),
        '2026_03_12_120000_add_team_members_to_complaints_table' => fn () => $schemaHasColumns('complaints', ['team_members']),
        '2026_03_13_120000_create_van_checklists_table' => fn () => $schemaHasTables(['van_checklists']),
        '2026_03_13_130000_add_workflow_fields_to_van_checklists_table' => fn () => $schemaHasColumns('van_checklists', ['checklist_type', 'trip_status']),
        '2026_03_13_140000_remove_legacy_fields_from_van_checklists_table' => fn () => $schemaMissingColumns('van_checklists', ['falla', 'signature', 'color']),
        '2026_03_19_090000_create_schedule_priority_logs_table' => fn () => $schemaHasTables(['schedule_priority_logs']),
        '2026_03_19_090100_create_schedule_assignments_table' => fn () => $schemaHasTables(['schedule_assignments']),
        '2026_06_04_000000_add_public_invoice_token_to_reservations_table' => fn () => $schemaHasColumns('reservations', ['public_invoice_token']),
        '2026_06_04_010000_extend_schedule_assignments_for_daily_schedule' => fn () => $schemaHasColumns('schedule_assignments', ['chef_1_id', 'chef_2_id', 'chef_3_id', 'chef_4_id', 'assistant_id', 'confirm_by_id', 'van', 'leave_at', 'time_to_drive', 'chef_tip', 'schedule_notes', 'created_by', 'updated_by']) && $columnIsNullable('schedule_assignments', 'user_id'),
        '2026_06_04_020000_add_extra_chef_ids_to_schedule_assignments_table' => fn () => $schemaHasColumns('schedule_assignments', ['extra_chef_ids']),
        '2026_06_05_120000_create_invoices_table' => fn () => $schemaHasTables(['invoices']),
        '2026_06_05_120100_create_invoice_items_table' => fn () => $schemaHasTables(['invoice_items']),
        '2026_06_05_120200_add_adjustments_to_invoices_table' => fn () => $schemaHasColumns('invoices', ['tax_enabled', 'tax_rate', 'gratuity_enabled', 'gratuity', 'deposit_enabled', 'deposit_amount', 'service_charge_enabled', 'service_charge_rate', 'service_charge', 'discount_enabled', 'discount_rate', 'discount']),
        '2026_06_05_120300_add_event_details_to_invoices_table' => fn () => $schemaHasColumns('invoices', ['customer_address', 'event_date', 'event_time', 'event_guests', 'event_type', 'setup_color']),
        '2026_06_06_150000_create_staff_event_confirmations_table' => fn () => $schemaHasTables(['staff_event_confirmations']),
    ];

    $rows = $missing->map(function (string $migration) use ($checks) {
        if (!array_key_exists($migration, $checks)) {
            return [
                'migration' => $migration,
                'status' => 'unknown',
                'action' => 'review manually',
            ];
        }

        $schemaPresent = (bool) $checks[$migration]();

        return [
            'migration' => $migration,
            'status' => $schemaPresent ? 'schema_present' : 'pending_real',
            'action' => $schemaPresent ? 'mark as applied' : 'run migration',
        ];
    });

    if ($rows->isEmpty()) {
        $this->info('No migration drift detected. migrations table is aligned with files.');
        return;
    }

    $this->table(['Migration', 'Status', 'Suggested action'], $rows->all());

    $toApply = $rows->where('status', 'schema_present')->pluck('migration')->values();
    $pendingReal = $rows->where('status', 'pending_real')->pluck('migration')->values();

    $this->newLine();
    $this->line('Summary:');
    $this->line('  Missing migration rows: ' . $rows->count());
    $this->line('  Safe to baseline: ' . $toApply->count());
    $this->line('  Still genuinely pending: ' . $pendingReal->count());

    if (!$this->option('apply')) {
        $this->comment('Dry run only. Re-run with --apply to insert safe baseline rows.');
        return;
    }

    if ($toApply->isEmpty()) {
        $this->warn('Nothing to baseline.');
        return;
    }

    $batch = ((int) DB::table('migrations')->max('batch')) + 1;

    DB::transaction(function () use ($toApply, $batch) {
        foreach ($toApply as $migration) {
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $batch,
            ]);
        }
    });

    $this->info('Inserted ' . $toApply->count() . ' baseline migration row(s) in batch ' . $batch . '.');

    if ($pendingReal->isNotEmpty()) {
        $this->warn('Still pending for real migration:');
        foreach ($pendingReal as $migration) {
            $this->line('  - ' . $migration);
        }
    }
})->purpose('Audit migration drift and optionally baseline migrations already reflected in the schema');

Artisan::command('app:production-readiness {--strict : Return a failing exit code when warnings are found}', function () {
    $env = (string) app()->environment();
    $appUrl = (string) config('app.url');
    $appKey = (string) config('app.key');
    $mailMailer = (string) config('mail.default');
    $mailFrom = (string) data_get(config('mail.from'), 'address', '');
    $adminAddress = (string) config('mail.admin_address', '');
    $stripeKey = (string) config('services.stripe.key', '');
    $stripeSecret = (string) config('services.stripe.secret', '');
    $stripeDebug = filter_var((string) config('services.stripe.debug', false), FILTER_VALIDATE_BOOL);
    $logLevel = strtolower((string) env('LOG_LEVEL', 'debug'));
    $dbConnection = (string) config('database.default');
    $appHost = strtolower((string) parse_url($appUrl, PHP_URL_HOST));
    $appScheme = strtolower((string) parse_url($appUrl, PHP_URL_SCHEME));

    $checks = collect([
        [
            'label' => 'APP_ENV',
            'status' => $env === 'production' ? 'ok' : 'warn',
            'details' => $env === 'production' ? 'production' : "current: {$env}",
        ],
        [
            'label' => 'APP_DEBUG',
            'status' => config('app.debug') ? 'warn' : 'ok',
            'details' => config('app.debug') ? 'enabled' : 'disabled',
        ],
        [
            'label' => 'APP_KEY',
            'status' => filled($appKey) ? 'ok' : 'fail',
            'details' => filled($appKey) ? 'set' : 'missing',
        ],
        [
            'label' => 'APP_URL',
            'status' => blank($appUrl)
                ? 'fail'
                : (($appScheme === 'https' && !in_array($appHost, ['localhost', '127.0.0.1'], true)) ? 'ok' : 'warn'),
            'details' => blank($appUrl) ? 'missing' : $appUrl,
        ],
        [
            'label' => 'LOG_LEVEL',
            'status' => in_array($logLevel, ['debug', 'trace'], true) ? 'warn' : 'ok',
            'details' => $logLevel,
        ],
        [
            'label' => 'DB_CONNECTION',
            'status' => $env === 'production' && $dbConnection === 'sqlite' ? 'warn' : 'ok',
            'details' => $dbConnection,
        ],
        [
            'label' => 'MAIL_MAILER',
            'status' => in_array($mailMailer, ['log', 'array'], true) ? 'warn' : 'ok',
            'details' => $mailMailer,
        ],
        [
            'label' => 'MAIL_FROM_ADDRESS',
            'status' => filter_var($mailFrom, FILTER_VALIDATE_EMAIL) && !str_ends_with(strtolower($mailFrom), '@example.com') ? 'ok' : 'warn',
            'details' => blank($mailFrom) ? 'missing' : $mailFrom,
        ],
        [
            'label' => 'ADMIN_NOTIFICATION_EMAIL',
            'status' => filter_var($adminAddress, FILTER_VALIDATE_EMAIL) && !str_ends_with(strtolower($adminAddress), '@example.com') ? 'ok' : 'warn',
            'details' => blank($adminAddress) ? 'missing' : $adminAddress,
        ],
        [
            'label' => 'STRIPE_KEY',
            'status' => blank($stripeKey)
                ? 'fail'
                : (($env === 'production' && str_starts_with($stripeKey, 'pk_test_')) ? 'warn' : 'ok'),
            'details' => blank($stripeKey) ? 'missing' : (str_starts_with($stripeKey, 'pk_live_') ? 'live key detected' : 'key present'),
        ],
        [
            'label' => 'STRIPE_SECRET',
            'status' => blank($stripeSecret)
                ? 'fail'
                : (($env === 'production' && str_starts_with($stripeSecret, 'sk_test_')) ? 'warn' : 'ok'),
            'details' => blank($stripeSecret) ? 'missing' : (str_starts_with($stripeSecret, 'sk_live_') ? 'live secret detected' : 'secret present'),
        ],
        [
            'label' => 'STRIPE_PAY_DEBUG',
            'status' => $stripeDebug ? 'warn' : 'ok',
            'details' => $stripeDebug ? 'enabled' : 'disabled',
        ],
    ]);

    $this->table(
        ['Check', 'Status', 'Details'],
        $checks->map(fn (array $row) => [
            $row['label'],
            strtoupper($row['status']),
            $row['details'],
        ])->all()
    );

    $failCount = $checks->where('status', 'fail')->count();
    $warnCount = $checks->where('status', 'warn')->count();

    $this->newLine();
    $this->line("Summary: {$failCount} fail, {$warnCount} warn.");

    if ($failCount > 0) {
        $this->error('Production readiness audit found blocking issues.');
        return 1;
    }

    if ($warnCount > 0) {
        $this->warn('Production readiness audit found non-blocking warnings.');
        return $this->option('strict') ? 1 : 0;
    }

    $this->info('Production readiness audit passed.');
    return 0;
})->purpose('Audit production-critical app, mail, and Stripe configuration without exposing secrets');
