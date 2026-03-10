<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\Van;
use App\Models\VanLoadout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_inventory_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.inventory.dashboard'));

        $response->assertOk();
        $response->assertSee('Inventory');
    }

    public function test_recording_restock_creates_movement_and_updates_stock(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $item = InventoryItem::create([
            'name' => 'Plates',
            'category' => 'Plates',
            'unit_type' => 'pieces',
            'current_stock' => 5,
            'minimum_stock' => 2,
            'item_type' => 'reusable',
            'allow_van_assignment' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('admin.inventory.movements.store'), [
            'inventory_item_id' => $item->id,
            'movement_type' => 'restock',
            'quantity' => 4,
            'reference_type' => 'purchase',
        ]);

        $response->assertRedirect(route('admin.inventory.movements.index'));
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'movement_type' => 'restock',
            'quantity' => 4,
            'previous_stock' => 5,
            'new_stock' => 9,
        ]);
        $this->assertSame('9.00', $item->fresh()->current_stock);
    }

    public function test_stock_movement_cannot_reduce_below_zero_without_override(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $item = InventoryItem::create([
            'name' => 'Napkins',
            'category' => 'Napkins',
            'unit_type' => 'packs',
            'current_stock' => 1,
            'minimum_stock' => 1,
            'item_type' => 'consumable',
            'allow_van_assignment' => false,
            'status' => 'active',
        ]);

        $response = $this->from(route('admin.inventory.movements.create'))->actingAs($user)->post(route('admin.inventory.movements.store'), [
            'inventory_item_id' => $item->id,
            'movement_type' => 'assigned_to_event',
            'quantity' => 3,
        ]);

        $response->assertRedirect(route('admin.inventory.movements.create'));
        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertSame('1.00', $item->fresh()->current_stock);
    }

    public function test_van_profile_uses_latest_loadout_snapshot(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $checker = User::factory()->create([
            'role' => 'staff',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $van = Van::create([
            'van_number' => 4,
            'name' => 'Van 4',
            'code' => 'VAN-4',
            'status' => 'active',
        ]);

        VanLoadout::create([
            'van_id' => $van->id,
            'van_status' => 'clean',
            'grills' => [2, 7, 9],
            'tables_count' => 4,
            'chairs_count' => 24,
            'propane_tanks_count' => 2,
            'dolly_count' => 1,
            'straps_count' => 8,
            'floor_mats_count' => 3,
            'trash_cans_count' => 2,
            'heaters_count' => 1,
            'buffet_warmers_count' => 3,
            'checked_by_user_id' => $checker->id,
            'checked_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.inventory.vans.show', $van->id));

        $response->assertOk();
        $response->assertSee('Van Loadout');
        $response->assertSee('Grill #2, Grill #7, Grill #9');
        $response->assertSee('Ready for next event');
        $response->assertSee($checker->name);
    }

    public function test_van_inventory_index_lists_all_vans_on_same_page(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        foreach (range(1, 14) as $number) {
            Van::create([
                'van_number' => $number,
                'name' => 'Van ' . $number,
                'status' => 'active',
            ]);
        }

        $response = $this->actingAs($user)->get(route('admin.inventory.vans.index'));

        $response->assertOk();
        $response->assertSee('Update Van Loadout');
        $response->assertSee('Van 1');
        $response->assertSee('Van 14');
    }

    public function test_saving_van_loadout_creates_new_history_snapshot(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $van = Van::create([
            'van_number' => 7,
            'name' => 'Van 7',
            'code' => 'VAN-7',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('admin.inventory.vans.loadout.store'), [
            'van_number' => 7,
            'van_status' => 'dirty',
            'grills' => [1, 4, 10],
            'tables_count' => 3,
            'chairs_count' => 18,
            'propane_tanks_count' => 2,
            'dolly_count' => 1,
            'straps_count' => 6,
            'floor_mats_count' => 2,
            'trash_cans_count' => 1,
            'heaters_count' => 0,
            'buffet_warmers_count' => 2,
            'checked_by_user_id' => $user->id,
            'loaded_by_user_id' => $user->id,
            'notes' => 'Loaded after Saturday cleanup.',
        ]);

        $response->assertRedirect(route('admin.inventory.vans.index'));
        $this->assertDatabaseHas('van_loadouts', [
            'van_id' => $van->id,
            'van_status' => 'dirty',
            'tables_count' => 3,
            'chairs_count' => 18,
            'checked_by_user_id' => $user->id,
        ]);
        $this->assertSame([1, 4, 10], $van->fresh()->currentLoadout->grills);
    }
}
