<?php

namespace Tests\Feature\Admin;

use App\Models\User;
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

    public function test_active_staff_without_admin_access_gets_403_on_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
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
}
