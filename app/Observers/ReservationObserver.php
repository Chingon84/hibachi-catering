<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Services\ClientSyncService;
use Illuminate\Support\Facades\Log;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        if (!$this->shouldSyncOnCreate($reservation)) {
            $this->debug('reservation.created.skip', $reservation, [
                'reason' => 'not_sync_eligible',
            ]);
            return;
        }

        $this->debug('reservation.created.sync', $reservation, [
            'reason' => 'created_sync_eligible',
        ]);
        app(ClientSyncService::class)->upsertClientFromReservation($reservation);
    }

    public function updated(Reservation $reservation): void
    {
        $reasons = $this->updateTriggerReasons($reservation);
        if (empty($reasons)) {
            $this->debug('reservation.updated.skip', $reservation, [
                'reason' => 'no_relevant_changes',
            ]);
            return;
        }

        $this->debug('reservation.updated.sync', $reservation, [
            'reason' => 'updated_sync_eligible',
            'trigger_reason' => implode(',', $reasons),
            'changed' => array_keys($reservation->getChanges()),
            'old_status' => $reservation->getOriginal('status'),
            'new_status' => $reservation->status,
        ]);
        app(ClientSyncService::class)->upsertClientFromReservation($reservation);
    }

    private function shouldSyncOnCreate(Reservation $reservation): bool
    {
        return $this->isSyncEligible($reservation, false);
    }

    private function shouldSyncOnUpdate(Reservation $reservation): bool
    {
        return !empty($this->updateTriggerReasons($reservation));
    }

    private function updateTriggerReasons(Reservation $reservation): array
    {
        $reasons = [];

        $statusChanged = $reservation->wasChanged('status')
            && strtolower((string) $reservation->status) === 'confirmed'
            && strtolower((string) $reservation->getOriginal('status')) !== 'confirmed';
        if ($statusChanged) {
            $reasons[] = 'status_confirmed';
        }

        $depositPaidChangedToPositive = $reservation->wasChanged('deposit_paid')
            && (float) ($reservation->deposit_paid ?? 0) > 0
            && (float) ($reservation->getOriginal('deposit_paid') ?? 0) <= 0;
        if ($depositPaidChangedToPositive) {
            $reasons[] = 'deposit_paid';
        }

        $paidChangedToPositive = $reservation->wasChanged('paid')
            && (float) ($reservation->paid ?? 0) > 0
            && (float) ($reservation->getOriginal('paid') ?? 0) <= 0;
        if ($paidChangedToPositive) {
            $reasons[] = 'paid';
        }

        $invoicePaidChanged = $reservation->wasChanged('invoice_status')
            && strtolower((string) $reservation->invoice_status) === 'paid'
            && strtolower((string) $reservation->getOriginal('invoice_status')) !== 'paid';
        if ($invoicePaidChanged) {
            $reasons[] = 'invoice_paid';
        }

        $invStatusPaidChanged = $reservation->wasChanged('inv_status')
            && strtolower((string) $reservation->inv_status) === 'paid'
            && strtolower((string) $reservation->getOriginal('inv_status')) !== 'paid';
        if ($invStatusPaidChanged) {
            $reasons[] = 'inv_status_paid';
        }

        $manualPaymentsChangedToPositive = $reservation->wasChanged('manual_payments')
            && $this->totalPaid($reservation, false) > 0
            && $this->totalPaid($reservation, true) <= 0;
        if ($manualPaymentsChangedToPositive) {
            $reasons[] = 'manual_paid';
        }

        $eventTimeChangedWhileEligible = (
            $reservation->wasChanged('date') || $reservation->wasChanged('time')
        ) && $this->isSyncEligible($reservation, false);
        if ($eventTimeChangedWhileEligible) {
            $reasons[] = 'event_datetime';
        }

        return $reasons;
    }

    private function isSyncEligible(Reservation $reservation, bool $useOriginal): bool
    {
        $status = strtolower((string) ($useOriginal ? $reservation->getOriginal('status') : $reservation->status));
        $invoiceStatus = strtolower((string) ($useOriginal ? $reservation->getOriginal('invoice_status') : $reservation->invoice_status));
        $invStatus = strtolower((string) ($useOriginal ? $reservation->getOriginal('inv_status') : $reservation->inv_status));
        $paid = $this->totalPaid($reservation, $useOriginal);

        return $status === 'confirmed' || $invoiceStatus === 'paid' || $invStatus === 'paid' || $paid > 0;
    }

    private function totalPaid(Reservation $reservation, bool $useOriginal): float
    {
        $deposit = (float) ($useOriginal ? ($reservation->getOriginal('deposit_paid') ?? 0) : ($reservation->deposit_paid ?? 0));
        $paidField = (float) ($useOriginal ? ($reservation->getOriginal('paid') ?? 0) : ($reservation->paid ?? 0));
        $manualRaw = $useOriginal ? $reservation->getOriginal('manual_payments') : $reservation->manual_payments;
        if (is_string($manualRaw)) {
            $decoded = json_decode($manualRaw, true);
            $manual = is_array($decoded) ? $decoded : [];
        } else {
            $manual = (array) $manualRaw;
        }

        $manualPaid = 0.0;
        foreach ($manual as $row) {
            if (strtolower((string) ($row['status'] ?? '')) === 'succeeded') {
                $manualPaid += (float) ($row['amount'] ?? 0);
            }
        }

        return round(max($deposit, $paidField) + $manualPaid, 2);
    }

    private function debug(string $event, Reservation $reservation, array $extra = []): void
    {
        Log::info($event, array_merge([
            'reservation_id' => $reservation->id,
            'status' => $reservation->status,
            'invoice_status' => $reservation->invoice_status,
            'inv_status' => $reservation->inv_status ?? null,
            'deposit_paid' => (float) ($reservation->deposit_paid ?? 0),
            'paid' => (float) ($reservation->paid ?? 0),
            'email' => $reservation->email,
            'phone' => $reservation->phone,
        ], $extra));
    }
}
