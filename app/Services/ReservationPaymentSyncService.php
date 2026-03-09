<?php

namespace App\Services;

use App\Models\Reservation;
use App\Support\ReservationTotals;

class ReservationPaymentSyncService
{
    public function recalculate(Reservation $reservation): Reservation
    {
        $reservation->loadMissing('payments');

        [$stripeDeposit, $stripeExtra] = ReservationTotals::stripeBreakdown($reservation);
        $manualPaid = ReservationTotals::manualPaid($reservation);

        $total = (float) ($reservation->total ?? 0);
        $depositCap = (float) ($reservation->deposit_due ?? 0);
        if ($depositCap <= 0) {
            $depositCap = $total;
        }
        $depositCap = max(0, min($total, $depositCap));

        $depositPaid = round(max(0, min($depositCap, (float) $stripeDeposit)), 2);
        $depositOverflow = max(0, round((float) $stripeDeposit - $depositPaid, 2));
        $amountPaidTotal = round(max(0, $depositPaid + $depositOverflow + max(0, (float) $stripeExtra) + $manualPaid), 2);
        $balance = max(0, round($total - $amountPaidTotal, 2));

        $reservation->deposit_paid = $depositPaid;
        $reservation->amount_paid_total = $amountPaidTotal;
        $reservation->balance = $balance;

        if ($balance <= 0.0) {
            $reservation->invoice_status = 'paid';
        }

        $reservation->save();

        return $reservation->fresh();
    }
}
