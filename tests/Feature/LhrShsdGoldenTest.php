<?php

namespace Tests\Feature;

use App\Actions\Lhr\ApproveAndIssueLhr;
use App\Actions\Lhr\CreateLhrFromAssignment;
use App\Actions\Lhr\GenerateLhrPdf;
use App\Actions\Lhr\ReviewLhr;
use App\Actions\Lhr\SubmitLhr;
use App\Enums\LhrStatus;
use App\Enums\SimpulanStatus;
use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\Lhr;
use App\Models\Lhr\LhrCatatan;
use App\Models\Lhr\LhrKategoriCatatan;
use App\Models\Recommendation;
use App\Models\UnitDiperiksa;
use App\Models\User;
use App\Services\Lhr\LhrLinter;
use App\Services\Lhr\LhrNumberingService;
use Carbon\Carbon;
use Database\Seeders\LhrShsdSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LhrShsdGoldenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(LhrShsdSeeder::class);
    }

    public function test_can_execute_full_lhr_shsd_lifecycle_golden_flow(): void
    {
        // 1. Setup Users & Roles
        $inspektur = User::factory()->create([
            'name' => 'Drs. H. Musthofa, M.Si',
            'nip' => '197001011995031001',
            'jabatan' => 'Inspektur Kabupaten Rembang',
        ]);
        $inspektur->assignRole(User::ROLE_KEPALA_INSPEKTORAT);

        $irban = User::factory()->create([
            'name' => 'Irban Wilayah I',
            'nip' => '197502022000031002',
            'jabatan' => 'Pengendali Teknis / Irban I',
        ]);
        $irban->assignRole(User::ROLE_PENGENDALI_TEKNIS);

        $ketuaTim = User::factory()->create([
            'name' => 'Ahmad Fauzi, SE',
            'nip' => '198203032008011003',
            'jabatan' => 'Auditor Muda / Ketua Tim',
        ]);
        $ketuaTim->assignRole(User::ROLE_KETUA_TIM);

        $anggota = User::factory()->create([
            'name' => 'Siti Aminah, S.Ak',
            'nip' => '199004042015022004',
            'jabatan' => 'Auditor Pertama / Anggota',
        ]);
        $anggota->assignRole(User::ROLE_ANGGOTA);

        // 2. Setup Auditee (BPPKAD)
        $bppkad = UnitDiperiksa::create([
            'nama_unit' => 'Badan Pendapatan, Pengelolaan Keuangan dan Aset Daerah (BPPKAD)',
        ]);

        // 3. Setup PKPT & SPT Reviu SHSD 2027
        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu Dokumen Keuangan Daerah 2026',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Rancangan SHSD TA 2027',
            'jenis_kegiatan' => 'Reviu',
            'personil' => '5 Orang',
            'tingkat_resiko' => 'Sedang',
            'tim' => 'Irban I',
        ]);

        $spt = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu SHSD',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $ketuaTim->id,
            'pengendali_teknis_id' => $irban->id,
            'nomor_surat' => '000.1.2.3/026/REVIU/INSP/2026',
            'tanggal_mulai' => Carbon::parse('2026-06-17'),
            'tanggal_selesai' => Carbon::parse('2026-06-25'),
            'status' => 'berjalan',
        ]);
        $spt->members()->attach($anggota->id, ['jabatan_tim' => 'Anggota Tim']);

        // 4. Action: Buat LHR dari SPT
        $jenisShsd = JenisDokumenReviu::where('kode', 'SHSD')->firstOrFail();

        $actionCreate = new CreateLhrFromAssignment;
        $lhr = $actionCreate->execute(
            assignment: $spt,
            unit: $bppkad,
            jenisDokumen: $jenisShsd,
            tahunAnggaran: 2027,
            permohonanNomor: '900/123/2026',
            permohonanTanggal: Carbon::parse('2026-06-15'),
            tanggalMulai: Carbon::parse('2026-06-17'),
            tanggalSelesai: Carbon::parse('2026-06-25'),
            creator: $ketuaTim
        );

        $this->assertEquals(LhrStatus::Draft, $lhr->status);
        $this->assertCount(6, $lhr->pengujians);
        $this->assertCount(6, $lhr->dasarHukums);
        $this->assertCount(9, $lhr->sections);

        // Verifikasi placeholder telah diganti di bagian naratif
        $secPernyataan = $lhr->sections()->where('kunci', 'pernyataan')->first();
        $this->assertStringContainsString('2027', $secPernyataan->isi);
        $this->assertStringNotContainsString('{{tahun}}', $secPernyataan->isi);

        // 5. Kerjakan Pengujian
        // Pengujian 1: Sesuai
        $p1 = $lhr->pengujians()->where('kode', 'SHSD.S1')->first();
        $p1->update([
            'status_simpulan' => SimpulanStatus::Sesuai,
            'teks_simpulan' => 'Proses penyusunan rancangan SHSD telah dilaksanakan sesuai dengan SOP penyusunan yang berlaku.',
        ]);

        // Pengujian 2: Sebagian Sesuai (Ada Catatan + Rekomendasi)
        $p2 = $lhr->pengujians()->where('kode', 'SHSD.S2')->first();
        $p2->update([
            'status_simpulan' => SimpulanStatus::SebagianSesuai,
            'teks_simpulan' => 'Terdapat penyesuaian batasan pagu honorarium yang belum sepenuhnya mengacu pada Standar Harga Satuan Regional.',
        ]);

        $katStandar = LhrKategoriCatatan::where('kode', 'STANDAR_REGIONAL')->first();
        $catatan = LhrCatatan::create([
            'lhr_pengujian_id' => $p2->id,
            'kategori_id' => $katStandar->id,
            'referensi_bagian' => 'Tabel Belanja Honorarium',
            'isi' => 'Standar honorarium narasumber seminar diusulkan Rp 1.200.000/jam, melampaui batas Perpres 72/2025 sebesar Rp 1.000.000/jam.',
            'urutan' => 1,
            'created_by' => $ketuaTim->id,
        ]);

        // Hubungkan ke tabel recommendations (Shared Pipeline)
        Recommendation::create([
            'lhr_catatan_id' => $catatan->id,
            'uraian_rekom' => 'BPPKAD agar menyesuaikan pagu honorarium narasumber menjadi maksimal Rp 1.000.000/jam sesuai Perpres 72/2025.',
            'jenis_rekomendasi' => Recommendation::JENIS_ADMIN,
            'batas_waktu' => Carbon::parse('2026-07-25'),
            'status' => Recommendation::STATUS_BELUM,
            'status_tl' => Recommendation::STATUS_TL_BELUM_SELESAI,
            'created_by' => $ketuaTim->id,
        ]);

        // Pengujian 3 s/d 6: Sesuai
        foreach (['SHSD.S3', 'SHSD.S4', 'SHSD.S5', 'SHSD.S6'] as $kd) {
            $lhr->pengujians()->where('kode', $kd)->first()->update([
                'status_simpulan' => SimpulanStatus::Sesuai,
                'teks_simpulan' => 'Kesesuaian dan kepatutan telah dipenuhi dengan baik.',
            ]);
        }

        // Set tanggal laporan LHR
        $lhr->update(['tanggal_lhr' => Carbon::parse('2026-06-26')]);

        // 6. Jalankan Linter
        $linter = new LhrLinter;
        $lintResult = $linter->lint($lhr);
        $this->assertTrue($lintResult->passes(), 'Linter harus lolos tanpa galat (errors).');

        // 7. Action: Submit LHR
        $actionSubmit = new SubmitLhr($linter);
        $lhr = $actionSubmit->execute($lhr);
        $this->assertEquals(LhrStatus::MenungguTelaah, $lhr->status);

        // 8. Action: Review LHR oleh Pengendali Teknis (Irban)
        $actionReview = new ReviewLhr;
        $lhr = $actionReview->execute($lhr, $irban, 'setujui');
        $this->assertEquals(LhrStatus::MenungguPersetujuan, $lhr->status);

        // 9. Action: Approve & Issue LHR oleh Kepala Inspektorat
        $numberingService = new LhrNumberingService;
        $actionApprove = new ApproveAndIssueLhr($numberingService);
        $lhr = $actionApprove->execute($lhr, $inspektur);

        $this->assertEquals(LhrStatus::Terbit, $lhr->status);
        $this->assertEquals('700/001/REVIU/INSP/2026', $lhr->nomor_lhr);
        $this->assertEquals($inspektur->id, $lhr->ditandatangani_oleh);
        $this->assertNotNull($lhr->snapshot_terbit);

        // 10. Generate PDF
        $actionPdf = app(GenerateLhrPdf::class);
        $pdfResult = $actionPdf->execute($lhr, false); // generate binary

        $this->assertNotEmpty($pdfResult['binary']);
        $this->assertEquals(64, strlen($pdfResult['sha256']));

        // 11. Verifikasi Pipeline Rekomendasi
        $this->assertEquals(1, Recommendation::forLhrCatatan()->count());
        $this->assertEquals(0, Recommendation::forTemuan()->count());

        $rekomLhr = Recommendation::forLhrCatatan()->first();
        $this->assertEquals($catatan->id, $rekomLhr->lhr_catatan_id);
        $this->assertNull($rekomLhr->temuan_id);
        $this->assertEquals(Recommendation::STATUS_TL_BELUM_SELESAI, $rekomLhr->status_tl);

        // Progres statistik LHR
        $this->assertEquals(1, $lhr->statistik->total_rekomendasi);
        $this->assertEquals(0, $lhr->statistik->selesai);
        $this->assertEquals(0.00, $lhr->statistik->persen);
    }
}
