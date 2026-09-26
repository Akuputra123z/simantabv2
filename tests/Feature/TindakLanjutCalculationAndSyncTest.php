<?php

namespace Tests\Feature;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\KodeRekomendasi;
use App\Models\KodeTemuan;
use App\Models\Lhp;
use App\Models\LhpStatistik;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\TindakLanjutCicilan;
use App\Models\UnitDiperiksa;
use App\Models\User;
use App\Services\LhpStatistikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TindakLanjutCalculationAndSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Lhp $lhp;
    protected KodeTemuan $kodeTemuan;
    protected KodeRekomendasi $kodeRekomendasi;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $unit = UnitDiperiksa::create([
            'nama_unit' => 'Inspektorat Test Unit',
            'jenis' => 'opd',
        ]);

        $program = AuditProgram::create([
            'nama_program' => 'Program Pengawasan 2026',
            'tahun' => 2026,
            'kategori' => 'reguler',
            'status' => 'disetujui',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Audit Operasional',
            'jenis_kegiatan' => 'Pemeriksaan',
            'unit_diperiksa_id' => $unit->id,
            'tim' => 'irban_1',
            'anggaran' => 10000000,
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->admin->id,
            'status' => 'berjalan',
            'nomor_surat' => 'ST/001/2026',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(30),
        ]);

        $this->lhp = Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $unit->id,
            'nomor_lhp' => 'LHP/001/2026',
            'tanggal_lhp' => now(),
            'status' => 'draft',
        ]);

        $this->seed(\Database\Seeders\KodeTemuanSeeder::class);
        $this->seed(\Database\Seeders\KodeRekomendasiSeeder::class);

        $this->kodeTemuan = KodeTemuan::first();
        $this->kodeRekomendasi = KodeRekomendasi::first();
    }

    public function test_newly_created_recommendation_and_temuan_are_marked_as_belum_ditindaklanjuti(): void
    {
        $temuan = Temuan::create([
            'lhp_id' => $this->lhp->id,
            'kode_temuan_id' => $this->kodeTemuan->id,
            'kondisi' => 'Kelebihan bayar honor',
            'nilai_temuan' => 10000000,
            'nilai_kerugian_daerah' => 10000000,
        ]);

        // Rekomendasi UANG
        $rekomUang = Recommendation::create([
            'temuan_id' => $temuan->id,
            'kode_rekomendasi_id' => $this->kodeRekomendasi->id,
            'uraian_rekom' => 'Setor ke Kas Daerah',
            'jenis_rekomendasi' => Recommendation::JENIS_UANG,
            'nilai_rekom' => 10000000,
            'batas_waktu' => now()->addDays(60),
            'status' => Recommendation::STATUS_BELUM,
        ]);

        // Rekomendasi NON-UANG
        $rekomNonUang = Recommendation::create([
            'temuan_id' => $temuan->id,
            'kode_rekomendasi_id' => $this->kodeRekomendasi->id,
            'uraian_rekom' => 'Perbaiki SOP dan tegur pejabat bersangkutan',
            'jenis_rekomendasi' => Recommendation::JENIS_ADMIN,
            'nilai_rekom' => 0,
            'batas_waktu' => now()->addDays(60),
            'status' => Recommendation::STATUS_BELUM,
        ]);

        $this->assertEquals(Recommendation::STATUS_BELUM, $rekomUang->fresh()->status);
        $this->assertEquals(Recommendation::STATUS_BELUM, $rekomNonUang->fresh()->status);

        $temuan->refresh();
        $this->assertEquals(Temuan::STATUS_BELUM, $temuan->status_tl);

        app(LhpStatistikService::class)->updateStatistik($this->lhp->id);
        $stats = LhpStatistik::where('lhp_id', $this->lhp->id)->first();

        $this->assertNotNull($stats);
        $this->assertEquals(2, $stats->total_rekomendasi);
        $this->assertEquals(2, $stats->rekom_belum);
        $this->assertEquals(0, $stats->rekom_proses);
        $this->assertEquals(0, $stats->rekom_selesai);
    }

    public function test_cicilan_creation_verification_and_cascading_sync(): void
    {
        $temuan = Temuan::create([
            'lhp_id' => $this->lhp->id,
            'kode_temuan_id' => $this->kodeTemuan->id,
            'kondisi' => 'Kelebihan bayar fisik proyek',
            'nilai_temuan' => 10000000,
            'nilai_kerugian_daerah' => 10000000,
        ]);

        $rekom = Recommendation::create([
            'temuan_id' => $temuan->id,
            'kode_rekomendasi_id' => $this->kodeRekomendasi->id,
            'uraian_rekom' => 'Setor ke Kas Daerah',
            'jenis_rekomendasi' => Recommendation::JENIS_UANG,
            'nilai_rekom' => 10000000,
            'batas_waktu' => now()->addDays(60),
        ]);

        $tl = $rekom->tindakLanjuts()->first();
        $tl->update([
            'jenis_penyelesaian' => 'cicilan',
            'is_cicilan' => true,
            'nilai_tindak_lanjut' => 10000000,
        ]);

        // Tambah cicilan ke-1: status menunggu_verifikasi
        $cicilan1 = $tl->cicilans()->create([
            'nilai_bayar' => 4000000,
            'tanggal_bayar' => now(),
            'status' => TindakLanjutCicilan::STATUS_MENUNGGU,
        ]);

        // Cicilan menunggu TIDAK menambah total_terbayar
        $tl->refresh();
        $this->assertEquals(0, (float) $tl->total_terbayar);
        $this->assertEquals(10000000, (float) $tl->sisa_belum_bayar);

        // Verifikasi cicilan ke-1: diterima
        $cicilan1->update([
            'status' => TindakLanjutCicilan::STATUS_DITERIMA,
            'diverifikasi_oleh' => $this->admin->id,
            'diverifikasi_pada' => now(),
        ]);

        $tl->refresh();
        $this->assertEquals(4000000, (float) $tl->total_terbayar);
        $this->assertEquals(6000000, (float) $tl->sisa_belum_bayar);
        $this->assertEquals('berjalan', $tl->status_verifikasi);

        $rekom->refresh();
        $this->assertEquals(Recommendation::STATUS_PROSES, $rekom->status);
        $this->assertEquals(4000000, (float) $rekom->nilai_tl_selesai);
        $this->assertEquals(6000000, (float) $rekom->nilai_sisa);

        $temuan->refresh();
        $this->assertEquals(Temuan::STATUS_PROSES, $temuan->status_tl);

        $stats = LhpStatistik::where('lhp_id', $this->lhp->id)->first();
        $this->assertEquals(1, $stats->rekom_proses);
        $this->assertEquals(0, $stats->rekom_selesai);
        $this->assertEquals(4000000, (float) $stats->total_nilai_tl_selesai);

        // Tambah cicilan ke-2 sebesar 6.000.000 dan langsung verifikasi diterima (LUNAS)
        $cicilan2 = $tl->cicilans()->create([
            'nilai_bayar' => 6000000,
            'tanggal_bayar' => now(),
            'status' => TindakLanjutCicilan::STATUS_DITERIMA,
            'diverifikasi_oleh' => $this->admin->id,
            'diverifikasi_pada' => now(),
        ]);

        $tl->refresh();
        $this->assertEquals(10000000, (float) $tl->total_terbayar);
        $this->assertEquals(0, (float) $tl->sisa_belum_bayar);
        $this->assertEquals('lunas', $tl->status_verifikasi);

        $rekom->refresh();
        $this->assertEquals(Recommendation::STATUS_SELESAI, $rekom->status);
        $this->assertEquals(10000000, (float) $rekom->nilai_tl_selesai);
        $this->assertEquals(0, (float) $rekom->nilai_sisa);

        $temuan->refresh();
        $this->assertEquals(Temuan::STATUS_SELESAI, $temuan->status_tl);

        $stats = LhpStatistik::where('lhp_id', $this->lhp->id)->first();
        $this->assertEquals(1, $stats->rekom_selesai);
        $this->assertEquals(0, $stats->rekom_proses);
        $this->assertEquals(0, $stats->rekom_belum);
        $this->assertEquals(100.0, (float) $stats->persen_selesai);
    }

    public function test_temuan_update_resets_cicilan_to_menunggu_verifikasi(): void
    {
        $this->actingAs($this->admin);

        $temuan = Temuan::create([
            'lhp_id' => $this->lhp->id,
            'kode_temuan_id' => $this->kodeTemuan->id,
            'kondisi' => 'Temuan awal',
            'nilai_temuan' => 5000000,
            'nilai_kerugian_daerah' => 5000000,
        ]);

        $rekom = Recommendation::create([
            'temuan_id' => $temuan->id,
            'kode_rekomendasi_id' => $this->kodeRekomendasi->id,
            'uraian_rekom' => 'Setor uang',
            'jenis_rekomendasi' => Recommendation::JENIS_UANG,
            'nilai_rekom' => 5000000,
            'batas_waktu' => now()->addDays(30),
        ]);

        $tl = $rekom->tindakLanjuts()->first();
        $tl->update([
            'jenis_penyelesaian' => 'cicilan',
            'is_cicilan' => true,
        ]);

        $cicilan = $tl->cicilans()->create([
            'nilai_bayar' => 2000000,
            'status' => TindakLanjutCicilan::STATUS_DITERIMA,
            'tanggal_bayar' => now(),
        ]);

        // Update nilai temuan via controller
        $response = $this->put(route('temuan.update', $temuan), [
            'kode_temuan_id' => $temuan->kode_temuan_id,
            'kondisi' => 'Temuan diperbaiki nilainya',
            'nilai_kerugian_daerah' => 8000000,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $cicilan->refresh();
        $this->assertEquals(TindakLanjutCicilan::STATUS_MENUNGGU, $cicilan->status);
    }

    public function test_tindak_lanjut_controller_update_accepts_setor_kas_without_422(): void
    {
        $this->actingAs($this->admin);

        $temuan = Temuan::create([
            'lhp_id' => $this->lhp->id,
            'kode_temuan_id' => $this->kodeTemuan->id,
            'kondisi' => 'Temuan kas',
            'nilai_temuan' => 5000000,
            'nilai_kerugian_daerah' => 5000000,
        ]);

        $rekom = Recommendation::create([
            'temuan_id' => $temuan->id,
            'kode_rekomendasi_id' => $this->kodeRekomendasi->id,
            'uraian_rekom' => 'Setor kas',
            'jenis_rekomendasi' => Recommendation::JENIS_UANG,
            'nilai_rekom' => 5000000,
            'batas_waktu' => now()->addDays(30),
        ]);

        $tl = $rekom->tindakLanjuts()->first();

        $response = $this->put(route('tindak-lanjuts.update', $tl), [
            'recommendation_id' => $rekom->id,
            'jenis_penyelesaian' => 'setor_kas',
            'nilai_tindak_lanjut' => 5000000,
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
            'status_verifikasi' => 'lunas',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('tindak-lanjuts.index'));

        $tl->refresh();
        $this->assertEquals('setor_kas', $tl->jenis_penyelesaian);
        $this->assertEquals('lunas', $tl->status_verifikasi);
    }
}
