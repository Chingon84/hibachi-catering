<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReservationFlowSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_step_one_clears_stale_paid_reservation_from_session(): void
    {
        $paid = $this->paidReservation();

        $this->withSession([
            'resv' => [
                'reservation_id' => $paid->id,
                'payment_status' => 'paid',
                'deposit_amount' => 200.00,
                'stripe_session_id' => 'cs_old_paid',
                'checkout_session_id' => 'cs_old_paid',
                'payment_intent_id' => 'pi_old_paid',
            ],
        ])->get(route('reservations.step', ['step' => 1]))
            ->assertOk()
            ->assertSessionMissing('resv');
    }

    public function test_step_one_submit_replaces_stale_paid_reservation_with_clean_reservation(): void
    {
        $paid = $this->paidReservation();
        $eventDate = '2026-12-04';

        DB::table('timeslots')->insert([
            'date' => $eventDate,
            'time' => '15:00:00',
            'capacity' => 50,
            'is_open' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'resv' => [
                'reservation_id' => $paid->id,
                'payment_status' => 'paid',
                'deposit_amount' => 200.00,
                'stripe_session_id' => 'cs_old_paid',
                'checkout_session_id' => 'cs_old_paid',
                'payment_intent_id' => 'pi_old_paid',
            ],
        ])->post(route('reservations.submit', ['step' => 1]), [
            'guests' => 10,
            'date' => $eventDate,
            'time' => '15:00',
        ])->assertRedirect(route('reservations.step', ['step' => 2]));

        $newReservationId = (int) session('resv.reservation_id');
        $this->assertNotSame($paid->id, $newReservationId);

        $newReservation = Reservation::findOrFail($newReservationId);
        $this->assertSame(0.00, (float) $newReservation->deposit_paid);
        $this->assertSame(0.00, (float) $newReservation->amount_paid_total);
        $this->assertSame(0.00, (float) $newReservation->balance);
        $this->assertSame('pending', $newReservation->invoice_status);

        $paid->refresh();
        $this->assertSame(200.00, (float) $paid->deposit_paid);
        $this->assertSame('confirmed', $paid->status);
    }

    public function test_step_three_rejects_quantity_above_menu_limit(): void
    {
        $reservation = $this->draftReservation(18);

        $this->withSession([
            'resv' => [
                'reservation_id' => $reservation->id,
                'guests' => 18,
                'travel_fee' => 0,
            ],
        ])->from(route('reservations.step', ['step' => 3]))
            ->post(route('reservations.submit', ['step' => 3]), [
                'items' => [
                    'chicken-shrimp' => 10001,
                ],
            ])
            ->assertRedirect(route('reservations.step', ['step' => 3]))
            ->assertSessionHasErrors('items.chicken-shrimp');

        $this->assertSame(0, $reservation->items()->count());
    }

    public function test_step_three_allows_selection_that_does_not_match_guest_count(): void
    {
        $reservation = $this->draftReservation(18);

        $this->withSession([
            'resv' => [
                'reservation_id' => $reservation->id,
                'guests' => 18,
                'travel_fee' => 0,
            ],
        ])->from(route('reservations.step', ['step' => 3]))
            ->post(route('reservations.submit', ['step' => 3]), [
                'items' => [
                    'gyoza-2pc' => 18,
                ],
            ])
            ->assertRedirect(route('reservations.step', ['step' => 4]));

        $this->assertSame(18, (int) $reservation->items()->where('name_snapshot', 'Gyoza (2 pc)')->value('qty'));
    }

    public function test_step_three_accepts_quantity_up_to_menu_limit(): void
    {
        $reservation = $this->draftReservation(18);

        $this->withSession([
            'resv' => [
                'reservation_id' => $reservation->id,
                'guests' => 18,
                'travel_fee' => 0,
            ],
        ])->post(route('reservations.submit', ['step' => 3]), [
            'items' => [
                'chicken-shrimp' => 10000,
                'gyoza-2pc' => 2,
            ],
        ])->assertRedirect(route('reservations.step', ['step' => 4]));

        $reservation->refresh();
        $this->assertSame(10000, (int) $reservation->items()->where('name_snapshot', 'Chicken & Shrimp')->value('qty'));
        $this->assertSame(2, (int) $reservation->items()->where('name_snapshot', 'Gyoza (2 pc)')->value('qty'));
        $this->assertSame(750012.00, (float) $reservation->subtotal);
        $this->assertSame(975728.11, (float) $reservation->total);
    }

    public function test_step_three_prefills_saved_menu_selection_after_returning_from_payment(): void
    {
        $reservation = $this->draftReservation(18);

        $this->withSession([
            'resv' => [
                'reservation_id' => $reservation->id,
                'guests' => 18,
                'travel_fee' => 187.50,
            ],
        ])->post(route('reservations.submit', ['step' => 3]), [
            'items' => [
                'chicken-shrimp' => 12,
                'gyoza-2pc' => 6,
            ],
        ])->assertRedirect(route('reservations.step', ['step' => 4]));

        $response = $this->get(route('reservations.step', ['step' => 3]))
            ->assertOk();

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/name="items\[chicken-shrimp\]"[^>]*value="12"/s', $html);
        $this->assertMatchesRegularExpression('/name="items\[gyoza-2pc\]"[^>]*value="6"/s', $html);
    }

    private function paidReservation(): Reservation
    {
        $reservation = Reservation::query()->create([
            'code' => 'RSV-OLDPAID',
            'status' => 'confirmed',
            'invoice_status' => 'paid',
            'guests' => 10,
            'date' => now()->addMonth()->toDateString(),
            'time' => '15:00:00',
            'customer_name' => 'Old Paid Customer',
            'phone' => '5550001111',
            'email' => 'old-paid@example.com',
            'subtotal' => 1000.00,
            'travel_fee' => 0.00,
            'gratuity' => 180.00,
            'tax' => 102.50,
            'total' => 1300.95,
            'deposit_due' => 200.00,
            'deposit_paid' => 200.00,
            'amount_paid_total' => 200.00,
            'balance' => 1082.50,
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'provider' => 'stripe',
            'type' => 'deposit',
            'amount' => 200.00,
            'currency' => 'USD',
            'status' => 'succeeded',
            'transaction_id' => 'pi_old_paid',
            'stripe_session_id' => 'cs_old_paid',
            'stripe_payment_intent_id' => 'pi_old_paid',
            'payload_json' => json_encode(['metadata' => ['reservation_id' => (string) $reservation->id, 'payment_type' => 'deposit']]),
        ]);

        return $reservation;
    }

    private function draftReservation(int $guests): Reservation
    {
        return Reservation::query()->create([
            'code' => 'RSV-DRAFT-' . $guests,
            'status' => 'draft',
            'invoice_status' => 'pending',
            'guests' => $guests,
            'date' => now()->addMonth()->toDateString(),
            'time' => '15:00:00',
            'customer_name' => 'Draft Customer',
            'phone' => '5550002222',
            'email' => 'draft@example.com',
            'subtotal' => 0.00,
            'travel_fee' => 0.00,
            'gratuity' => 0.00,
            'tax' => 0.00,
            'total' => 0.00,
            'deposit_due' => 0.00,
            'deposit_paid' => 0.00,
            'amount_paid_total' => 0.00,
            'balance' => 0.00,
        ]);
    }
}
