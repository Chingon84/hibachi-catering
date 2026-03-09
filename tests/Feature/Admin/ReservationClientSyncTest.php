<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ClientSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationClientSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_reservation_shows_action_and_click_creates_client(): void
    {
        $user = $this->adminUser();
        $reservation = $this->createReservation([
            'status' => 'draft',
            'email' => 'draft@example.com',
            'phone' => '3051112233',
        ]);

        $list = $this->actingAs($user, 'web')->get('/admin/reservations');
        $list->assertOk()->assertSee('Add to Clients');

        $response = $this->actingAs($user, 'web')->postJson(route('admin.reservations.add_to_clients', ['id' => $reservation->id]));

        $response->assertStatus(201)->assertJson(['status' => 'created']);
        $this->assertDatabaseHas('clients', [
            'email_primary' => 'draft@example.com',
            'phone_primary' => '3051112233',
            'source' => 'reservation_manual_add',
            'created_from_reservation_id' => $reservation->id,
            'last_guests' => 10,
        ]);
        $this->assertDatabaseHas('client_reservations', [
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_manual_add_returns_409_when_client_already_exists(): void
    {
        $user = $this->adminUser();
        $reservation = $this->createReservation([
            'status' => 'pending_payment',
            'email' => 'existing@example.com',
            'total' => 150,
        ]);

        Client::query()->create([
            'first_name' => 'Existing',
            'email_primary' => 'existing@example.com',
        ]);

        $this->actingAs($user, 'web')->postJson(route('admin.reservations.add_to_clients', ['id' => $reservation->id]))
            ->assertStatus(409)
            ->assertJson([
                'status' => 'exists',
                'message' => 'This client already exists and cannot be added again.',
            ]);

        $this->assertSame(1, Client::query()->where('email_primary', 'existing@example.com')->count());
        $this->assertDatabaseMissing('client_reservations', ['reservation_id' => $reservation->id]);
    }

    public function test_changing_status_to_confirmed_auto_creates_or_updates_client(): void
    {
        $this->createReservation([
            'status' => 'draft',
            'email' => 'auto@example.com',
            'phone' => '3054446677',
        ]);

        $reservation = Reservation::query()->where('email', 'auto@example.com')->firstOrFail();
        $reservation->status = 'confirmed';
        $reservation->save();

        $this->assertDatabaseHas('clients', [
            'email_primary' => 'auto@example.com',
            'phone_primary' => '3054446677',
        ]);
        $this->assertDatabaseHas('client_reservations', [
            'reservation_id' => $reservation->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_created_reservation_with_deposit_paid_syncs_client_automatically(): void
    {
        $reservation = $this->createReservation([
            'status' => 'pending_payment',
            'email' => 'deposit-created@example.com',
            'deposit_paid' => 60.00,
        ]);

        $this->assertDatabaseHas('clients', [
            'email_primary' => 'deposit-created@example.com',
        ]);
        $this->assertDatabaseHas('client_reservations', [
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_updating_deposit_from_zero_to_positive_syncs_client_automatically(): void
    {
        $reservation = $this->createReservation([
            'status' => 'pending_payment',
            'email' => 'deposit-updated@example.com',
            'deposit_paid' => 0.00,
        ]);

        $this->assertDatabaseMissing('clients', [
            'email_primary' => 'deposit-updated@example.com',
        ]);

        $reservation->deposit_paid = 30.00;
        $reservation->save();

        $this->assertDatabaseHas('clients', [
            'email_primary' => 'deposit-updated@example.com',
        ]);
        $this->assertDatabaseHas('client_reservations', [
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_email_match_has_priority_over_phone_when_email_exists(): void
    {
        $existingPhoneOwner = Client::query()->create([
            'first_name' => 'VALERIA',
            'last_name' => 'ALVARADO',
            'email_primary' => 'julis@gmail.com',
            'phone_primary' => '9515465684',
        ]);

        $reservation = $this->createReservation([
            'status' => 'confirmed',
            'customer_name' => 'BRIANDA FLORENTINO',
            'email' => 'BRIANDA@GMAIL.COM',
            'phone' => '951-546-5684',
            'deposit_paid' => 1375.42,
        ]);

        $brianda = Client::query()
            ->whereRaw('LOWER(TRIM(email_primary)) = ?', ['brianda@gmail.com'])
            ->first();

        $this->assertNotNull($brianda);
        $this->assertNotSame($existingPhoneOwner->id, $brianda->id);
        $this->assertDatabaseHas('client_reservations', [
            'client_id' => $brianda->id,
            'reservation_id' => $reservation->id,
        ]);
        $this->assertDatabaseMissing('client_reservations', [
            'client_id' => $existingPhoneOwner->id,
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_confirmed_reservation_shows_add_to_clients_action(): void
    {
        $user = $this->adminUser();
        $reservation = $this->createReservation([
            'status' => 'confirmed',
            'email' => 'confirmed@example.com',
        ]);

        $list = $this->actingAs($user, 'web')->get('/admin/reservations');

        $list->assertOk()->assertSee("addToClients({$reservation->id}", false);
    }

    public function test_without_email_but_with_phone_uses_phone(): void
    {
        $user = $this->adminUser();
        $reservation = $this->createReservation([
            'status' => 'draft',
            'email' => null,
            'phone' => '3057778888',
        ]);

        $this->actingAs($user, 'web')->postJson(route('admin.reservations.add_to_clients', ['id' => $reservation->id]))
            ->assertStatus(201);

        $this->assertDatabaseHas('clients', [
            'phone_primary' => '3057778888',
            'created_from_reservation_id' => $reservation->id,
        ]);
    }

    public function test_without_email_or_phone_uses_reservation_id_fallback(): void
    {
        $user = $this->adminUser();
        $reservation = $this->createReservation([
            'status' => 'draft',
            'email' => null,
            'phone' => null,
        ]);

        $this->actingAs($user, 'web')->postJson(route('admin.reservations.add_to_clients', ['id' => $reservation->id]))
            ->assertStatus(201);

        $this->assertDatabaseHas('clients', [
            'created_from_reservation_id' => $reservation->id,
            'source' => 'reservation_manual_add',
        ]);
    }

    public function test_existing_client_returns_exists_and_does_not_update_fields(): void
    {
        $user = $this->adminUser();
        $reservation = $this->createReservation([
            'status' => 'pending_payment',
            'email' => 'guest-update@example.com',
            'guests' => 24,
        ]);

        Client::query()->create([
            'first_name' => 'Guest',
            'email_primary' => 'guest-update@example.com',
            'last_guests' => 8,
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('admin.reservations.add_to_clients', ['id' => $reservation->id]))
            ->assertStatus(409)
            ->assertJson(['status' => 'exists']);

        $this->assertDatabaseHas('clients', [
            'email_primary' => 'guest-update@example.com',
            'last_guests' => 8,
        ]);
    }

    public function test_double_request_does_not_create_duplicate_clients(): void
    {
        $user = $this->adminUser();
        $reservation = $this->createReservation([
            'status' => 'draft',
            'email' => 'double@example.com',
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('admin.reservations.add_to_clients', ['id' => $reservation->id]))
            ->assertStatus(201);

        $this->actingAs($user, 'web')
            ->postJson(route('admin.reservations.add_to_clients', ['id' => $reservation->id]))
            ->assertStatus(409)
            ->assertJson(['status' => 'exists']);

        $this->assertSame(1, Client::query()->where('email_primary', 'double@example.com')->count());
    }

    public function test_events_count_is_two_when_two_reservations_are_attached(): void
    {
        $service = app(ClientSyncService::class);
        $first = $this->createReservation([
            'email' => 'events-two@example.com',
            'status' => 'confirmed',
        ]);
        $second = $this->createReservation([
            'email' => 'events-two@example.com',
            'status' => 'confirmed',
        ]);

        $client = $service->upsertClientFromReservation($first);
        $client = $service->upsertClientFromReservation($second);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'events_count' => 2,
        ]);
        $this->assertSame(2, (int) \DB::table('client_reservations')->where('client_id', $client->id)->count());
    }

    public function test_attaching_same_reservation_twice_keeps_events_count_at_one(): void
    {
        $service = app(ClientSyncService::class);
        $reservation = $this->createReservation([
            'email' => 'events-once@example.com',
            'status' => 'confirmed',
        ]);

        $client = $service->upsertClientFromReservation($reservation);
        $service->attachReservationToClient($client, $reservation);
        $service->attachReservationToClient($client, $reservation);

        $client->refresh();
        $this->assertSame(1, (int) $client->events_count);
        $this->assertSame(1, (int) \DB::table('client_reservations')
            ->where('client_id', $client->id)
            ->where('reservation_id', $reservation->id)
            ->count());
    }

    public function test_events_filter_returns_only_exact_events_count(): void
    {
        $user = $this->adminUser();
        Client::query()->create([
            'first_name' => 'TwoEvents',
            'events_count' => 2,
        ]);
        Client::query()->create([
            'first_name' => 'ThreeEvents',
            'events_count' => 3,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/clients?events=2');

        $response->assertOk();
        $response->assertSee('TwoEvents');
        $response->assertDontSee('ThreeEvents');
    }

    public function test_events_filter_empty_returns_all_clients(): void
    {
        $user = $this->adminUser();
        Client::query()->create([
            'first_name' => 'AnyOne',
            'events_count' => 1,
        ]);
        Client::query()->create([
            'first_name' => 'AnyTwo',
            'events_count' => 4,
        ]);

        $response = $this->actingAs($user, 'web')->get('/admin/clients?events=');

        $response->assertOk();
        $response->assertSee('AnyOne');
        $response->assertSee('AnyTwo');
    }

    public function test_client_with_new_reservation_moves_to_top_by_last_event_at(): void
    {
        $user = $this->adminUser();
        $service = app(ClientSyncService::class);

        $older = Client::query()->create([
            'first_name' => 'Older Client',
            'email_primary' => 'older@example.com',
            'events_count' => 1,
            'last_event_at' => now()->subDays(5),
        ]);
        Client::query()->create([
            'first_name' => 'Recent Client',
            'email_primary' => 'recent@example.com',
            'events_count' => 1,
            'last_event_at' => now()->subDay(),
        ]);

        $reservation = $this->createReservation([
            'status' => 'confirmed',
            'email' => 'older@example.com',
            'date' => now()->addDay()->toDateString(),
            'time' => '21:00:00',
        ]);

        $service->upsertClientFromReservation($reservation);
        $older->refresh();
        $this->assertNotNull($older->last_event_at);

        $response = $this->actingAs($user, 'web')->get('/admin/clients');
        $response->assertOk()->assertSeeInOrder(['Older Client', 'Recent Client']);
    }

    public function test_paginate_50_places_51st_client_on_second_page(): void
    {
        $user = $this->adminUser();

        for ($i = 1; $i <= 51; $i++) {
            Client::query()->create([
                'first_name' => 'PageClient-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'events_count' => 0,
                'last_event_at' => now()->subMinutes(52 - $i),
            ]);
        }

        $page1 = $this->actingAs($user, 'web')->get('/admin/clients');
        $page1->assertOk();
        $page1->assertSee('PageClient-51');
        $page1->assertDontSee('PageClient-01');

        $page2 = $this->actingAs($user, 'web')->get('/admin/clients?page=2');
        $page2->assertOk();
        $page2->assertSee('PageClient-01');
    }

    public function test_pagination_links_preserve_filters_with_query_string(): void
    {
        $user = $this->adminUser();

        for ($i = 1; $i <= 55; $i++) {
            Client::query()->create([
                'first_name' => "Filtered {$i}",
                'events_count' => 3,
                'last_event_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->actingAs($user, 'web')->get('/admin/clients?status=regular&events=3');

        $response->assertOk();
        $response->assertSee('events=3', false);
        $response->assertSee('status=regular', false);
    }

    private function adminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
    }

    private function createReservation(array $overrides = []): Reservation
    {
        static $seq = 100;
        $seq++;

        return Reservation::query()->create(array_merge([
            'code' => 'RSV-' . $seq,
            'status' => 'draft',
            'guests' => 10,
            'date' => now()->addWeek()->toDateString(),
            'time' => '18:00:00',
            'customer_name' => 'Test Customer',
            'phone' => '3050000000',
            'email' => 'test+' . $seq . '@example.com',
            'address' => '123 Main St',
            'city' => 'Miami',
            'zip_code' => '33101',
            'notes' => 'Test notes',
            'total' => 120.00,
            'deposit_paid' => 0.00,
            'balance' => 120.00,
        ], $overrides));
    }
}
