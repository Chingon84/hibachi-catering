<?php

namespace Tests\Feature\Admin;

use App\Models\AdminSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsReservationRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_reservation_rules_settings_page(): void
    {
        $admin = $this->owner();

        $this->actingAs($admin, 'web')
            ->get(route('admin.settings.reservation-rules'))
            ->assertOk()
            ->assertSee('Reservation Rules')
            ->assertSee('Manage booking limits, deposit requirements, travel fee defaults, gratuity rules, and event handling policies used across Hibachi Catering reservations.')
            ->assertSee('Reset to Recommended Defaults');
    }

    public function test_reservation_rules_settings_can_be_saved(): void
    {
        $admin = $this->owner();

        $payload = [
            'minimum_guests' => 12,
            'maximum_guests' => 250,
            'default_reservation_status' => 'Pending',
            'reservation_cutoff_hours' => 8,
            'allow_same_day_booking' => '1',
            'allow_booking_without_deposit' => '0',
            'deposit_required' => '1',
            'deposit_percentage' => '40.50',
            'minimum_deposit_amount' => '650.00',
            'deposit_due_message' => 'Deposit due before confirmation.',
            'mark_confirmed_after_deposit' => '1',
            'base_zip_code' => '92562',
            'travel_fee_per_mile' => '4.25',
            'free_travel_radius_miles' => '5',
            'long_distance_threshold_miles' => '180',
            'long_distance_policy_note' => 'Hotel may apply after 180 miles.',
            'auto_gratuity_enabled' => '1',
            'auto_gratuity_percentage' => '20.00',
            'auto_gratuity_minimum_guests' => 25,
            'gratuity_label' => 'Service Charge',
            'included_service_hours' => 4,
            'extra_time_billing_increment_minutes' => 30,
            'extra_time_fee' => '75.00',
            'setup_time_note' => 'Setup included in standard service window.',
            'late_customer_policy_note' => 'Late access may trigger added time.',
            'required_booking_fields' => ['customer_name', 'phone', 'event_date', 'menu_selection'],
            'reservation_received_message' => 'Reservation received.',
            'deposit_required_message' => 'Deposit is required.',
            'confirmation_message_after_deposit' => 'Reservation confirmed.',
            'internal_admin_note' => 'Internal office rule.',
        ];

        $this->actingAs($admin, 'web')
            ->post(route('admin.settings.reservation-rules.update'), $payload)
            ->assertRedirect(route('admin.settings.reservation-rules'))
            ->assertSessionHas('ok', 'Reservation rules updated.');

        $this->assertDatabaseHas('admin_settings', [
            'group_name' => 'reservation_rules',
            'key' => 'deposit_percentage',
            'value' => '40.50',
        ]);

        $stored = AdminSetting::valuesForGroup('reservation_rules');
        $this->assertSame('customer_name,phone,event_date,menu_selection', $stored['required_booking_fields']);
    }

    public function test_reservation_rules_validation_rejects_invalid_values(): void
    {
        $admin = $this->owner();

        $this->actingAs($admin, 'web')
            ->from(route('admin.settings.reservation-rules'))
            ->post(route('admin.settings.reservation-rules.update'), [
                'minimum_guests' => 0,
                'maximum_guests' => 0,
                'default_reservation_status' => 'Unknown',
                'reservation_cutoff_hours' => -1,
                'allow_same_day_booking' => '2',
                'allow_booking_without_deposit' => '0',
                'deposit_required' => '1',
                'deposit_percentage' => 150,
                'minimum_deposit_amount' => -10,
                'mark_confirmed_after_deposit' => '1',
                'base_zip_code' => 'ABC',
                'travel_fee_per_mile' => -1,
                'free_travel_radius_miles' => -5,
                'long_distance_threshold_miles' => -2,
                'auto_gratuity_enabled' => '1',
                'auto_gratuity_percentage' => 101,
                'auto_gratuity_minimum_guests' => 0,
                'gratuity_label' => '',
                'included_service_hours' => 0,
                'extra_time_billing_increment_minutes' => 0,
                'extra_time_fee' => -1,
                'required_booking_fields' => ['bad-field'],
                'reservation_received_message' => '',
                'deposit_required_message' => '',
                'confirmation_message_after_deposit' => '',
            ])
            ->assertRedirect(route('admin.settings.reservation-rules'))
            ->assertSessionHasErrors([
                'minimum_guests',
                'maximum_guests',
                'default_reservation_status',
                'reservation_cutoff_hours',
                'allow_same_day_booking',
                'deposit_percentage',
                'minimum_deposit_amount',
                'base_zip_code',
                'travel_fee_per_mile',
                'free_travel_radius_miles',
                'long_distance_threshold_miles',
                'auto_gratuity_percentage',
                'auto_gratuity_minimum_guests',
                'gratuity_label',
                'included_service_hours',
                'extra_time_billing_increment_minutes',
                'extra_time_fee',
                'required_booking_fields.0',
                'reservation_received_message',
                'deposit_required_message',
                'confirmation_message_after_deposit',
            ]);
    }

    public function test_reservation_rules_can_be_reset_to_recommended_defaults(): void
    {
        $admin = $this->owner();

        AdminSetting::storeGroupValues('reservation_rules', [
            'minimum_guests' => '22',
            'deposit_percentage' => '45.00',
        ]);

        $this->actingAs($admin, 'web')
            ->post(route('admin.settings.reservation-rules.reset'))
            ->assertRedirect(route('admin.settings.reservation-rules'))
            ->assertSessionHas('ok', 'Reservation rules reset to recommended defaults.');

        $this->assertDatabaseHas('admin_settings', [
            'group_name' => 'reservation_rules',
            'key' => 'minimum_guests',
            'value' => '10',
        ]);
        $this->assertDatabaseHas('admin_settings', [
            'group_name' => 'reservation_rules',
            'key' => 'deposit_percentage',
            'value' => '35.00',
        ]);
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
