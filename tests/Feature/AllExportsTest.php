<?php

namespace Tests\Feature;

use App\Exports\DetailProgramExport;
use App\Exports\RekapPerLhpExport;
use App\Exports\RekapSemuaLhpExport;
use App\Exports\TemplateAuditDetailExport;
use App\Exports\TemplateUnitDiperiksaExport;
use App\Exports\UnitDiperiksaExport;
use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\KodeTemuan;
use App\Models\KodeRekomendasi;
use App\Models\Lhp;
use App\Models\LhpStatistik;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AllExportsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');
    }

    public function test_template_audit_detail_export_generates_valid_binary(): void
    {
        $export = new TemplateAuditDetailExport();
        $content = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        $this->assertNotEmpty($content);
    }

    public function test_template_unit_diperiksa_export_generates_valid_binary(): void
    {
        $export = new TemplateUnitDiperiksaExport();
        $content = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        $this->assertNotEmpty($content);
    }

    public function test_unit_diperiksa_export_with_data_and_empty(): void
    {
        // 1. Empty units
        $exportEmpty = new UnitDiperiksaExport(collect());
        $contentEmpty = Excel::raw($exportEmpty, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertNotEmpty($contentEmpty);

        // 2. Populated units
        UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan',
            'kategori' => 'OPD',
            'nama_kecamatan' => 'Rembang',
            'alamat' => 'Jl. Pemuda',
            'telepon' => '08123',
            'keterangan' => 'Ket',
        ]);
        $export = new UnitDiperiksaExport(UnitDiperiksa::all());
        $content = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertNotEmpty($content);
    }

    public function test_detail_program_export_with_data_and_empty(): void
    {
        $program = AuditProgram::create([
            'nama_program' => 'Audit 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        // 1. Empty details
        $exportEmpty = new DetailProgramExport($program, collect());
        $contentEmpty = Excel::raw($exportEmpty, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertNotEmpty($contentEmpty);

        // 2. With details
        AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Sub Program A',
            'jenis_kegiatan' => 'Audit',
            'objek_pengawasan' => 'Dinas A',
            'ruang_lingkup' => 'Keuangan',
            'personil' => '4',
            'tujuan' => 'Evaluasi',
            'anggaran' => 10000000,
            'tingkat_resiko' => 'Tinggi',
            'laporan_akhir' => '1',
            'jadwal' => '01/01/2026',
            'tim' => 'Irban I',
            'status' => 'aktif',
        ]);

        $export = new DetailProgramExport($program, $program->details);
        $content = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertNotEmpty($content);
    }

    public function test_rekap_semua_lhp_export_with_empty_and_populated_data(): void
    {
        // 1. Empty collection
        $exportEmpty = new RekapSemuaLhpExport(collect());
        $contentEmpty = Excel::raw($exportEmpty, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertNotEmpty($contentEmpty);

        // 2. Populated LHP
        $program = AuditProgram::create([
            'nama_program' => 'Audit 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Sub Program',
            'jenis_kegiatan' => 'Audit',
            'tim' => 'Irban I',
            'anggaran' => 1000000,
            'status' => 'aktif',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->admin->id,
            'nomor_surat' => 'ST/01/2026',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $unit = UnitDiperiksa::create([
            'nama_unit' => 'Dinkes',
            'kategori' => 'OPD',
        ]);

        $lhp = Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => '700/01/2026',
            'tanggal_lhp' => now()->toDateString(),
            'semester' => 1,
            'status' => 'final',
            'unit_diperiksa_id' => $unit->id,
            'created_by' => $this->admin->id,
        ]);

        LhpStatistik::create([
            'lhp_id' => $lhp->id,
            'total_temuan' => 1,
            'total_rekomendasi' => 2,
            'rekom_selesai' => 1,
            'rekom_proses' => 1,
            'rekom_belum' => 0,
            'total_kerugian' => 5000000,
            'total_nilai_tl_selesai' => 3000000,
            'total_sisa_kerugian' => 2000000,
            'persen_selesai_gabungan' => 60,
        ]);

        $lhps = Lhp::with(['statistik', 'unitDiperiksa', 'auditAssignment.auditProgramDetail', 'auditAssignment.auditProgram'])->get();

        $export = new RekapSemuaLhpExport($lhps);
        $content = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertNotEmpty($content);
    }

    public function test_rekap_per_lhp_export_with_empty_temuans_and_with_full_data(): void
    {
        $program = AuditProgram::create([
            'nama_program' => 'Audit 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Sub Program',
            'jenis_kegiatan' => 'Audit',
            'tim' => 'Irban I',
            'anggaran' => 1000000,
            'status' => 'aktif',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->admin->id,
            'nomor_surat' => 'ST/01/2026',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $unit = UnitDiperiksa::create([
            'nama_unit' => 'Dinkes',
            'kategori' => 'OPD',
        ]);

        $lhp = Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => '700/01/2026',
            'tanggal_lhp' => now()->toDateString(),
            'semester' => 1,
            'status' => 'final',
            'unit_diperiksa_id' => $unit->id,
            'created_by' => $this->admin->id,
        ]);

        // 1. Test when LHP has NO findings (temuans empty)
        $lhp->load(['statistik', 'unitDiperiksa', 'auditAssignment.auditProgramDetail', 'auditAssignment.auditProgram', 'temuans.recommendations']);
        $exportEmptyTemuans = new RekapPerLhpExport($lhp);
        $contentEmpty = Excel::raw($exportEmptyTemuans, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertNotEmpty($contentEmpty);

        // 2. Test when LHP has full findings, recommendations, and tindak lanjut
        $kodeTemuan = KodeTemuan::create([
            'kode' => '1.01.01',
            'kode_numerik' => '10101',
            'kel' => 1,
            'sub_kel' => 1,
            'jenis' => 1,
            'kelompok' => 'Kel 1',
            'sub_kelompok' => 'Sub 1',
            'deskripsi' => 'Kerugian',
        ]);

        $kodeRekom = KodeRekomendasi::create([
            'kode' => '01',
            'kode_numerik' => 1,
            'deskripsi' => 'Setor Kas',
        ]);

        $temuan = Temuan::create([
            'lhp_id' => $lhp->id,
            'kode_temuan_id' => $kodeTemuan->id,
            'kondisi' => 'Kelebihan bayar',
            'sebab' => 'Lalai',
            'akibat' => 'Rugi',
            'nilai_kerugian_daerah' => 10000000,
            'created_by' => $this->admin->id,
        ]);

        $rekom = Recommendation::create([
            'temuan_id' => $temuan->id,
            'kode_rekomendasi_id' => $kodeRekom->id,
            'jenis_rekomendasi' => 'uang',
            'uraian_rekom' => 'Setor kas daerah',
            'nilai_rekom' => 10000000,
            'status' => 'proses',
            'created_by' => $this->admin->id,
        ]);

        TindakLanjut::create([
            'recommendation_id' => $rekom->id,
            'jenis_penyelesaian' => 'setor_kas',
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
            'status_verifikasi' => 'berjalan',
            'nilai_tindak_lanjut' => 5000000,
            'created_by' => $this->admin->id,
        ]);

        $lhp->refresh();
        $lhp->load(['statistik', 'unitDiperiksa', 'auditAssignment.auditProgramDetail', 'auditAssignment.auditProgram', 'temuans.recommendations.tindakLanjuts', 'temuans.kodeTemuan', 'temuans.recommendations.kodeRekomendasi']);

        $exportFull = new RekapPerLhpExport($lhp);
        $contentFull = Excel::raw($exportFull, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertNotEmpty($contentFull);
    }

    public function test_controller_export_endpoints(): void
    {
        // 1. Download template sub program
        $res1 = $this->actingAs($this->admin)->get(route('audit-program-detail.download-template'));
        $res1->assertOk();

        // 2. Download template unit diperiksa
        $res2 = $this->actingAs($this->admin)->get(route('unit-diperiksa.download-template'));
        $res2->assertOk();

        // 3. Export unit diperiksa
        $res3 = $this->actingAs($this->admin)->get(route('unit-diperiksa.export'));
        $res3->assertOk();

        // 4. Export semua LHP
        $res4 = $this->actingAs($this->admin)->get(route('laporan.download-excel-semua'));
        $res4->assertOk();
    }
}
