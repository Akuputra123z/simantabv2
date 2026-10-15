<?php

namespace Tests\Feature;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhp;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LhpUuidRoutingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Lhp $lhp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $program = AuditProgram::create([
            'nama_program' => 'Audit Keuangan 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Pemeriksaan Rutin',
            'jenis_kegiatan' => 'Audit Operasional',
            'tim' => 'Irban I',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->admin->id,
            'nomor_surat' => 'ST/TEST/001',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'status' => 'berjalan',
        ]);

        $unit = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan',
            'kategori' => 'OPD',
        ]);

        $this->lhp = Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $unit->id,
            'nomor_lhp' => 'LHP/TEST/2026/001',
            'tanggal_lhp' => now()->toDateString(),
            'status' => 'final',
        ]);
    }

    public function test_lhp_automatically_receives_uuid(): void
    {
        $this->assertNotEmpty($this->lhp->uuid);
        $this->assertTrue(Str::isUuid($this->lhp->uuid));
    }

    public function test_route_generation_uses_uuid_instead_of_id(): void
    {
        $showUrl = route('lhps.show', $this->lhp);
        $editUrl = route('lhps.edit', $this->lhp);

        $this->assertStringContainsString('/lhps/' . $this->lhp->uuid, $showUrl);
        $this->assertStringNotContainsString('/lhps/' . $this->lhp->id, $showUrl);

        $this->assertStringContainsString('/lhps/' . $this->lhp->uuid . '/edit', $editUrl);
        $this->assertStringNotContainsString('/lhps/' . $this->lhp->id . '/edit', $editUrl);
    }

    public function test_authenticated_user_can_access_lhp_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->get(route('lhps.show', $this->lhp));

        $response->assertStatus(200);
        $response->assertSee($this->lhp->nomor_lhp);
    }

    public function test_accessing_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/lhps/' . $this->lhp->id);

        $response->assertRedirect(route('lhps.show', $this->lhp));
        $response->assertStatus(301);
    }

    public function test_accessing_edit_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/lhps/' . $this->lhp->id . '/edit');

        $response->assertRedirect(route('lhps.edit', $this->lhp));
        $response->assertStatus(301);
    }

    public function test_accessing_non_existent_uuid_returns_404(): void
    {
        $fakeUuid = (string) Str::uuid();
        $response = $this->actingAs($this->admin)->get('/lhps/' . $fakeUuid);

        $response->assertStatus(404);
    }

    public function test_can_delete_lhp_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('lhps.destroy', $this->lhp));

        $response->assertRedirect(route('lhps.index'));
        $this->assertDatabaseMissing('lhps', ['id' => $this->lhp->id]);
    }
}
