<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\Reservation;
use App\Models\Payment;
use App\Services\ClientSyncService;
use App\Services\ReservationPaymentSyncService;
use App\Support\InvoiceRenderer;
use App\Support\ReservationTotals;

class PaymentController extends Controller
{
    public function checkout(Request $req)
    {
        $state = session('resv', []);
        $reservationId = data_get($state, 'reservation_id');
        abort_if(!$reservationId, 400, 'Reservation not started.');

        $reservation = Reservation::findOrFail($reservationId);

        $paymentType = strtolower((string) $req->input('payment_type', 'deposit'));
        if (!in_array($paymentType, ['deposit', 'full'], true)) {
            $paymentType = 'deposit';
        }

        // Payment amount from Step 4 form; defaults to 20% deposit.
        $amount = (float) $req->input('deposit_amount', data_get($state, 'deposit_amount', 0));
        if ($amount <= 0) {
            $estimate = data_get($state, 'estimate', []);
            $total = (float) ($estimate['total'] ?? 0);
            $amount = round($total * 0.20, 2);
        }

        $total = (float) ($reservation->total ?? data_get($state, 'estimate.total', 0));
        if ($paymentType === 'deposit') {
            [$existingDepositPaid] = ReservationTotals::stripeBreakdown($reservation);
            $depositTarget = (float) ($reservation->deposit_due ?? 0);
            if ($depositTarget <= 0) {
                $depositTarget = round(max(0, $total) * 0.20, 2);
            }
            $remainingDeposit = max(0, round($depositTarget - max(0, (float) $existingDepositPaid), 2));
            $this->debugPayment('deposit.checkout.remaining', [
                'reservation_id' => $reservation->id,
                'deposit_target' => $depositTarget,
                'existing_deposit_paid' => (float) $existingDepositPaid,
                'remaining_deposit' => $remainingDeposit,
            ]);
            if ($remainingDeposit <= 0.0) {
                \Log::warning('deposit.checkout.blocked_already_paid', [
                    'reservation_id' => $reservation->id,
                    'deposit_target' => $depositTarget,
                    'existing_deposit_paid' => (float) $existingDepositPaid,
                ]);
                return back()->withErrors(['payment' => 'Deposit is already paid for this reservation.']);
            }
            $amount = min($amount, $remainingDeposit);
            $amount = min($amount, $total > 0 ? $total : $amount);
        }

        $stripeSecret = config('services.stripe.secret');
        $stripeKey = config('services.stripe.key');
        abort_if(!$stripeSecret || !$stripeKey, 500, 'Stripe keys not configured.');

        $amountCents = (int) round($amount * 100);
        if ($amountCents < 50) { // Stripe minimum ~ $0.50 USD
            return back()->withErrors(['payment' => 'Deposit amount is too low. Please review your estimate.']);
        }

        $successUrl = route('payments.success') . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = route('payments.cancel');

        try {
            // Create Checkout Session via Stripe REST API
            $this->debugPayment('stripe.checkout.created', [
                'reservation_id' => $reservation->id,
                'total' => (float) ($reservation->total ?? 0),
                'deposit_due' => $paymentType === 'deposit' ? $amount : (float) ($reservation->deposit_due ?? 0),
                'deposit_percent' => 0.20,
                'stripe_amount_sent_cents' => $amountCents,
                'expected_amount_cents' => $amountCents,
                'payment_type' => $paymentType,
            ]);

            $response = Http::asForm()
                ->withToken($stripeSecret)
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'mode' => 'payment',
                    'success_url' => $successUrl,
                    'cancel_url'  => $cancelUrl,
                    'currency'    => 'usd',
                    'payment_method_types[]' => 'card',
                    'line_items[0][price_data][currency]' => 'usd',
                    'line_items[0][price_data][product_data][name]' => ($paymentType === 'full' ? 'Reservation Full Payment ' : 'Reservation Deposit ') . ($reservation->code ?? ('#'.$reservation->id)),
                    'line_items[0][price_data][unit_amount]' => $amountCents,
                    'line_items[0][quantity]' => 1,
                    // pass reservation id via metadata
                    'metadata[reservation_id]' => (string)$reservation->id,
                    'metadata[purpose]' => $paymentType === 'full' ? 'full' : 'deposit',
                    'metadata[payment_type]' => $paymentType,
                    'metadata[expected_amount_cents]' => (string) $amountCents,
                ]);

