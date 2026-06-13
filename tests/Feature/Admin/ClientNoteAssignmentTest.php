<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientNoteAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_note_can_be_assigned_to_allowed_team_member(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

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

        $response = $this->actingAs($owner)->post(route('admin.clients.notes.store', $client->id), [
            'assigned_to' => $manager->id,
            'body' => 'Call back about next event.',
        ]);

        $response->assertRedirect(route('admin.clients.show', [
            'id' => $client->id,
            'tab' => 'activities',
            'activity_tab' => 'NOTES',
        ]));

        $this->assertDatabaseHas('client_activities', [
            'client_id' => $client->id,
            'type' => 'NOTE',
            'assigned_to' => $manager->id,
            'body' => 'Call back about next event.',
        ]);

        $this->actingAs($owner)
            ->get(route('admin.clients.show', ['id' => $client->id, 'tab' => 'activities', 'activity_tab' => 'NOTES']))
            ->assertOk()
            ->assertSee('Activity assigned to')
            ->assertSee('Eric Florentino');
    }

    public function test_client_note_rejects_assignment_to_regular_staff(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => 'staff',
            'staff_type' => 'Server',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $client = Client::create([
            'first_name' => 'Danna',
            'last_name' => 'Paola',
            'email_primary' => 'danna@example.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)
            ->from(route('admin.clients.show', $client->id))
            ->post(route('admin.clients.notes.store', $client->id), [
                'assigned_to' => $staff->id,
                'body' => 'This should not be assigned to staff.',
            ]);

        $response->assertRedirect(route('admin.clients.show', $client->id));
        $response->assertSessionHasErrors('assigned_to');
        $this->assertSame(0, ClientActivity::where('client_id', $client->id)->count());
    }

    public function test_client_task_stores_hubspot_style_details(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-12 10:00:00'));

        try {
            $owner = User::factory()->create([
                'role' => 'owner',
                'can_access_admin' => true,
                'is_active' => true,
            ]);

            $assignee = User::factory()->create([
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

            $response = $this->actingAs($owner)->post(route('admin.clients.tasks.store', $client->id), [
                'title' => 'importante',
                'description' => 'Prepare final details.',
                'assigned_to' => $assignee->id,
                'due_date_preset' => '3_business_days',
                'due_time' => '08:00',
                'reminder' => '1_hour_before',
                'task_type' => 'to_do',
                'priority' => 'medium',
                'queue' => 'Sales follow-up',
                'repeat' => 1,
            ]);

            $response->assertRedirect(route('admin.clients.show', [
                'id' => $client->id,
                'tab' => 'activities',
                'activity_tab' => 'TASKS',
            ]));

            $task = ClientActivity::where('client_id', $client->id)->where('type', 'TASK')->firstOrFail();

            $this->assertSame('importante', $task->title);
            $this->assertSame('Prepare final details.', $task->body);
            $this->assertSame($assignee->id, $task->assigned_to);
            $this->assertSame('2026-06-17 08:00:00', $task->due_at->toDateTimeString());
            $this->assertSame('to_do', $task->meta['task_type']);
            $this->assertSame('medium', $task->meta['priority']);
            $this->assertSame('Sales follow-up', $task->meta['queue']);
            $this->assertSame('1_hour_before', $task->meta['reminder']);
            $this->assertTrue($task->meta['repeat']);
        } finally {
            Carbon::setTestNow();
        }
    }
}
