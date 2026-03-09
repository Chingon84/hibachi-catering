<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Client;
use App\Models\Reservation;
use App\Services\ReservationPaymentSyncService;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
