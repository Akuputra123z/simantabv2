<?php

namespace Tests\Feature;

use App\Enums\LhrStatus;
use App\Enums\SimpulanStatus;
use App\Models\AuditAssignment;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\Lhr;
use App\Models\Lhr\LhrCatatan;
use App\Models\Lhr\LhrPengujian;
use App\Models\Recommendation;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LhrIrbanVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $irban;
    private User $opdUser;
    private Lhr $lhr;
    private TindakLanjut $tindakLanjut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\LhrShsdSeeder::class);

        $this->irban = User::factory()->create([
            'name' => 'Irban Wilayah I',
            'email' => 'irban1@example.com',
            'unit_kerja' => 'Irban I',
            'jabatan' => 'Irban I',
            'jenis_kelamin' => 'L',
        ]);
        $this->irban->assignRole(User::ROLE_PENGENDALI_TEKNIS);

        $this->opdUser = User::factory()->create([
            'name' => 'Admin Bappeda',
            'email' => 'bappeda@example.com',
            'unit_kerja' => 'Bappeda',
            'jabatan' => 'Staff',
            'jenis_kelamin' => 'P',
        ]);
        $this->opdUser->assignRole('opd');

        $unit = UnitDiperiksa::create([
            'nama_unit' => 'Bappeda Kabupaten Rembang',
            'kategori' => 'OPD',
        ]);
        $this->opdUser->opdUnits()->attach($unit->id);

        $jenis = JenisDokumenReviu::where('kode', 'SHSD')->first() ?? JenisDokumenReviu::first();
        $template = \App\Models\Lhr\LhrTemplate::first();

        $program = \App\Models\AuditProgram::create([
            'nama_program' => 'PKPT Reviu 2026',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);

        $detail = \App\Models\AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu Dokumen Perencanaan',
            'jenis_kegiatan' => 'Reviu',
            'personil' => '3 Orang',
            'tingkat_resiko' => 'Sedang',
            'tim' => 'Irban I',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nomor_surat' => 'ST-IRBAN-001/2026',
            'ketua_tim_id' => $this->irban->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(14),
            'pengendali_teknis_id' => $this->irban->id,
            'pengendali_teknis' => $this->irban->name,
            'created_by' => $this->irban->id,
        ]);

        $this->lhr = Lhr::create([
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $unit->id,
            'jenis_dokumen_reviu_id' => $jenis->id,
            'lhr_template_id' => $template->id,
            'tahun_anggaran' => 2026,
            'nomor_lhr' => 'LHR/001/2026',
            'tanggal_lhr' => now(),
            'status' => LhrStatus::Terbit,
            'created_by' => $this->irban->id,
        ]);

        $pengujian = LhrPengujian::create([
            'lhr_id' => $this->lhr->id,
            'kode' => 'F1',
            'judul' => 'Kesesuaian Format Dokumen',
            'grup' => 'format',
            'urutan' => 1,
            'status_simpulan' => SimpulanStatus::BelumSesuai,
        ]);

        $catatan = LhrCatatan::create([
            'lhr_pengujian_id' => $pengujian->id,
            'isi' => 'Format sampul belum mencantumkan logo daerah.',
        ]);

        $rekomendasi = Recommendation::create([
            'lhr_catatan_id' => $catatan->id,
            'uraian_rekom' => 'Perbaiki sampul dengan logo resmi Pemkab.',
            'status' => Recommendation::STATUS_BELUM,
        ]);

        $this->tindakLanjut = $rekomendasi->tindakLanjuts()->first();
        $this->tindakLanjut->update([
            'keterangan_pendukung_opd' => 'Naskah sampul telah kami revisi sesuai pedoman.',
            'status_opd' => 'dikirim',
            'dikirim_pada' => now(),
            'status_verifikasi' => 'menunggu_verifikasi',
            'upload_opd_oleh' => $this->opdUser->id,
        ]);

        $this->lhr->refreshStatistik();
    }

    public function test_irban_can_view_opd_revisi_in_workspace(): void
    {
        $response = $this->actingAs($this->irban)
            ->get(route('lhr.show', ['uuid' => $this->lhr->uuid, 'step' => 3]));

        $response->assertOk();
        $response->assertSee('Format sampul belum mencantumkan logo daerah.');
        $response->assertSee('Naskah sampul telah kami revisi sesuai pedoman.');
        $response->assertSee('Setujui Revisi (Selesai)');
        $response->assertSee('Tolak / Minta Revisi Ulang');
    }

    public function test_irban_can_approve_opd_revisi_as_selesai(): void
    {
        $response = $this->actingAs($this->irban)
            ->post(route('lhr.tindak-lanjut.verifikasi', [
                'uuid' => $this->lhr->uuid,
                'tindakLanjut' => $this->tindakLanjut->id,
            ]), [
                'status_verifikasi' => 'lunas',
            ]);

        $response->assertRedirect(route('lhr.show', ['uuid' => $this->lhr->uuid, 'step' => 3]));
        $response->assertSessionHas('success');

        $this->tindakLanjut->refresh();
        $this->assertSame('lunas', $this->tindakLanjut->status_verifikasi);
        $this->assertSame($this->irban->id, $this->tindakLanjut->diverifikasi_oleh);
        $this->assertNotNull($this->tindakLanjut->diverifikasi_pada);

        $this->lhr->refresh();
        $this->assertSame(1, $this->lhr->statistik->selesai);
        $this->assertEquals(100.0, (float) $this->lhr->statistik->persen);
    }

    public function test_irban_can_reject_opd_revisi_with_reason(): void
    {
        $response = $this->actingAs($this->irban)
            ->post(route('lhr.tindak-lanjut.tolak', [
                'uuid' => $this->lhr->uuid,
                'tindakLanjut' => $this->tindakLanjut->id,
            ]), [
                'alasan_tolak' => 'File sampul yang dilampirkan resolusinya pecah, mohon unggah ulang.',
            ]);

        $response->assertRedirect(route('lhr.show', ['uuid' => $this->lhr->uuid, 'step' => 3]));
        $response->assertSessionHas('success');

        $this->tindakLanjut->refresh();
        $this->assertSame('draft', $this->tindakLanjut->status_opd);
        $this->assertSame('File sampul yang dilampirkan resolusinya pecah, mohon unggah ulang.', $this->tindakLanjut->alasan_tolak_opd);
        $this->assertNull($this->tindakLanjut->dikirim_pada);
    }
}
