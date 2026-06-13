<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upload_team_member_profile_photo(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner, 'web')->post(
            route('admin.team.profile-photo.update', $staff->id),
            ['profile_photo' => UploadedFile::fake()->image('chef.png', 240, 240)]
        );

        $response->assertRedirect(route('admin.team.show', ['id' => $staff->id, 'tab' => 'overview']));

        $staff->refresh();
        $this->assertNotNull($staff->profile_photo_path);
        Storage::disk('public')->assertExists($staff->profile_photo_path);
    }

    public function test_read_only_team_user_cannot_upload_profile_photo(): void
    {
        Storage::fake('public');

        $viewer = User::factory()->create([
            'role' => 'office',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->actingAs($viewer, 'web')->post(
            route('admin.team.profile-photo.update', $staff->id),
            ['profile_photo' => UploadedFile::fake()->image('chef.png', 240, 240)]
        )->assertForbidden();
    }

    public function test_owner_can_remove_team_member_profile_photo_from_edit_page(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
            'profile_photo_path' => UploadedFile::fake()
                ->image('old-photo.png', 240, 240)
                ->store('team-profile-photos/1', 'public'),
        ]);

        $oldPath = $staff->profile_photo_path;

        $response = $this->actingAs($owner, 'web')->post(
            route('admin.team.profile-photo.delete', ['id' => $staff->id, 'back' => 'edit'])
        );

        $response->assertRedirect(route('admin.team.edit', $staff->id));

        $staff->refresh();
        $this->assertNull($staff->profile_photo_path);
        Storage::disk('public')->assertMissing($oldPath);
    }
}
