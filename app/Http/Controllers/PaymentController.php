<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Models\Reservation;
use App\Models\Payment;
use App\Services\ClientSyncService;
use App\Services\ReservationPaymentSyncService;
use App\Services\TaxRateResolver;
use App\Support\CaliforniaCateringTax;
use App\Support\InvoiceRenderer;
use App\Support\ReservationTotals;

class PaymentController extends Controller
{
    public function checkout(Request $req)
    {
        $state = session('resv', []);
        $reservationId = data_get($state, 'reservation_id');
        $directReservationId = (int) $req->input('reservation_id', 0);
        $usingDirectReservation = false;
        if ($directReservationId > 0) {
            $user = $req->user();
            abort_unless(
                $user
                && (int) ($user->is_active ?? 1) === 1
                && method_exists($user, 'hasPermission')
                && $user->hasPermission('reservations.manage'),
                403
            );
            $reservationId = $directReservationId;
            $usingDirectReservation = true;
        }
        abort_if(!$reservationId, 400, 'Reservation not started.');

        $reservation = Reservation::findOrFail($reservationId);

        $paymentType = strtolower((string) $req->input('payment_type', 'deposit'));
        if (!in_array($paymentType, ['deposit', 'full'], true)) {
            $paymentType = 'deposit';
        }

        // Payment amount from Step 4 form; defaults to 20% deposit.
        $amount = (float) $req->input('deposit_amount', data_get($state, 'deposit_amount', 0));
        if ($usingDirectReservation && $paymentType === 'full') {
            $totals = ReservationTotals::compute($reservation);
            $amount = (float) ($totals['balance'] ?? 0);
        }
        if ($amount <= 0) {
            $estimate = data_get($state, 'estimate', []);
            $total = (float) ($estimate['total'] ?? 0);
            $amount = round($total * 0.20, 2);
        }

        $total = (float) ($reservation->total ?? data_get($state, 'estimate.total', 0));
        if ($paymentType === 'deposit') {
            $totals = ReservationTotals::compute($reservation);
            $depositTarget = (float) ($reservation->deposit_due ?? 0);
            if ($depositTarget <= 0) {
                $depositTarget = round(max(0, $total) * 0.20, 2);
            }
            $paidTotal = max(0, (float) ($totals['paid_total'] ?? 0));
            $depositPaid = max(0, (float) ($totals['deposit_paid'] ?? 0));
            $paidTowardDeposit = min($depositTarget, max($depositPaid, min($paidTotal, $depositTarget)));
            $remainingDeposit = max(0, round($depositTarget - $paidTowardDeposit, 2));
            $this->debugPayment('deposit.checkout.remaining', [
                'reservation_id' => $reservation->id,
                'session_reservation_id' => $reservationId,
                'deposit_target' => $depositTarget,
                'deposit_paid' => $depositPaid,
                'amount_paid_total' => $paidTotal,
                'paid_toward_deposit' => $paidTowardDeposit,
                'remaining_deposit' => $remainingDeposit,
                'invoice_status' => (string) ($reservation->invoice_status ?? ''),
            ]);
            if ($remainingDeposit <= 0.0) {
                \Log::warning('deposit.checkout.blocked_already_paid', [
                    'reservation_id' => $reservation->id,
                    'session_reservation_id' => $reservationId,
                    'deposit_target' => $depositTarget,
                    'deposit_paid' => $depositPaid,
                    'amount_paid_total' => $paidTotal,
                    'invoice_status' => (string) ($reservation->invoice_status ?? ''),
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

            $checkoutPayload = [
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,
                'currency'    => 'usd',
                'payment_method_types[]' => 'card',
                'line_items[0][price_data][currency]' => 'usd',
                'line_items[0][price_data][product_data][name]' => ($paymentType === 'full' ? 'Reservation Full Payment ' : 'Reservation Deposit ') . ($reservation->code ?? ('#'.$reservation->id)),
                'line_items[0][price_data][unit_amount]' => $amountCents,
                'line_items[0][quantity]' => 1,
                'metadata[reservation_id]' => (string)$reservation->id,
                'metadata[customer_name]' => (string) ($reservation->customer_name ?? ''),
                'metadata[customer_email]' => (string) ($reservation->email ?? ''),
                'metadata[total]' => number_format((float) ($reservation->total ?? $total), 2, '.', ''),
                'metadata[deposit_amount]' => number_format($paymentType === 'deposit' ? $amount : round(max(0, (float) ($reservation->total ?? $total)) * 0.20, 2), 2, '.', ''),
                'metadata[purpose]' => $paymentType === 'full' ? 'full' : 'deposit',
                'metadata[payment_type]' => $paymentType,
                'metadata[expected_amount_cents]' => (string) $amountCents,
                'client_reference_id' => (string) $reservation->id,
            ];
            if (!empty($reservation->email)) {
                $checkoutPayload['customer_email'] = $reservation->email;
            }

            $response = Http::asForm()
                ->withToken($stripeSecret)
                ->post('https://api.stripe.com/v1/checkout/sessions', $checkoutPayload);

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
        // 2) Keep only current checkout context in session; never carry old Stripe/payment IDs forward.
        $nextState = array_merge($state, [
            'reservation_id' => $reservation->id,
            'deposit_amount' => $amount,
            'payment_status' => 'pending',
            'checkout_session_id' => $session['id'] ?? null,
            'stripe_session_id' => $session['id'] ?? null,
            'payment_intent_id' => null,
        ]);
        session(['resv' => $nextState]);

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
            $reservation = $this->findReservationForPayment($reservationId);
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

                $attrs = $this->paymentAttributesForSchema([
                    'reservation_id' => $reservation->id,
                    'provider'       => 'stripe',
                    'type'           => in_array($paymentType, ['deposit', 'full'], true) ? $paymentType : 'deposit',
                    'amount'         => $amountTotal,
                    'currency'       => strtoupper($currency),
                    'status'         => $status === 'paid' ? 'succeeded' : $status,
                    'stripe_session_id' => $sessionId,
                    'stripe_payment_intent_id' => $txn,
                    'payload_json'   => $this->compactStripeSessionPayload($data),
                    'card_brand' => $brand ?: null,
                    'card_last4' => $last4 ?: null,
                ]);

                $paymentQuery = $this->paymentColumnExists('deleted_at')
                    ? Payment::withTrashed()
                    : Payment::withoutGlobalScopes();
                $payment = $paymentQuery->firstOrNew([
                    'provider' => 'stripe',
                    'transaction_id' => $txn,
                ]);
                $payment->fill($attrs);
                $payment->transaction_id = $txn;
                if ($this->paymentColumnExists('deleted_at') && method_exists($payment, 'trashed') && $payment->trashed()) {
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
                        $taxRate       = app(TaxRateResolver::class)->rateForCity((string) ($reservation->city ?? ''));
                        // California catering tax: taxable base includes food/items subtotal, travel fee,
                        // and mandatory gratuity/service charge. Voluntary tips are excluded.
                        $taxableBase   = CaliforniaCateringTax::taxableBase($itemsSubtotal, $travelFee, $gratuity, 0, $adjSum);
                        $tax           = CaliforniaCateringTax::tax($taxableBase, $taxRate);
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
                        if ($this->reservationColumnExists('invoice_number') && empty($reservation->invoice_number)) {
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
                        if ($this->reservationColumnExists('booked_by') && empty($reservation->booked_by)) {
                            $reservation->booked_by = 'Online';
                        }

                        $reservation = $this->markReservationPaid($reservation, $amountTotal);
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
                    $fresh = $this->freshReservationForPayment($reservation);
                    $pdf = null;
                    try { $pdf = InvoiceRenderer::renderPdf($fresh); } catch (\Throwable $e) { $pdf = null; }
                    $htmlInvoice = null;
                    try { $htmlInvoice = InvoiceRenderer::renderHtml($fresh); } catch (\Throwable $e) { $htmlInvoice = null; }

                    // Refresh with relations for emails and session
                    $fresh = $this->freshReservationForPayment($reservation);

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
                            'payment_status' => (string) ($data['payment_status'] ?? 'paid'),
                            'checkout_session_id' => $sessionId,
                            'stripe_session_id' => $sessionId,
                            'payment_intent_id' => $txn,
                        ]);
                        session(['resv' => $sessionState]);
                    } catch (\Throwable $e) {}

                    // Notify admin
                    try {
                        $to = config('mail.admin_address');
                        if (is_string($to) && filter_var($to, FILTER_VALIDATE_EMAIL)) {
                            $payload = [ 'reservation' => $fresh ];
                            Mail::send('emails.reservation_paid', $payload, function ($m) use ($to, $fresh, $pdf, $htmlInvoice) {
                                $code = $fresh->code ?: ('#'.$fresh->id);
                                $invName = 'invoice-'.($fresh->invoice_number ?? $code).'.pdf';
                                $m->to($to)->subject('New Reservation Paid: '.$code);
                                if ($pdf) {
                                    $m->attachData($pdf, $invName, ['mime' => 'application/pdf']);
                                } elseif ($htmlInvoice) {
                                    $m->attachData($htmlInvoice, str_replace('.pdf','.html',$invName), ['mime' => 'text/html']);
                                }
                            });
                        }
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
                                } elseif ($htmlInvoice) {
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

    private function compactStripeSessionPayload(mixed $payload): ?string
    {
        if (!is_array($payload)) {
            return is_string($payload) && $payload !== '' ? $payload : null;
        }

        $compact = [
            'id' => data_get($payload, 'id'),
            'currency' => data_get($payload, 'currency'),
            'payment_status' => data_get($payload, 'payment_status'),
            'amount_total' => data_get($payload, 'amount_total'),
            'metadata' => array_filter([
                'reservation_id' => data_get($payload, 'metadata.reservation_id'),
                'purpose' => data_get($payload, 'metadata.purpose'),
                'payment_type' => data_get($payload, 'metadata.payment_type'),
                'expected_amount_cents' => data_get($payload, 'metadata.expected_amount_cents'),
            ], fn ($value) => $value !== null && $value !== ''),
            'payment_intent' => array_filter([
                'id' => data_get($payload, 'payment_intent.id'),
                'status' => data_get($payload, 'payment_intent.status'),
                'amount_received' => data_get($payload, 'payment_intent.amount_received'),
                'payment_method' => array_filter([
                    'card' => array_filter([
                        'brand' => data_get($payload, 'payment_intent.payment_method.card.brand'),
                        'last4' => data_get($payload, 'payment_intent.payment_method.card.last4'),
                    ], fn ($value) => $value !== null && $value !== ''),
                ]),
            ], fn ($value) => !is_array($value) || !empty($value)),
        ];

        return json_encode(
            array_filter($compact, fn ($value) => !is_array($value) || !empty($value)),
            JSON_UNESCAPED_SLASHES
        ) ?: null;
    }

    private function findReservationForPayment(int $reservationId): ?Reservation
    {
        try {
            if ($this->reservationColumnExists('deleted_at')) {
                return Reservation::find($reservationId);
            }

            return Reservation::withoutGlobalScopes()->find($reservationId);
        } catch (\Throwable $e) {
            \Log::error('payment.success.reservation_lookup_failed', [
                'reservation_id' => $reservationId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function markReservationPaid(Reservation $reservation, float $amountTotal): Reservation
    {
        if (
            $this->reservationColumnExists('amount_paid_total')
            && $this->reservationColumnExists('balance')
            && $this->reservationColumnExists('invoice_status')
            && $this->paymentColumnExists('deleted_at')
        ) {
            try {
                return app(ReservationPaymentSyncService::class)->recalculate($reservation);
            } catch (\Throwable $e) {
                \Log::error('payment.success.recalculate_failed', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            $total = max(0, (float) ($reservation->total ?? 0));
            $depositDue = max(0, (float) ($reservation->deposit_due ?? 0));
            if ($depositDue <= 0 && $total > 0) {
                $depositDue = round($total * 0.20, 2);
            }

            $paidTotal = max(
                max(0, (float) ($reservation->amount_paid_total ?? 0)),
                max(0, (float) ($reservation->deposit_paid ?? 0)),
                max(0, $amountTotal)
            );
            $depositPaid = $depositDue > 0
                ? min($depositDue, $paidTotal)
                : $paidTotal;
            $balance = $total > 0 ? max(0, round($total - $paidTotal, 2)) : 0;

            if ($this->reservationColumnExists('deposit_due') && $depositDue > 0) {
                $reservation->deposit_due = $depositDue;
            }
            if ($this->reservationColumnExists('deposit_paid')) {
                $reservation->deposit_paid = round($depositPaid, 2);
            }
            if ($this->reservationColumnExists('amount_paid_total')) {
                $reservation->amount_paid_total = round($paidTotal, 2);
            }
            if ($this->reservationColumnExists('balance')) {
                $reservation->balance = $balance;
            }
            if ($this->reservationColumnExists('invoice_status')) {
                $reservation->invoice_status = $balance <= 0.0 && $total > 0 ? 'paid' : 'partial';
            }
            if ($this->reservationColumnExists('status')) {
                $reservation->status = 'confirmed';
            }

            $reservation->save();
        } catch (\Throwable $e) {
            \Log::error('payment.success.minimal_reservation_update_failed', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->freshReservationForPayment($reservation);
    }

    private function freshReservationForPayment(Reservation $reservation): Reservation
    {
        $fresh = $this->findReservationForPayment((int) $reservation->id) ?: $reservation;
        $relations = [];

        if ($this->reservationItemColumnExists('deleted_at')) {
            $relations[] = 'items';
        }
        if ($this->paymentColumnExists('deleted_at')) {
            $relations[] = 'payments';
        }

        if (!empty($relations)) {
            try {
                $fresh->load($relations);
            } catch (\Throwable $e) {
                \Log::error('payment.success.relation_refresh_failed', [
                    'reservation_id' => $reservation->id,
                    'relations' => $relations,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $fresh;
    }

    private function paymentAttributesForSchema(array $attrs): array
    {
        return array_filter(
            $attrs,
            fn ($value, string $column) => $this->paymentColumnExists($column),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function reservationColumnExists(string $column): bool
    {
        static $columns = [];

        if (!array_key_exists($column, $columns)) {
            try {
                $columns[$column] = Schema::hasColumn('reservations', $column);
            } catch (\Throwable $e) {
                $columns[$column] = false;
            }
        }

        return $columns[$column];
    }

    private function paymentColumnExists(string $column): bool
    {
        static $columns = [];

        if (!array_key_exists($column, $columns)) {
            try {
                $columns[$column] = Schema::hasColumn('payments', $column);
            } catch (\Throwable $e) {
                $columns[$column] = false;
            }
        }

        return $columns[$column];
    }

    private function reservationItemColumnExists(string $column): bool
    {
        static $columns = [];

        if (!array_key_exists($column, $columns)) {
            try {
                $columns[$column] = Schema::hasColumn('reservation_items', $column);
            } catch (\Throwable $e) {
                $columns[$column] = false;
            }
        }

        return $columns[$column];
    }

    private function debugPayment(string $event, array $context = []): void
    {
        if (!filter_var((string) config('services.stripe.debug', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        \Log::info($event, $context);
    }
}
