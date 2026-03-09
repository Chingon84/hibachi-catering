<?php

namespace App\Support;

use App\Models\Reservation;
use Illuminate\Support\Facades\Schema;

/**
 * Utility helpers to derive consistent totals/balance figures
 * across invoices, emails, PDFs, and admin views.
 */
class ReservationTotals
{
    private const DEFAULT_GRATUITY = 0.18;
    private const DEFAULT_TAX      = 0.1025;

    /**
     * Build a normalized list of adjustments (label + amount).
     */
    public static function adjustments(?Reservation $reservation, array $fallback = []): array
    {
        $raw = [];
        if ($reservation && is_array($reservation->invoice_adjustments ?? null)) {
            $raw = $reservation->invoice_adjustments;
        } elseif (is_array(data_get($fallback, 'adjustments'))) {
            $raw = (array) data_get($fallback, 'adjustments');
        }

        $out = [];
        foreach ((array) $raw as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $amount = (float) ($row['amount'] ?? 0);
            if ($label === '' && abs($amount) < 0.005) {
                continue;
            }
            $out[] = [
                'label'  => $label !== '' ? $label : 'Adjustment',
                'amount' => round($amount, 2),
            ];
            if (count($out) >= 2) {
                break; // admin UI caps to two
            }
        }

        return $out;
    }

    /**
     * Sum manual payments (status == succeeded).
     */
    public static function manualPaid(?Reservation $reservation): float
    {
        if (!$reservation) {
            return 0.0;
        }

        $sum = 0.0;
        foreach ((array) ($reservation->manual_payments ?? []) as $mp) {
            if (strtolower((string) ($mp['status'] ?? '')) === 'succeeded') {
                $sum += (float) ($mp['amount'] ?? 0);
            }
        }

        return round($sum, 2);
    }

    /**
     * Produce a consistent totals summary.
     */
    public static function compute(?Reservation $reservation, array $fallback = []): array
    {
        $subtotal = self::value($reservation?->subtotal, data_get($fallback, 'subtotal', 0));
        $travel   = self::value($reservation?->travel_fee, data_get($fallback, 'travel', 0));
        $gratuity = self::value($reservation?->gratuity, data_get($fallback, 'gratuity'));
        if ($gratuity === null) {
            $gratuity = round($subtotal * self::DEFAULT_GRATUITY, 2);
        }

        $adjustments = self::adjustments($reservation, $fallback);
        $adjSum = array_reduce($adjustments, fn($c, $a) => $c + (float) ($a['amount'] ?? 0), 0.0);

        $tax = self::value($reservation?->tax, data_get($fallback, 'tax'));
        if ($tax === null) {
            $tax = round(max(0, $subtotal + $adjSum) * self::DEFAULT_TAX, 2);
        }

        $computedTotal = round($subtotal + $travel + $gratuity + $tax + $adjSum, 2);
        $total = self::value($reservation?->total, data_get($fallback, 'total', $computedTotal));
        if ($total === null || $total <= 0) {
            $total = $computedTotal;
        }

        $depositDue = self::value($reservation?->deposit_due, data_get($fallback, 'deposit_due'));
        if ($depositDue === null || $depositDue <= 0) {
            $depositDue = round($total * 0.20, 2);
        }

        [$stripeDeposit, $stripeExtra, $stripeTotal] = self::stripeBreakdown($reservation);

        $depositPaidStored = self::value($reservation?->deposit_paid, data_get($fallback, 'deposit_paid', 0));
        if ($stripeTotal > 0) {
            $depositPaid = min($stripeDeposit, $depositDue > 0 ? $depositDue : $stripeDeposit);
            // If metadata/purpose is missing in older rows, fall back to session hint.
            if ($depositPaid <= 0 && ($fallbackDeposit = (float) data_get($fallback, 'deposit_paid_session', 0)) > 0) {
                $depositPaid = min($fallbackDeposit, $depositDue);
                $stripeExtra = max(0, $stripeTotal - $depositPaid);
            }
        } else {
            $depositPaid = max(0, $depositPaidStored ?? 0.0);
            $stripeExtra = 0.0;
        }

        $manualPaid = self::manualPaid($reservation);
        $paidTotalStored = self::value($reservation?->amount_paid_total, data_get($fallback, 'amount_paid_total'));
        $depositOverflow = max(0, round($stripeDeposit - $depositPaid, 2));
        $paidTotalComputed = round($depositPaid + $depositOverflow + max(0, $stripeExtra) + $manualPaid, 2);
        $paidTotal = $paidTotalComputed;
        if ($paidTotalStored !== null) {
            $stored = max(0, (float) $paidTotalStored);
            if ($stripeTotal <= 0 && $manualPaid <= 0) {
                // Legacy rows may only have cached totals in reservations table.
                $paidTotal = $stored;
            } elseif (abs($stored - $paidTotalComputed) < 0.01) {
                $paidTotal = $stored;
            }
        }
        if ($paidTotal < 0) {
            $paidTotal = 0.0;
        }

        $depositDisplay = max(0, min((float) $depositPaid, (float) $total));

        $balanceStored = self::value($reservation?->balance, data_get($fallback, 'balance'));
        $balance = $balanceStored !== null ? (float) $balanceStored : max(0, round($total - $paidTotal, 2));
        if (abs($balance - ($total - $paidTotal)) > 0.009) {
            $balance = max(0, round($total - $paidTotal, 2));
        }

        return [
            'subtotal'       => round($subtotal, 2),
            'travel'         => round($travel, 2),
            'gratuity'       => round($gratuity, 2),
            'tax'            => round($tax, 2),
            'adjustments'    => $adjustments,
            'adjustments_sum'=> round($adjSum, 2),
            'total'          => $total,
            'deposit_due'    => round($depositDue, 2),
            'deposit_paid'   => round($depositPaid, 2),
            'stripe_deposit' => round($stripeDeposit, 2),
            'deposit_display'=> round($depositDisplay, 2),
            'manual_paid'    => round($manualPaid, 2),
            'paid_total'     => round($paidTotal, 2),
            'balance'        => $balance,
        ];
    }

