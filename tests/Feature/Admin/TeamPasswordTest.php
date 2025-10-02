<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Http\Controllers\Admin\TeamController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
    }

    public function test_store_requires_password_and_hashes_it(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.team.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'username' => 'newuser',
            'position' => 'Tester',
            'role' => 'staff',
            'password' => 'secret123',
            'can_access_admin' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.team.index'));

        $created = User::where('email', 'new@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('secret123', $created->password));
    }

    public function test_update_keeps_existing_password_when_field_blank(): void
    {
        $admin = $this->makeAdmin();

        $user = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'username' => 'existinguser',
            'position' => 'Analyst',
            'role' => 'staff',
            'can_access_admin' => false,
            'is_active' => true,
            'password' => 'existing123',
        ]);

        $originalHash = $user->password;

        $response = $this->actingAs($admin)->post(route('admin.team.update', $user->id), [
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'username' => 'existinguser',
            'position' => 'Analyst',
            'role' => 'staff',
            'password' => '',
            'can_access_admin' => 0,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.team.index'));

        $user->refresh();
        $this->assertSame($originalHash, $user->password);
    }

    public function test_update_hashes_new_password_when_provided(): void
    {
        $admin = $this->makeAdmin();

        $user = User::factory()->create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'username' => 'anotheruser',
            'position' => 'Analyst',
            'role' => 'staff',
            'can_access_admin' => false,
            'is_active' => true,
            'password' => 'existing123',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.team.update', $user->id), [
            'name' => 'Another User',
            'email' => 'another@example.com',
            'username' => 'anotheruser',
            'position' => 'Analyst',
            'role' => 'staff',
            'password' => 'newpass123',
            'can_access_admin' => 0,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.team.index'));

        $user->refresh();
        $this->assertTrue(Hash::check('newpass123', $user->password));
    }

    public function test_update_ignores_placeholder_password_value(): void
    {
        $admin = $this->makeAdmin();

        $user = User::factory()->create([
            'name' => 'Placeholder User',
            'email' => 'placeholder@example.com',
            'username' => 'placeholderuser',
            'position' => 'Analyst',
            'role' => 'staff',
            'can_access_admin' => false,
            'is_active' => true,
            'password' => 'existing123',
        ]);

        $originalHash = $user->password;

        $response = $this->actingAs($admin)->post(route('admin.team.update', $user->id), [
            'name' => 'Placeholder User',
            'email' => 'placeholder@example.com',
            'username' => 'placeholderuser',
            'position' => 'Analyst',
            'role' => 'staff',
            'password' => TeamController::PASSWORD_PLACEHOLDER,
            'can_access_admin' => 0,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.team.index'));

        $user->refresh();
        $this->assertSame($originalHash, $user->password);
    }
}
