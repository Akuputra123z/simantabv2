<?php

namespace Tests\Feature;

use App\Actions\Lhr\CreateLhrFromAssignment;
use App\Actions\Lhr\GenerateLhrPdf;
use App\Enums\SimpulanStatus;
use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\LhrKategoriCatatan;
use App\Models\UnitDiperiksa;
use App\Models\User;
use App\Services\Lhr\LhrLinter;
use Database\Seeders\LhrRkpdKuaSeeder;
use Database\Seeders\LhrShsdSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LhrRkpdKuaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(LhrShsdSeeder::class);
        $this->seed(LhrRkpdKuaSeeder::class);
    }

    public function test_can_create_rkpd_lhr_with_hierarchical_sub_pengujians(): void
    {
        $admin = User::where('email', 'admin@inspektorat.go.id')->first()
            ?? User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole(User::ROLE_SUPER_ADMIN);

        $bappeda = UnitDiperiksa::create(['nama_unit' => 'Badan Perencanaan Pembangunan Daerah (BAPPEDA)']);

        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu RKPD 2027',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Rankir RKPD 2027',
            'jenis_kegiatan' => 'Reviu',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu RKPD 2027',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $admin->id,
            'pengendali_teknis_id' => $admin->id,
            'nomor_surat' => '000.1.2.3/020/INSP/2026',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(14),
            'status' => 'aktif',
        ]);

        $jenisRkpd = JenisDokumenReviu::where('kode', 'RKPD')->firstOrFail();

        // 1. Eksekusi CreateLhrFromAssignment
        $action = app(CreateLhrFromAssignment::class);
        $lhr = $action->execute(
            assignment: $assignment,
            unit: $bappeda,
            jenisDokumen: $jenisRkpd,
            tahunAnggaran: 2027,
            creator: $admin
        );

        // 2. Verifikasi 10 Bagian Dokumen (A s/d J)
        $this->assertCount(10, $lhr->sections);
        $this->assertDatabaseHas('lhr_sections', ['lhr_id' => $lhr->id, 'kunci' => 'pernyataan']);
        $this->assertDatabaseHas('lhr_sections', ['lhr_id' => $lhr->id, 'kunci' => 'apresiasi']);

        // 3. Verifikasi Hierarki Pengujian (14 Total: 9 Induk + 5 Sub RKPD.S6)
        $this->assertCount(14, $lhr->pengujians);

        $parentS6 = $lhr->pengujians()->where('kode', 'RKPD.S6')->firstOrFail();
        $this->assertNull($parentS6->parent_id);

        $childrenS6 = $lhr->pengujians()->where('parent_id', $parentS6->id)->get();
        $this->assertCount(5, $childrenS6);
        $this->assertTrue($childrenS6->pluck('kode')->contains('RKPD.S6.a'));
        $this->assertTrue($childrenS6->pluck('kode')->contains('RKPD.S6.e'));

        // 4. Verifikasi Linter mengenali butir belum dinilai
        $lhr->pengujians()->where('kode', 'RKPD.F')->update(['status_simpulan' => SimpulanStatus::BelumDinilai]);
        $linter = app(LhrLinter::class);
        $result = $linter->lint($lhr->fresh());
        $this->assertTrue($result->hasErrors()); // Masih ada butir belum dinilai

        // Nilai semua butir
        foreach ($lhr->pengujians as $peng) {
            $peng->update([
                'status_simpulan' => SimpulanStatus::Sesuai,
                'teks_simpulan' => 'Pengujian memenuhi kriteria.',
            ]);
        }

        $resultAfter = $linter->lint($lhr->fresh());
        $this->assertTrue($resultAfter->passes());

        // 5. Verifikasi Generate PDF untuk RKPD
        $pdfAction = app(GenerateLhrPdf::class);
        $pdfResult = $pdfAction->execute($lhr, false);
        $this->assertNotEmpty($pdfResult['binary']);
        $this->assertNotEmpty($pdfResult['sha256']);

        // 6. Verifikasi Generate Word (.docx) untuk RKPD
        $wordAction = app(\App\Actions\Lhr\GenerateLhrWord::class);
        $wordResult = $wordAction->execute($lhr, false);
        $this->assertNotEmpty($wordResult['binary']);

        // 7. Verifikasi Unduh Word via HTTP Route
        $resWord = $this->actingAs($admin)->get(route('lhr.word', $lhr->uuid));
        $resWord->assertStatus(200);
        $resWord->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        // 8. Verifikasi Pratinjau HTML Cetak Resmi
        $resPreview = $this->actingAs($admin)->get(route('lhr.preview', $lhr->uuid));
        $resPreview->assertStatus(200);
        $resPreview->assertSee('LAPORAN HASIL REVIU');
        $resPreview->assertSee('INSPEKTORAT DAERAH');
        $resPreview->assertSee('KABUPATEN REMBANG');
        $resPreview->assertSee('IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.');
        $resPreview->assertSee('I. Uraian Hasil Reviu');
    }

    public function test_can_create_kua_ppas_lhr_with_planning_categories(): void
    {
        $admin = User::where('email', 'admin@inspektorat.go.id')->first()
            ?? User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole(User::ROLE_SUPER_ADMIN);

        $bppkad = UnitDiperiksa::create(['nama_unit' => 'Badan Pendapatan, Pengelolaan Keuangan dan Aset Daerah']);

        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu KUA-PPAS 2027',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Rankir KUA-PPAS 2027',
            'jenis_kegiatan' => 'Reviu',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu KUA-PPAS 2027',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $admin->id,
            'pengendali_teknis_id' => $admin->id,
            'nomor_surat' => '000.1.2.3/026/INSP/2026',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(14),
            'status' => 'aktif',
        ]);

        $jenisKua = JenisDokumenReviu::where('kode', 'KUA_PPAS')->firstOrFail();

        $action = app(CreateLhrFromAssignment::class);
        $lhr = $action->execute(
            assignment: $assignment,
            unit: $bppkad,
            jenisDokumen: $jenisKua,
            tahunAnggaran: 2027,
            creator: $admin
        );

        // 1. Verifikasi Pengujian KUA (11 Butir: 6 Induk + 5 Sub KP.S6)
        $this->assertCount(11, $lhr->pengujians);

        $parentKpS6 = $lhr->pengujians()->where('kode', 'KP.S6')->firstOrFail();
        $this->assertNull($parentKpS6->parent_id);

        $childrenKpS6 = $lhr->pengujians()->where('parent_id', $parentKpS6->id)->get();
        $this->assertCount(5, $childrenKpS6);
        $this->assertTrue($childrenKpS6->pluck('kode')->contains('KP.S6.a'));

        // 2. Verifikasi Kategori Catatan Khusus Perencanaan & Anggaran
        $katFiskal = LhrKategoriCatatan::where('kode', 'PROYEKSI_FISKAL')->first();
        $this->assertNotNull($katFiskal);

        $katRpjmd = LhrKategoriCatatan::where('kode', 'KONSISTENSI_RPJMD')->first();
        $this->assertNotNull($katRpjmd);

        // 3. Verifikasi Generate PDF untuk KUA-PPAS
        $pdfAction = app(GenerateLhrPdf::class);
        $pdfResult = $pdfAction->execute($lhr, false);
        $this->assertNotEmpty($pdfResult['binary']);
    }

    public function test_can_view_workspace_hierarchically_and_apply_standard_template(): void
    {
        $admin = User::where('email', 'admin@inspektorat.go.id')->first()
            ?? User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole(User::ROLE_SUPER_ADMIN);

        $bappeda = UnitDiperiksa::create(['nama_unit' => 'Badan Perencanaan Pembangunan Daerah (BAPPEDA)']);
        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu RKPD 2027',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);
        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Rankir RKPD 2027',
            'jenis_kegiatan' => 'Reviu',
        ]);
        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu RKPD 2027',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $admin->id,
            'pengendali_teknis_id' => $admin->id,
            'nomor_surat' => '000.1.2.3/020/INSP/2026',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(14),
            'status' => 'aktif',
        ]);

        $jenisRkpd = JenisDokumenReviu::where('kode', 'RKPD')->firstOrFail();
        $action = app(CreateLhrFromAssignment::class);
        $lhr = $action->execute(
            assignment: $assignment,
            unit: $bappeda,
            jenisDokumen: $jenisRkpd,
            tahunAnggaran: 2027,
            creator: $admin
        );

        // 1. Kunjungi Halaman Ruang Kerja LHR
        $response = $this->actingAs($admin)->get(route('lhr.show', $lhr->uuid));
        $response->assertOk();
        $response->assertSee('RKPD.S2');
        $response->assertSee('Struktur Evaluasi Bab Sesuai Template Resmi');
        $response->assertSee('Sinkronisasi Template Rembang');
        $response->assertSee('Langkah 3: Catatan Temuan & Rekomendasi Perbaikan', false);

        // 2. Terapkan 14 Catatan & Simpulan Standar Rembang 2027
        $applyResponse = $this->actingAs($admin)->post(route('lhr.apply-standard-template', $lhr->uuid));
        $applyResponse->assertRedirect();
        $applyResponse->assertSessionHas('success');

        // 3. Verifikasi Database Terisi 14 Catatan & Status Sesuai
        $lhr->refresh();
        $s2 = $lhr->pengujians()->where('kode', 'RKPD.S2')->first();
        $this->assertEquals(SimpulanStatus::BelumSesuai, $s2->status_simpulan);
        $this->assertCount(4, $s2->catatans);

        $s6 = $lhr->pengujians()->where('kode', 'RKPD.S6')->first();
        $this->assertEquals(SimpulanStatus::BelumSesuai, $s6->status_simpulan);

        $s1 = $lhr->pengujians()->where('kode', 'RKPD.S1')->first();
        $this->assertEquals(SimpulanStatus::Sesuai, $s1->status_simpulan);

        $this->assertGreaterThanOrEqual(14, $lhr->catatans()->count());
    }

    public function test_can_edit_update_and_destroy_draft_lhr(): void
    {
        $admin = User::where('email', 'admin@inspektorat.go.id')->first()
            ?? User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole(User::ROLE_SUPER_ADMIN);

        $bappeda = UnitDiperiksa::create(['nama_unit' => 'Badan Perencanaan Pembangunan Daerah (BAPPEDA)']);
        $dinasLain = UnitDiperiksa::create(['nama_unit' => 'Badan Pendapatan, Pengelolaan Keuangan dan Aset Daerah']);

        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu RKPD 2027 Test Edit',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);
        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Rankir RKPD 2027',
            'jenis_kegiatan' => 'Reviu',
        ]);
        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu RKPD 2027',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $admin->id,
            'pengendali_teknis_id' => $admin->id,
            'nomor_surat' => '000.1.2.3/030/INSP/2026',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(14),
            'status' => 'aktif',
        ]);

        $jenisRkpd = JenisDokumenReviu::where('kode', 'RKPD')->firstOrFail();
        $action = app(CreateLhrFromAssignment::class);
        $lhr = $action->execute(
            assignment: $assignment,
            unit: $bappeda,
            jenisDokumen: $jenisRkpd,
            tahunAnggaran: 2027,
            creator: $admin
        );

        // 1. Kunjungi Halaman Edit
        $editResponse = $this->actingAs($admin)->get(route('lhr.edit', $lhr->uuid));
        $editResponse->assertOk();
        $editResponse->assertSee('Edit Informasi Dokumen LHR');

        // 2. Lakukan Update Metadata LHR
        $updateResponse = $this->actingAs($admin)->put(route('lhr.update', $lhr->uuid), [
            'unit_diperiksa_id' => $dinasLain->id,
            'tahun_anggaran' => 2027,
            'nomor_lhr' => '700/999/REVIU/INSP/2026',
            'tanggal_lhr' => '2026-06-01',
            'permohonan_nomor' => '000.7/999/2026',
            'permohonan_tanggal' => '2026-05-20',
            'tanggal_mulai' => '2026-05-22',
            'tanggal_selesai' => '2026-06-01',
        ]);
        $updateResponse->assertRedirect(route('lhr.show', $lhr->uuid));
        $updateResponse->assertSessionHas('success');

        $lhr->refresh();
        $this->assertEquals('700/999/REVIU/INSP/2026', $lhr->nomor_lhr);
        $this->assertEquals($dinasLain->id, $lhr->unit_diperiksa_id);

        // 3. Hapus Dokumen Draft LHR
        $destroyResponse = $this->actingAs($admin)->delete(route('lhr.destroy', $lhr->uuid));
        $destroyResponse->assertRedirect(route('lhr.index'));
        $destroyResponse->assertSessionHas('success');

        $this->assertSoftDeleted('lhrs', ['id' => $lhr->id]);
    }

    public function test_can_store_and_update_catatan_and_rekomendasi_with_rich_text(): void
    {
        $admin = User::where('email', 'admin@inspektorat.go.id')->first()
            ?? User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole(User::ROLE_SUPER_ADMIN);

        $bappeda = UnitDiperiksa::create(['nama_unit' => 'BAPPEDA Test Rich Editor']);
        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu RKPD Rich Editor',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);
        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Rankir RKPD 2027 Rich',
            'jenis_kegiatan' => 'Reviu',
        ]);
        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu RKPD Rich',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $admin->id,
            'pengendali_teknis_id' => $admin->id,
            'nomor_surat' => '000.1.2.3/021/INSP/2026',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(14),
            'status' => 'aktif',
        ]);

        $jenisRkpd = JenisDokumenReviu::where('kode', 'RKPD')->firstOrFail();
        $action = app(CreateLhrFromAssignment::class);
        $lhr = $action->execute(
            assignment: $assignment,
            unit: $bappeda,
            jenisDokumen: $jenisRkpd,
            tahunAnggaran: 2027,
            creator: $admin
        );

        $pengujian = $lhr->pengujians()->where('kode', 'RKPD.S2')->firstOrFail();
        $kategori = LhrKategoriCatatan::firstOrCreate(['kode' => 'TEST_KAT'], ['nama' => 'Kategori Pengujian', 'aktif' => true]);

        // 1. Simpan Catatan Baru dengan format HTML Rich Text
        $richIsi = '<p><strong>Temuan Reviu:</strong> Terdapat indikator sasaran yang belum sinkron.</p><ul><li>Indikator A: Belum ada target</li><li>Indikator B: Target berlebih</li></ul>';
        $richRekom = '<ol><li>Melakukan perbaikan target kinerja pada Bab IV.</li><li>Menyelaraskan dengan tabel SIPD-RI.</li></ol>';

        $storeResponse = $this->actingAs($admin)->post(route('lhr.store-catatan', [$lhr->uuid, $pengujian->id]), [
            'kategori_id' => $kategori->id,
            'referensi_bagian' => 'Bab II Sub 2.1.4 (Aspek Pelayanan Umum)',
            'isi' => $richIsi,
            'uraian_rekom' => $richRekom,
            'batas_waktu' => now()->addDays(30)->toDateString(),
        ]);
        $storeResponse->assertRedirect();
        $storeResponse->assertSessionHas('success');

        $createdCat = \App\Models\Lhr\LhrCatatan::where('lhr_pengujian_id', $pengujian->id)->where('isi', $richIsi)->firstOrFail();
        $this->assertEquals($richIsi, $createdCat->isi);
        $this->assertEquals($richRekom, $createdCat->rekomendasis->first()->uraian_rekom);

        // 2. Update Catatan dengan revisi HTML Rich Text
        $updatedIsi = '<p><strong>Revisi Temuan:</strong> Target telah disesuaikan tetapi pagu masih selisih <em>Rp 500.000.000</em>.</p>';
        $updatedRekom = '<p>Harap sesuaikan pagu anggaran pada <strong>Subkegiatan 1.01.02</strong> sesuai berita acara rapat.</p>';

        $updateResponse = $this->actingAs($admin)->put(route('lhr.update-catatan', [$lhr->uuid, $createdCat->id]), [
            'kategori_id' => $kategori->id,
            'referensi_bagian' => 'Bab III Sub 3.2 (Arah Kebijakan Keuangan Daerah)',
            'isi' => $updatedIsi,
            'uraian_rekom' => $updatedRekom,
            'batas_waktu' => now()->addDays(14)->toDateString(),
        ]);
        $updateResponse->assertRedirect(route('lhr.show', ['uuid' => $lhr->uuid, 'step' => 3]));
        $updateResponse->assertSessionHas('success');

        $createdCat->refresh();
        $this->assertEquals('Bab III Sub 3.2 (Arah Kebijakan Keuangan Daerah)', $createdCat->referensi_bagian);
        $this->assertEquals($updatedIsi, $createdCat->isi);
        $this->assertEquals($updatedRekom, $createdCat->rekomendasis->first()->uraian_rekom);

        // 3. Pastikan halaman workspace menampilkan HTML secara unescaped
        $viewResponse = $this->actingAs($admin)->get(route('lhr.show', $lhr->uuid));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Revisi Temuan:', false);
        $viewResponse->assertSee('Subkegiatan 1.01.02', false);

        // 4. Pastikan pratinjau laporan cetak/PDF juga menampilkan Catatan dan Rekomendasi dinamis
        $previewResponse = $this->actingAs($admin)->get(route('lhr.preview', $lhr->uuid));
        $previewResponse->assertOk();
        $previewResponse->assertSee('Revisi Temuan:', false);
        $previewResponse->assertSee('Subkegiatan 1.01.02', false);

        // 5. Pastikan unduh Word (.docx) berhasil dan menghasilkan dokumen OpenXML yang valid
        $wordResponse = $this->actingAs($admin)->get(route('lhr.word', $lhr->uuid));
        $wordResponse->assertOk();
        $wordResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $wordBinary = $wordResponse->getContent();
        $this->assertNotEmpty($wordBinary);

        // Validasi struktur arsip zip Word
        $tmpZip = tempnam(sys_get_temp_dir(), 'test_docx_') . '.docx';
        file_put_contents($tmpZip, $wordBinary);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tmpZip) === true);
        $this->assertNotFalse($zip->locateName('word/document.xml'));
        $docXml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($tmpZip);

        // Validasi XML Well-Formedness
        $xmlDoc = new \DOMDocument();
        $this->assertTrue($xmlDoc->loadXML($docXml), 'document.xml must be valid well-formed XML');
        $this->assertStringContainsString('LAPORAN HASIL REVIU', $docXml);
        $this->assertStringContainsString('Revisi Temuan', $docXml);
        $this->assertStringContainsString('Subkegiatan 1.01.02', $docXml);
    }

    public function test_can_create_lhr_via_web_form_and_handles_duplicate_number(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $bappeda = UnitDiperiksa::where('kode_unit', 'BAPPEDA')->first() ?? UnitDiperiksa::create([
            'kode_unit' => 'BAPPEDA-WEB',
            'nama_unit' => 'Badan Perencanaan Pembangunan Daerah',
            'kategori' => 'OPD',
        ]);

        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu RKPD Web Test',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);
        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Rankir RKPD Web Test',
            'jenis_kegiatan' => 'Reviu',
        ]);
        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu RKPD Web',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $admin->id,
            'pengendali_teknis_id' => $admin->id,
            'nomor_surat' => '000.1.2.3/999/INSP/2026',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(14),
            'status' => 'aktif',
        ]);

        $jenisRkpd = JenisDokumenReviu::where('kode', 'RKPD')->firstOrFail();

        // 1. Kunjungi Halaman Pembuatan LHR
        $createPageResponse = $this->actingAs($admin)->get(route('lhr.create'));
        $createPageResponse->assertOk();
        $createPageResponse->assertSee('name="nomor_lhr" value=""', false);
        $createPageResponse->assertSee('name="permohonan_nomor" value=""', false);

        // 2. Submit form untuk membuat LHR
        $storeResponse = $this->actingAs($admin)->post(route('lhr.store'), [
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $bappeda->id,
            'jenis_dokumen_reviu_id' => $jenisRkpd->id,
            'tahun_anggaran' => 2027,
            'nomor_lhr' => '700/TEST-WEB-001/REVIU/INSP/2026',
            'tanggal_lhr' => '2026-05-11',
            'ditandatangani_oleh' => $admin->id,
            'permohonan_nomor' => '000.7/250/2026',
            'permohonan_tanggal' => '2026-04-28',
            'tanggal_mulai' => '2026-05-01',
            'tanggal_selesai' => '2026-05-14',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('lhrs', [
            'nomor_lhr' => '700/TEST-WEB-001/REVIU/INSP/2026',
            'tahun_anggaran' => 2027,
        ]);

        // 3. Tes submit nomor yang sama persis (duplicate check)
        $duplicateResponse = $this->actingAs($admin)->post(route('lhr.store'), [
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $bappeda->id,
            'jenis_dokumen_reviu_id' => $jenisRkpd->id,
            'tahun_anggaran' => 2027,
            'nomor_lhr' => '700/TEST-WEB-001/REVIU/INSP/2026',
            'tanggal_lhr' => '2026-05-11',
            'ditandatangani_oleh' => $admin->id,
        ]);

        // Tidak boleh 500 error, melainkan validation error
        $duplicateResponse->assertSessionHasErrors(['nomor_lhr']);
    }

    public function test_validates_lhr_form_with_clear_indonesian_messages(): void
    {
        $admin = User::where('email', 'admin@inspektorat.go.id')->first()
            ?? User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole(User::ROLE_SUPER_ADMIN);

        // Submit form kosong
        $response = $this->actingAs($admin)->post(route('lhr.store'), []);

        $response->assertSessionHasErrors([
            'audit_assignment_id' => 'Surat Perintah Tugas (SPT) Reviu wajib dipilih.',
            'unit_diperiksa_id' => 'Perangkat Daerah Pengampu (Auditee) wajib dipilih.',
            'jenis_dokumen_reviu_id' => 'Jenis Dokumen Reviu wajib dipilih.',
            'tahun_anggaran' => 'Tahun Anggaran Dokumen wajib diisi.',
        ]);

        // Submit dengan tanggal selesai < tanggal mulai
        $dateMismatchResponse = $this->actingAs($admin)->post(route('lhr.store'), [
            'audit_assignment_id' => 9999,
            'unit_diperiksa_id' => 9999,
            'jenis_dokumen_reviu_id' => 9999,
            'tahun_anggaran' => 2026,
            'tanggal_mulai' => '2026-05-10',
            'tanggal_selesai' => '2026-05-01',
        ]);

        $dateMismatchResponse->assertSessionHasErrors([
            'tanggal_selesai' => 'Tanggal Selesai Reviu tidak boleh lebih awal dari Tanggal Mulai Reviu.',
            'audit_assignment_id' => 'Surat Perintah Tugas (SPT) yang dipilih tidak valid atau tidak ditemukan.',
            'unit_diperiksa_id' => 'Perangkat Daerah yang dipilih tidak valid atau tidak ditemukan.',
            'jenis_dokumen_reviu_id' => 'Jenis Dokumen Reviu yang dipilih tidak valid.',
        ]);
    }

    public function test_spt_already_used_does_not_appear_in_create_form_and_prevents_duplicate_store(): void
    {
        $admin = User::where('email', 'admin@inspektorat.go.id')->first()
            ?? User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole(User::ROLE_SUPER_ADMIN);

        $bappeda = UnitDiperiksa::firstOrCreate(
            ['nama_unit' => 'Badan Perencanaan Pembangunan Daerah'],
            ['kategori' => 'OPD']
        );

        $program = \App\Models\AuditProgram::create([
            'nama_program' => 'PKPT Reviu SPT Unik Test',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);
        $detail = \App\Models\AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Detail Reviu SPT Unik Test',
            'jenis_kegiatan' => 'Reviu',
        ]);

        $assignment1 = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu No Dup 1',
            'nomor_surat' => 'SPT/REVIU/001/2026',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $admin->id,
            'pengendali_teknis_id' => $admin->id,
            'tanggal_mulai' => '2026-05-01',
            'tanggal_selesai' => '2026-05-10',
            'status' => 'aktif',
        ]);

        $assignment2 = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu No Dup 2',
            'nomor_surat' => 'SPT/REVIU/002/2026',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $admin->id,
            'pengendali_teknis_id' => $admin->id,
            'tanggal_mulai' => '2026-06-01',
            'tanggal_selesai' => '2026-06-10',
            'status' => 'aktif',
        ]);

        $jenisRkpd = JenisDokumenReviu::where('kode', 'RKPD')->firstOrFail();

        // Buat LHR untuk assignment1
        $this->actingAs($admin)->post(route('lhr.store'), [
            'audit_assignment_id' => $assignment1->id,
            'unit_diperiksa_id' => $bappeda->id,
            'jenis_dokumen_reviu_id' => $jenisRkpd->id,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => '700/TEST-NO-DUP/REVIU/INSP/2026',
        ])->assertRedirect();

        // 1. Cek form /lhr/create: assignment1 TIDAK boleh muncul, assignment2 HARUS muncul
        $response = $this->actingAs($admin)->get(route('lhr.create'));
        $response->assertOk();
        $this->assertFalse($response->viewData('assignments')->contains('id', $assignment1->id));
        $this->assertTrue($response->viewData('assignments')->contains('id', $assignment2->id));
        $response->assertDontSee('Nomor SPT: ' . $assignment1->nomor_surat);
        $response->assertSee('Nomor SPT: ' . $assignment2->nomor_surat);

        // 2. Submit form memakai assignment1 lagi (duplikat SPT) harus gagal validasi
        $dupResponse = $this->actingAs($admin)->post(route('lhr.store'), [
            'audit_assignment_id' => $assignment1->id,
            'unit_diperiksa_id' => $bappeda->id,
            'jenis_dokumen_reviu_id' => $jenisRkpd->id,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => '700/TEST-ANOTHER/REVIU/INSP/2026',
        ]);

        $dupResponse->assertSessionHasErrors([
            'audit_assignment_id' => 'Surat Perintah Tugas (SPT) ini sudah memiliki dokumen LHR. Tidak dapat membuat LHR duplikat untuk SPT yang sama.',
        ]);

        // 3. Jika LHR assignment1 dibatalkan, assignment1 harus muncul kembali di /lhr/create
        $lhr1 = \App\Models\Lhr\Lhr::where('audit_assignment_id', $assignment1->id)->first();
        $lhr1->update(['status' => \App\Enums\LhrStatus::Dibatalkan]);

        $responseAfterCancel = $this->actingAs($admin)->get(route('lhr.create'));
        $responseAfterCancel->assertOk();
        $this->assertTrue($responseAfterCancel->viewData('assignments')->contains('id', $assignment1->id));
        $responseAfterCancel->assertSee('Nomor SPT: ' . $assignment1->nomor_surat);
    }

    public function test_can_upload_attachments_during_lhr_creation_and_synchronize_with_preview_pdf_and_word(): void
    {
        Storage::fake('local');

        $admin = User::where('email', 'admin@inspektorat.go.id')->first()
            ?? User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole(User::ROLE_SUPER_ADMIN);

        $bappeda = UnitDiperiksa::create(['nama_unit' => 'BAPPEDA Kabupaten Rembang']);
        $jenisRkpd = JenisDokumenReviu::where('kode', 'RKPD')->firstOrFail();

        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu Lampiran',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);
        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Rankir RKPD 2027 Lampiran',
            'jenis_kegiatan' => 'Reviu',
        ]);
        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu Lampiran RKPD',
            'jenis_pengawasan' => 'reviu',
            'status' => 'berjalan',
            'nomor_surat' => '000.1.2.3/ATT-TEST/INSP/2026',
            'tanggal_mulai' => '2026-04-20',
            'tanggal_selesai' => '2026-05-10',
            'ketua_tim_id' => $admin->id,
        ]);

        $filePdf = UploadedFile::fake()->create('draft_rancangan_akhir_rkpd_2027.pdf', 1500, 'application/pdf');
        $fileDocx = UploadedFile::fake()->create('berita_acara_musrenbang_2027.docx', 800, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $fileImg = UploadedFile::fake()->image('foto_bukti_musrenbang.jpg', 640, 480);

        // 1. Submit LHR baru dengan berkas lampiran pendukung & catatan
        $response = $this->actingAs($admin)->post(route('lhr.store'), [
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $bappeda->id,
            'jenis_dokumen_reviu_id' => $jenisRkpd->id,
            'tahun_anggaran' => 2027,
            'nomor_lhr' => '700/088/REVIU/INSP/2026',
            'tanggal_lhr' => '2026-05-11',
            'attachments' => [
                $filePdf,
                $fileDocx,
                $fileImg,
            ],
            'attachment_notes' => [
                'Dokumen Draft Rankir RKPD Bappeda',
                'Berita Acara Kesepakatan Musrenbang',
                'Foto Dokumentasi Kesepakatan Musrenbang',
            ],
        ]);

        $lhr = \App\Models\Lhr\Lhr::where('nomor_lhr', '700/088/REVIU/INSP/2026')->firstOrFail();
        $response->assertRedirect(route('lhr.show', $lhr->uuid));
        $response->assertSessionHas('success');

        // 2. Verifikasi keterkaitan polymorph Attachment
        $this->assertCount(3, $lhr->attachments);
        $att1 = $lhr->attachments->where('file_name', 'draft_rancangan_akhir_rkpd_2027.pdf')->first();
        $att2 = $lhr->attachments->where('file_name', 'berita_acara_musrenbang_2027.docx')->first();
        $att3 = $lhr->attachments->where('file_name', 'foto_bukti_musrenbang.jpg')->first();

        $this->assertNotNull($att1);
        $this->assertEquals('Dokumen Draft Rankir RKPD Bappeda', $att1->keterangan);
        $this->assertTrue(Storage::disk('local')->exists($att1->file_path));

        $this->assertNotNull($att2);
        $this->assertEquals('Berita Acara Kesepakatan Musrenbang', $att2->keterangan);
        $this->assertTrue(Storage::disk('local')->exists($att2->file_path));

        $this->assertNotNull($att3);
        $this->assertTrue($att3->isImage());
        $this->assertNotNull($att3->base64_data);
        $this->assertEquals('Foto Dokumentasi Kesepakatan Musrenbang', $att3->keterangan);
        $this->assertTrue(Storage::disk('local')->exists($att3->file_path));

        // 3. Verifikasi sinkronisasi di Ruang Kerja (Step 2)
        $workspaceResponse = $this->actingAs($admin)->get(route('lhr.show', ['uuid' => $lhr->uuid, 'step' => 2]));
        $workspaceResponse->assertOk();
        $workspaceResponse->assertSee('Berkas Lampiran Dokumen Pendukung (3)');
        $workspaceResponse->assertSee('draft_rancangan_akhir_rkpd_2027.pdf');
        $workspaceResponse->assertSee('Dokumen Draft Rankir RKPD Bappeda');
        $workspaceResponse->assertSee('berita_acara_musrenbang_2027.docx');
        $workspaceResponse->assertSee('foto_bukti_musrenbang.jpg');
        $workspaceResponse->assertSee('Foto Dokumentasi Kesepakatan Musrenbang');

        // 4. Verifikasi sinkronisasi di Pratinjau Dokumen (/preview)
        $previewResponse = $this->actingAs($admin)->get(route('lhr.preview', $lhr->uuid));
        $previewResponse->assertOk();
        $previewResponse->assertSee('DAFTAR LAMPIRAN DOKUMEN PENDUKUNG');
        $previewResponse->assertSee('draft_rancangan_akhir_rkpd_2027.pdf');
        $previewResponse->assertSee('Dokumen Draft Rankir RKPD Bappeda');
        $previewResponse->assertSee('berita_acara_musrenbang_2027.docx');
        $previewResponse->assertSee('DOKUMENTASI FOTO / BUKTI GAMBAR DOKUMEN PENDUKUNG');
        $previewResponse->assertSee('foto_bukti_musrenbang.jpg');
        $previewResponse->assertSee('Foto Dokumentasi Kesepakatan Musrenbang');

        // 5. Verifikasi sinkronisasi di Unduh PDF (/pdf)
        $signedPdfUrl = \Illuminate\Support\Facades\URL::signedRoute('lhr.pdf', $lhr->uuid);
        $pdfResponse = $this->actingAs($admin)->get($signedPdfUrl);
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($pdfResponse->getContent());

        // 6. Verifikasi sinkronisasi di Unduh Word (/word)
        $wordResponse = $this->actingAs($admin)->get(route('lhr.word', $lhr->uuid));
        $wordResponse->assertOk();
        $wordResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertNotEmpty($wordResponse->getContent());

        // 7. Verifikasi Secure Download & Secure Preview Lampiran
        $downloadResponse = $this->actingAs($admin)->get(route('attachments.download', $att1));
        $downloadResponse->assertOk();

        $showResponse = $this->actingAs($admin)->get(route('attachments.show', $att1));
        $showResponse->assertOk();
    }

    public function test_can_manage_attachments_during_lhr_edit(): void
    {
        Storage::fake('local');

        $admin = User::where('email', 'superadmin_edit_att@rembangkab.go.id')->first()
            ?? User::factory()->create([
                'email' => 'superadmin_edit_att@rembangkab.go.id',
                'name' => 'Superadmin Edit Attachment Test',
                'jenis_kelamin' => 'L',
            ]);
        $admin->syncRoles([User::ROLE_SUPER_ADMIN]);

        $bappeda = UnitDiperiksa::firstOrCreate(
            ['nama_unit' => 'BAPPEDA Test Edit Attachment'],
            ['kategori' => 'OPD']
        );

        $program = \App\Models\AuditProgram::create([
            'nama_program' => 'PKPT Reviu Edit Attachment',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);
        $detail = \App\Models\AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Detail Reviu Edit Attachment',
            'jenis_kegiatan' => 'Reviu',
        ]);
        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu Edit Attachment',
            'nomor_surat' => 'SPT/REVIU/EDIT-ATT/2026',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $admin->id,
            'pengendali_teknis_id' => $admin->id,
            'tanggal_mulai' => '2026-05-01',
            'tanggal_selesai' => '2026-05-10',
            'status' => 'aktif',
        ]);

        $jenisRkpd = JenisDokumenReviu::where('kode', 'RKPD')->firstOrFail();

        // Buat LHR awal dengan 1 lampiran
        $file1 = UploadedFile::fake()->create('lampiran_lama.pdf', 300, 'application/pdf');
        $this->actingAs($admin)->post(route('lhr.store'), [
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $bappeda->id,
            'jenis_dokumen_reviu_id' => $jenisRkpd->id,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => '700/EDIT-ATT/REVIU/INSP/2026',
            'attachments' => [$file1],
            'attachment_notes' => ['Lampiran Versi Lama'],
        ])->assertRedirect();

        $lhr = \App\Models\Lhr\Lhr::where('nomor_lhr', '700/EDIT-ATT/REVIU/INSP/2026')->firstOrFail();
        $this->assertCount(1, $lhr->attachments);
        $oldAtt = $lhr->attachments->first();

        // 1. Kunjungi halaman /lhr/{uuid}/edit
        $editResponse = $this->actingAs($admin)->get(route('lhr.edit', $lhr->uuid));
        $editResponse->assertOk();
        $editResponse->assertSee('Dokumen Lampiran Pendukung Reviu');
        $editResponse->assertSee('Sinkron ke PDF, Word & Pratinjau');
        $editResponse->assertSee('lampiran_lama.pdf');
        $editResponse->assertSee('Pilih Berkas Lampiran Baru');

        // 2. Lakukan Update: Hapus $oldAtt dan unggah lampiran baru
        $file2 = UploadedFile::fake()->create('sk_tim_perencanaan_2027.docx', 450, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $updateResponse = $this->actingAs($admin)->put(route('lhr.update', $lhr->uuid), [
            'unit_diperiksa_id' => $bappeda->id,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => '700/EDIT-ATT/REVIU/INSP/2026',
            'delete_attachments' => [$oldAtt->id],
            'attachments' => [$file2],
            'attachment_notes' => ['SK Tim Perencanaan Baru'],
        ]);

        $updateResponse->assertRedirect(route('lhr.show', $lhr->uuid));
        $updateResponse->assertSessionHas('success');

        // 3. Verifikasi lampiran lama terhapus dan lampiran baru tersimpan
        $lhr->refresh();
        $this->assertCount(1, $lhr->attachments);
        $newAtt = $lhr->attachments->first();
        $this->assertEquals('sk_tim_perencanaan_2027.docx', $newAtt->file_name);
        $this->assertEquals('SK Tim Perencanaan Baru', $newAtt->keterangan);
        $this->assertDatabaseMissing('attachments', ['id' => $oldAtt->id]);
        $this->assertDatabaseHas('attachments', ['id' => $newAtt->id]);

        // 4. Verifikasi di pratinjau dokumen
        $previewResponse = $this->actingAs($admin)->get(route('lhr.preview', $lhr->uuid));
        $previewResponse->assertOk();
        $previewResponse->assertSee('sk_tim_perencanaan_2027.docx');
        $previewResponse->assertDontSee('lampiran_lama.pdf');
    }
}


