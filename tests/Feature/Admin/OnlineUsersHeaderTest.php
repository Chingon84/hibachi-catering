<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\OnlineUserPresence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OnlineUsersHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_owner_dashboard_includes_online_staff_widget(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'name' => 'Line Cook',
            'role' => 'staff',
            'staff_type' => 'Chef',
            'is_active' => true,
        ]);

        app(OnlineUserPresence::class)->mark($staff->id);

        $this->actingAs($owner, 'web')
            ->get('/admin')
            ->assertOk()
            ->assertSee('class="admin-online"', false)
            ->assertSee('Staff Online')
            ->assertSee('Line Cook');
    }

    public function test_staff_with_admin_access_cannot_see_or_fetch_online_users(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($staff, 'web')
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('class="admin-online"', false)
            ->assertDontSee('Staff Online');

        $this->actingAs($staff, 'web')
            ->getJson('/admin/online-users')
            ->assertForbidden();
    }

    public function test_owner_online_users_endpoint_separates_staff_and_admins(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'name' => 'Prep Chef',
            'role' => 'staff',
            'staff_type' => 'Chef',
            'is_active' => true,
        ]);
        $admin = User::factory()->create([
            'name' => 'Floor Admin',
            'role' => 'admin',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $presence = app(OnlineUserPresence::class);
        $presence->mark($staff->id);
        $presence->mark($admin->id);

        $this->actingAs($owner, 'web')
            ->getJson('/admin/online-users')
            ->assertOk()
            ->assertJsonPath('staff.0.name', 'Prep Chef')
            ->assertJsonPath('staff.0.role', 'Chef')
            ->assertJsonPath('admins.0.name', 'Floor Admin')
            ->assertJsonPath('admins.0.role', 'Admin')
            ->assertJsonPath('total', 2);
    }
}
