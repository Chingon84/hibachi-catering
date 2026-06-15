<?php

namespace Tests\Feature\Admin;

use App\Models\Timeslot;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }

    public function test_active_staff_without_admin_access_is_redirected_to_staff_portal(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin');

        $response->assertRedirect(route('staff.dashboard'));
    }

    public function test_active_user_with_unknown_role_without_admin_access_gets_403(): void
    {
        $user = User::factory()->create([
            'role' => 'unknown-role',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin');

        $response->assertForbidden();
    }

    public function test_active_admin_role_gets_200_on_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin');

        $response->assertOk();
    }

    public function test_active_user_with_admin_flag_gets_200_on_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin');

        $response->assertOk();
    }

    public function test_admin_role_can_access_reservations_page(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/reservations');

        $response->assertOk();
    }

    public function test_user_with_admin_flag_can_access_reservations_page(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/reservations');

        $response->assertOk();
    }

    public function test_admin_role_can_access_timeslots_json_endpoint(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/timeslots/json?d=2026-02-27');

        $response->assertOk()->assertHeader('Content-Type', 'application/json');
    }

    public function test_guest_is_redirected_to_login_for_permission_route(): void
    {
        $response = $this->get('/admin/trash');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_permission_gets_403_on_permission_route(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/trash');

        $response->assertForbidden();
    }

    public function test_admin_with_permission_gets_200_on_permission_route(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/trash');

        $response->assertOk();
    }

    public function test_owner_can_access_settings_page(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/settings');

        $response->assertOk()
            ->assertSee('Access Control')
            ->assertSee('Access Control & Permissions')
            ->assertSee('Open Access Control')
            ->assertDontSee('Open Permission Policy');
    }

    public function test_settings_placeholder_route_renders_without_404(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/settings/security')
            ->assertOk()
            ->assertSee('Security')
            ->assertSee('Coming Soon');
    }

    public function test_settings_page_hides_team_admin_links_without_team_permissions(): void
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
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Settings')
            ->assertDontSee('Open Access Control')
            ->assertDontSee('Open Team Directory')
            ->assertSee('No team access granted');
    }

    public function test_feedback_center_no_longer_shows_hardcoded_demo_records(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/feedback-center');

        $response->assertOk();
        $response->assertDontSee('AL-91');
        $response->assertDontSee('DO-1048');
    }

    public function test_financial_permissions_split_view_and_manage_access(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $office = User::factory()->create([
            'role' => 'office',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($manager, 'web')
            ->get('/admin/reports/financial-overview')
            ->assertOk();

        $this->actingAs($manager, 'web')
            ->get('/admin/reports/financial-overview/expenses/create')
            ->assertForbidden();

        $this->actingAs($office, 'web')
            ->get('/admin/reports/financial-overview')
            ->assertForbidden();
    }

    public function test_team_index_is_read_only_without_team_manage_permission(): void
    {
        $office = User::factory()->create([
            'role' => 'office',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($office, 'web')
            ->get('/admin/team')
            ->assertOk()
            ->assertSee('Read-only directory access')
            ->assertDontSee('Add Member')
            ->assertDontSee('Access Control');
    }

    public function test_access_control_page_shows_live_workspace_notice(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/team/permissions')
            ->assertOk()
            ->assertSee('This is the live access-control workspace. Changes here affect what each role can view or manage.')
            ->assertSee('Owner has full access and cannot be restricted.')
            ->assertSee('Back to Settings');
    }

    public function test_reports_dashboard_hides_financial_data_without_financial_permission(): void
    {
        $office = User::factory()->create([
            'role' => 'office',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        Reservation::query()->create([
            'code' => 'RSV-REPORT-1',
            'status' => 'confirmed',
            'invoice_status' => 'paid',
            'guests' => 12,
            'date' => now()->toDateString(),
            'time' => '18:00:00',
            'customer_name' => 'Reports Guard',
            'email' => 'reports@example.com',
            'phone' => '5551114444',
            'address' => '789 Main St',
            'city' => 'Los Angeles',
            'zip_code' => '90001',
            'subtotal' => 1000,
            'tax' => 80,
            'gratuity' => 150,
            'travel_fee' => 0,
            'discount' => 0,
            'total' => 1230,
            'deposit_due' => 250,
            'deposit_paid' => 250,
            'amount_paid_total' => 1230,
            'balance' => 0,
        ]);

        $response = $this->actingAs($office, 'web')->get('/admin/reports');

        $response->assertOk();
        $response->assertSee('Reports Dashboard');
        $response->assertSee('Reservations');
        $response->assertSee('Financial details require additional access');
        $response->assertDontSee('Total Revenue');
        $response->assertDontSee('Deposits');
        $response->assertDontSee('Gratuity');
        $response->assertDontSee('Tax');
        $response->assertDontSee('Chart.js');
        $response->assertDontSee('$1,230.00');
    }

    public function test_reports_dashboard_shows_financial_data_with_financial_permission(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        Reservation::query()->create([
            'code' => 'RSV-REPORT-2',
            'status' => 'confirmed',
            'invoice_status' => 'paid',
            'guests' => 12,
            'date' => now()->toDateString(),
            'time' => '19:00:00',
            'customer_name' => 'Reports Finance',
            'email' => 'finance@example.com',
            'phone' => '5551115555',
            'address' => '159 Main St',
            'city' => 'Los Angeles',
            'zip_code' => '90001',
            'subtotal' => 800,
            'tax' => 64,
            'gratuity' => 120,
            'travel_fee' => 0,
            'discount' => 0,
            'total' => 984,
            'deposit_due' => 200,
            'deposit_paid' => 200,
            'amount_paid_total' => 984,
            'balance' => 0,
        ]);

        $response = $this->actingAs($manager, 'web')->get('/admin/reports');

        $response->assertOk();
        $response->assertSee('Total Revenue');
        $response->assertSee('Deposits');
        $response->assertSee('Gratuity');
        $response->assertSee('Tax');
        $response->assertSee('$984.00');
    }

    public function test_timeslot_delete_requires_post_and_post_deletes_record(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $slot = Timeslot::query()->create([
            'date' => '2026-06-27',
            'time' => '15:00:00',
            'capacity' => 10,
            'is_open' => true,
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/timeslots/' . $slot->id . '/delete')
            ->assertMethodNotAllowed();

        $this->actingAs($user, 'web')
            ->post('/admin/timeslots/' . $slot->id . '/delete', ['d' => '2026-06-27'])
            ->assertRedirect();

        $this->assertDatabaseMissing('timeslots', ['id' => $slot->id]);
    }

    public function test_timeslot_delete_is_blocked_when_slot_has_active_reservations(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $slot = Timeslot::query()->create([
            'date' => '2026-06-27',
            'time' => '15:00:00',
            'capacity' => 10,
            'is_open' => true,
        ]);

        Reservation::query()->create([
            'code' => 'RSV-BLOCK-1',
            'status' => 'confirmed',
            'invoice_status' => 'pending',
            'guests' => 4,
            'date' => '2026-06-27',
            'time' => '15:00:00',
            'customer_name' => 'Delete Guard',
            'email' => 'guard@example.com',
            'phone' => '5551112222',
            'address' => '123 Main St',
            'city' => 'Los Angeles',
            'zip_code' => '90001',
            'subtotal' => 0,
            'tax' => 0,
            'gratuity' => 0,
            'travel_fee' => 0,
            'discount' => 0,
            'total' => 0,
            'deposit_due' => 0,
            'deposit_paid' => 0,
            'amount_paid_total' => 0,
            'balance' => 0,
        ]);

        $this->actingAs($user, 'web')
            ->from('/admin/timeslots?d=2026-06-27')
            ->post('/admin/timeslots/' . $slot->id . '/delete', ['d' => '2026-06-27'])
            ->assertRedirect('/admin/timeslots?d=2026-06-27')
            ->assertSessionHasErrors(['timeslot']);

        $this->assertDatabaseHas('timeslots', ['id' => $slot->id]);
    }

    public function test_clear_month_skips_booked_slots(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $bookedSlot = Timeslot::query()->create([
            'date' => '2026-06-27',
            'time' => '15:00:00',
            'capacity' => 10,
            'is_open' => true,
        ]);

        $freeSlot = Timeslot::query()->create([
            'date' => '2026-06-28',
            'time' => '16:00:00',
            'capacity' => 10,
            'is_open' => true,
        ]);

        Reservation::query()->create([
            'code' => 'RSV-BLOCK-2',
            'status' => 'confirmed',
            'invoice_status' => 'pending',
            'guests' => 3,
            'date' => '2026-06-27',
            'time' => '15:00:00',
            'customer_name' => 'Month Guard',
            'email' => 'month@example.com',
            'phone' => '5551113333',
            'address' => '456 Main St',
            'city' => 'Los Angeles',
            'zip_code' => '90001',
            'subtotal' => 0,
            'tax' => 0,
            'gratuity' => 0,
            'travel_fee' => 0,
            'discount' => 0,
            'total' => 0,
            'deposit_due' => 0,
            'deposit_paid' => 0,
            'amount_paid_total' => 0,
            'balance' => 0,
        ]);

        $this->actingAs($user, 'web')
            ->post('/admin/timeslots/clear-month', ['y' => 2026, 'm' => 6])
            ->assertRedirect('/admin/timeslots?d=2026-06-01')
            ->assertSessionHas('ok');

        $this->assertDatabaseHas('timeslots', ['id' => $bookedSlot->id]);
        $this->assertDatabaseMissing('timeslots', ['id' => $freeSlot->id]);
    }

    public function test_office_role_can_access_feedback_center_with_canonical_permission_name(): void
    {
        $user = User::factory()->create([
            'role' => 'office',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/feedback-center')
            ->assertOk();
    }

    public function test_feedback_view_without_manage_is_read_only(): void
    {
        $user = User::factory()->create([
            'role' => 'custom-feedback-view',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        DB::table('role_permissions')->insert([
            ['role' => 'custom-feedback-view', 'permission' => 'feedback.view'],
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/feedback-center')
            ->assertOk()
            ->assertSee('read-only access to the Feedback Center')
            ->assertDontSee('New Days Off Request')
            ->assertDontSee('Create Complaint');

        $this->actingAs($user, 'web')
            ->get('/admin/feedback-center/create')
            ->assertForbidden();

        $this->actingAs($user, 'web')
            ->post('/admin/feedback-center/workflow', [
                'item_id' => 'CP-1',
                'item_group' => 'complaints',
                'status' => 'Pending',
                'team_members' => ['Angel'],
            ])
            ->assertForbidden();
    }

    public function test_legacy_complains_permission_alias_still_grants_feedback_center_access(): void
    {
        $user = User::factory()->create([
            'role' => 'custom-feedback',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        DB::table('role_permissions')->insert([
            ['role' => 'custom-feedback', 'permission' => 'complains.view'],
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/feedback-center')
            ->assertOk();
    }

    // -----------------------------------------------------------------------
    // Login rate limiting
    // -----------------------------------------------------------------------

    public function test_login_is_blocked_after_five_failed_attempts(): void
    {
        $throttleKey = 'baduser@example.com|127.0.0.1';
        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login'    => 'baduser@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'login'    => 'baduser@example.com',
            'password' => 'any-password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('login')
        );

        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
    }

    // -----------------------------------------------------------------------
    // TeamPolicy — role elevation protection
    // -----------------------------------------------------------------------

    public function test_non_owner_admin_cannot_assign_owner_role_to_new_member(): void
    {
        $admin = User::factory()->create([
            'role'             => 'admin',
            'can_access_admin' => true,
            'is_active'        => true,
        ]);

        $response = $this->actingAs($admin, 'web')->post('/admin/team', [
            '_token'               => csrf_token(),
            'name'                 => 'New Owner Attempt',
            'email'                => 'ownertry@example.com',
            'role'                 => 'owner',
            'password'             => 'secret12345',
            'password_confirmation'=> 'secret12345',
        ]);

        // FormRequest withValidator blocks this with a validation error (422)
        // or a redirect back with errors — not a 200.
        $this->assertFalse(User::query()->where('email', 'ownertry@example.com')->exists());
    }

    public function test_non_owner_admin_cannot_edit_owner_account(): void
    {
        $owner = User::factory()->create([
            'role'             => 'owner',
            'can_access_admin' => true,
            'is_active'        => true,
        ]);

        $admin = User::factory()->create([
            'role'             => 'admin',
            'can_access_admin' => true,
            'is_active'        => true,
        ]);

        $response = $this->actingAs($admin, 'web')->post('/admin/team/' . $owner->id, [
            '_token'   => csrf_token(),
            'name'     => 'Hacked Name',
            'email'    => $owner->email,
            'role'     => 'admin',
            'password' => '',
        ]);

        // TeamPolicy::update() denies this → 403
        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $owner->id, 'name' => $owner->name]);
    }

    public function test_owner_can_create_another_owner(): void
    {
        $owner = User::factory()->create([
            'role'             => 'owner',
            'can_access_admin' => true,
            'is_active'        => true,
        ]);

        $this->actingAs($owner, 'web')->post('/admin/team', [
            '_token'               => csrf_token(),
            'name'                 => 'Second Owner',
            'email'                => 'owner2@example.com',
            'role'                 => 'owner',
            'password'             => 'securepass99',
            'password_confirmation'=> 'securepass99',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'owner2@example.com', 'role' => 'owner']);
    }
}
