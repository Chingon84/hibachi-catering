<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\ScheduleAssignment;
use App\Models\StaffEventConfirmation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_staff_login_redirects_to_staff_dashboard(): void
    {
        $staff = $this->staffUser('Angel');

        $this->post(route('login.submit'), [
            'login' => $staff->email,
            'password' => 'password',
        ])->assertRedirect(route('staff.dashboard'));

        $this->actingAs($staff)
            ->get('/')
            ->assertRedirect(route('staff.dashboard'));
    }

    public function test_admin_login_still_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->post(route('login.submit'), [
            'login' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_staff_dashboard_only_shows_assigned_events_without_financial_data(): void
    {
        CarbonImmutable::setTestNow('2026-06-06 10:00:00');

        $angel = $this->staffUser('Angel');
        $ariel = $this->staffUser('Ariel');
        $assigned = $this->reservation('Andrea Carrillo', 'RSV-ANGEL', '2026-06-27');
        $unassigned = $this->reservation('Private Customer', 'RSV-OTHER', '2026-06-28');

        $assigned->items()->create([
            'name_snapshot' => 'Genki',
            'description' => 'Regular',
            'unit_price_snapshot' => 125,
            'qty' => 8,
            'line_total' => 1000,
        ]);

        ScheduleAssignment::query()->create([
            'reservation_id' => $assigned->id,
            'user_id' => $angel->id,
            'chef_1_id' => $angel->id,
            'chef_2_id' => $ariel->id,
            'van' => '5',
            'week_start_date' => '2026-06-21',
        ]);

        ScheduleAssignment::query()->create([
            'reservation_id' => $unassigned->id,
            'user_id' => $ariel->id,
            'chef_1_id' => $ariel->id,
            'week_start_date' => '2026-06-21',
        ]);

        $response = $this->actingAs($angel)->get(route('staff.dashboard'));

        $response->assertOk();
        $response->assertSee('My Assigned Events');
        $response->assertSee('Andrea Carrillo');
        $response->assertSee('RSV-ANGEL');
        $response->assertSee('Saturday, June 27, 2026', false);
        $response->assertSee('Invoice: Partial');
        $response->assertSee('Balance Due: $800.95');
        $response->assertSee('Gratuity: $180.00');
        $response->assertSee('Your Role');
        $response->assertSee('Chef 1');
        $response->assertSee('Open in Maps');
        $response->assertSee('Genki');
        $response->assertSeeInOrder(['Menu', 'Qty', 'Genki', '8']);
        $response->assertDontSee('Qty 8');
        $response->assertSee('Angel');
        $response->assertSee('Ariel');
        $response->assertSee('Van #');
        $response->assertDontSee('Private Customer');
        $response->assertDontSee('$1,000');
        $response->assertDontSee('Subtotal');
        $response->assertDontSee('Tax');
        $response->assertDontSee('Total');
        $response->assertDontSee('Paid Amount');
        $response->assertDontSee('$125.00');
    }

    public function test_staff_can_view_assigned_event_details_but_not_unassigned_event(): void
    {
        CarbonImmutable::setTestNow('2026-06-06 10:00:00');

        $angel = $this->staffUser('Angel');
        $ariel = $this->staffUser('Ariel');
        $assigned = $this->reservation('Andrea Carrillo', 'RSV-ANGEL', '2026-06-27');
        $unassigned = $this->reservation('Private Customer', 'RSV-OTHER', '2026-06-28');

        $assigned->items()->create([
            'name_snapshot' => 'Grill Asparagus',
            'description' => '',
            'unit_price_snapshot' => 7,
            'qty' => 1,
            'line_total' => 7,
        ]);

        ScheduleAssignment::query()->create([
            'reservation_id' => $assigned->id,
            'user_id' => $angel->id,
            'chef_1_id' => $angel->id,
            'chef_2_id' => $ariel->id,
            'van' => '5',
            'schedule_notes' => 'Bring extra propane.',
            'week_start_date' => '2026-06-21',
        ]);

        ScheduleAssignment::query()->create([
            'reservation_id' => $unassigned->id,
            'user_id' => $ariel->id,
            'chef_1_id' => $ariel->id,
            'week_start_date' => '2026-06-21',
        ]);

        $this->actingAs($angel)
            ->get(route('staff.events.show', ['reservation' => $assigned]))
            ->assertOk()
            ->assertSee('Event Details')
            ->assertSee('Andrea Carrillo')
            ->assertSee('Saturday, June 27, 2026', false)
            ->assertSee('Invoice Information')
            ->assertSee('Partial')
            ->assertSee('Balance Due')
            ->assertSee('$800.95')
            ->assertSee('Gratuity')
            ->assertSee('$180.00')
            ->assertSee('Your Role')
            ->assertSee('Chef 1')
            ->assertSee('Open in Maps')
            ->assertSee('Grill Asparagus')
            ->assertSeeInOrder(['Menu', 'Qty', 'Grill Asparagus', '1'])
            ->assertDontSee('Qty 1')
            ->assertSee('Bring extra propane.')
            ->assertDontSee('Subtotal')
            ->assertDontSee('Tax')
            ->assertDontSee('Total')
            ->assertDontSee('Paid Amount')
            ->assertDontSee('$7.00');

        $this->actingAs($angel)
            ->get(route('staff.events.show', ['reservation' => $unassigned]))
            ->assertForbidden();
    }

    public function test_staff_dashboard_limits_upcoming_and_past_events(): void
    {
        CarbonImmutable::setTestNow('2026-06-06 10:00:00');

        $angel = $this->staffUser('Angel');

        $today = $this->reservation('Today Customer', 'RSV-TODAY', '2026-06-06');
        $this->assign($today, $angel);

        for ($i = 1; $i <= 12; $i++) {
            $event = $this->reservation("Future Customer {$i}", "RSV-FUT-{$i}", CarbonImmutable::parse('2026-06-06')->addDays($i)->toDateString());
            $this->assign($event, $angel);
        }

        for ($i = 1; $i <= 9; $i++) {
            $event = $this->reservation("Past Customer {$i}", "RSV-PAST-{$i}", CarbonImmutable::parse('2026-06-06')->subDays($i)->toDateString());
            $this->assign($event, $angel);
        }

        $response = $this->actingAs($angel)->get(route('staff.dashboard'));

        $response->assertOk();
        $response->assertSee('Today Customer');
        $response->assertSee('Saturday, June 6, 2026', false);
        $response->assertSee('Future Customer 10');
        $response->assertDontSee('Future Customer 11');
        $response->assertDontSee('Future Customer 12');
        $response->assertSee('Past Customer 7');
        $response->assertDontSee('Past Customer 8');
        $response->assertDontSee('Past Customer 9');
        $response->assertSee('Upcoming');
        $response->assertSee('Past');
    }

    public function test_staff_dashboard_shows_invoice_status_balance_due_and_gratuity_only(): void
    {
        CarbonImmutable::setTestNow('2026-06-06 10:00:00');

        $angel = $this->staffUser('Angel');

        $paid = $this->reservation('Paid Customer', 'RSV-PAID', '2026-06-07', [
            'invoice_status' => 'paid',
            'amount_paid_total' => 1300.95,
            'balance' => 0,
        ]);
        $partial = $this->reservation('Partial Customer', 'RSV-PARTIAL', '2026-06-08', [
            'invoice_status' => 'pending',
            'amount_paid_total' => 500,
            'balance' => 800.95,
        ]);
        $unpaid = $this->reservation('Unpaid Customer', 'RSV-UNPAID', '2026-06-09', [
            'invoice_status' => 'pending',
            'amount_paid_total' => 0,
            'balance' => 1300.95,
        ]);

        $this->assign($paid, $angel);
        $this->assign($partial, $angel);
        $this->assign($unpaid, $angel);

        $response = $this->actingAs($angel)->get(route('staff.dashboard'));

        $response->assertOk();
        $response->assertSee('Invoice: Paid');
        $response->assertSee('Balance Due: $0.00');
        $response->assertSee('Invoice: Partial');
        $response->assertSee('Balance Due: $800.95');
        $response->assertSee('Invoice: Unpaid');
        $response->assertSee('Balance Due: $1,300.95');
        $response->assertSee('Gratuity: $180.00');
        $response->assertDontSee('Subtotal');
        $response->assertDontSee('Tax');
        $response->assertDontSee('Paid Amount');
    }

    public function test_staff_event_confirmation_is_per_employee_and_moves_from_viewed_to_confirmed(): void
    {
        CarbonImmutable::setTestNow('2026-06-06 14:45:00');

        $angel = $this->staffUser('Angel');
        $ariel = $this->staffUser('Ariel');
        $event = $this->reservation('Andrea Carrillo', 'RSV-CONFIRM', '2026-06-27');

        ScheduleAssignment::query()->create([
            'reservation_id' => $event->id,
            'user_id' => $angel->id,
            'chef_1_id' => $angel->id,
            'chef_2_id' => $ariel->id,
            'van' => '5',
            'week_start_date' => '2026-06-21',
        ]);

        $this->actingAs($angel)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('Confirmation: Not viewed');

        $this->assertDatabaseMissing('staff_event_confirmations', [
            'reservation_id' => $event->id,
            'user_id' => $angel->id,
        ]);

        $this->actingAs($angel)
            ->get(route('staff.events.show', ['reservation' => $event]))
            ->assertOk()
            ->assertSee('Event Confirmation')
            ->assertSee('Viewed');

        $this->assertDatabaseHas('staff_event_confirmations', [
            'reservation_id' => $event->id,
            'user_id' => $angel->id,
            'status' => StaffEventConfirmation::STATUS_VIEWED,
        ]);

        $this->actingAs($angel)
            ->post(route('staff.events.confirm', ['reservation' => $event]))
            ->assertRedirect();

        $this->assertDatabaseHas('staff_event_confirmations', [
            'reservation_id' => $event->id,
            'user_id' => $angel->id,
            'status' => StaffEventConfirmation::STATUS_CONFIRMED,
        ]);

        $this->actingAs($angel)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('Confirmation: Confirmed')
            ->assertDontSee('Confirm Event');

        $this->actingAs($ariel)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('Confirmation: Not viewed');

        $this->assertDatabaseMissing('staff_event_confirmations', [
            'reservation_id' => $event->id,
            'user_id' => $ariel->id,
            'status' => StaffEventConfirmation::STATUS_CONFIRMED,
        ]);
    }

    public function test_staff_cannot_confirm_unassigned_or_cancelled_events(): void
    {
        CarbonImmutable::setTestNow('2026-06-06 14:45:00');

        $angel = $this->staffUser('Angel');
        $ariel = $this->staffUser('Ariel');
        $unassigned = $this->reservation('Other Event', 'RSV-OTHER', '2026-06-27');
        $cancelled = $this->reservation('Cancelled Event', 'RSV-CANCEL', '2026-06-27', [
            'status' => 'canceled',
        ]);

        $this->assign($unassigned, $ariel);
        $this->assign($cancelled, $angel);

        $this->actingAs($angel)
            ->post(route('staff.events.confirm', ['reservation' => $unassigned]))
            ->assertForbidden();

        $this->actingAs($angel)
            ->get(route('staff.events.show', ['reservation' => $cancelled]))
            ->assertOk()
            ->assertSee('Cancelled events cannot be newly confirmed.');

        $this->actingAs($angel)
            ->post(route('staff.events.confirm', ['reservation' => $cancelled]))
            ->assertForbidden();
    }

    public function test_admin_schedule_and_reservation_details_show_staff_confirmation_statuses(): void
    {
        CarbonImmutable::setTestNow('2026-06-06 14:45:00');

        $admin = $this->adminUser();
        $angel = $this->staffUser('Angel');
        $ariel = $this->staffUser('Ariel');
        $event = $this->reservation('Andrea Carrillo', 'RSV-ADMIN-CONFIRM', '2026-06-27');

        ScheduleAssignment::query()->create([
            'reservation_id' => $event->id,
            'user_id' => $angel->id,
            'chef_1_id' => $angel->id,
            'chef_2_id' => $ariel->id,
            'van' => '5',
            'week_start_date' => '2026-06-21',
        ]);

        StaffEventConfirmation::query()->create([
            'reservation_id' => $event->id,
            'user_id' => $angel->id,
            'status' => StaffEventConfirmation::STATUS_CONFIRMED,
            'viewed_at' => now(),
            'confirmed_at' => now(),
        ]);

        StaffEventConfirmation::query()->create([
            'reservation_id' => $event->id,
            'user_id' => $ariel->id,
            'status' => StaffEventConfirmation::STATUS_VIEWED,
            'viewed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.schedule.index', ['date' => '2026-06-27']))
            ->assertOk()
            ->assertSee('Confirmed')
            ->assertSee('Viewed');

        $this->actingAs($admin)
            ->get(route('admin.reservations.show', ['id' => $event->id]))
            ->assertOk()
            ->assertSee('Assigned Staff')
            ->assertSee('Angel')
            ->assertSee('Confirmed')
            ->assertSee('Ariel')
            ->assertSee('Viewed');
    }

    private function staffUser(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => strtolower($name).'@example.test',
            'username' => strtolower($name),
            'password' => Hash::make('password'),
            'role' => 'staff',
            'staff_type' => 'Chef',
            'can_access_admin' => false,
            'is_active' => true,
        ]);
    }

    private function adminUser(): User
    {
        return User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@example.test',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
    }

    private function reservation(string $customer, string $code, string $date, array $overrides = []): Reservation
    {
        static $invoiceNumber = 143;

        return Reservation::query()->create(array_merge([
            'code' => $code,
            'invoice_number' => $invoiceNumber++,
            'status' => 'confirmed',
            'invoice_status' => 'paid',
            'customer_name' => $customer,
            'guests' => 12,
            'date' => $date,
            'time' => '21:00',
            'address' => '25075 Jefferson Avenue',
            'city' => 'Murrieta',
            'zip_code' => '92883',
            'setup_color' => 'Black & Red',
            'event_type' => 'Birthday',
            'stairs' => true,
            'notes' => 'Gate code 1234.',
            'subtotal' => 1000,
            'tax' => 102.50,
            'gratuity' => 180,
            'total' => 1300.95,
            'amount_paid_total' => 500,
            'balance' => 800.95,
        ], $overrides));
    }

    private function assign(Reservation $reservation, User $user): ScheduleAssignment
    {
        return ScheduleAssignment::query()->create([
            'reservation_id' => $reservation->id,
            'user_id' => $user->id,
            'chef_1_id' => $user->id,
            'van' => '5',
            'week_start_date' => '2026-06-01',
        ]);
    }
}
