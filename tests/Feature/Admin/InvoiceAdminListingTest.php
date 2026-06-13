<?php

namespace Tests\Feature\Admin;

use App\Models\AdminSetting;
use App\Models\CustomTaxRate;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceAdminListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_index_lists_standalone_and_reservation_rows_from_unified_query(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        Reservation::query()->create([
            'code' => 'RSV-INV-001',
            'invoice_number' => 401,
            'invoice_status' => 'pending',
            'status' => 'confirmed',
            'guests' => 12,
            'date' => now()->addDays(10)->toDateString(),
            'time' => '18:00:00',
            'customer_name' => 'Reservation Customer',
            'email' => 'reservation@example.com',
            'subtotal' => 500,
            'gratuity' => 90,
            'tax' => 51.25,
            'total' => 641.25,
            'deposit_due' => 128.25,
            'deposit_paid' => 0,
            'amount_paid_total' => 0,
            'balance' => 641.25,
        ]);

        Invoice::query()->create([
            'invoice_number' => 'INV-900001',
            'customer_name' => 'Standalone Customer',
            'customer_email' => 'standalone@example.com',
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'payment_collection' => 'request_payment',
            'subtotal' => 120,
            'tax' => 12,
            'total' => 132,
            'amount_paid' => 0,
            'balance' => 132,
            'memo' => 'Standalone test invoice',
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/invoices');

        $response->assertOk();
        $response->assertSee('reservation@example.com');
        $response->assertSee('standalone@example.com');
        $response->assertSee('INV-900001');
        $response->assertSee('401');
    }

    public function test_invoice_creation_uses_custom_tax_rate_for_matching_city(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        CustomTaxRate::create([
            'city_name' => 'Corona',
            'tax_rate' => '10.15',
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user, 'web')
            ->post(route('admin.invoices.store'), $this->invoicePayload([
                'customer_city' => 'corona',
                'tax_rate' => '2.00',
            ]))
            ->assertRedirect();

        $invoice = Invoice::query()->firstOrFail();

        $this->assertSame('Corona', $invoice->customer_city);
        $this->assertSame('10.15', $invoice->tax_rate);
        $this->assertSame('101.50', $invoice->tax);
        $this->assertSame('1101.50', $invoice->total);
    }

    public function test_invoice_creation_falls_back_to_default_tax_rate_when_city_has_no_custom_tax(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        AdminSetting::storeGroupValues('business_profile', [
            'default_tax_rate' => '8.75',
        ]);

        $this->actingAs($user, 'web')
            ->post(route('admin.invoices.store'), $this->invoicePayload([
                'customer_city' => '',
                'customer_address' => '123 Test St, Anaheim, CA 92805',
                'tax_rate' => '2.00',
            ]))
            ->assertRedirect();

        $invoice = Invoice::query()->firstOrFail();

        $this->assertSame('Anaheim', $invoice->customer_city);
        $this->assertSame('8.75', $invoice->tax_rate);
        $this->assertSame('87.50', $invoice->tax);
        $this->assertSame('1087.50', $invoice->total);
    }

    public function test_invoice_creation_ignores_non_numeric_menu_item_ids(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web')
            ->post(route('admin.invoices.store'), $this->invoicePayload([
                'item_description' => ['House Salad'],
                'item_qty' => [1],
                'item_unit_price' => [9],
                'item_menu_id' => ['null'],
            ]))
            ->assertRedirect();

        $invoice = Invoice::query()->with('items')->firstOrFail();

        $this->assertNull($invoice->items->first()->menu_item_id);
        $this->assertSame('House Salad', $invoice->items->first()->description);
    }

    public function test_invoice_tax_includes_travel_fee_and_mandatory_gratuity(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        AdminSetting::storeGroupValues('business_profile', [
            'default_tax_rate' => '10.25',
        ]);

        $this->actingAs($user, 'web')
            ->post(route('admin.invoices.store'), $this->invoicePayload([
                'item_description' => ['Catering package'],
                'item_qty' => [1],
                'item_unit_price' => [820],
                'travel_fee' => '194.40',
                'gratuity_enabled' => '1',
                'gratuity_amount' => '147.60',
                'deposit_enabled' => '1',
                'deposit_amount' => '249.21',
            ]))
            ->assertRedirect();

        $invoice = Invoice::query()->firstOrFail();

        $this->assertSame('194.40', $invoice->travel_fee);
        $this->assertSame('147.60', $invoice->gratuity);
        $this->assertSame('119.11', $invoice->tax);
        $this->assertSame('1281.11', $invoice->total);
        $this->assertSame('249.21', $invoice->amount_paid);
        $this->assertSame('1031.90', $invoice->balance);
    }

    public function test_invoice_pdf_can_be_downloaded(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web')
            ->post(route('admin.invoices.store'), $this->invoicePayload())
            ->assertRedirect();

        $invoice = Invoice::query()->firstOrFail();

        $response = $this->actingAs($user, 'web')
            ->get(route('admin.invoices.download', ['invoice' => $invoice]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('invoice-' . $invoice->invoice_number . '.pdf', (string) $response->headers->get('content-disposition'));
    }

    private function invoicePayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Standalone Customer',
            'customer_email' => 'standalone@example.com',
            'customer_phone' => '951-555-0100',
            'customer_address' => '400 S Vicentia Ave, Corona, CA 92882',
            'customer_city' => 'Corona',
            'due_option' => '30',
            'tax_enabled' => '1',
            'tax_rate' => '0.00',
            'gratuity_enabled' => '0',
            'deposit_enabled' => '0',
            'service_charge_enabled' => '0',
            'discount_enabled' => '0',
            'item_description' => ['Hibachi Dinner'],
            'item_qty' => [10],
            'item_unit_price' => [100],
            'item_menu_id' => [null],
        ], $overrides);
    }
}
