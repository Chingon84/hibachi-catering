<?php

namespace Tests\Feature\Admin;

use App\Models\AdminSetting;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingsMenuPricingRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_menu_pricing_rules_page(): void
    {
        $admin = $this->owner();

        $this->actingAs($admin, 'web')
            ->get(route('admin.settings.menu-pricing-rules'))
            ->assertOk()
            ->assertSee('Menu & Pricing Rules')
            ->assertSee('Manage menu availability, pricing policies, approval rules, and future bulk pricing controls used across Hibachi Catering.')
            ->assertSee('Reset to Recommended Defaults');
    }

    public function test_menu_pricing_rules_settings_can_be_saved(): void
    {
        $admin = $this->owner();
        $menu = $this->seedMenu();

        $payload = [
            'enable_menu_price_editing' => '1',
            'require_manager_approval_for_price_changes' => '1',
            'allow_category_editing' => '1',
            'allow_item_availability_changes' => '1',
            'allow_deleting_menu_items' => '0',
            'require_confirmation_before_deleting_item' => '1',
            'active_menu_categories' => ['STARTERS', 'SEAFOOD'],
            'hide_unavailable_items_from_booking_forms' => '1',
            'show_unavailable_items_in_admin' => '1',
            'allow_temporary_unavailable_status' => '1',
            'allow_seasonal_items' => '1',
            'require_unavailable_reason' => '1',
            'require_reason_for_price_changes' => '1',
            'store_price_change_history' => '1',
            'notify_admin_when_price_changes' => '1',
            'lock_prices_after_invoice_is_created' => '1',
            'lock_prices_after_reservation_is_confirmed' => '1',
            'allow_custom_price_override' => '1',
            'require_manager_approval_for_custom_price_override' => '1',
            'bulk_increase_type' => 'fixed',
            'bulk_apply_to_category' => 'STARTERS',
            'bulk_price_amount' => '9.50',
            'preview_changes_before_applying' => '1',
            'require_final_confirmation' => '1',
            'enable_catering_menu' => '1',
            'allow_per_person_pricing' => '1',
            'allow_add_on_pricing' => '1',
            'allow_package_pricing' => '1',
            'show_menu_item_descriptions' => '1',
            'show_menu_item_images' => '1',
            'price_changes_require_approval_from_role' => 'admin',
            'menu_item_deletion_requires_approval_from_role' => 'owner',
            'new_menu_item_requires_approval' => '1',
            'approval_status_options' => ['draft', 'approved'],
            'approval_note_field' => 'Manager note required.',
        ];

        $this->actingAs($admin, 'web')
            ->post(route('admin.settings.menu-pricing-rules.update'), $payload)
            ->assertRedirect(route('admin.settings.menu-pricing-rules'))
            ->assertSessionHas('ok', 'Menu & pricing rules saved. No live menu prices were changed.');

        $this->assertDatabaseHas('admin_settings', [
            'group_name' => 'menu_pricing_rules',
            'key' => 'bulk_price_amount',
            'value' => '9.50',
        ]);

        $stored = AdminSetting::valuesForGroup('menu_pricing_rules');
        $this->assertSame('STARTERS,SEAFOOD', $stored['active_menu_categories']);
        $this->assertSame('draft,approved', $stored['approval_status_options']);
        $this->assertSame('6.00', $menu->fresh()->price);
    }

    public function test_menu_pricing_rules_validation_rejects_invalid_values(): void
    {
        $admin = $this->owner();

        $this->actingAs($admin, 'web')
            ->from(route('admin.settings.menu-pricing-rules'))
            ->post(route('admin.settings.menu-pricing-rules.update'), [
                'enable_menu_price_editing' => '2',
                'require_manager_approval_for_price_changes' => '1',
                'allow_category_editing' => '1',
                'allow_item_availability_changes' => '1',
                'allow_deleting_menu_items' => '0',
                'require_confirmation_before_deleting_item' => '1',
                'active_menu_categories' => ['bad-category'],
                'hide_unavailable_items_from_booking_forms' => '1',
                'show_unavailable_items_in_admin' => '1',
                'allow_temporary_unavailable_status' => '1',
                'allow_seasonal_items' => '1',
                'require_unavailable_reason' => '1',
                'require_reason_for_price_changes' => '1',
                'store_price_change_history' => '1',
                'notify_admin_when_price_changes' => '1',
                'lock_prices_after_invoice_is_created' => '1',
                'lock_prices_after_reservation_is_confirmed' => '1',
                'allow_custom_price_override' => '1',
                'require_manager_approval_for_custom_price_override' => '1',
                'bulk_increase_type' => 'percentage',
                'bulk_apply_to_category' => 'wrong',
                'bulk_price_amount' => '150',
                'preview_changes_before_applying' => '1',
                'require_final_confirmation' => '1',
                'enable_catering_menu' => '1',
                'allow_per_person_pricing' => '1',
                'allow_add_on_pricing' => '1',
                'allow_package_pricing' => '1',
                'show_menu_item_descriptions' => '1',
                'show_menu_item_images' => '1',
                'price_changes_require_approval_from_role' => 'bad-role',
                'menu_item_deletion_requires_approval_from_role' => 'bad-role',
                'new_menu_item_requires_approval' => '1',
                'approval_status_options' => ['wrong-status'],
            ])
            ->assertRedirect(route('admin.settings.menu-pricing-rules'))
            ->assertSessionHasErrors([
                'enable_menu_price_editing',
                'active_menu_categories.0',
                'bulk_apply_to_category',
                'bulk_price_amount',
                'price_changes_require_approval_from_role',
                'menu_item_deletion_requires_approval_from_role',
                'approval_status_options.0',
            ]);
    }

    public function test_menu_pricing_rules_can_be_reset_to_recommended_defaults(): void
    {
        $admin = $this->owner();

        AdminSetting::storeGroupValues('menu_pricing_rules', [
            'allow_deleting_menu_items' => '1',
            'price_changes_require_approval_from_role' => 'owner',
        ]);

        $this->actingAs($admin, 'web')
            ->post(route('admin.settings.menu-pricing-rules.reset'))
            ->assertRedirect(route('admin.settings.menu-pricing-rules'))
            ->assertSessionHas('ok', 'Menu & pricing rules reset to recommended defaults.');

        $this->assertDatabaseHas('admin_settings', [
            'group_name' => 'menu_pricing_rules',
            'key' => 'allow_deleting_menu_items',
            'value' => '0',
        ]);
        $this->assertDatabaseHas('admin_settings', [
            'group_name' => 'menu_pricing_rules',
            'key' => 'price_changes_require_approval_from_role',
            'value' => 'admin',
        ]);
    }

    public function test_user_without_menu_manage_cannot_modify_menu_pricing_rules(): void
    {
        $user = User::factory()->create([
            'role' => 'custom-settings',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        DB::table('role_permissions')->insert([
            ['role' => 'custom-settings', 'permission' => 'settings.view'],
        ]);

        $this->actingAs($user, 'web')
            ->post(route('admin.settings.menu-pricing-rules.update'), [
                'enable_menu_price_editing' => '1',
            ])
            ->assertForbidden();
    }

    public function test_menu_pricing_rules_page_uses_real_menu_categories(): void
    {
        $admin = $this->owner();
        $this->seedMenu();

        $response = $this->actingAs($admin, 'web')->get(route('admin.settings.menu-pricing-rules'));

        $response->assertOk()
            ->assertSee('STARTERS')
            ->assertSee('SEAFOOD')
            ->assertDontSee('Kids Menu');
    }

    public function test_bulk_price_preview_shows_live_menu_changes_without_applying(): void
    {
        $admin = $this->owner();
        $menu = $this->seedMenu();

        $this->actingAs($admin, 'web')
            ->post(route('admin.settings.menu-pricing-rules.preview'), [
                'bulk_increase_type' => 'fixed',
                'bulk_apply_to_category' => 'STARTERS',
                'bulk_price_amount' => '2.00',
                'preview_changes_before_applying' => '1',
                'require_final_confirmation' => '1',
            ])
            ->assertRedirect(route('admin.settings.menu-pricing-rules'))
            ->assertSessionHas('ok', 'Preview generated. No live menu prices were changed.');

        $response = $this->actingAs($admin, 'web')->get(route('admin.settings.menu-pricing-rules'));

        $response->assertOk()
            ->assertSee('Preview Changes')
            ->assertSee('Gyoza (2 pc)')
            ->assertSee('$6.00', false)
            ->assertSee('$8.00', false)
            ->assertSee('+2.00');

        $this->assertSame('6.00', $menu->fresh()->price);
    }

    public function test_bulk_price_apply_updates_only_selected_live_menu_category(): void
    {
        $admin = $this->owner();
        $starter = $this->seedMenu();
        $seafood = Menu::query()->where('item_key', 'garlic-scallops')->firstOrFail();

        $this->actingAs($admin, 'web')
            ->post(route('admin.settings.menu-pricing-rules.apply'), [
                'bulk_increase_type' => 'fixed',
                'bulk_apply_to_category' => 'STARTERS',
                'bulk_price_amount' => '2.00',
                'preview_changes_before_applying' => '1',
                'require_final_confirmation' => '1',
                'bulk_apply_confirmation' => '1',
            ])
            ->assertRedirect(route('admin.settings.menu-pricing-rules'))
            ->assertSessionHas('ok', 'Bulk price update applied successfully. 1 menu items were updated.');

        $this->assertSame('8.00', $starter->fresh()->price);
        $this->assertSame('72.00', $seafood->fresh()->price);
        $this->assertSame('Gyoza (2 pc)', $starter->fresh()->name);
        $this->assertSame('STARTERS', $starter->fresh()->category);
    }

    public function test_bulk_price_apply_requires_confirmation(): void
    {
        $admin = $this->owner();
        $this->seedMenu();

        $this->actingAs($admin, 'web')
            ->from(route('admin.settings.menu-pricing-rules'))
            ->post(route('admin.settings.menu-pricing-rules.apply'), [
                'bulk_increase_type' => 'fixed',
                'bulk_apply_to_category' => 'STARTERS',
                'bulk_price_amount' => '2.00',
                'preview_changes_before_applying' => '1',
                'require_final_confirmation' => '1',
            ])
            ->assertRedirect(route('admin.settings.menu-pricing-rules'))
            ->assertSessionHasErrors(['bulk_apply_confirmation']);
    }

    private function seedMenu(): Menu
    {
        Menu::query()->create([
            'item_key' => 'gyoza-2pc',
            'name' => 'Gyoza (2 pc)',
            'description' => 'Starter item',
            'category' => 'STARTERS',
            'category_sort' => 0,
            'price' => 6.00,
            'is_active' => true,
            'sort' => 0,
        ]);

        Menu::query()->create([
            'item_key' => 'garlic-scallops',
            'name' => 'Garlic Scallops (6oz)',
            'description' => 'Seafood item',
            'category' => 'SEAFOOD',
            'category_sort' => 1,
            'price' => 72.00,
            'is_active' => true,
            'sort' => 0,
        ]);

        return Menu::query()->where('item_key', 'gyoza-2pc')->firstOrFail();
    }

    private function owner(): User
    {
        return User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
    }
}
