<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientReservation;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ClientSyncService
{
    public function syncFromReservationId(int $reservationId): ?Client
    {
        $reservation = Reservation::find($reservationId);
        if (!$reservation) {
            $this->debug('client_sync.reservation_missing', [
                'reservation_id' => $reservationId,
            ]);
            return null;
        }

        return $this->upsertClientFromReservation($reservation);
    }

    public function addClientFromReservationIfNotExists(Reservation $reservation): array
    {
        return DB::transaction(function () use ($reservation) {
            $reservation->refresh();

            $existing = $this->findExistingClientByReservation($reservation, true);
            if ($existing) {
                $this->debug('client_sync.manual_add', [
                    'reservation_id' => $reservation->id,
                    'client_id' => $existing->id,
                    'trigger_reason' => 'manual_add',
                    'result' => 'exists',
                    'events_count' => (int) ($existing->events_count ?? 0),
                    'last_event_at' => optional($existing->last_event_at)->toDateTimeString(),
                ]);
                return ['status' => 'exists', 'client' => $existing];
            }

            [$firstName, $lastName] = $this->splitName((string) ($reservation->customer_name ?? ''));
            $email = $this->normalizeEmail($reservation->email);
            $phone = $this->normalizePhoneDigits($reservation->phone);
            $eventAt = $this->reservationEventAt($reservation);

            $client = new Client();
            $client->first_name = $firstName;
            $client->last_name = $lastName;
            $client->company = $reservation->company ?: null;
            $client->email_primary = $email;
            $client->phone_primary = $phone;
            $client->address1_street = $reservation->address ?: null;
            $client->address1_city = $reservation->city ?: null;
            $client->address1_state = null;
            $client->address1_zip = $reservation->zip_code ?: null;
            $client->internal_notes = $reservation->notes ?: null;
            $client->last_guests = !is_null($reservation->guests) ? (int) $reservation->guests : null;
            $client->source = 'reservation_manual_add';
            $client->created_from_reservation_id = $reservation->id;
            $client->last_event_at = $eventAt;
            $client->events_count = 1;

            try {
                $client->save();
            } catch (QueryException $e) {
                if ($this->isUniqueConstraintViolation($e)) {
                    $existing = $this->findExistingClientByReservation($reservation, true);
                    if ($existing) {
                        return ['status' => 'exists', 'client' => $existing];
                    }
                }
                throw $e;
            }

            $this->attachReservationToClient($client, $reservation);

            $fresh = $client->fresh();
            $this->debug('client_sync.manual_add', [
                'reservation_id' => $reservation->id,
                'client_id' => $fresh->id,
                'trigger_reason' => 'manual_add',
                'result' => 'created',
                'events_count' => (int) ($fresh->events_count ?? 0),
                'last_event_at' => optional($fresh->last_event_at)->toDateTimeString(),
            ]);

            return ['status' => 'created', 'client' => $fresh];
        }, 3);
    }

    public function hasExistingClientLink(Reservation $reservation): bool
    {
        $reservationId = (int) $reservation->id;
        $email = $this->normalizeEmail($reservation->email);
        $phoneDigits = $this->normalizePhoneDigits($reservation->phone);

        if (Schema::hasTable('client_reservations')) {
            if (ClientReservation::query()->where('reservation_id', $reservationId)->exists()) {
                return true;
            }
        }

        $query = Client::query()->where(function ($q) use ($reservationId, $email, $phoneDigits) {
            $q->where('created_from_reservation_id', $reservationId);

            if (!empty($email)) {
                $q->orWhereRaw('LOWER(TRIM(email_primary)) = ?', [$email])
                    ->orWhereRaw('LOWER(TRIM(email_alt)) = ?', [$email]);
            }

            if (!empty($phoneDigits)) {
                $exprPrimary = $this->phoneDigitsSql('phone_primary');
                $exprAlt = $this->phoneDigitsSql('phone_alt');
                $q->orWhereRaw("{$exprPrimary} = ?", [$phoneDigits])
                    ->orWhereRaw("{$exprAlt} = ?", [$phoneDigits]);
            }
        });

        return $query->exists();
    }

    public function upsertClientFromReservation(Reservation $reservation): Client
    {
        return DB::transaction(function () use ($reservation) {
            $reservation->refresh();

            $email = $this->normalizeEmail($reservation->email);
            $phone = $this->normalizePhoneDigits($reservation->phone);
            [$firstName, $lastName] = $this->splitName((string) ($reservation->customer_name ?? ''));

            $client = $this->findClient($reservation, $email, $phone);
            if (!$client) {
                $client = new Client();
            }
            $isNewClient = !$client->exists;

            $client->first_name = $client->first_name ?: $firstName;
            $client->last_name = $client->last_name ?: $lastName;
            $client->company = $client->company ?: ($reservation->company ?: null);
            $client->email_primary = $client->email_primary ?: $email;
            $client->phone_primary = $client->phone_primary ?: $phone;
            $client->address1_street = $client->address1_street ?: ($reservation->address ?: null);
            $client->address1_city = $client->address1_city ?: ($reservation->city ?: null);
            $client->address1_state = $client->address1_state ?: null;
            $client->address1_zip = $client->address1_zip ?: ($reservation->zip_code ?: null);
            if (!is_null($reservation->guests)) {
                $client->last_guests = (int) $reservation->guests;
            }

            if (!empty($reservation->notes)) {
                $existingNotes = trim((string) ($client->internal_notes ?? ''));
                if ($existingNotes === '') {
                    $client->internal_notes = $reservation->notes;
                }
            }

            $client->source = 'reservations';
            if (empty($client->created_from_reservation_id)) {
                $client->created_from_reservation_id = $reservation->id;
            }

            $client->save();

            $this->attachReservationToClient($client, $reservation);

            $this->debug('client_sync.upsert', [
                'reservation_id' => $reservation->id,
                'client_id' => $client->id,
                'trigger_reason' => 'observer_or_explicit',
                'result' => $isNewClient ? 'created' : 'updated',
                'email' => $email,
                'phone' => $phone,
                'events_count' => (int) ($client->events_count ?? 0),
                'last_event_at' => optional($client->last_event_at)->toDateTimeString(),
            ]);

            return $client->fresh();
        });
    }

    private function findClient(Reservation $reservation, ?string $email, ?string $phone): ?Client
    {
        if (!empty($email)) {
            return Client::query()
                ->whereRaw('LOWER(TRIM(email_primary)) = ?', [$email])
                ->orWhereRaw('LOWER(TRIM(email_alt)) = ?', [$email])
                ->first();
        }

        if (!empty($phone)) {
            $exprPrimary = $this->phoneDigitsSql('phone_primary');
            $exprAlt = $this->phoneDigitsSql('phone_alt');
            return Client::query()
                ->whereRaw("{$exprPrimary} = ?", [$phone])
                ->orWhereRaw("{$exprAlt} = ?", [$phone])
                ->first();
        }

        return Client::query()
            ->where('created_from_reservation_id', $reservation->id)
            ->first();
    }

    private function findExistingClientByReservation(Reservation $reservation, bool $lock = false): ?Client
    {
        $email = $this->normalizeEmail($reservation->email) ?? '';
        if ($email !== '') {
            $query = Client::query()
                ->whereRaw('LOWER(TRIM(email_primary)) = ?', [$email])
                ->orWhereRaw('LOWER(TRIM(email_alt)) = ?', [$email]);
            if ($lock) {
                $query->lockForUpdate();
            }
            $client = $query->first();
            if ($client) {
                return $client;
            }
        }

        $phoneDigits = $this->normalizePhoneDigits($reservation->phone);
        if ($email === '' && !empty($phoneDigits)) {
            $exprPrimary = $this->phoneDigitsSql('phone_primary');
            $exprAlt = $this->phoneDigitsSql('phone_alt');
            $query = Client::query()
                ->whereRaw("{$exprPrimary} = ?", [$phoneDigits])
                ->orWhereRaw("{$exprAlt} = ?", [$phoneDigits]);
            if ($lock) {
                $query->lockForUpdate();
            }
            $client = $query->first();
            if ($client) {
                return $client;
            }
        }

        $name = strtolower($this->normalizeName((string) ($reservation->customer_name ?? '')));
        $zip = strtolower(trim((string) ($reservation->zip_code ?? '')));
        $city = strtolower(trim((string) ($reservation->city ?? '')));

        if ($name !== '' && $zip !== '' && $city !== '') {
            $query = Client::query()
                ->where(function ($q) use ($zip) {
                    $q->whereRaw('LOWER(TRIM(address1_zip)) = ?', [$zip])
                        ->orWhereRaw('LOWER(TRIM(address2_zip)) = ?', [$zip]);
                })
                ->where(function ($q) use ($city) {
                    $q->whereRaw('LOWER(TRIM(address1_city)) = ?', [$city])
                        ->orWhereRaw('LOWER(TRIM(address2_city)) = ?', [$city]);
                });
            if ($lock) {
                $query->lockForUpdate();
            }
            $candidates = $query->get();
            foreach ($candidates as $candidate) {
                if (strtolower($this->normalizeName((string) $candidate->full_name)) === $name) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    public function attachReservationToClient(Client $client, Reservation $reservation): void
    {
        if (!Schema::hasTable('client_reservations')) {
            return;
        }

        $previousClientIds = ClientReservation::query()
            ->where('reservation_id', $reservation->id)
            ->where('client_id', '!=', $client->id)
            ->pluck('client_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (!empty($previousClientIds)) {
            ClientReservation::query()
                ->where('reservation_id', $reservation->id)
                ->whereIn('client_id', $previousClientIds)
                ->delete();
        }

        $paid = $this->calculatePaid($reservation);
        $total = (float) ($reservation->total ?? 0);
        $balance = max(0, $total - $paid);

        ClientReservation::query()->updateOrCreate(
            [
                'client_id' => $client->id,
                'reservation_id' => $reservation->id,
            ],
            [
                'event_date' => optional($reservation->date)->toDateString(),
                'total' => $total,
                'paid' => $paid,
                'balance' => $balance,
                'status' => $reservation->status,
            ]
        );

        $this->refreshClientStats($client);
        foreach ($previousClientIds as $previousClientId) {
            $previous = Client::query()->find($previousClientId);
            if ($previous) {
                $this->refreshClientStats($previous);
            }
        }
        $client->refresh();
        $this->debug('client_sync.attach', [
            'reservation_id' => $reservation->id,
            'client_id' => $client->id,
            'trigger_reason' => 'attach',
            'result' => 'attached',
            'events_count' => (int) ($client->events_count ?? 0),
            'last_event_at' => optional($client->last_event_at)->toDateTimeString(),
        ]);
    }

    public function refreshClientStats(Client $client): void
    {
        if (!Schema::hasTable('client_reservations')) {
            return;
        }

        $rows = ClientReservation::query()
            ->where('client_id', $client->id)
            ->select(['reservation_id', 'event_date', 'total'])
            ->get();

        $reservationIds = $rows->pluck('reservation_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $count = count($reservationIds);
        $lifetimeValue = (float) $rows->sum('total');
        $lastEventDate = $rows->max('event_date');
        $lastEventTotal = null;
        $lastEventAt = null;

        if (!empty($lastEventDate)) {
            $lastRow = $rows->where('event_date', $lastEventDate)->last();
            if ($lastRow) {
                $lastEventTotal = (float) ($lastRow->total ?? 0);
            }
        }

        if (!empty($reservationIds)) {
            $events = Reservation::query()
                ->whereIn('id', $reservationIds)
                ->get(['date', 'time']);

            foreach ($events as $event) {
                if (empty($event->date)) {
                    continue;
                }

                $datePart = $event->date instanceof Carbon
                    ? $event->date->toDateString()
                    : Carbon::parse((string) $event->date)->toDateString();
                $timePart = !empty($event->time) ? (string) $event->time : '00:00:00';
                $candidate = Carbon::parse($datePart . ' ' . $timePart);

                if (!$lastEventAt || $candidate->gt($lastEventAt)) {
                    $lastEventAt = $candidate;
                }
            }
        }

        $client->events_count = $count;
        $client->total_events_count = $count;
        $client->lifetime_value = $lifetimeValue;
        $client->last_event_date = $lastEventDate;
        $client->last_event_at = $lastEventAt;
        $client->last_event_total = $lastEventTotal;
        $client->save();

        $this->debug('client_sync.refresh_stats', [
            'client_id' => $client->id,
            'trigger_reason' => 'refresh_stats',
            'result' => 'updated',
            'events_count' => (int) $client->events_count,
            'last_event_at' => optional($client->last_event_at)->toDateTimeString(),
        ]);
    }

    private function reservationEventAt(Reservation $reservation): ?Carbon
    {
        if (empty($reservation->date)) {
            return null;
        }

        $datePart = $reservation->date instanceof Carbon
            ? $reservation->date->toDateString()
            : Carbon::parse((string) $reservation->date)->toDateString();
        $timePart = !empty($reservation->time) ? (string) $reservation->time : '00:00:00';

        return Carbon::parse($datePart . ' ' . $timePart);
    }

    private function calculatePaid(Reservation $reservation): float
    {
        $paid = max(
            (float) ($reservation->deposit_paid ?? 0),
            (float) ($reservation->paid ?? 0)
        );

        foreach ((array) ($reservation->manual_payments ?? []) as $payment) {
            if (strtolower((string) ($payment['status'] ?? '')) === 'succeeded') {
                $paid += (float) ($payment['amount'] ?? 0);
            }
        }

        return round($paid, 2);
    }

    private function splitName(string $name): array
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '') {
            return [null, null];
        }

        $parts = explode(' ', $name);
        $first = array_shift($parts);
        $last = !empty($parts) ? implode(' ', $parts) : null;

        return [$first ?: null, $last ?: null];
    }

    private function normalizeEmail(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));
        return $value === '' ? null : $value;
    }

    private function normalizePhoneDigits(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        return $digits === '' ? null : $digits;
    }

    private function normalizeName(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function phoneDigitsSql(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE({$column},''),'-',''),'(',''),')',''),' ',''),'+',''),'.',''),'/','')";
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $msg = strtolower($e->getMessage());
        return str_contains($msg, 'unique') || str_contains($msg, 'duplicate');
    }

    private function debug(string $event, array $context = []): void
    {
        Log::info($event, $context);
    }
}
