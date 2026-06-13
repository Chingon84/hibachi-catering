<?php

namespace Tests\Feature\Admin;

use App\Models\TeamMemberDocument;
use App\Models\User;
use App\Support\UploadedFiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamDocumentViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_document_can_be_viewed_inline(): void
    {
        Storage::fake(UploadedFiles::disk());

        $owner = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);
        Storage::disk(UploadedFiles::disk())->put('team-documents/' . $staff->id . '/contract.pdf', '%PDF-1.4 test');

        $document = TeamMemberDocument::create([
            'team_member_id' => $staff->id,
            'type' => 'Contract',
            'file_path' => 'team-documents/' . $staff->id . '/contract.pdf',
            'uploaded_by' => $owner->id,
        ]);

        $this->actingAs($owner, 'web')
            ->get(route('admin.team.documents.view', [$staff->id, $document->id]))
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename="contract.pdf"');
    }
}
