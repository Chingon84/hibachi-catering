<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Reservation;
use App\Support\ReservationTotals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentDepositConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_stores_deposit_paid_from_stripe_cents_and_keeps_balance_consistent(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');
        config()->set('services.stripe.key', 'pk_test_123');

        $reservation = $this->createReservationForDepositScenario();

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_test_deposit*' => Http::response([
                'id' => 'cs_test_deposit',
                'currency' => 'usd',
                'payment_status' => 'paid',
                'amount_total' => 54481,
                'metadata' => [
                    'reservation_id' => (string) $reservation->id,
                    'purpose' => 'deposit',
                    'payment_type' => 'deposit',
                    'expected_amount_cents' => '54481',
                ],
                'payment_intent' => [
                    'id' => 'pi_test_deposit',
                    'amount_received' => 54481,
                    'payment_method' => [
                        'card' => [
                            'brand' => 'visa',
                            'last4' => '4242',
                        ],
                    ],
                ],
            ], 200),
        ]);
        Mail::fake();

        $this->get(route('payments.success', ['session_id' => 'cs_test_deposit']))
            ->assertRedirect(route('reservations.step', ['step' => 5]));

        $reservation->refresh();

        $this->assertSame(544.81, (float) $reservation->deposit_paid);
        $this->assertSame(544.81, (float) $reservation->amount_paid_total);
        $this->assertSame(2724.07, (float) $reservation->total);
        $this->assertSame(2179.26, (float) $reservation->balance);

        $totals = ReservationTotals::compute($reservation);
        $this->assertSame(544.81, (float) $totals['deposit_display']);
        $this->assertSame(2179.26, (float) $totals['balance']);

        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'provider' => 'stripe',
            'transaction_id' => 'pi_test_deposit',
            'amount' => 544.81,
            'status' => 'succeeded',
            'type' => 'deposit',
        ]);
    }

    public function test_success_is_idempotent_for_same_session_and_does_not_duplicate_payment_rows(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');
        config()->set('services.stripe.key', 'pk_test_123');

        $reservation = $this->createReservationForDepositScenario();

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_test_repeat*' => Http::response([
                'id' => 'cs_test_repeat',
                'currency' => 'usd',
                'payment_status' => 'paid',
                'amount_total' => 54481,
                'metadata' => [
                    'reservation_id' => (string) $reservation->id,
                    'purpose' => 'deposit',
                    'payment_type' => 'deposit',
                    'expected_amount_cents' => '54481',
                ],
                'payment_intent' => [
                    'id' => 'pi_test_repeat',
                    'amount_received' => 54481,
                    'payment_method' => [
                        'card' => [
                            'brand' => 'visa',
                            'last4' => '4242',
                        ],
                    ],
                ],
            ], 200),
        ]);
        Mail::fake();

        $this->get(route('payments.success', ['session_id' => 'cs_test_repeat']))
            ->assertRedirect(route('reservations.step', ['step' => 5]));
        $this->get(route('payments.success', ['session_id' => 'cs_test_repeat']))
            ->assertRedirect(route('reservations.step', ['step' => 5]));

        $reservation->refresh();
        $this->assertSame(544.81, (float) $reservation->deposit_paid);
        $this->assertSame(544.81, (float) $reservation->amount_paid_total);
        $this->assertSame(2179.26, (float) $reservation->balance);
        $this->assertSame(1, Payment::query()->where('transaction_id', 'pi_test_repeat')->count());
    }

    public function test_real_case_1993_total_and_39861_cents_keeps_fields_consistent(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');
        config()->set('services.stripe.key', 'pk_test_123');

        $reservation = Reservation::query()->create([
            'code' => 'RSV-PAY-REAL',
            'status' => 'pending_payment',
            'invoice_status' => 'pending',
            'guests' => 18,
            'date' => now()->addDays(10)->toDateString(),
            'time' => '17:00:00',
            'customer_name' => 'Real Case',
            'phone' => '3050002222',
            'email' => 'real-case@example.com',
            'subtotal' => 1550.00,
            'travel_fee' => 0.00,
            'gratuity' => 279.00,
            'tax' => 164.05,
            'total' => 1993.05,
            'deposit_due' => 398.61,
            'deposit_paid' => 0.00,
            'amount_paid_total' => 0.00,
            'balance' => 1993.05,
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_test_real_case*' => Http::response([
                'id' => 'cs_test_real_case',
                'currency' => 'usd',
                'payment_status' => 'paid',
                'amount_total' => 39861,
                'metadata' => [
                    'reservation_id' => (string) $reservation->id,
                    'payment_type' => 'deposit',
                    'expected_amount_cents' => '39861',
                ],
                'payment_intent' => [
                    'id' => 'pi_test_real_case',
                    'amount_received' => 39861,
                ],
            ], 200),
        ]);
        Mail::fake();

        $this->get(route('payments.success', ['session_id' => 'cs_test_real_case']))
            ->assertRedirect(route('reservations.step', ['step' => 5]));

        $reservation->refresh();
        $this->assertSame(398.61, (float) $reservation->deposit_paid);
        $this->assertSame(398.61, (float) $reservation->amount_paid_total);
        $this->assertSame(1594.44, (float) $reservation->balance);
    }

    public function test_mismatch_expected_amount_does_not_update_reservation_payment_fields(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');
        config()->set('services.stripe.key', 'pk_test_123');

        $reservation = $this->createReservationForDepositScenario();
        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_test_mismatch*' => Http::response([
                'id' => 'cs_test_mismatch',
                'currency' => 'usd',
                'payment_status' => 'paid',
                'amount_total' => 30000,
                'metadata' => [
                    'reservation_id' => (string) $reservation->id,
                    'payment_type' => 'deposit',
                    'expected_amount_cents' => '54481',
                ],
                'payment_intent' => [
                    'id' => 'pi_test_mismatch',
                    'amount_received' => 30000,
                ],
            ], 200),
        ]);
        Mail::fake();

        $this->get(route('payments.success', ['session_id' => 'cs_test_mismatch']))
            ->assertRedirect(route('reservations.step', ['step' => 5]));

        $reservation->refresh();
        $this->assertSame(0.00, (float) $reservation->deposit_paid);
        $this->assertSame(0.00, (float) $reservation->amount_paid_total);
        $this->assertSame(2724.07, (float) $reservation->balance);
        $this->assertDatabaseHas('payments', [
            'transaction_id' => 'pi_test_mismatch',
            'status' => 'mismatch',
        ]);
    }

    public function test_legacy_payments_without_type_do_not_inflate_deposit_display(): void
    {
        $reservation = Reservation::query()->create([
            'code' => 'RSV-PAY-LEGACY',
            'status' => 'confirmed',
            'invoice_status' => 'pending',
            'guests' => 12,
            'date' => now()->addDays(12)->toDateString(),
            'time' => '18:00:00',
            'customer_name' => 'Legacy Case',
            'phone' => '3050003333',
            'email' => 'legacy@example.com',
            'total' => 1993.05,
            'deposit_due' => 398.61,
            'deposit_paid' => 2642.30,
            'amount_paid_total' => 2642.30,
            'balance' => 0.00,
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'provider' => 'stripe',
            'amount' => 398.61,
            'currency' => 'USD',
            'status' => 'succeeded',
            'transaction_id' => 'pi_dep_legacy',
            'payload_json' => json_encode(['metadata' => ['purpose' => 'deposit']]),
        ]);
        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'provider' => 'stripe',
            'amount' => 515.84,
            'currency' => 'USD',
            'status' => 'succeeded',
            'transaction_id' => 'pi_full_legacy',
            'payload_json' => json_encode(['metadata' => ['purpose' => 'full']]),
        ]);

        $this->assertSame(2, Payment::query()->where('reservation_id', $reservation->id)->count());

        $totals = ReservationTotals::compute($reservation->fresh());
        $this->assertSame(398.61, (float) $totals['deposit_display']);
        $this->assertSame(914.45, (float) $totals['paid_total']);
    }

    public function test_success_full_payment_marks_balance_zero_without_additional_paid_in_confirmation(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');
        config()->set('services.stripe.key', 'pk_test_123');

        $reservation = $this->createReservationForDepositScenario();

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_test_full*' => Http::response([
                'id' => 'cs_test_full',
                'currency' => 'usd',
                'payment_status' => 'paid',
                'amount_total' => 272407,
                'metadata' => [
                    'reservation_id' => (string) $reservation->id,
                    'purpose' => 'full',
                    'payment_type' => 'full',
                    'expected_amount_cents' => '272407',
                ],
                'payment_intent' => [
                    'id' => 'pi_test_full',
                    'amount_received' => 272407,
                ],
            ], 200),
        ]);
        Mail::fake();

        $this->get(route('payments.success', ['session_id' => 'cs_test_full']))
            ->assertRedirect(route('reservations.step', ['step' => 5]));

        $reservation->refresh();
        $this->assertSame(2724.07, (float) $reservation->amount_paid_total);
        $this->assertSame(0.00, (float) $reservation->balance);

        $this->get(route('reservations.step', ['step' => 5]))
            ->assertOk()
            ->assertDontSeeText('Additional paid');
    }

    public function test_checkout_caps_deposit_to_remaining_amount_when_previous_deposit_exists(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');
        config()->set('services.stripe.key', 'pk_test_123');

        $reservation = Reservation::query()->create([
            'code' => 'RSV-PAY-REMAIN',
            'status' => 'pending_payment',
            'invoice_status' => 'pending',
            'guests' => 12,
            'date' => now()->addDays(7)->toDateString(),
            'time' => '18:00:00',
            'customer_name' => 'Remaining Deposit',
            'phone' => '3050007777',
            'email' => 'remaining@example.com',
            'total' => 2066.18,
            'deposit_due' => 413.24,
            'deposit_paid' => 0.00,
            'amount_paid_total' => 278.18,
            'balance' => 1788.00,
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'provider' => 'stripe',
            'type' => 'deposit',
            'amount' => 278.18,
            'currency' => 'USD',
            'status' => 'succeeded',
            'transaction_id' => 'pi_existing_deposit',
            'payload_json' => json_encode(['metadata' => ['purpose' => 'deposit', 'payment_type' => 'deposit']]),
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_remaining',
                'url' => 'https://checkout.stripe.test/session/remaining',
            ], 200),
        ]);

        $this->withSession([
            'resv' => [
                'reservation_id' => $reservation->id,
                'estimate' => ['total' => 2066.18],
                'deposit_amount' => 413.24,
            ],
        ])->post(route('payments.checkout'), [
            'payment_type' => 'deposit',
            'deposit_amount' => 413.24,
        ])->assertRedirect('https://checkout.stripe.test/session/remaining');

        Http::assertSent(function (HttpClientRequest $request) {
            $payload = $request->data();
            return (int) ($payload['line_items[0][price_data][unit_amount]'] ?? 0) === 13506
                && ($payload['metadata[payment_type]'] ?? '') === 'deposit';
        });
    }

    public function test_totals_use_paid_total_for_balance_consistency(): void
    {
        $reservation = Reservation::query()->create([
            'code' => 'RSV-PAY-STEP5',
            'status' => 'confirmed',
            'invoice_status' => 'pending',
            'guests' => 10,
            'date' => now()->addDays(7)->toDateString(),
            'time' => '18:00:00',
            'customer_name' => 'Step 5 Consistency',
            'phone' => '3050008888',
            'email' => 'step5@example.com',
            'subtotal' => 1600.00,
            'travel_fee' => 0.00,
            'gratuity' => 288.00,
            'tax' => 178.18,
            'total' => 2066.18,
            'deposit_due' => 413.24,
            'deposit_paid' => 413.24,
            'amount_paid_total' => 691.42,
            'balance' => 1374.76,
        ]);

        $totals = ReservationTotals::compute($reservation);
        $this->assertSame(691.42, (float) $totals['paid_total']);
        $this->assertSame(1374.76, (float) $totals['balance']);
        $this->assertSame(413.24, (float) $totals['deposit_paid']);
    }

    public function test_totals_clamp_negative_deposit_paid_to_zero(): void
    {
        $reservation = Reservation::query()->create([
            'code' => 'RSV-PAY-NEG',
            'status' => 'confirmed',
            'invoice_status' => 'pending',
            'guests' => 8,
            'date' => now()->addDays(4)->toDateString(),
            'time' => '18:00:00',
            'customer_name' => 'Negative Deposit',
            'phone' => '3050009999',
            'email' => 'negative@example.com',
            'total' => 1000.00,
            'deposit_due' => 200.00,
            'deposit_paid' => -50.00,
            'amount_paid_total' => 0.00,
            'balance' => 1000.00,
        ]);

        $totals = ReservationTotals::compute($reservation);
        $this->assertSame(0.00, (float) $totals['deposit_display']);
        $this->assertSame(1000.00, (float) $totals['balance']);
    }

    private function createReservationForDepositScenario(): Reservation
    {
        $reservation = Reservation::query()->create([
            'code' => 'RSV-PAY-001',
            'status' => 'pending_payment',
            'invoice_status' => 'pending',
            'guests' => 20,
            'date' => now()->addWeek()->toDateString(),
            'time' => '18:00:00',
            'customer_name' => 'Payment Test',
            'phone' => '3050001111',
            'email' => 'payment-test@example.com',
            'address' => '123 Main St',
            'city' => 'Miami',
            'zip_code' => '33101',
            'subtotal' => 2124.03,
            'travel_fee' => 0.00,
            'gratuity' => 382.33,
            'tax' => 217.71,
            'total' => 2724.07,
            'deposit_due' => 544.81,
            'deposit_paid' => 0.00,
            'amount_paid_total' => 0.00,
            'balance' => 2724.07,
        ]);

        $reservation->items()->create([
            'name_snapshot' => 'Hibachi Package',
            'description' => 'Test package',
            'unit_price_snapshot' => 2124.03,
            'qty' => 1,
            'line_total' => 2124.03,
        ]);

        return $reservation;
    }
}
