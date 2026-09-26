<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\TindakLanjut;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_unauthorized_user_cannot_delete_attachment(): void
    {
        $owner = User::factory()->create(['jenis_kelamin' => 'L']);
        $attacker = User::factory()->create(['jenis_kelamin' => 'L']);
        $attacker->assignRole(User::ROLE_OPD);

        $file = UploadedFile::fake()->create('rahasia.pdf', 100);
        $path = $file->store('attachments/test', 'public');

        $attachment = Attachment::create([
            'attachable_type' => 'App\Models\Lhp',
            'attachable_id' => 999,
            'file_name' => 'rahasia.pdf',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $owner->id,
            'visibilitas' => 'internal',
        ]);

        $response = $this->actingAs($attacker)->delete(route('attachments.destroy', $attachment->id));
        $response->assertStatus(403);

        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
    }

    public function test_super_admin_can_delete_attachment(): void
    {
        $admin = User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole(User::ROLE_SUPER_ADMIN);

        $file = UploadedFile::fake()->create('doc.pdf', 100);
        $path = $file->store('attachments/test', 'public');

        $uploader = User::factory()->create(['jenis_kelamin' => 'P']);

        $attachment = Attachment::create([
            'attachable_type' => 'App\Models\Lhp',
            'attachable_id' => 999,
            'file_name' => 'doc.pdf',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $uploader->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('attachments.destroy', $attachment->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }
}
