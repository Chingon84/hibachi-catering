<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Support\ReservationTotals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class InvoiceStatusController extends Controller
{
    public function show(Request $request, string $token)
    {
        if (!Schema::hasColumn('reservations', 'public_invoice_token')) {
            return response()->view('invoices.public_status_not_found', [], 404);
        }

        $reservation = Reservation::query()
            ->where('public_invoice_token', $token)
            ->first();

        if (!$reservation) {
            return response()->view('invoices.public_status_not_found', [], 404);
        }

        $totals = ReservationTotals::compute($reservation);
        $balance = (float) ($totals['balance'] ?? 0);
        $paidTotal = (float) ($totals['paid_total'] ?? 0);

        $invoiceStatus = $this->invoiceStatus($reservation, $balance, $paidTotal);

        return view('invoices.public_status', [
            'reservation' => $reservation,
            'totals' => $totals,
            'invoiceStatus' => $invoiceStatus,
        ]);
    }

    private function invoiceStatus(Reservation $reservation, float $balance, float $paidTotal): string
    {
        if ($balance <= 0.009) {
            return 'Paid';
        }

        if ($paidTotal > 0.009) {
            return 'Partially Paid';
        }

        $stored = strtolower((string) ($reservation->invoice_status ?? ''));
        if ($stored === 'paid') {
            return 'Paid';
        }

        return 'Pending';
    }
}
