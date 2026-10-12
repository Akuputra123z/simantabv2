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
use App\Models\UnitDiperiksa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TindakLanjutCreateFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Lhp $lhp;
    protected Temuan $temuan;
    protected Recommendation $rekomUang;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $unit = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan',
            'jenis' => 'opd',
        ]);

        $program = AuditProgram::create([
            'nama_program' => 'Pengawasan Reguler 2026',
            'tahun' => 2026,
            'kategori' => 'reguler',
            'status' => 'disetujui',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Audit Operasional Sekolah',
            'jenis_kegiatan' => 'Pemeriksaan',
            'unit_diperiksa_id' => $unit->id,
            'tim' => 'irban_1',
            'anggaran' => 10000000,
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->admin->id,
            'status' => 'berjalan',
            'nomor_surat' => 'ST/002/2026',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(30),
        ]);

        $this->lhp = Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $unit->id,
            'nomor_lhp' => 'LHP/002/2026',
            'tanggal_lhp' => now(),
            'status' => 'terbit',
        ]);

        $this->seed(\Database\Seeders\KodeTemuanSeeder::class);
        $this->seed(\Database\Seeders\KodeRekomendasiSeeder::class);

        $this->temuan = Temuan::create([
            'lhp_id' => $this->lhp->id,
            'kode_temuan_id' => KodeTemuan::first()->id,
            'kondisi' => 'Kelebihan pembayaran belanja modal pengadaan laptop',
            'nilai_temuan' => 10000000,
            'nilai_kerugian_daerah' => 10000000,
        ]);

        $this->rekomUang = Recommendation::create([
            'temuan_id' => $this->temuan->id,
            'kode_rekomendasi_id' => KodeRekomendasi::first()->id,
            'uraian_rekom' => 'Setor ke Kas Daerah sebesar Rp 10.000.000',
            'jenis_rekomendasi' => Recommendation::JENIS_UANG,
            'nilai_rekom' => 10000000,
            'nilai_sisa' => 10000000,
            'batas_waktu' => now()->addDays(60),
            'status' => Recommendation::STATUS_BELUM,
        ]);
    }

    public function test_create_tindak_lanjut_finansial_setor_kas_lunas(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('tindak-lanjuts.store'), [
            'recommendation_id' => $this->rekomUang->id,
            'jenis_penyelesaian' => 'setor_kas',
            'nilai_tindak_lanjut' => 10000000,
            'nomor_bukti' => 'STS-2026/001',
            'tanggal_jatuh_tempo' => now()->addDays(60)->toDateString(),
            'status_verifikasi' => 'lunas',
            'catatan_tl' => 'Penyetoran lunas melalui Kasda Bank Jateng',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('tindak-lanjuts.lhp', $this->lhp->id));

        $tl = TindakLanjut::where('recommendation_id', $this->rekomUang->id)->first();
        $this->assertNotNull($tl);
        $this->assertEquals('setor_kas', $tl->jenis_penyelesaian);
        $this->assertEquals('lunas', $tl->status_verifikasi);
        $this->assertEquals(10000000, (float) $tl->total_terbayar);
        $this->assertEquals(0, (float) $tl->sisa_belum_bayar);

        $this->rekomUang->refresh();
        $this->assertEquals(Recommendation::STATUS_SELESAI, $this->rekomUang->status);
        $this->assertEquals(10000000, (float) $this->rekomUang->nilai_tl_selesai);
        $this->assertEquals(0, (float) $this->rekomUang->nilai_sisa);

        $this->temuan->refresh();
        $this->assertEquals(Temuan::STATUS_SELESAI, $this->temuan->status_tl);
    }

    public function test_create_tindak_lanjut_finansial_setor_kas_sebagian_berjalan(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('tindak-lanjuts.store'), [
            'recommendation_id' => $this->rekomUang->id,
            'jenis_penyelesaian' => 'setor_kas',
            'nilai_tindak_lanjut' => 4000000,
            'nomor_bukti' => 'STS-2026/002',
            'tanggal_jatuh_tempo' => now()->addDays(60)->toDateString(),
            'status_verifikasi' => 'berjalan',
            'catatan_tl' => 'Setoran tahap awal sebesar 4 juta rupiah',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('tindak-lanjuts.lhp', $this->lhp->id));

        $tl = TindakLanjut::where('recommendation_id', $this->rekomUang->id)->first();
        $this->assertNotNull($tl);
        $this->assertEquals('setor_kas', $tl->jenis_penyelesaian);
        $this->assertEquals('berjalan', $tl->status_verifikasi);
        $this->assertEquals(4000000, (float) $tl->total_terbayar);
        $this->assertEquals(6000000, (float) $tl->sisa_belum_bayar);

        $this->rekomUang->refresh();
        $this->assertEquals(Recommendation::STATUS_PROSES, $this->rekomUang->status);
        $this->assertEquals(4000000, (float) $this->rekomUang->nilai_tl_selesai);
        $this->assertEquals(6000000, (float) $this->rekomUang->nilai_sisa);

        $this->temuan->refresh();
        $this->assertEquals(Temuan::STATUS_PROSES, $this->temuan->status_tl);
    }

    public function test_create_tindak_lanjut_accepts_formatted_rupiah_string(): void
    {
        $this->actingAs($this->admin);

        // Simulasi input formatted dari user/browser
        $response = $this->post(route('tindak-lanjuts.store'), [
            'recommendation_id' => $this->rekomUang->id,
            'jenis_penyelesaian' => 'setor_kas',
            'nilai_tindak_lanjut' => 'Rp 10.000.000',
            'nomor_bukti' => 'STS-2026/003',
            'tanggal_jatuh_tempo' => now()->addDays(60)->toDateString(),
            'status_verifikasi' => 'lunas',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('tindak-lanjuts.lhp', $this->lhp->id));

        $tl = TindakLanjut::where('recommendation_id', $this->rekomUang->id)->first();
        $this->assertEquals(10000000, (float) $tl->total_terbayar);
    }

    public function test_create_tindak_lanjut_non_finansial_administrasi(): void
    {
        $this->actingAs($this->admin);

        $rekomAdmin = Recommendation::create([
            'temuan_id' => $this->temuan->id,
            'kode_rekomendasi_id' => KodeRekomendasi::first()->id,
            'uraian_rekom' => 'Perbaiki SOP dan beri teguran tertulis',
            'jenis_rekomendasi' => Recommendation::JENIS_ADMIN,
            'nilai_rekom' => 0,
            'nilai_sisa' => 0,
            'batas_waktu' => now()->addDays(60),
            'status' => Recommendation::STATUS_BELUM,
        ]);

        $response = $this->post(route('tindak-lanjuts.store'), [
            'recommendation_id' => $rekomAdmin->id,
            'jenis_penyelesaian' => 'perbaikan_administrasi',
            'nilai_tindak_lanjut' => 0,
            'nomor_bukti' => 'SK-800/2026/01',
            'tanggal_jatuh_tempo' => now()->addDays(60)->toDateString(),
            'status_verifikasi' => 'lunas',
            'catatan_tl' => 'Telah diterbitkan SK Teguran dan SOP baru',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('tindak-lanjuts.lhp', $this->lhp->id));

        $tl = TindakLanjut::where('recommendation_id', $rekomAdmin->id)->first();
        $this->assertNotNull($tl);
        $this->assertEquals('perbaikan_administrasi', $tl->jenis_penyelesaian);
        $this->assertEquals('lunas', $tl->status_verifikasi);
        $this->assertEquals(1, (float) $tl->total_terbayar);
        $this->assertEquals(0, (float) $tl->sisa_belum_bayar);

        $rekomAdmin->refresh();
        $this->assertEquals(Recommendation::STATUS_SELESAI, $rekomAdmin->status);
    }

    public function test_create_page_renders_and_populates_recommendations_with_selected_lhp(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('tindak-lanjuts.create', ['lhp_id' => $this->lhp->id]));

        $response->assertOk();
        $response->assertSee($this->lhp->nomor_lhp);
        $response->assertSee('Pencatatan Tindak Lanjut Rekomendasi');
    }
}
