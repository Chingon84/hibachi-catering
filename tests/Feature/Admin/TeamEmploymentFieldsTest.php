<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamEmploymentFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_employment_fields_are_editable_and_visible_on_overview(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Bianca Flores',
            'email' => 'bianca@example.com',
            'username' => 'hcbiancha',
            'position' => 'Office',
            'phone' => '951-852-5656',
            'staff_type' => 'Office',
            'role' => 'manager',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->post(route('admin.team.update', $user->id), [
            'name' => 'Bianca Flores',
            'email' => 'bianca@example.com',
            'username' => 'hcbiancha',
            'position' => 'Office',
            'phone' => '951-852-5656',
            'employee_number' => '1012',
            'employee_type' => 'Part Time',
            'hire_date' => '2025-11-03',
            'staff_type' => 'Office',
            'role' => 'manager',
            'password' => '',
            'can_access_admin' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.team.index'));

        $user->refresh();
        $this->assertSame('1012', $user->employee_number);
        $this->assertSame('Part Time', $user->employee_type);
        $this->assertSame('2025-11-03', $user->hire_date->toDateString());

        $this->actingAs($owner)
            ->get(route('admin.team.show', ['id' => $user->id]))
            ->assertOk()
            ->assertSee('Employee number')
            ->assertSee('1012')
            ->assertSee('Employee type')
            ->assertSee('Part Time')
            ->assertSee('Hire date')
            ->assertSee('Nov 03, 2025');
    }

    public function test_manager_can_edit_employment_fields(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Team Staff',
            'email' => 'staff@example.com',
            'username' => 'teamstaff',
            'position' => 'Server',
            'staff_type' => 'Server',
            'role' => 'staff',
            'can_access_admin' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)->post(route('admin.team.update', $user->id), [
            'name' => 'Team Staff',
            'email' => 'staff@example.com',
            'username' => 'teamstaff',
            'position' => 'Server',
            'employee_number' => '2044',
            'employee_type' => 'Seasonal',
            'hire_date' => '2026-01-15',
            'staff_type' => 'Server',
            'role' => 'staff',
            'password' => '',
            'can_access_admin' => 0,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.team.index'));

        $user->refresh();
        $this->assertSame('2044', $user->employee_number);
        $this->assertSame('Seasonal', $user->employee_type);
        $this->assertSame('2026-01-15', $user->hire_date->toDateString());
    }
}
