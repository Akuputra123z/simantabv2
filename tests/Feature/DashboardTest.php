<?php

namespace Tests\Feature;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/dashboard')->assertStatus(200);
    }

    public function test_opd_users_can_visit_opd_dashboard(): void
    {
        Role::create(['name' => 'opd']);
        $user = User::factory()->create();
        $user->assignRole('opd');

        $this->actingAs($user);
        $this->get('/opd/dashboard')->assertStatus(200);
    }

    public function test_opd_users_can_visit_opd_tindak_lanjut_index_with_default_pkpt_filter(): void
    {
        Role::create(['name' => 'opd']);
        $user = User::factory()->create();
        $user->assignRole('opd');

        $this->actingAs($user);
        $response = $this->get('/opd/tindak-lanjut');

        $response->assertStatus(200);
        $response->assertViewHas('kategori', 'PKPT');
    }

    public function test_opd_users_can_visit_lhp_detail_page(): void
    {
        Role::create(['name' => 'opd']);
        $user = User::factory()->create();
        $user->assignRole('opd');

        $unit = \App\Models\UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan',
            'jenis' => 'opd',
        ]);
        $user->opdUnits()->attach($unit->id);

        $program = \App\Models\AuditProgram::create([
            'nama_program' => 'Audit Kinerja',
            'kategori' => 'PKPT',
            'tahun' => 2026,
            'status' => 'draft',
        ]);

        $detail = \App\Models\AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Detail Program 1',
            'jenis_kegiatan' => 'Pemeriksaan',
            'nomor_lhp' => 'LHP/2026/001',
            'ruang_lingkup' => 'Pendidikan',
        ]);

        $assignment = \App\Models\AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $user->id,
            'nomor_surat' => 'ST/2026/001',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'status' => 'berjalan',
        ]);

        $lhp = \App\Models\Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => 'LHP/2026/001',
            'tanggal_lhp' => now(),
            'unit_diperiksa_id' => $unit->id,
            'status' => 'final',
        ]);

        $this->actingAs($user);
        $response = $this->get("/opd/tindak-lanjut/lhp/{$lhp->id}");

        $response->assertStatus(200);
        $response->assertSee('LHP/2026/001');
    }

    public function test_opd_can_upload_and_admin_can_verify(): void
    {
        Role::create(['name' => 'opd']);
        Role::create(['name' => 'super_admin']);

        $opdUser = User::factory()->create();
        $opdUser->assignRole('opd');

        $adminUser = User::factory()->create();
        $adminUser->assignRole('super_admin');

        $unit = \App\Models\UnitDiperiksa::create([
            'nama_unit' => 'Dinas Kesehatan',
            'jenis' => 'opd',
        ]);
        $opdUser->opdUnits()->attach($unit->id);

        $program = \App\Models\AuditProgram::create([
            'nama_program' => 'Audit Dana BOK',
            'kategori' => 'PKPT',
            'tahun' => 2026,
            'status' => 'draft',
        ]);

        $detail = \App\Models\AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Pemeriksaan BOK',
            'jenis_kegiatan' => 'Pemeriksaan',
            'nomor_lhp' => 'LHP/2026/002',
            'ruang_lingkup' => 'Kesehatan',
        ]);

        $assignment = \App\Models\AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $adminUser->id,
            'nomor_surat' => 'ST/2026/002',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'status' => 'berjalan',
        ]);

        $lhp = \App\Models\Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => 'LHP/2026/002',
            'tanggal_lhp' => now(),
            'unit_diperiksa_id' => $unit->id,
            'status' => 'final',
        ]);

        $temuan = \App\Models\Temuan::create([
            'lhp_id' => $lhp->id,
            'uraian_temuan' => 'Kelebihan pembayaran insentif nakes',
            'kondisi' => 'Terdapat kelebihan pembayaran',
            'nilai_temuan' => 10000000,
        ]);

        $rekom = \App\Models\Recommendation::create([
            'temuan_id' => $temuan->id,
            'uraian_rekom' => 'Setorkan kelebihan insentif ke kas daerah',
            'jenis_rekomendasi' => 'uang',
            'nilai_rekom' => 10000000,
            'nilai_sisa' => 10000000,
            'status' => 'belum_ditindaklanjuti',
        ]);

        $tl = $rekom->tindakLanjuts()->first();

        // 1. Test OPD uploads Cicilan
        $this->actingAs($opdUser);
        $res = $this->post(route('opd.tindak-lanjut.upload', $tl), [
            'jenis_penyelesaian' => 'cicilan',
            'nilai_tindak_lanjut' => 5000000,
            'nomor_bukti' => 'STS-01/2026',
            'tanggal_bayar' => now()->toDateString(),
            'keterangan_pendukung' => 'Setoran cicilan tahap 1',
        ]);

        $res->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tindak_lanjut_cicilans', [
            'tindak_lanjut_id' => $tl->id,
            'nilai_bayar' => 5000000,
            'nomor_bukti' => 'STS-01/2026',
        ]);

        // 2. Test Admin verifikasi
        $this->actingAs($adminUser);
        $verifRes = $this->post(route('tindak-lanjuts.verifikasi-opd', $tl), [
            'status_verifikasi' => 'lunas',
        ]);

        $verifRes->assertSessionHasNoErrors();
        $tl->refresh();
        $this->assertEquals('lunas', $tl->status_verifikasi);
        $this->assertEquals(10000000, (float) $tl->total_terbayar);
    }

    public function test_opd_can_upload_barang_bast_and_admin_verifies(): void
    {
        Role::create(['name' => 'opd']);
        Role::create(['name' => 'super_admin']);

        $opdUser = User::factory()->create();
        $opdUser->assignRole('opd');

        $adminUser = User::factory()->create();
        $adminUser->assignRole('super_admin');

        $unit = \App\Models\UnitDiperiksa::create([
            'nama_unit' => 'Dinas Perhubungan',
            'jenis' => 'opd',
        ]);
        $opdUser->opdUnits()->attach($unit->id);

        $program = \App\Models\AuditProgram::create([
            'nama_program' => 'Audit Pengelolaan Aset',
            'kategori' => 'PKPT',
            'tahun' => 2026,
            'status' => 'draft',
        ]);

        $detail = \App\Models\AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Pemeriksaan Aset Randis',
            'jenis_kegiatan' => 'Pemeriksaan',
            'nomor_lhp' => 'LHP/2026/003',
            'ruang_lingkup' => 'Aset Daerah',
        ]);

        $assignment = \App\Models\AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $adminUser->id,
            'nomor_surat' => 'ST/2026/003',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'status' => 'berjalan',
        ]);

        $lhp = \App\Models\Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => 'LHP/2026/003',
            'tanggal_lhp' => now(),
            'unit_diperiksa_id' => $unit->id,
            'status' => 'final',
        ]);

        $temuan = \App\Models\Temuan::create([
            'lhp_id' => $lhp->id,
            'uraian_temuan' => 'Kendaraan dinas belum dikembalikan oleh pensiunan',
            'kondisi' => 'Randis belum diserahkan',
            'nilai_temuan' => 0,
        ]);

        $rekom = \App\Models\Recommendation::create([
            'temuan_id' => $temuan->id,
            'uraian_rekom' => 'Tarik dan amankan randis disertai Berita Acara Serah Terima (BAST)',
            'jenis_rekomendasi' => 'barang',
            'nilai_rekom' => 0,
            'nilai_sisa' => 0,
            'status' => 'belum_ditindaklanjuti',
        ]);

        $tl = $rekom->tindakLanjuts()->first();

        // 1. OPD uploads BAST
        $this->actingAs($opdUser);
        $res = $this->post(route('opd.tindak-lanjut.upload', $tl), [
            'jenis_penyelesaian' => 'pengembalian_barang',
            'nomor_bukti' => 'BAST/05/Randis/2026',
            'tanggal_bayar' => now()->toDateString(),
            'keterangan_pendukung' => 'Randis Kijang Innova nopol K-123-AB telah diserahkan kembali ke Bagian Aset',
        ]);

        $res->assertSessionHasNoErrors();
        $tl->refresh();
        $this->assertEquals('pengembalian_barang', $tl->jenis_penyelesaian);
        $this->assertStringContainsString('BAST/05/Randis/2026', $tl->keterangan_pendukung_opd);

        // 2. Admin verifies Lunas
        $this->actingAs($adminUser);
        $verifRes = $this->post(route('tindak-lanjuts.verifikasi-opd', $tl), [
            'status_verifikasi' => 'lunas',
        ]);

        $verifRes->assertSessionHasNoErrors();
        $tl->refresh();
        $this->assertEquals('lunas', $tl->status_verifikasi);
        $rekom->refresh();
        $this->assertEquals(\App\Models\Recommendation::STATUS_SELESAI, $rekom->status);
    }

    public function test_admin_can_view_tindak_lanjut_detail_and_synchronize_opd_upload(): void
    {
        Role::create(['name' => 'opd']);
        Role::create(['name' => 'super_admin']);

        $opdUser = User::factory()->create();
        $opdUser->assignRole('opd');

        $adminUser = User::factory()->create();
        $adminUser->assignRole('super_admin');

        $unit = \App\Models\UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan',
            'jenis' => 'opd',
        ]);
        $opdUser->opdUnits()->attach($unit->id);

        $program = \App\Models\AuditProgram::create([
            'nama_program' => 'Audit Operasional Sekolah',
            'kategori' => 'PKPT',
            'tahun' => 2026,
            'status' => 'draft',
        ]);

        $detail = \App\Models\AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Pemeriksaan Dana BOS',
            'jenis_kegiatan' => 'Pemeriksaan',
            'nomor_lhp' => 'LHP/2026/013',
            'ruang_lingkup' => 'BOS',
        ]);

        $assignment = \App\Models\AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $adminUser->id,
            'nomor_surat' => 'ST/2026/013',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'status' => 'berjalan',
        ]);

        $lhp = \App\Models\Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => 'LHP/2026/013',
            'tanggal_lhp' => now(),
            'unit_diperiksa_id' => $unit->id,
            'status' => 'final',
        ]);

        $temuan = \App\Models\Temuan::create([
            'lhp_id' => $lhp->id,
            'uraian_temuan' => 'Kelebihan pembayaran honorarium',
            'kondisi' => 'Terdapat kelebihan bayar',
            'nilai_temuan' => 10000000,
        ]);

        $rekom = \App\Models\Recommendation::create([
            'temuan_id' => $temuan->id,
            'uraian_rekom' => 'Setorkan kelebihan honorarium ke Kas Daerah',
            'jenis_rekomendasi' => 'uang',
            'nilai_rekom' => 10000000,
            'nilai_sisa' => 10000000,
            'status' => 'belum_ditindaklanjuti',
        ]);

        $tl = $rekom->tindakLanjuts()->first();

        // OPD uploads setoran kas
        $this->actingAs($opdUser);
        $this->post(route('opd.tindak-lanjut.upload', $tl), [
            'jenis_penyelesaian' => 'setor_kas',
            'nilai_tindak_lanjut' => 10000000,
            'nomor_bukti' => 'STS-99882',
            'keterangan_pendukung' => 'Setor penuh via Kasda',
        ]);

        $tl->refresh();
        $this->assertEquals('draft', $tl->status_opd);

        // Admin views the detail page: should display STS number and verification button
        $this->actingAs($adminUser);
        $showRes = $this->get(route('tindak-lanjuts.show', $tl));
        $showRes->assertStatus(200);
        $showRes->assertSee('STS-99882');
        $showRes->assertSee('Verifikasi Lunas');

        // Admin verifies lunas: status_opd becomes 'dikirim', total_terbayar becomes 10.000.000, sisa 0
        $verifRes = $this->post(route('tindak-lanjuts.verifikasi-opd', $tl), [
            'status_verifikasi' => 'lunas',
        ]);
        $verifRes->assertSessionHasNoErrors();

        $tl->refresh();
        $this->assertEquals('lunas', $tl->status_verifikasi);
        $this->assertEquals('dikirim', $tl->status_opd);
        $this->assertEquals(10000000, (float) $tl->total_terbayar);
        $this->assertEquals(0, (float) $tl->sisa_belum_bayar);

        $rekom->refresh();
        $this->assertEquals(\App\Models\Recommendation::STATUS_SELESAI, $rekom->status);
    }

    public function test_admin_can_visit_tindak_lanjut_lhp_index_and_view_all_recommendations_with_dropdown(): void
    {
        Role::create(['name' => 'super_admin']);
        $adminUser = User::factory()->create();
        $adminUser->assignRole('super_admin');

        $unit = \App\Models\UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pekerjaan Umum',
            'jenis' => 'opd',
        ]);

        $program = \App\Models\AuditProgram::create([
            'nama_program' => 'Audit Infrastruktur Jalan',
            'kategori' => 'PKPT',
            'tahun' => 2026,
            'status' => 'draft',
        ]);

        $detail = \App\Models\AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Pemeriksaan Aspal Hotmix',
            'jenis_kegiatan' => 'Pemeriksaan',
            'nomor_lhp' => 'LHP/2026/088',
            'ruang_lingkup' => 'Jalan',
        ]);

        $assignment = \App\Models\AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $adminUser->id,
            'nomor_surat' => 'ST/2026/088',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'status' => 'berjalan',
        ]);

        $lhp = \App\Models\Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => 'LHP/2026/088',
            'tanggal_lhp' => now(),
            'unit_diperiksa_id' => $unit->id,
            'status' => 'final',
        ]);

        $temuan = \App\Models\Temuan::create([
            'lhp_id' => $lhp->id,
            'uraian_temuan' => 'Kekurangan volume pekerjaan jalan',
            'kondisi' => 'Tebal aspal kurang 1 cm',
            'nilai_temuan' => 15000000,
        ]);

        $rekom1 = \App\Models\Recommendation::create([
            'temuan_id' => $temuan->id,
            'uraian_rekom' => 'Setor kekurangan volume ke kasda',
            'jenis_rekomendasi' => 'uang',
            'nilai_rekom' => 15000000,
            'nilai_sisa' => 15000000,
            'status' => 'belum_ditindaklanjuti',
        ]);

        // 1. Visit /tindak-lanjuts (LHP list)
        $this->actingAs($adminUser);
        $indexRes = $this->get(route('tindak-lanjuts.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('LHP/2026/088');
        $indexRes->assertSee('Dinas Pekerjaan Umum');
        $indexRes->assertSee(route('tindak-lanjuts.lhp', $lhp->id));

        // 2. Visit /tindak-lanjuts/lhp/{lhp} (Cumulative LHP detail with dropdowns)
        $detailRes = $this->get(route('tindak-lanjuts.lhp', $lhp->id));
        $detailRes->assertStatus(200);
        $detailRes->assertSee('LHP/2026/088');
        $detailRes->assertSee('Dinas Pekerjaan Umum');
        $detailRes->assertSee('Progres Komulatif LHP');
        $detailRes->assertSee('Tebal aspal kurang 1 cm');
        $detailRes->assertSee('Setor kekurangan volume ke kasda');
        $detailRes->assertSee('Verifikasi Lunas');
    }

    public function test_admin_can_create_tindak_lanjut_with_lhp_schema(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);

        $adminUser = User::factory()->create();
        $adminUser->assignRole('super_admin');

        $unit = \App\Models\UnitDiperiksa::create([
            'nama_unit' => 'Inspektorat Pembantu IV',
            'jenis' => 'opd',
        ]);

        $program = \App\Models\AuditProgram::create([
            'nama_program' => 'Pemeriksaan Kepatuhan 2026',
            'kategori' => 'PKPT',
            'tahun' => 2026,
            'status' => 'draft',
        ]);

        $detail = \App\Models\AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Audit Dana BOS',
            'jenis_kegiatan' => 'Pemeriksaan',
            'nomor_lhp' => 'LHP/2026/099',
            'ruang_lingkup' => 'Keuangan',
        ]);

        $assignment = \App\Models\AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $adminUser->id,
            'nomor_surat' => 'ST/2026/099',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(5),
            'status' => 'berjalan',
        ]);

        $lhp = \App\Models\Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => 'LHP/2026/099',
            'tanggal_lhp' => now(),
            'unit_diperiksa_id' => $unit->id,
            'status' => 'final',
        ]);

        $temuan = \App\Models\Temuan::create([
            'lhp_id' => $lhp->id,
            'uraian_temuan' => 'Pengeluaran tanpa kuitansi sah',
            'kondisi' => 'Terdapat belanja ATK tanpa bukti pengeluaran',
            'nilai_temuan' => 5000000,
        ]);

        $rekom = \App\Models\Recommendation::create([
            'temuan_id' => $temuan->id,
            'uraian_rekom' => 'Setor ke kasda sebesar Rp 5.000.000',
            'jenis_rekomendasi' => 'uang',
            'nilai_rekom' => 5000000,
            'nilai_sisa' => 5000000,
            'status' => 'belum_ditindaklanjuti',
        ]);

        $this->actingAs($adminUser);

        // 1. Visit /tindak-lanjuts/create with lhp_id prefill
        $createRes = $this->get(route('tindak-lanjuts.create', ['lhp_id' => $lhp->id, 'recommendation_id' => $rekom->id]));
        $createRes->assertStatus(200);
        $createRes->assertSee('Pencatatan Tindak Lanjut Rekomendasi');
        $createRes->assertSee('LHP/2026/099');
        $createRes->assertSee('Inspektorat Pembantu IV');

        // 2. Fetch recommendations by LHP API
        $apiRes = $this->getJson(route('tindak-lanjuts.rekom-by-lhp', $lhp->id));
        $apiRes->assertStatus(200);
        $apiRes->assertJsonFragment([
            'id' => $rekom->id,
            'jenis' => 'uang',
            'nilai_rekom' => 5000000,
        ]);

        // 3. Post invalid without recommendation_id
        $invalidRes = $this->post(route('tindak-lanjuts.store'), [
            'recommendation_id' => '',
            'jenis_penyelesaian' => 'setor_kas',
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
            'status_verifikasi' => 'lunas',
        ]);
        $invalidRes->assertSessionHasErrors('recommendation_id');

        // 4. Post new Tindak Lanjut via store
        $storeRes = $this->post(route('tindak-lanjuts.store'), [
            'recommendation_id' => $rekom->id,
            'jenis_penyelesaian' => 'setor_kas',
            'nomor_bukti' => 'STS/2026/BOS/001',
            'nilai_tindak_lanjut' => 5000000,
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
            'status_verifikasi' => 'lunas',
            'catatan_tl' => 'Penyetoran ke Kasda via Bank Jateng telah diverifikasi lunas.',
        ]);

        $storeRes->assertRedirect(route('tindak-lanjuts.lhp', $lhp->id));
        $storeRes->assertSessionHas('success');

        // 4. Verify database state
        $this->assertDatabaseHas('tindak_lanjuts', [
            'recommendation_id' => $rekom->id,
            'jenis_penyelesaian' => 'setor_kas',
            'status_verifikasi' => 'lunas',
            'total_terbayar' => 5000000,
        ]);

        $rekom->refresh();
        $this->assertEquals('selesai', $rekom->status);
    }

    public function test_tindak_lanjut_created_is_accessible_in_opd_module(): void
    {
        $opdRole = Role::firstOrCreate(['name' => 'opd']);
        $opdUser = User::factory()->create();
        $opdUser->assignRole('opd');

        $unit = \App\Models\UnitDiperiksa::create([
            'nama_unit' => 'Kecamatan Kragan',
            'kategori' => 'OPD',
        ]);
        $opdUser->opdUnits()->attach($unit->id);

        $program = AuditProgram::create([
            'nama_program' => 'Audit Kepatuhan 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'approved',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Audit Keuangan Kragan',
            'jenis_kegiatan' => 'Pemeriksaan',
            'fokus_audit' => 'Keuangan Kragan',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $opdUser->id,
            'nomor_surat' => 'ST/KRG/2026/01',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(3),
            'status' => 'approved',
        ]);

        $lhp = Lhp::create([
            'nomor_lhp' => 'LHP/KRG/2026/01',
            'tanggal_lhp' => now(),
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $unit->id,
        ]);

        $temuan = Temuan::create([
            'lhp_id' => $lhp->id,
            'uraian_temuan' => 'Kelebihan Pembayaran Perjalanan Dinas',
            'kondisi' => 'Kelebihan pembayaran SPPD',
            'nilai_temuan' => 2000000,
        ]);

        $rekom = Recommendation::create([
            'temuan_id' => $temuan->id,
            'uraian_rekom' => 'Setorkan kelebihan kas ke kas daerah',
            'jenis_rekomendasi' => 'uang',
            'nilai_rekom' => 2000000,
            'nilai_sisa' => 2000000,
            'status' => 'belum_selesai',
        ]);

        // Simpan TL sebagai admin
        $adminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin);
        $res = $this->post(route('tindak-lanjuts.store'), [
            'recommendation_id' => $rekom->id,
            'jenis_penyelesaian' => 'setor_kas',
            'nomor_bukti' => 'STS-009',
            'nilai_tindak_lanjut' => 2000000,
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
            'status_verifikasi' => 'menunggu_verifikasi',
            'catatan_tl' => 'Bukti setoran terlampir',
        ]);
        $res->assertSessionHas('success');

        // Pastikan status_opd tetap null (belum upload/terkirim oleh OPD) agar OPD yang mengunggah bukti
        $tl = TindakLanjut::where('recommendation_id', $rekom->id)->first();
        $this->assertNotNull($tl);
        $this->assertNull($tl->status_opd);

        // Login sebagai OPD user dan pastikan LHP muncul di /opd/tindak-lanjut
        $this->actingAs($opdUser);
        $opdIndexRes = $this->get('/opd/tindak-lanjut');
        $opdIndexRes->assertStatus(200);
        $opdIndexRes->assertSee('LHP/KRG/2026/01');

        // Pastikan detail LHP dapat dibuka oleh OPD user
        $opdDetailRes = $this->get(route('opd.tindak-lanjut.lhp', $lhp->id));
        $opdDetailRes->assertStatus(200);
        $opdDetailRes->assertSee('Setorkan kelebihan kas ke kas daerah');
    }
}