    public static function stripeBreakdown(?Reservation $reservation): array
    {
        if (!$reservation) {
            return [0.0, 0.0, 0.0];
        }

        try {
            $columns = ['amount', 'payload_json'];
            if (Schema::hasColumn('payments', 'type')) {
                $columns[] = 'type';
            }
            $payments = $reservation->payments()
                ->where('status', 'succeeded')
                ->get($columns);
        } catch (\Throwable $e) {
            return [0.0, 0.0, 0.0];
        }

        $deposit = 0.0;
        $extra   = 0.0;
        $depositDue = (float) ($reservation->deposit_due ?? 0);
        foreach ($payments as $p) {
            $type = strtolower((string) ($p->type ?? ''));
            $payload = null;
            try {
                $payload = $p->payload_json ? json_decode($p->payload_json, true, 512, JSON_THROW_ON_ERROR) : null;
            } catch (\Throwable $e) {
                $payload = json_decode($p->payload_json ?? '', true);
            }
            $purpose = $type !== '' ? $type : strtolower((string) data_get($payload, 'metadata.purpose', data_get($payload, 'metadata.payment_type', '')));
            $amount  = (float) ($p->amount ?? 0);

            if ($purpose === '') {
                // Legacy rows without metadata: treat only the exact expected deposit as deposit.
                if ($deposit <= 0 && $depositDue > 0 && abs($amount - $depositDue) < 0.01) {
                    $purpose = 'deposit';
                } else {
                    $purpose = 'full';
                }
            }

            if (in_array($purpose, ['balance', 'full'], true)) {
                $extra += $amount;
            } else {
                $deposit += $amount;
            }
        }

        $deposit = round($deposit, 2);
        $extra   = round($extra, 2);
        return [$deposit, $extra, round($deposit + $extra, 2)];
    }

    private static function value($primary, $fallback)
    {
        if ($primary !== null) {
            return (float) $primary;
        }
        if ($fallback !== null) {
            return (float) $fallback;
        }
        return null;
    }
}
