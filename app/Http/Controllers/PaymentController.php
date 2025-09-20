<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Models\Reservation;
use App\Models\Payment;
use App\Support\InvoiceRenderer;

class PaymentController extends Controller
{
    public function checkout(Request $req)
    {
        $state = session('resv', []);
        $reservationId = data_get($state, 'reservation_id');
        abort_if(!$reservationId, 400, 'Reservation not started.');

        $reservation = Reservation::findOrFail($reservationId);

        // Deposit amount: from step 4 form preferred, or recompute from estimate (20%)
        $deposit = (float) $req->input('deposit_amount', data_get($state, 'deposit_amount', 0));
        if ($deposit <= 0) {
            $estimate = data_get($state, 'estimate', []);
            $total = (float) ($estimate['total'] ?? 0);
            $deposit = round($total * 0.20, 2);
        }

        $stripeSecret = config('services.stripe.secret');
        $stripeKey = config('services.stripe.key');
        abort_if(!$stripeSecret || !$stripeKey, 500, 'Stripe keys not configured.');

        $amountCents = (int) round($deposit * 100);
        if ($amountCents < 50) { // Stripe minimum ~ $0.50 USD
            return back()->withErrors(['payment' => 'Deposit amount is too low. Please review your estimate.']);
        }

        $successUrl = route('payments.success') . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = route('payments.cancel');

        try {
            // Create Checkout Session via Stripe REST API
            $response = Http::asForm()
                ->withToken($stripeSecret)
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'mode' => 'payment',
                    'success_url' => $successUrl,
                    'cancel_url'  => $cancelUrl,
                    'currency'    => 'usd',
                    'payment_method_types[]' => 'card',
                    'line_items[0][price_data][currency]' => 'usd',
                    'line_items[0][price_data][product_data][name]' => 'Reservation Deposit ' . ($reservation->code ?? ('#'.$reservation->id)),
                    'line_items[0][price_data][unit_amount]' => $amountCents,
                    'line_items[0][quantity]' => 1,
                    // pass reservation id via metadata
                    'metadata[reservation_id]' => (string)$reservation->id,
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
            $reservation->deposit_due = $deposit;
            $reservation->save();
        } catch (\Throwable $e) {}
        // 2) Keep in session for redundancy
        session(['resv' => array_merge($state, [ 'deposit_amount' => $deposit ])]);

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
                $amountTotal = ((int) ($data['amount_total'] ?? 0)) / 100.0;
                $currency    = (string) ($data['currency'] ?? 'usd');
                $status      = (string) ($data['payment_status'] ?? 'unpaid');
                $pi          = data_get($data, 'payment_intent');
                $txn         = is_array($pi) ? (string) data_get($pi, 'id', $sessionId) : (string) ($pi ?: $sessionId);

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
                    'amount'         => $amountTotal,
                    'currency'       => strtoupper($currency),
                    'status'         => $status === 'paid' ? 'succeeded' : $status,
                    'transaction_id' => $txn,
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

                Payment::create($attrs);

                // Recompute and persist totals from items for DB consistency
                try {
                    $itemsSubtotal = (float) ($reservation->items()->sum('line_total'));
                    $travelFee     = (float) ($reservation->travel_fee ?? 0);
                    $gratuity      = round($itemsSubtotal * 0.18, 2);
                    $tax           = round($itemsSubtotal * 0.1025, 2);
                    $grandTotal    = round($itemsSubtotal + $travelFee + $gratuity + $tax, 2);

                    $reservation->subtotal  = round($itemsSubtotal, 2);
                    $reservation->gratuity  = $gratuity;
                    $reservation->tax       = $tax;
                    $reservation->total     = $grandTotal;
                } catch (\Throwable $e) {
                    // ignore compute errors; not critical
                }

                // Mark payment if succeeded
                if (($data['payment_status'] ?? '') === 'paid') {
                    // Ensure invoice number exists
                    if (empty($reservation->invoice_number)) {
                        try {
                            $max = (int) (Reservation::max('invoice_number') ?? 0);
                            $reservation->invoice_number = $max >= 100 ? ($max + 1) : 100;
                        } catch (\Throwable $e) {}
                    }
                    $purpose = data_get($data, 'metadata.purpose', 'deposit');
                    // Ensure deposit_due is set; if missing, use session or 20%
                    if ((float) ($reservation->deposit_due ?? 0) <= 0) {
                        try {
                            $fromSession = (float) data_get(session('resv', []), 'deposit_amount', 0);
                            $fallback = $fromSession > 0 ? $fromSession : round((float) ($reservation->total ?? 0) * 0.20, 2);
                            $reservation->deposit_due = $fallback;
                        } catch (\Throwable $e) {}
                    }
                    // Sum payments into deposit_paid to reflect total paid toward invoice
                    $reservation->deposit_paid = (float) ($reservation->deposit_paid ?? 0) + $amountTotal;
                    $reservation->status = 'confirmed';
                    // Mark source as Online
                    if (empty($reservation->booked_by)) {
                        $reservation->booked_by = 'Online';
                    }
                    // Update balance after payment
                    $reservation->balance = max(0, round(($reservation->total ?? 0) - $reservation->deposit_paid, 2));
                    // If fully paid, mark invoice as paid
                    if (($reservation->balance ?? 0) <= 0.0) {
                        $reservation->invoice_status = 'paid';
                    }
                    $reservation->save();

                    // Prepare invoice attachment (PDF if available; else HTML)
                    $fresh = $reservation->fresh(['items','payments']);
                    $pdf = null;
                    try { $pdf = InvoiceRenderer::renderPdf($fresh); } catch (\Throwable $e) { $pdf = null; }
                    $htmlInvoice = InvoiceRenderer::renderHtml($fresh);

                    // Signed pay link for remaining balance
                    $payUrl = '';
                    try {
                        $payUrl = URL::signedRoute('invoice.pay', ['code' => $fresh->code]);
                    } catch (\Throwable $e) { $payUrl = ''; }

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
                        $payload = [ 'reservation' => $fresh, 'pay_url' => $payUrl ];
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
                            $payload = [ 'reservation' => $fresh, 'pay_url' => $payUrl ];
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

    // Signed public link to pay remaining balance for an invoice
    public function payBalance(Request $req, string $code)
    {
        $reservation = Reservation::where('code', $code)->first();
        if (!$reservation) {
            return response('Invoice not found.', 404);
        }
        $balance = max(0, (float) ($reservation->total ?? 0) - (float) ($reservation->deposit_paid ?? 0));
        if ($balance <= 0.0) {
            return response('Nothing to pay for this invoice.', 200);
        }

        $stripeSecret = config('services.stripe.secret');
        $stripeKey = config('services.stripe.key');
        if (!$stripeSecret || !$stripeKey) {
            return response('Payment processor not configured.', 500);
        }

        $amountCents = (int) round($balance * 100);
        if ($amountCents < 50) {
            return response('Amount too low to process.', 400);
        }

        $successUrl = route('payments.success') . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = route('payments.cancel');

        try {
            $response = Http::asForm()
                ->withToken($stripeSecret)
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'mode' => 'payment',
                    'success_url' => $successUrl,
                    'cancel_url'  => $cancelUrl,
                    'currency'    => 'usd',
                    'payment_method_types[]' => 'card',
                    'line_items[0][price_data][currency]' => 'usd',
                    'line_items[0][price_data][product_data][name]' => 'Invoice Balance #' . ($reservation->invoice_number ?? ($reservation->code ?? $reservation->id)),
                    'line_items[0][price_data][unit_amount]' => $amountCents,
                    'line_items[0][quantity]' => 1,
                    'metadata[reservation_id]' => (string)$reservation->id,
                    'metadata[purpose]' => 'balance',
                ]);

            if (!$response->ok()) {
                return response('Could not start payment.', 500);
            }

            $session = $response->json();
            if (!isset($session['url'])) {
                return response('Payment error: no URL.', 500);
            }
        } catch (\Throwable $e) {
            return response('Payment service unavailable.', 500);
        }

        // Return a small HTML that breaks out of iframes and redirects the top window
        $url = (string) ($session['url'] ?? '');
        return response()->view('payments.redirect', ['url' => $url]);
    }

    public function cancel()
    {
        return redirect()->route('reservations.step', ['step'=>4])->withErrors(['payment' => 'Payment canceled.']);
    }
}
