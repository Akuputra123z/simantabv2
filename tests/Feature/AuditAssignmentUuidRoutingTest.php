<?php

namespace Tests\Feature;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditAssignmentUuidRoutingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AuditAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $program = AuditProgram::create([
            'nama_program' => 'Audit Keuangan TA 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Audit Kepatuhan Disdik',
            'jenis_kegiatan' => 'Audit Ketaatan',
            'tim' => 'Irban I',
            'anggaran' => 10000000,
            'status' => 'aktif',
        ]);

        $unit = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan Pemuda dan Olahraga',
            'kategori' => 'OPD',
        ]);

        $this->assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->admin->id,
            'nomor_surat' => 'ST/700/TEST/2026',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $this->assignment->unitDiperiksas()->attach($unit->id);
    }

    public function test_audit_assignment_automatically_receives_uuid(): void
    {
        $this->assertNotEmpty($this->assignment->uuid);
        $this->assertTrue(Str::isUuid($this->assignment->uuid));
    }

    public function test_route_generation_uses_uuid_instead_of_id(): void
    {
        $showUrl = route('audit-assignment.show', $this->assignment);
        $editUrl = route('audit-assignment.edit', $this->assignment);
        $previewUrl = route('audit-assignment.preview', $this->assignment);
        $printUrl = route('audit-assignment.print', $this->assignment);

        $this->assertStringContainsString('/audit-assignment/' . $this->assignment->uuid, $showUrl);
        $this->assertStringNotContainsString('/audit-assignment/' . $this->assignment->id . '/', rtrim($showUrl, '/') . '/');

        $this->assertStringContainsString('/audit-assignment/' . $this->assignment->uuid . '/edit', $editUrl);
        $this->assertStringNotContainsString('/audit-assignment/' . $this->assignment->id . '/edit', $editUrl);

        $this->assertStringContainsString('/audit-assignment/' . $this->assignment->uuid . '/preview', $previewUrl);
        $this->assertStringNotContainsString('/audit-assignment/' . $this->assignment->id . '/preview', $previewUrl);

        $this->assertStringContainsString('/audit-assignment/' . $this->assignment->uuid . '/print', $printUrl);
        $this->assertStringNotContainsString('/audit-assignment/' . $this->assignment->id . '/print', $printUrl);
    }

    public function test_authenticated_user_can_access_assignment_show_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->get(route('audit-assignment.show', $this->assignment));

        $response->assertStatus(200);
        $response->assertSee($this->assignment->nomor_surat);
    }

    public function test_accessing_show_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/audit-assignment/' . $this->assignment->id);

        $response->assertRedirect(route('audit-assignment.show', $this->assignment));
        $response->assertStatus(301);
    }

    public function test_accessing_edit_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/audit-assignment/' . $this->assignment->id . '/edit');

        $response->assertRedirect(route('audit-assignment.edit', $this->assignment));
        $response->assertStatus(301);
    }

    public function test_accessing_preview_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/audit-assignment/' . $this->assignment->id . '/preview');

        $response->assertRedirect(route('audit-assignment.preview', $this->assignment));
        $response->assertStatus(301);
    }

    public function test_can_stream_pdf_print_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->get(route('audit-assignment.print', $this->assignment));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-type') ?? '', 'application/pdf')
        );
    }

    public function test_accessing_non_existent_uuid_returns_404(): void
    {
        $fakeUuid = (string) Str::uuid();
        $response = $this->actingAs($this->admin)->get('/audit-assignment/' . $fakeUuid);

        $response->assertStatus(404);
    }

    public function test_can_sign_and_batal_sign_assignment_by_uuid(): void
    {
        // Sign
        $response = $this->actingAs($this->admin)->post(route('audit-assignment.sign', $this->assignment));
        $response->assertRedirect(route('audit-assignment.show', $this->assignment));
        $this->assertNotNull($this->assignment->fresh()->approved_by);

        // Batal sign
        $batalResponse = $this->actingAs($this->admin)->post(route('audit-assignment.batal-sign', $this->assignment));
        $batalResponse->assertRedirect(route('audit-assignment.show', $this->assignment));
        $this->assertNull($this->assignment->fresh()->approved_by);
    }

    public function test_can_delete_assignment_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('audit-assignment.destroy', $this->assignment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('audit_assignments', ['id' => $this->assignment->id]);
    }
}
