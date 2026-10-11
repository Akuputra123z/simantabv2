<?php

namespace Tests\Feature;

use App\Enums\LhrStatus;
use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\KodeRekomendasi;
use App\Models\KodeTemuan;
use App\Models\Lhp;
use App\Models\Lhr\Lhr;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IrbanDashboardAndScopingTest extends TestCase
{
    use RefreshDatabase;

    private User $irban1User;
    private User $irban2User;
    private User $inspekturUser;
    private UnitDiperiksa $opd1;
    private UnitDiperiksa $opd2;
    private Lhp $lhp1;
    private Lhp $lhp2;
    private Lhr $lhr1;
    private Lhr $lhr2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        // 1. Setup Irban Users
        $this->irban1User = User::factory()->create([
            'name' => 'Irban I User',
            'unit_kerja' => 'Irban I',
            'jabatan' => 'Irban I',
            'jenis_kelamin' => 'P',
        ]);
        $this->irban1User->assignRole(User::ROLE_PENGENDALI_TEKNIS);

        $this->irban2User = User::factory()->create([
            'name' => 'Irban II User',
            'unit_kerja' => 'Irban II',
            'jabatan' => 'Irban II',
            'jenis_kelamin' => 'L',
        ]);
        $this->irban2User->assignRole(User::ROLE_PENGENDALI_TEKNIS);

        $this->inspekturUser = User::factory()->create([
            'name' => 'Inspektur Utama',
            'unit_kerja' => 'Inspektur',
            'jabatan' => 'Inspektur Daerah',
            'jenis_kelamin' => 'P',
        ]);
        $this->inspekturUser->assignRole(User::ROLE_KEPALA_INSPEKTORAT);

        // 2. Setup OPD Units
        $this->opd1 = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan Wilayah Irban 1',
            'kategori' => 'OPD',
            'nama_kecamatan' => 'Rembang',
        ]);

        $this->opd2 = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Kesehatan Wilayah Irban 2',
            'kategori' => 'OPD',
            'nama_kecamatan' => 'Sulang',
        ]);

        // 3. Setup PKPT Program & Details
        $program = AuditProgram::create([
            'tahun' => (int) date('Y'),
            'nama_program' => 'PKPT Tahunan Inspektorat',
            'kategori' => 'PKPT',
        ]);

        $detail1 = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Pengawasan Bidang 1',
            'jenis_kegiatan' => 'Reviu',
            'tim' => 'Irban I',
        ]);

        $detail2 = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Pengawasan Bidang 2',
            'jenis_kegiatan' => 'Reviu',
            'tim' => 'Irban II',
        ]);

        // 4. Setup Audit Assignments
        $ketuaTim1 = User::factory()->create(['name' => 'Ketua Tim Irban 1', 'jenis_kelamin' => 'L']);
        $ketuaTim1->assignRole(User::ROLE_KETUA_TIM);

        $ketuaTim2 = User::factory()->create(['name' => 'Ketua Tim Irban 2', 'jenis_kelamin' => 'L']);
        $ketuaTim2->assignRole(User::ROLE_KETUA_TIM);

        $assignment1 = AuditAssignment::create([
            'audit_program_detail_id' => $detail1->id,
            'ketua_tim_id' => $ketuaTim1->id,
            'nomor_surat' => 'ST/001/IRBAN-I/' . date('Y'),
            'tanggal_mulai' => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'pengendali_teknis_id' => $this->irban1User->id,
            'pengendali_teknis' => $this->irban1User->name,
            'status' => 'berjalan',
        ]);
        $assignment1->unitDiperiksas()->attach($this->opd1->id);

        $assignment2 = AuditAssignment::create([
            'audit_program_detail_id' => $detail2->id,
            'ketua_tim_id' => $ketuaTim2->id,
            'nomor_surat' => 'ST/002/IRBAN-II/' . date('Y'),
            'tanggal_mulai' => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'pengendali_teknis_id' => $this->irban2User->id,
            'pengendali_teknis' => $this->irban2User->name,
            'status' => 'berjalan',
        ]);
        $assignment2->unitDiperiksas()->attach($this->opd2->id);

        // 5. Setup Kode Temuan & Kode Rekomendasi
        $this->seed(\Database\Seeders\KodeTemuanSeeder::class);
        $this->seed(\Database\Seeders\KodeRekomendasiSeeder::class);

        $kodeTemuan = KodeTemuan::first();
        $kodeRekom = KodeRekomendasi::first();

        // 6. Setup LHP 1 (Irban I)
        $this->lhp1 = Lhp::create([
            'audit_assignment_id' => $assignment1->id,
            'unit_diperiksa_id' => $this->opd1->id,
            'nomor_lhp' => 'LHP/IRBAN1/001',
            'tanggal_lhp' => now()->toDateString(),
            'status' => 'final',
        ]);

        $temuan1 = Temuan::create([
            'lhp_id' => $this->lhp1->id,
            'kode_temuan_id' => $kodeTemuan->id,
            'judul_temuan' => 'Temuan di Dinas Pendidikan Irban 1',
            'kondisi' => 'Kondisi temuan bidang 1',
            'nilai_temuan' => 15000000,
            'status_tl' => 'belum_ditindaklanjuti',
        ]);

        $rekom1 = Recommendation::create([
            'temuan_id' => $temuan1->id,
            'kode_rekomendasi_id' => $kodeRekom->id,
            'uraian_rekom' => 'Kembalikan kelebihan bayar 15jt',
            'status' => 'belum_ditindaklanjuti',
        ]);

        TindakLanjut::create([
            'recommendation_id' => $rekom1->id,
            'status_verifikasi' => 'menunggu_verifikasi',
            'nilai_tindak_lanjut' => 15000000,
        ]);

        // 7. Setup LHP 2 (Irban II)
        $this->lhp2 = Lhp::create([
            'audit_assignment_id' => $assignment2->id,
            'unit_diperiksa_id' => $this->opd2->id,
            'nomor_lhp' => 'LHP/IRBAN2/002',
            'tanggal_lhp' => now()->toDateString(),
            'status' => 'final',
        ]);

        $temuan2 = Temuan::create([
            'lhp_id' => $this->lhp2->id,
            'kode_temuan_id' => $kodeTemuan->id,
            'judul_temuan' => 'Temuan di Dinas Kesehatan Irban 2',
            'kondisi' => 'Kondisi temuan bidang 2',
            'nilai_temuan' => 25000000,
            'status_tl' => 'selesai',
        ]);

        $rekom2 = Recommendation::create([
            'temuan_id' => $temuan2->id,
            'kode_rekomendasi_id' => $kodeRekom->id,
            'uraian_rekom' => 'Telah disetor 25jt',
            'status' => 'selesai',
        ]);

        TindakLanjut::create([
            'recommendation_id' => $rekom2->id,
            'status_verifikasi' => 'lunas',
            'nilai_tindak_lanjut' => 25000000,
        ]);

        // 8. Setup LHR 1 & LHR 2
        $this->seed(\Database\Seeders\LhrShsdSeeder::class);
        $jenisDoc = \App\Models\Lhr\JenisDokumenReviu::first();
        $template = \App\Models\Lhr\LhrTemplate::first();

        $this->lhr1 = Lhr::create([
            'nomor_lhr' => 'LHR/IRBAN1/001',
            'tahun_anggaran' => (int) date('Y'),
            'audit_assignment_id' => $assignment1->id,
            'unit_diperiksa_id' => $this->opd1->id,
            'jenis_dokumen_reviu_id' => $jenisDoc->id,
            'lhr_template_id' => $template->id,
            'judul' => 'LHR Reviu Irban 1',
            'status' => LhrStatus::Terbit,
        ]);

        $this->lhr2 = Lhr::create([
            'nomor_lhr' => 'LHR/IRBAN2/002',
            'tahun_anggaran' => (int) date('Y'),
            'audit_assignment_id' => $assignment2->id,
            'unit_diperiksa_id' => $this->opd2->id,
            'jenis_dokumen_reviu_id' => $jenisDoc->id,
            'lhr_template_id' => $template->id,
            'judul' => 'LHR Reviu Irban 2',
            'status' => LhrStatus::Terbit,
        ]);
    }

    public function test_irban_1_only_sees_irban_1_lhp_and_not_irban_2(): void
    {
        $scopedLhps = Lhp::forUser($this->irban1User)->pluck('nomor_lhp');

        $this->assertContains('LHP/IRBAN1/001', $scopedLhps);
        $this->assertNotContains('LHP/IRBAN2/002', $scopedLhps);
    }

    public function test_irban_2_only_sees_irban_2_lhp_and_not_irban_1(): void
    {
        $scopedLhps = Lhp::forUser($this->irban2User)->pluck('nomor_lhp');

        $this->assertContains('LHP/IRBAN2/002', $scopedLhps);
        $this->assertNotContains('LHP/IRBAN1/001', $scopedLhps);
    }

    public function test_irban_1_only_sees_irban_1_lhr_and_not_irban_2(): void
    {
        $scopedLhrs = Lhr::forUser($this->irban1User)->pluck('nomor_lhr');

        $this->assertContains('LHR/IRBAN1/001', $scopedLhrs);
        $this->assertNotContains('LHR/IRBAN2/002', $scopedLhrs);
    }

    public function test_irban_2_only_sees_irban_2_lhr_and_not_irban_1(): void
    {
        $scopedLhrs = Lhr::forUser($this->irban2User)->pluck('nomor_lhr');

        $this->assertContains('LHR/IRBAN2/002', $scopedLhrs);
        $this->assertNotContains('LHR/IRBAN1/001', $scopedLhrs);
    }

    public function test_irban_1_dashboard_displays_only_irban_1_data_without_leakage(): void
    {
        $response = $this->actingAs($this->irban1User)->get(route('dashboard'));
        $response->assertOk();

        // Must see Irban 1's unit
        $response->assertSee('Dinas Pendidikan Wilayah Irban 1');
        // Must NOT see Irban 2's unit
        $response->assertDontSee('Dinas Kesehatan Wilayah Irban 2');
        // Must NOT leak dummy 77 kejadian fallback
        $response->assertDontSee('77 Kejadian');
        $response->assertDontSee('DESA TIREMAN');
    }

    public function test_irban_2_dashboard_displays_only_irban_2_data_without_leakage(): void
    {
        $response = $this->actingAs($this->irban2User)->get(route('dashboard'));
        $response->assertOk();

        // Must see Irban 2's unit
        $response->assertSee('Dinas Kesehatan Wilayah Irban 2');
        // Must NOT see Irban 1's unit
        $response->assertDontSee('Dinas Pendidikan Wilayah Irban 1');
    }

    public function test_irban_empty_period_shows_zero_and_no_dummy_data(): void
    {
        // Query a year with 0 data (e.g. 2015)
        $response = $this->actingAs($this->irban1User)->get(route('dashboard', ['tahun' => 2015]));
        $response->assertOk();

        $response->assertDontSee('77 Kejadian');
        $response->assertDontSee('DESA TIREMAN');
        $response->assertDontSee('DESA PAKIS');
    }

    public function test_inspektur_and_super_admin_can_see_all_irban_data(): void
    {
        $scopedLhps = Lhp::forUser($this->inspekturUser)->pluck('nomor_lhp');

        $this->assertContains('LHP/IRBAN1/001', $scopedLhps);
        $this->assertContains('LHP/IRBAN2/002', $scopedLhps);

        $response = $this->actingAs($this->inspekturUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Dinas Pendidikan Wilayah Irban 1');
        $response->assertSee('Dinas Kesehatan Wilayah Irban 2');
    }

    public function test_tindak_lanjut_index_scoped_properly_for_irban(): void
    {
        $responseIrban1 = $this->actingAs($this->irban1User)->get(route('tindak-lanjuts.index'));
        $responseIrban1->assertOk();
        $responseIrban1->assertSee('LHP/IRBAN1/001');
        $responseIrban1->assertDontSee('LHP/IRBAN2/002');

        $responseIrban2 = $this->actingAs($this->irban2User)->get(route('tindak-lanjuts.index'));
        $responseIrban2->assertOk();
        $responseIrban2->assertSee('LHP/IRBAN2/002');
        $responseIrban2->assertDontSee('LHP/IRBAN1/001');
    }

    public function test_irban_role_registration_and_scoping(): void
    {
        $irbanRoleUser = User::factory()->create([
            'name' => 'Irban 3 User',
            'unit_kerja' => 'Irban III',
            'jabatan' => 'Inspektur Pembantu Wilayah III',
        ]);
        $irbanRoleUser->assignRole(User::ROLE_IRBAN);

        $this->assertTrue($irbanRoleUser->isIrban());
        $this->assertTrue($irbanRoleUser->isPengendaliTeknis());
        $this->assertTrue($irbanRoleUser->canVerify());
        $this->assertEquals('Irban (Inspektur Pembantu)', $irbanRoleUser->role_name);

        // Check permission access
        $this->assertTrue($irbanRoleUser->hasPermissionTo('lhp.view'));
        $this->assertTrue($irbanRoleUser->hasPermissionTo('lhr.review'));
        $this->assertTrue($irbanRoleUser->hasPermissionTo('tindak-lanjut.view'));
    }

    public function test_irban_lhps_index_displays_kategori_properly(): void
    {
        $response = $this->actingAs($this->irban1User)->get(route('lhps.index'));
        $response->assertOk();
        $response->assertSee('LHP/IRBAN1/001');
        $response->assertSee('PKPT');
    }

    public function test_ketua_tim_in_irban_only_sees_assigned_lhp_not_entire_irban(): void
    {
        $otherKetuaTim = User::factory()->create([
            'name' => 'Other Ketua Tim',
            'unit_kerja' => 'Irban I',
            'jabatan' => 'Auditor Ahli Muda',
        ]);
        $otherKetuaTim->assignRole(User::ROLE_KETUA_TIM);

        $this->assertFalse($otherKetuaTim->isIrban());

        $scoped = Lhp::forUser($otherKetuaTim)->pluck('nomor_lhp');
        $this->assertNotContains('LHP/IRBAN1/001', $scoped);
        $this->assertNotContains('LHP/IRBAN2/002', $scoped);
    }
}



