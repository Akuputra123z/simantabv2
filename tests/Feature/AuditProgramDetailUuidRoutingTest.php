<?php

namespace Tests\Feature;

use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditProgramDetailUuidRoutingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AuditProgram $program;
    private AuditProgramDetail $detail;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $this->program = AuditProgram::create([
            'nama_program' => 'Audit Pengawasan Tahunan',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $this->detail = AuditProgramDetail::create([
            'audit_program_id' => $this->program->id,
            'nama_detail_program' => 'Evaluasi Kinerja Pengadaan Barang',
            'jenis_kegiatan' => 'Audit Kepatuhan',
            'tujuan' => 'Menguji kesesuaian prosedur pengadaan',
            'anggaran' => 50000000,
            'status' => 'aktif',
        ]);
    }

    public function test_audit_program_detail_automatically_receives_uuid(): void
    {
        $this->assertNotEmpty($this->detail->uuid);
        $this->assertTrue(Str::isUuid($this->detail->uuid));
    }

    public function test_route_generation_uses_uuid_instead_of_id(): void
    {
        $showUrl = route('audit-program-detail.show', $this->detail);
        $editUrl = route('audit-program-detail.edit', $this->detail);

        $this->assertStringContainsString('/audit-program-detail/' . $this->detail->uuid, $showUrl);
        $this->assertStringNotContainsString('/audit-program-detail/' . $this->detail->id . '/', rtrim($showUrl, '/') . '/');

        $this->assertStringContainsString('/audit-program-detail/' . $this->detail->uuid . '/edit', $editUrl);
        $this->assertStringNotContainsString('/audit-program-detail/' . $this->detail->id . '/edit', $editUrl);
    }

    public function test_authenticated_user_can_access_audit_program_detail_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->get(route('audit-program-detail.show', $this->detail));

        $response->assertStatus(200);
        $response->assertSee($this->detail->nama_detail_program);
    }

    public function test_accessing_show_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/audit-program-detail/' . $this->detail->id);

        $response->assertRedirect(route('audit-program-detail.show', $this->detail));
        $response->assertStatus(301);
    }

    public function test_accessing_edit_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/audit-program-detail/' . $this->detail->id . '/edit');

        $response->assertRedirect(route('audit-program-detail.edit', $this->detail));
        $response->assertStatus(301);
    }

    public function test_accessing_non_existent_uuid_returns_404(): void
    {
        $fakeUuid = (string) Str::uuid();
        $response = $this->actingAs($this->admin)->get('/audit-program-detail/' . $fakeUuid);

        $response->assertStatus(404);
    }

    public function test_can_update_audit_program_detail_via_uuid_route(): void
    {
        $response = $this->actingAs($this->admin)->put(route('audit-program-detail.update', $this->detail), [
            'nama_detail_program' => 'Evaluasi Kinerja Pengadaan Update',
            'jenis_kegiatan' => 'Audit Operasional',
            'tujuan' => 'Tujuan baru',
            'anggaran' => '75000000',
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('audit-program-detail.show', $this->detail));
        $this->assertDatabaseHas('audit_program_details', [
            'id' => $this->detail->id,
            'nama_detail_program' => 'Evaluasi Kinerja Pengadaan Update',
        ]);
    }

    public function test_can_delete_audit_program_detail_via_uuid_route(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('audit-program-detail.destroy', $this->detail));

        $response->assertRedirect(route('audit-program.show', $this->program));
        $this->assertDatabaseMissing('audit_program_details', ['id' => $this->detail->id]);
    }
}
