<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_notifications_only_returns_current_users_notifications(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'can_access_admin' => true, 'is_active' => true]);
        $other = User::factory()->create(['role' => 'manager', 'can_access_admin' => true, 'is_active' => true]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'task',
            'title' => 'Your task',
            'message' => 'Visible to current user.',
        ]);
        Notification::create([
            'user_id' => $other->id,
            'type' => 'task',
            'title' => 'Other task',
            'message' => 'Must not leak.',
        ]);

        $this->actingAs($user)
            ->getJson(route('admin.notifications.recent'))
            ->assertOk()
            ->assertJsonPath('unread', 1)
            ->assertJsonFragment(['title' => 'Your task'])
            ->assertJsonMissing(['title' => 'Other task']);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'can_access_admin' => true, 'is_active' => true]);
        $other = User::factory()->create(['role' => 'admin', 'can_access_admin' => true, 'is_active' => true]);
        $notification = Notification::create([
            'user_id' => $other->id,
            'type' => 'note',
            'title' => 'Private note',
            'message' => 'Owned by another user.',
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.notifications.read', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_read_and_read_all_update_current_user_notifications(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'can_access_admin' => true, 'is_active' => true]);
        $other = User::factory()->create(['role' => 'admin', 'can_access_admin' => true, 'is_active' => true]);
        $first = Notification::create([
            'user_id' => $user->id,
            'type' => 'note',
            'title' => 'First',
            'message' => 'First message.',
        ]);
        $second = Notification::create([
            'user_id' => $user->id,
            'type' => 'task',
            'title' => 'Second',
            'message' => 'Second message.',
        ]);
        $otherNotification = Notification::create([
            'user_id' => $other->id,
            'type' => 'task',
            'title' => 'Other',
            'message' => 'Other message.',
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.notifications.read', $first))
            ->assertOk();

        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNull($second->fresh()->read_at);

        $this->actingAs($user)
            ->postJson(route('admin.notifications.read-all'))
            ->assertOk();

        $this->assertNotNull($second->fresh()->read_at);
        $this->assertNull($otherNotification->fresh()->read_at);
    }

    public function test_assigned_client_note_and_task_create_notifications(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'can_access_admin' => true, 'is_active' => true]);
        $manager = User::factory()->create([
            'name' => 'Eric Florentino',
            'role' => 'manager',
            'staff_type' => 'Manager',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
        $client = Client::create([
            'first_name' => 'Danna',
            'last_name' => 'Paola',
            'email_primary' => 'danna@example.com',
            'status' => 'active',
        ]);

        $this->actingAs($owner)->post(route('admin.clients.notes.store', $client->id), [
            'assigned_to' => $manager->id,
            'body' => 'Please follow up.',
        ])->assertRedirect();

        $this->actingAs($owner)->post(route('admin.clients.tasks.store', $client->id), [
            'title' => 'Call client',
            'assigned_to' => $manager->id,
            'task_type' => 'to_do',
            'priority' => 'none',
            'reminder' => 'none',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $manager->id,
            'type' => 'note',
            'title' => 'New note assigned',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $manager->id,
            'type' => 'task',
            'title' => 'New task assigned',
        ]);
        $this->assertSame(2, ClientActivity::where('client_id', $client->id)->count());
    }

    public function test_dashboard_renders_notification_center(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-notification-center', false)
            ->assertSee(route('admin.notifications.recent'), false);
    }
}
