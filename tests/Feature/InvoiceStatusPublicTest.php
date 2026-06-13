<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceStatusPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_invoice_status_uses_token_without_login_and_hides_private_notes(): void
    {
        $reservation = Reservation::query()->create([
            'code' => 'RSV-TOKEN',
            'invoice_number' => 1234,
            'public_invoice_token' => 'publictoken1234567890publictoken1234567890',
            'invoice_status' => 'pending',
            'status' => 'confirmed',
            'guests' => 12,
            'date' => '2026-08-15',
            'time' => '18:00:00',
            'customer_name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'phone' => '555-111-2222',
            'address' => '123 Private Street',
            'notes' => 'Private admin note',
            'subtotal' => 1000.00,
            'tax' => 100.00,
            'gratuity' => 180.00,
            'travel_fee' => 20.00,
            'total' => 1323.00,
            'deposit_paid' => 300.00,
            'amount_paid_total' => 300.00,
            'balance' => 1023.00,
        ]);

        $response = $this->get(route('invoice.status.public', ['token' => $reservation->public_invoice_token]));

        $response->assertOk();
        $response->assertSee('Invoice payment status');
        $response->assertSee('#1234');
        $response->assertSee('08/15/2026');
        $response->assertSee('Jane Customer');
        $response->assertSee('$1,323.00');
        $response->assertSee('$300.00');
        $response->assertSee('$1,023.00');
        $response->assertSee('Partially Paid');
        $response->assertDontSee('Private admin note');
        $response->assertDontSee('123 Private Street');
        $response->assertDontSee('jane@example.com');
    }

    public function test_invalid_public_invoice_token_returns_elegant_404(): void
    {
        $response = $this->get('/invoice/status/not-a-real-public-token-1234567890');

        $response->assertNotFound();
        $response->assertSee('Invoice status not found');
    }

    public function test_admin_invoice_generates_missing_token_and_printable_qr(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $reservation = Reservation::query()->create([
            'code' => 'RSV-QR',
            'invoice_number' => 2222,
            'public_invoice_token' => null,
            'invoice_status' => 'pending',
            'status' => 'confirmed',
            'guests' => 10,
            'date' => '2026-09-10',
            'time' => '17:00:00',
            'customer_name' => 'QR Customer',
            'subtotal' => 500.00,
            'tax' => 50.00,
            'gratuity' => 90.00,
            'travel_fee' => 0.00,
            'total' => 640.00,
            'deposit_paid' => 0.00,
            'amount_paid_total' => 0.00,
            'balance' => 640.00,
        ]);
        DB::table('reservations')
            ->where('id', $reservation->id)
            ->update(['public_invoice_token' => null]);
        $reservation->refresh();

        $response = $this->actingAs($user, 'web')
            ->get(route('admin.reservations.invoice', ['id' => $reservation->id]));

        $token = $reservation->fresh()->public_invoice_token;

        $response->assertOk();
        $this->assertNotEmpty($token);
        $response->assertSee('Scan to view invoice status');
        $response->assertSee(route('invoice.status.public', ['token' => $token]));
        $response->assertSee('<svg', false);
    }
}
