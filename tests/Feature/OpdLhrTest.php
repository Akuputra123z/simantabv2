<?php

namespace Tests\Feature;

use App\Enums\LhrStatus;
use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\Lhr;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\LhrShsdSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OpdLhrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(LhrShsdSeeder::class);
    }

    private function createDummyAssignment(): AuditAssignment
    {
        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu 2026',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Dokumen Perencanaan',
            'jenis_kegiatan' => 'Reviu',
            'personil' => '3 Orang',
            'tingkat_resiko' => 'Sedang',
            'tim' => 'Irban I',
        ]);

        $ketua = User::factory()->create(['jenis_kelamin' => 'L']);
        $ketua->assignRole(User::ROLE_KETUA_TIM);

        return AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu',
            'ketua_tim_id' => $ketua->id,
            'jenis_pengawasan' => 'reviu',
            'nomor_surat' => '000.1.2.3/' . rand(100, 999) . '/REVIU/2026',
            'tanggal_mulai' => Carbon::now()->subDays(5),
            'tanggal_selesai' => Carbon::now(),
            'status' => 'berjalan',
        ]);
    }

    public function test_opd_can_access_opd_lhr_index_and_only_sees_their_own_terbit_lhrs(): void
    {
        $unitA = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan',
            'kategori' => 'OPD',
        ]);

        $unitB = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Kesehatan',
            'kategori' => 'OPD',
        ]);

        $opdUser = User::factory()->create(['jenis_kelamin' => 'L']);
        $opdUser->assignRole(User::ROLE_OPD);
        $opdUser->opdUnits()->attach($unitA->id);

        $jenisShsd = JenisDokumenReviu::where('kode', 'SHSD')->first();
        $template = \App\Models\Lhr\LhrTemplate::first();
        $asg = $this->createDummyAssignment();

        // 1. LHR unit A yang Terbit (harus terlihat oleh user OPD A)
        Lhr::create([
            'uuid' => (string) Str::uuid(),
            'audit_assignment_id' => $asg->id,
            'unit_diperiksa_id' => $unitA->id,
            'jenis_dokumen_reviu_id' => $jenisShsd->id,
            'lhr_template_id' => $template->id,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => '700/001/REVIU/2026',
            'status' => LhrStatus::Terbit,
        ]);

        // 2. LHR unit A yang masih Draft (TIDAK boleh terlihat oleh user OPD A)
        Lhr::create([
            'uuid' => (string) Str::uuid(),
            'audit_assignment_id' => $asg->id,
            'unit_diperiksa_id' => $unitA->id,
            'jenis_dokumen_reviu_id' => $jenisShsd->id,
            'lhr_template_id' => $template->id,
            'versi' => 2,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => '700/002/DRAFT/2026',
            'status' => LhrStatus::Draft,
        ]);

        // 3. LHR unit B yang Terbit (TIDAK boleh terlihat oleh user OPD A)
        Lhr::create([
            'uuid' => (string) Str::uuid(),
            'audit_assignment_id' => $asg->id,
            'unit_diperiksa_id' => $unitB->id,
            'jenis_dokumen_reviu_id' => $jenisShsd->id,
            'lhr_template_id' => $template->id,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => '700/003/B_TERBIT/2026',
            'status' => LhrStatus::Terbit,
        ]);

        $this->actingAs($opdUser);

        $response = $this->get(route('opd.lhr.index'));
        $response->assertStatus(200);

        // Memastikan hanya LHR unit A yang Terbit yang muncul
        $response->assertSee('700/001/REVIU/2026');
        $response->assertDontSee('700/002/DRAFT/2026');
        $response->assertDontSee('700/003/B_TERBIT/2026');
    }

    public function test_opd_can_show_their_own_lhr_and_cannot_access_other_opd_lhr(): void
    {
        $unitA = UnitDiperiksa::create(['nama_unit' => 'Dinas Pendidikan', 'kategori' => 'OPD']);
        $unitB = UnitDiperiksa::create(['nama_unit' => 'Dinas Kesehatan', 'kategori' => 'OPD']);

        $opdUser = User::factory()->create(['jenis_kelamin' => 'L']);
        $opdUser->assignRole(User::ROLE_OPD);
        $opdUser->opdUnits()->attach($unitA->id);

        $jenisShsd = JenisDokumenReviu::where('kode', 'SHSD')->first();
        $template = \App\Models\Lhr\LhrTemplate::first();
        $asg = $this->createDummyAssignment();

        $lhrA = Lhr::create([
            'uuid' => (string) Str::uuid(),
            'audit_assignment_id' => $asg->id,
            'unit_diperiksa_id' => $unitA->id,
            'jenis_dokumen_reviu_id' => $jenisShsd->id,
            'lhr_template_id' => $template->id,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => '700/100/A/2026',
            'status' => LhrStatus::Terbit,
        ]);

        $lhrB = Lhr::create([
            'uuid' => (string) Str::uuid(),
            'audit_assignment_id' => $asg->id,
            'unit_diperiksa_id' => $unitB->id,
            'jenis_dokumen_reviu_id' => $jenisShsd->id,
            'lhr_template_id' => $template->id,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => '700/200/B/2026',
            'status' => LhrStatus::Terbit,
        ]);

        $this->actingAs($opdUser);

        // Sukses membuka LHR unit miliknya
        $responseA = $this->get(route('opd.lhr.show', $lhrA->uuid));
        $responseA->assertStatus(200);
        $responseA->assertSee('700/100/A/2026');

        // Gagal / 404 ketika membuka LHR milik unit lain
        $responseB = $this->get(route('opd.lhr.show', $lhrB->uuid));
        $responseB->assertStatus(404);
    }
}
