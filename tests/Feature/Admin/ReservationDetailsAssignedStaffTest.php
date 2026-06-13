<?php

namespace Tests\Feature\Admin;

use App\Models\Reservation;
use App\Models\ScheduleAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationDetailsAssignedStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_details_shows_staff_assigned_from_schedule(): void
    {
        $admin = $this->adminUser();
        $chef1 = $this->chef('Angel');
        $chef2 = $this->chef('Ariel');
        $chef3 = $this->chef('Marco H');
        $chef4 = $this->chef('Luis');
        $reservation = $this->reservation();

        ScheduleAssignment::query()->create([
            'reservation_id' => $reservation->id,
            'user_id' => $chef1->id,
            'chef_1_id' => $chef1->id,
            'chef_2_id' => $chef2->id,
            'chef_3_id' => $chef3->id,
            'chef_4_id' => $chef4->id,
            'extra_chef_ids' => ['4' => $chef4->id],
            'van' => '5',
            'week_start_date' => '2026-06-21',
        ]);

        $response = $this->actingAs($admin, 'web')->get(route('admin.reservations.show', ['id' => $reservation->id]));

        $response->assertOk();
        $response->assertSee('Assigned Staff');
        $response->assertSee('Chef 1');
        $response->assertSee('Angel');
        $response->assertSee('Chef 2');
        $response->assertSee('Ariel');
        $response->assertSee('Chef 3');
        $response->assertSee('Marco H');
        $response->assertDontSee('Chef 4');
        $response->assertDontSee('Luis');
        $response->assertSee('Van');
        $response->assertSee('5');
    }

    public function test_reservation_details_handles_missing_schedule_assignment(): void
    {
        $admin = $this->adminUser();
        $reservation = $this->reservation();

        $response = $this->actingAs($admin, 'web')->get(route('admin.reservations.show', ['id' => $reservation->id]));

        $response->assertOk();
        $response->assertSee('Assigned Staff');
        $response->assertSee('No staff assigned yet.');
    }

    public function test_schedule_assignment_update_endpoint_feeds_reservation_details(): void
    {
        $admin = $this->adminUser();
        $chef1 = $this->chef('Angel');
        $chef2 = $this->chef('Ariel');
        $replacement = $this->chef('Carlos');
        $reservation = $this->reservation();

        $this->actingAs($admin, 'web')->postJson(route('admin.schedule.assignment.update', ['reservation' => $reservation]), [
            'field' => 'chef_1_id',
            'value' => $chef1->id,
        ])->assertOk()->assertJson(['ok' => true]);

        $this->actingAs($admin, 'web')->postJson(route('admin.schedule.assignment.update', ['reservation' => $reservation]), [
            'field' => 'chef_2_id',
            'value' => $chef2->id,
        ])->assertOk()->assertJson(['ok' => true]);

        $this->actingAs($admin, 'web')->postJson(route('admin.schedule.assignment.update', ['reservation' => $reservation]), [
            'field' => 'van',
            'value' => '5',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->actingAs($admin, 'web')->get(route('admin.reservations.show', ['id' => $reservation->id]))
            ->assertOk()
            ->assertSee('Angel')
            ->assertSee('Ariel')
            ->assertSee('Van')
            ->assertSee('5');

        $this->actingAs($admin, 'web')->postJson(route('admin.schedule.assignment.update', ['reservation' => $reservation]), [
            'field' => 'chef_2_id',
            'value' => $replacement->id,
        ])->assertOk()->assertJson(['ok' => true]);

        $updatedDetails = $this->actingAs($admin, 'web')->get(route('admin.reservations.show', ['id' => $reservation->id]));

        $updatedDetails->assertOk();
        $updatedDetails->assertSee('Angel');
        $updatedDetails->assertSee('Carlos');
        $updatedDetails->assertDontSee('Ariel');
    }

    public function test_reservation_details_uses_admin_menu_config_for_line_item_search_and_save(): void
    {
        $admin = $this->adminUser();
        $reservation = $this->reservation();

        $response = $this->actingAs($admin, 'web')->get(route('admin.reservations.show', ['id' => $reservation->id]));

        $response->assertOk();
        $response->assertSee('House Salad', false);
        $response->assertSee('STARTERS', false);
        $response->assertSee('Garlic Scallops', false);
        $response->assertSee('SEAFOOD', false);
        $response->assertSee('Chicken', false);
        $response->assertSee('Shrimp', false);
        $response->assertSee('COMBINATIONS', false);

        $this->actingAs($admin, 'web')->post(route('admin.reservations.items.add', ['id' => $reservation->id]), [
            'menu_key' => 'chicken-shrimp',
            'qty' => 2,
            'description' => 'From admin menu',
        ])->assertRedirect(route('admin.reservations.show', ['id' => $reservation->id]));

        $saved = $reservation->fresh('items')->items->first();

        $this->assertNotNull($saved);
        $this->assertNull($saved->menu_id);
        $this->assertSame('Chicken & Shrimp', $saved->name_snapshot);
        $this->assertSame(75.0, (float) $saved->unit_price_snapshot);
        $this->assertSame(2, (int) $saved->qty);
        $this->assertSame(150.0, (float) $saved->line_total);
    }

    public function test_reservation_line_items_autosave_json_flow_updates_items_and_totals(): void
    {
        $admin = $this->adminUser();
        $reservation = $this->reservation();
        $price = 75.0;

        $firstAdd = $this->actingAs($admin, 'web')->postJson(route('admin.reservations.items.add', ['id' => $reservation->id]), [
            'menu_key' => 'chicken-shrimp',
            'qty' => 1,
        ])->assertOk()->json();

        $this->assertCount(1, $firstAdd['items']);
        $this->assertSame('Chicken & Shrimp', $firstAdd['items'][0]['name']);
        $this->assertSame($price, (float) $firstAdd['items'][0]['price']);
        $this->assertSame($price, (float) $firstAdd['totals']['subtotal']);

        $secondAdd = $this->actingAs($admin, 'web')->postJson(route('admin.reservations.items.add', ['id' => $reservation->id]), [
            'menu_key' => 'chicken-shrimp',
            'qty' => 1,
        ])->assertOk()->json();

        $this->assertCount(1, $secondAdd['items']);
        $this->assertSame(2, (int) $secondAdd['items'][0]['qty']);
        $this->assertSame(round($price * 2, 2), (float) $secondAdd['totals']['subtotal']);

        $itemId = $secondAdd['items'][0]['id'];
        $updated = $this->actingAs($admin, 'web')->postJson(route('admin.reservations.items.update', ['id' => $reservation->id]), [
            'items' => [$itemId => 3],
            'prices' => [$itemId => 9.5],
            'desc' => [$itemId => 'Auto saved'],
        ])->assertOk()->json();

        $this->assertSame(3, (int) $updated['items'][0]['qty']);
        $this->assertSame(9.5, (float) $updated['items'][0]['price']);
        $this->assertSame(28.5, (float) $updated['totals']['subtotal']);

        $custom = $this->actingAs($admin, 'web')->postJson(route('admin.reservations.items.add', ['id' => $reservation->id]), [
            'custom_name' => 'Chicken And Lamb',
            'custom_price' => 0,
            'qty' => 1,
        ])->assertOk()->json();

        $this->assertCount(2, $custom['items']);
        $this->assertTrue(collect($custom['items'])->contains(fn ($item) => $item['name'] === 'Chicken And Lamb'));

        $deleted = $this->actingAs($admin, 'web')->postJson(route('admin.reservations.items.delete', [
            'id' => $reservation->id,
            'itemId' => $itemId,
        ]))->assertOk()->json();

        $this->assertCount(1, $deleted['items']);
        $this->assertSame(0.0, (float) $deleted['totals']['subtotal']);
    }

    private function adminUser(): User
    {
        return User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
    }

    private function chef(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'staff_type' => 'Chef',
            'role' => 'staff',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
    }

    private function reservation(): Reservation
    {
        return Reservation::query()->create([
            'code' => 'RSV-STAFF',
            'status' => 'confirmed',
            'guests' => 15,
            'date' => '2026-06-27',
            'time' => '15:00:00',
            'customer_name' => 'Andrea Carrillo',
            'email' => 'andrea@example.com',
            'phone' => '555-0100',
            'address' => '1521 Woodbine Lane',
            'city' => 'City of Industry',
            'zip_code' => '92331',
            'event_type' => 'Birthday',
            'setup_color' => 'Black & Navy Blue',
            'subtotal' => 0,
            'tax' => 0,
            'travel_fee' => 0,
            'discount' => 0,
            'total' => 0,
            'deposit_due' => 0,
            'deposit_paid' => 0,
            'balance' => 0,
        ]);
    }

}
