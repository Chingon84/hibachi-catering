<?php

namespace Tests\Feature\Admin;

use App\Models\AdminSetting;
use App\Models\CustomTaxRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingsBusinessProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_business_profile_settings_page(): void
    {
        $admin = $this->owner();

        $this->actingAs($admin, 'web')
            ->get(route('admin.settings.business-profile'))
            ->assertOk()
            ->assertSee('Business Profile')
            ->assertSee('Manage the company identity, contact details, location defaults, and brand information used across the admin panel.')
            ->assertSee('Custom Tax')
            ->assertSee('Manage custom tax rates by city')
            ->assertSee('Save Changes');
    }

    public function test_business_profile_settings_can_be_saved(): void
    {
        $admin = $this->owner();

        $payload = [
            'business_name' => 'Hibachi Catering West',
            'legal_business_name' => 'Hibachi Catering LLC',
            'dba_name' => 'Hibachi Catering',
            'hq_name' => 'Corona HQ',
            'business_phone' => '(951) 555-0100',
            'business_email' => 'office@example.com',
            'website' => 'https://hibachicatering.test',
            'business_address' => '123 Main St',
            'city' => 'Corona',
            'state' => 'CA',
            'zip_code' => '92883',
            'country' => 'United States',
            'timezone' => 'America/Los_Angeles',
            'default_tax_rate' => '10.25',
            'admin_notes' => 'Internal use only.',
        ];

        $this->actingAs($admin, 'web')
            ->post(route('admin.settings.business-profile.update'), $payload)
            ->assertRedirect(route('admin.settings.business-profile'))
            ->assertSessionHas('ok', 'Business profile updated.');

        $this->assertDatabaseHas('admin_settings', [
            'group_name' => 'business_profile',
            'key' => 'business_name',
            'value' => 'Hibachi Catering West',
        ]);

        $this->assertSame('office@example.com', AdminSetting::valuesForGroup('business_profile')['business_email']);
    }

    public function test_business_profile_validation_rejects_invalid_email_tax_and_zip(): void
    {
        $admin = $this->owner();

        $this->actingAs($admin, 'web')
            ->from(route('admin.settings.business-profile'))
            ->post(route('admin.settings.business-profile.update'), [
                'business_name' => '',
                'business_email' => 'not-an-email',
                'default_tax_rate' => '16.50',
                'timezone' => 'America/Los_Angeles',
                'zip_code' => 'ABC',
            ])
            ->assertRedirect(route('admin.settings.business-profile'))
            ->assertSessionHasErrors(['business_name', 'business_email', 'default_tax_rate', 'zip_code']);
    }

    public function test_owner_can_sync_custom_tax_rates(): void
    {
        $admin = $this->owner();

        $this->actingAs($admin, 'web')
            ->postJson(route('admin.settings.custom-tax-rates.store'), [
                'rates' => [
                    ['city_name' => 'Corona', 'tax_rate' => '10.15'],
                    ['city_name' => 'Riverside', 'tax_rate' => '8.75'],
                    ['city_name' => 'Los Angeles', 'tax_rate' => '9.50'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Custom tax rates saved successfully.')
            ->assertJsonCount(3, 'rates');

        $this->assertDatabaseHas('custom_tax_rates', [
            'city_name' => 'Corona',
            'city_key' => 'corona',
            'tax_rate' => '10.15',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $corona = CustomTaxRate::query()->where('city_key', 'corona')->firstOrFail();

        $this->actingAs($admin, 'web')
            ->postJson(route('admin.settings.custom-tax-rates.store'), [
                'rates' => [
                    ['id' => $corona->id, 'city_name' => 'Corona', 'tax_rate' => '10.25'],
                    ['city_name' => 'Anaheim', 'tax_rate' => '7.75'],
                ],
            ])
            ->assertOk()
            ->assertJsonCount(2, 'rates');

        $this->assertDatabaseHas('custom_tax_rates', [
            'city_name' => 'Corona',
            'tax_rate' => '10.25',
        ]);
        $this->assertDatabaseHas('custom_tax_rates', [
            'city_name' => 'Anaheim',
            'tax_rate' => '7.75',
        ]);
        $this->assertDatabaseMissing('custom_tax_rates', [
            'city_name' => 'Riverside',
        ]);
    }

    public function test_custom_tax_rates_reject_duplicate_city_names_case_insensitive(): void
    {
        $admin = $this->owner();

        $this->actingAs($admin, 'web')
            ->postJson(route('admin.settings.custom-tax-rates.store'), [
                'rates' => [
                    ['city_name' => 'Corona', 'tax_rate' => '10.15'],
                    ['city_name' => ' corona ', 'tax_rate' => '9.25'],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rates.1.city_name']);
    }

    public function test_owner_can_update_and_delete_individual_custom_tax_rate(): void
    {
        $admin = $this->owner();
        $rate = CustomTaxRate::create([
            'city_name' => 'Corona',
            'tax_rate' => '10.15',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin, 'web')
            ->putJson(route('admin.settings.custom-tax-rates.update', $rate), [
                'city_name' => 'Riverside',
                'tax_rate' => '8.75',
            ])
            ->assertOk()
            ->assertJsonPath('rate.city_name', 'Riverside')
            ->assertJsonPath('rate.tax_rate', '8.75');

        $this->assertDatabaseHas('custom_tax_rates', [
            'id' => $rate->id,
            'city_name' => 'Riverside',
            'city_key' => 'riverside',
            'tax_rate' => '8.75',
        ]);

        $this->actingAs($admin, 'web')
            ->deleteJson(route('admin.settings.custom-tax-rates.destroy', $rate))
            ->assertOk()
            ->assertJsonPath('message', 'Custom tax rate deleted.');

        $this->assertDatabaseMissing('custom_tax_rates', [
            'id' => $rate->id,
        ]);
    }

    public function test_settings_view_user_can_view_but_not_edit_custom_tax_rates(): void
    {
        $viewer = User::factory()->create([
            'role' => 'custom-settings',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        DB::table('role_permissions')->insert([
            ['role' => 'custom-settings', 'permission' => 'settings.view'],
        ]);

        $this->actingAs($viewer, 'web')
            ->get(route('admin.settings.business-profile'))
            ->assertOk()
            ->assertSee('Custom Tax');

        $this->actingAs($viewer, 'web')
            ->getJson(route('admin.settings.custom-tax-rates.index'))
            ->assertOk()
            ->assertJsonPath('can_manage', false);

        $this->actingAs($viewer, 'web')
            ->postJson(route('admin.settings.custom-tax-rates.store'), [
                'rates' => [
                    ['city_name' => 'Corona', 'tax_rate' => '10.15'],
                ],
            ])
            ->assertForbidden();
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