            if (!$response->ok()) {
                $body = $response->body();
                \Log::error('Stripe checkout error', ['status'=>$response->status(), 'body'=>$body]);
                return back()->withErrors(['payment' => 'Could not start payment (Stripe response).']);
            }

            $session = $response->json();
            if (!isset($session['url'])) {
                \Log::error('Stripe checkout missing url', ['session'=>$session]);
                return back()->withErrors(['payment' => 'Stripe error (no URL).']);
            }
        } catch (\Throwable $e) {
            \Log::error('Stripe checkout exception', ['message'=>$e->getMessage()]);
            return back()->withErrors(['payment' => 'Payment service unavailable. Try again later.']);
        }

        // Store the expected deposit
        // 1) Persist in DB as deposit_due so later views can classify payments correctly
        try {
            if ($paymentType === 'deposit') {
                $reservation->deposit_due = $amount;
            } elseif ((float) ($reservation->deposit_due ?? 0) <= 0) {
                $reservation->deposit_due = round(max(0, (float) ($reservation->total ?? 0)) * 0.20, 2);
            }
            $reservation->save();
        } catch (\Throwable $e) {}
        // 2) Keep in session for redundancy
        session(['resv' => array_merge($state, [ 'deposit_amount' => $amount ])]);

        return redirect()->away($session['url']);
    }

    public function success(Request $req)
    {
        $sessionId = $req->query('session_id');
        if (!$sessionId) return redirect()->route('reservations.step', ['step'=>5]);

        $secret = config('services.stripe.secret');
        // Expand payment_intent and its payment_method to extract card details
        $resp = Http::withToken($secret)->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId, [
            'expand[]' => 'payment_intent.payment_method',
        ]);
        if (!$resp->ok()) return redirect()->route('reservations.step', ['step'=>5]);

        $data = $resp->json();
        $reservationId = (int) data_get($data, 'metadata.reservation_id');
        if ($reservationId) {
            $reservation = Reservation::find($reservationId);
            if ($reservation) {
                $amountCents = $this->extractPaidAmountCents($data);
                $amountTotal = $this->normalizeStripeAmountToDollars($amountCents);
                $expectedAmountCents = (int) data_get($data, 'metadata.expected_amount_cents', 0);
                $paymentType = strtolower((string) data_get($data, 'metadata.payment_type', data_get($data, 'metadata.purpose', 'deposit')));
                $currency    = (string) ($data['currency'] ?? 'usd');
                $status      = (string) ($data['payment_status'] ?? 'unpaid');
                $pi          = data_get($data, 'payment_intent');
                $txn         = is_array($pi) ? (string) data_get($pi, 'id', $sessionId) : (string) ($pi ?: $sessionId);
                if ($txn === '') {
                    $txn = $sessionId;
                }

                $this->debugPayment('stripe.success.received', [
                    'reservation_id' => $reservation->id,
                    'payment_type' => $paymentType,
                    'expected_amount_cents' => $expectedAmountCents,
                    'session_amount_total_cents' => (int) data_get($data, 'amount_total', 0),
                    'payment_intent_amount_received_cents' => (int) data_get($data, 'payment_intent.amount_received', 0),
                    'amount_received_cents' => $amountCents,
                    'amount_received_dollars' => $amountTotal,
                    'stripe_session_id' => $sessionId,
                    'transaction_id' => $txn,
                    'currency' => $currency,
                ]);

                // Try to extract card brand/last4 from expanded session or fetch PaymentIntent directly
                $brand = strtoupper((string) data_get($data, 'payment_intent.payment_method.card.brand', ''));
                $last4 = (string) data_get($data, 'payment_intent.payment_method.card.last4', '');

                if (($brand === '' || $last4 === '') && $txn) {
                    try {
                        $piResp = Http::withToken($secret)->get('https://api.stripe.com/v1/payment_intents/'.$txn, [ 'expand[]' => 'payment_method' ]);
                        if ($piResp->ok()) {
                            $pi = $piResp->json();
                            $brand = strtoupper((string) data_get($pi, 'payment_method.card.brand', $brand));
                            $last4 = (string) data_get($pi, 'payment_method.card.last4', $last4);
                            if ($brand === '' || $last4 === '') {
                                $brand = strtoupper((string) data_get($pi, 'charges.data.0.payment_method_details.card.brand', $brand));
                                $last4 = (string) data_get($pi, 'charges.data.0.payment_method_details.card.last4', $last4);
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $attrs = [
                    'reservation_id' => $reservation->id,
                    'provider'       => 'stripe',
                    'type'           => in_array($paymentType, ['deposit', 'full'], true) ? $paymentType : 'deposit',
                    'amount'         => $amountTotal,
                    'currency'       => strtoupper($currency),
                    'status'         => $status === 'paid' ? 'succeeded' : $status,
                    'stripe_session_id' => $sessionId,
                    'stripe_payment_intent_id' => $txn,
                    'payload_json'   => is_array($data) ? json_encode($data) : (string) $data,
                ];
                // Only include card columns if they exist (in case migration not run yet)
                try {
                    if (\Schema::hasColumn('payments', 'card_brand')) {
                        $attrs['card_brand'] = $brand ?: null;
                    }
                    if (\Schema::hasColumn('payments', 'card_last4')) {
                        $attrs['card_last4'] = $last4 ?: null;
                    }
                } catch (\Throwable $e) {}

                $payment = Payment::withTrashed()->firstOrNew([
                    'provider' => 'stripe',
                    'transaction_id' => $txn,
                ]);
                $payment->fill($attrs);
                $payment->transaction_id = $txn;
                if (method_exists($payment, 'trashed') && $payment->trashed()) {
                    $payment->restore();
                }
                $payment->save();

                // Recompute and persist totals from items for DB consistency
                try {
                    $itemsSubtotal = (float) ($reservation->items()->sum('line_total'));
                    if ($itemsSubtotal > 0) {
                        $travelFee     = (float) ($reservation->travel_fee ?? 0);
                        $adjustments   = ReservationTotals::adjustments($reservation);
                        $adjSum        = array_reduce($adjustments, fn($c, $a) => $c + (float) ($a['amount'] ?? 0), 0.0);
                        $gratuity      = round($itemsSubtotal * 0.18, 2);
                        $tax           = round(max(0, $itemsSubtotal + $adjSum) * 0.1025, 2);
                        $grandTotal    = round($itemsSubtotal + $travelFee + $gratuity + $tax + $adjSum, 2);

                        $reservation->subtotal  = round($itemsSubtotal, 2);
                        $reservation->gratuity  = $gratuity;
                        $reservation->tax       = $tax;
                        $reservation->total     = $grandTotal;
                    }
                } catch (\Throwable $e) {
                    // ignore compute errors; not critical
                }

                // Mark payment if succeeded
                if (($data['payment_status'] ?? '') === 'paid') {
                    $amountMismatch = $expectedAmountCents > 0 && $expectedAmountCents !== $amountCents;
                    if ($amountMismatch) {
                        $payment->status = 'mismatch';
                        $payment->save();
                        \Log::error('payment.success.amount_mismatch', [
                            'reservation_id' => $reservation->id,
                            'payment_type' => $paymentType,
                            'expected_amount_cents' => $expectedAmountCents,
                            'received_amount_cents' => $amountCents,
                            'transaction_id' => $txn,
                        ]);
                    }

                    if (!$amountMismatch) {
                        // Ensure invoice number exists
                        if (empty($reservation->invoice_number)) {
                            try {
                                $max = (int) (Reservation::max('invoice_number') ?? 0);
                                $reservation->invoice_number = $max >= 100 ? ($max + 1) : 100;
                            } catch (\Throwable $e) {}
                        }
                        // Ensure deposit_due is set; if missing, use session or 20%
                        if ((float) ($reservation->deposit_due ?? 0) <= 0) {
                            try {
                                $fromSession = (float) data_get(session('resv', []), 'deposit_amount', 0);
                                $fallback = $fromSession > 0 ? $fromSession : round((float) ($reservation->total ?? 0) * 0.20, 2);
                                $reservation->deposit_due = $fallback;
                            } catch (\Throwable $e) {}
                        }
                        $reservation->status = 'confirmed';
                        // Mark source as Online
                        if (empty($reservation->booked_by)) {
                            $reservation->booked_by = 'Online';
                        }

                        $reservation = app(ReservationPaymentSyncService::class)->recalculate($reservation);
                    }

                    $this->debugPayment('db.update.payment_fields', [
                        'reservation_id' => $reservation->id,
                        'payment_type' => $paymentType,
                        'deposit_paid' => (float) ($reservation->deposit_paid ?? 0),
                        'amount_paid_total' => (float) ($reservation->amount_paid_total ?? 0),
                        'balance' => (float) ($reservation->balance ?? 0),
                        'invoice_status' => (string) ($reservation->invoice_status ?? ''),
                        'mismatch' => $amountMismatch,
                    ]);

                    // Safety net: explicit sync in payment flow in case observers were bypassed elsewhere.
                    if (!$amountMismatch) {
                        try {
                            \Log::info('payment.sync.requested', [
                                'reservation_id' => $reservation->id,
                                'trigger_reason' => 'payment_success',
                                'status' => $reservation->status,
                                'invoice_status' => $reservation->invoice_status,
                                'deposit_paid' => (float) ($reservation->deposit_paid ?? 0),
                                'paid' => (float) ($reservation->paid ?? 0),
                            ]);

                            $syncedClient = app(ClientSyncService::class)->syncFromReservationId((int) $reservation->id);
                            \Log::info('payment.success.client_sync', [
                                'reservation_id' => $reservation->id,
                                'client_id' => $syncedClient?->id,
                                'trigger_reason' => 'payment_success',
                                'result' => $syncedClient ? 'synced' : 'not_found',
                                'status' => $reservation->status,
                                'invoice_status' => $reservation->invoice_status,
                                'deposit_paid' => (float) ($reservation->deposit_paid ?? 0),
                                'paid' => (float) ($reservation->paid ?? 0),
                                'events_count' => (int) ($syncedClient?->events_count ?? 0),
                                'last_event_at' => optional($syncedClient?->last_event_at)->toDateTimeString(),
                            ]);
                        } catch (\Throwable $e) {
                            \Log::error('payment.success.client_sync_failed', [
                                'reservation_id' => $reservation->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    // Prepare invoice attachment (PDF if available; else HTML)
                    $fresh = $reservation->fresh(['items','payments']);
                    $pdf = null;
                    try { $pdf = InvoiceRenderer::renderPdf($fresh); } catch (\Throwable $e) { $pdf = null; }
                    $htmlInvoice = InvoiceRenderer::renderHtml($fresh);

                    // Refresh with relations for emails and session
                    $fresh = $reservation->fresh(['items','payments']);

                    // Ensure Step 5 has data even when coming from a signed pay link (no wizard session)
                    try {
                        $est = [
                            'subtotal' => (float) ($fresh->subtotal ?? 0),
                            'travel'   => (float) ($fresh->travel_fee ?? 0),
                            'gratuity' => (float) ($fresh->gratuity ?? 0),
                            'tax'      => (float) ($fresh->tax ?? 0),
                            'total'    => (float) ($fresh->total ?? 0),
                        ];
                        $sessionState = array_merge(session('resv', []), [
                            'reservation_id' => $fresh->id,
                            'guests' => (int) ($fresh->guests ?? 0),
                            'date'   => optional($fresh->date)->toDateString() ?? ($fresh->date ?? null),
                            'time'   => substr((string) ($fresh->time ?? ''), 0, 5),
                            'first_name' => null, // keep names consolidated in customer_name
                            'last_name'  => null,
                            'company'    => $fresh->company,
                            'phone'      => $fresh->phone,
                            'email'      => $fresh->email,
                            'address'    => $fresh->address,
                            'city'       => $fresh->city,
                            'zip_code'   => $fresh->zip_code,
                            'event_type' => $fresh->event_type,
                            'setup_color'=> $fresh->setup_color,
                            'stairs'     => (bool) ($fresh->stairs ?? false),
                            'notes'      => $fresh->notes,
                            'estimate'   => $est,
                            // last payment (this transaction), used for Step5 breakdown
                            'last_payment_amount' => (float) ($amountTotal ?? 0),
                        ]);
                        session(['resv' => $sessionState]);
                    } catch (\Throwable $e) {}

                    // Notify admin
                    try {
                        $to = 'eric@hibachicater.com';
                        $payload = [ 'reservation' => $fresh ];
                        Mail::send('emails.reservation_paid', $payload, function ($m) use ($to, $fresh, $pdf, $htmlInvoice) {
                            $code = $fresh->code ?: ('#'.$fresh->id);
                            $invName = 'invoice-'.($fresh->invoice_number ?? $code).'.pdf';
                            $m->to($to)->subject('New Reservation Paid: '.$code);
                            if ($pdf) {
                                $m->attachData($pdf, $invName, ['mime' => 'application/pdf']);
                            } else {
                                $m->attachData($htmlInvoice, str_replace('.pdf','.html',$invName), ['mime' => 'text/html']);
                            }
                        });
                    } catch (\Throwable $e) {
                        // swallow email errors
                    }

                    // Notify client (if email present)
                    try {
                        if (!empty($fresh->email)) {
                            $toClient = $fresh->email;
                            $payload = [ 'reservation' => $fresh ];
                            Mail::send('emails.reservation_customer', $payload, function ($m) use ($toClient, $fresh, $pdf, $htmlInvoice) {
                                $code = $fresh->code ?: ('#'.$fresh->id);
                                $invNo = $fresh->invoice_number ?? $code;
                                $m->to($toClient)->subject('Your Reservation Confirmation – Invoice #'.$invNo);
                                if ($pdf) {
                                    $m->attachData($pdf, 'invoice-'.$invNo.'.pdf', ['mime' => 'application/pdf']);
                                } else {
                                    $m->attachData($htmlInvoice, 'invoice-'.$invNo.'.html', ['mime' => 'text/html']);
                                }
                            });
                        }
                    } catch (\Throwable $e) {
                        // ignore email failures to client
                    }
                }
            }
        }

        return redirect()->route('reservations.step', ['step'=>5]);
    }

    public function cancel()
    {
        return redirect()->route('reservations.step', ['step'=>4])->withErrors(['payment' => 'Payment canceled.']);
    }

    private function extractPaidAmountCents(array $session): int
    {
        $amountReceived = data_get($session, 'payment_intent.amount_received');
        if (is_numeric($amountReceived) && (int) $amountReceived > 0) {
            return (int) $amountReceived;
        }

        $amountTotal = data_get($session, 'amount_total');
        if (is_numeric($amountTotal) && (int) $amountTotal > 0) {
            return (int) $amountTotal;
        }

        return 0;
    }

    private function normalizeStripeAmountToDollars(int $cents): float
    {
        return round($cents / 100, 2);
    }

    private function debugPayment(string $event, array $context = []): void
    {
        if (!filter_var((string) env('STRIPE_PAY_DEBUG', 'false'), FILTER_VALIDATE_BOOL)) {
            return;
        }

        \Log::info($event, $context);
    }
}
