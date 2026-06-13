<?php

namespace Tests\Feature\Admin;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamIncidentsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_incidents_tab_renders_records_from_feedback_tables(): void
    {
        $admin = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'name' => 'Chef Marco Ruiz',
            'role' => 'staff',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        Complaint::query()->create([
            'event_date' => now()->toDateString(),
            'date_received' => now()->toDateString(),
            'chef' => 'Chef Marco Ruiz',
            'category' => 'Food pacing',
            'description' => 'Lag between courses',
            'resolution_status' => 'In Review',
            'assistant' => 'QA',
        ]);

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.team.show', ['id' => $staff->id, 'tab' => 'incidents']));

        $response->assertOk();
        $response->assertSee('Incidents');
        $response->assertSee('Food pacing', false);
        $response->assertSee('CMP-1', false);
    }
}
