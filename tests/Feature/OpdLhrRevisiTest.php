<?php

namespace Tests\Feature;

use App\Enums\LhrStatus;
use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\Lhr;
use App\Models\Lhr\LhrCatatan;
use App\Models\Lhr\LhrPengujian;
use App\Models\Recommendation;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OpdLhrRevisiTest extends TestCase
{
    use RefreshDatabase;

    private User $opdUser;
    private UnitDiperiksa $unit;
    private Lhr $lhr;
    private Recommendation $rekomendasi;
    private TindakLanjut $tindakLanjut;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\LhrShsdSeeder::class);

        $this->unit = UnitDiperiksa::create([
            'nama_unit' => 'Bappeda Rembang',
            'kategori'  => 'OPD',
        ]);

        $this->opdUser = User::factory()->create([
            'name'  => 'Operator Bappeda',
            'email' => 'bappeda@rembangkab.go.id',
        ]);
        $this->opdUser->assignRole(User::ROLE_OPD);
        $this->opdUser->opdUnits()->attach($this->unit->id);

        $program = AuditProgram::create([
            'nama_program' => 'PKPT Reviu 2026',
            'tahun'        => 2026,
            'kategori'     => 'Reviu Dokumen',
            'status'       => 'disetujui',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id'    => $program->id,
            'nama_detail_program' => 'Reviu Dokumen Perencanaan',
            'jenis_kegiatan'      => 'Reviu',
            'personil'            => '3 Orang',
            'tingkat_resiko'       => 'Sedang',
            'tim'                 => 'Irban I',
        ]);

        $ketua = User::factory()->create(['jenis_kelamin' => 'L']);
        $ketua->assignRole(User::ROLE_KETUA_TIM);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim'                => 'Tim Reviu',
            'ketua_tim_id'            => $ketua->id,
            'jenis_pengawasan'        => 'reviu',
            'nomor_surat'             => '000.1.2.3/888/REVIU/2026',
            'tanggal_mulai'           => Carbon::now()->subDays(5),
            'tanggal_selesai'         => Carbon::now(),
            'status'                  => 'selesai',
        ]);

        $jenis = JenisDokumenReviu::where('kode', 'SHSD')->first() ?? JenisDokumenReviu::first();
        $template = \App\Models\Lhr\LhrTemplate::first();

        $this->lhr = Lhr::create([
            'audit_assignment_id'    => $assignment->id,
            'unit_diperiksa_id'      => $this->unit->id,
            'jenis_dokumen_reviu_id' => $jenis->id,
            'lhr_template_id'        => $template->id,
            'tahun_anggaran'         => 2026,
            'nomor_lhr'              => '700/01/REVIU/2026',
            'status'                 => LhrStatus::Terbit,
            'tanggal_lhr'            => now(),
        ]);

        $pengujian = LhrPengujian::create([
            'lhr_id'          => $this->lhr->id,
            'kode'            => 'RKPD.01',
            'judul'           => 'Kesesuaian Asta Cita',
            'status_simpulan' => 'belum_sesuai',
            'urutan'          => 1,
        ]);

        $catatan = LhrCatatan::create([
            'lhr_pengujian_id' => $pengujian->id,
            'isi'              => 'Indikator belum selaras dengan prioritas nasional',
            'urutan'           => 1,
        ]);

        $this->rekomendasi = Recommendation::create([
            'lhr_catatan_id' => $catatan->id,
            'uraian_rekom'   => 'Lakukan penyesuaian target indikator pada Bab IV',
            'status'         => 'belum_ditindaklanjuti',
        ]);

        $this->tindakLanjut = TindakLanjut::create([
            'recommendation_id' => $this->rekomendasi->id,
            'status_verifikasi' => 'menunggu_verifikasi',
        ]);
    }

    public function test_opd_can_view_lhr_revisi_workspace()
    {
        $response = $this->actingAs($this->opdUser)->get(route('opd.lhr.show', $this->lhr->uuid));

        $response->assertOk();
        $response->assertSee('Hasil Reviu: ' . ($this->lhr->jenisDokumen->nama ?? ''));
        $response->assertSee('Lakukan penyesuaian target indikator pada Bab IV');
        $response->assertSee('Lembar Kerja Revisi & Tindak Lanjut OPD', false);
    }

    public function test_opd_can_upload_and_save_draft_revisi()
    {
        $file = UploadedFile::fake()->create('naskah_revisi_rkpd.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->opdUser)->post(
            route('opd.lhr.tindak-lanjut.upload', [$this->lhr->uuid, $this->tindakLanjut->id]),
            [
                'uraian_tindak_lanjut' => 'Telah disesuaikan pada Bab IV matriks target',
                'attachments'          => [$file],
            ]
        );

        $response->assertRedirect(route('opd.lhr.show', $this->lhr->uuid));
        $response->assertSessionHas('success');

        $this->tindakLanjut->refresh();
        $this->assertSame('Telah disesuaikan pada Bab IV matriks target', $this->tindakLanjut->uraian_tindak_lanjut);
        $this->assertSame('draft', $this->tindakLanjut->status_opd);
        $this->assertCount(1, $this->tindakLanjut->attachments);
    }

    public function test_opd_can_delete_draft_attachment()
    {
        $file = UploadedFile::fake()->create('naskah_lama.pdf', 200, 'application/pdf');

        $this->actingAs($this->opdUser)->post(
            route('opd.lhr.tindak-lanjut.upload', [$this->lhr->uuid, $this->tindakLanjut->id]),
            [
                'attachments' => [$file],
            ]
        );

        $this->tindakLanjut->refresh();
        $attachment = $this->tindakLanjut->attachments->first();
        $this->assertNotNull($attachment);

        $delResponse = $this->actingAs($this->opdUser)->delete(
            route('opd.lhr.tindak-lanjut.hapus-lampiran', [$this->lhr->uuid, $this->tindakLanjut->id, $attachment->id])
        );

        $delResponse->assertRedirect(route('opd.lhr.show', $this->lhr->uuid));
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }

    public function test_opd_can_submit_tindak_lanjut_to_inspectorate()
    {
        $this->tindakLanjut->update([
            'uraian_tindak_lanjut' => 'Revisi final dokumen selesai',
            'status_opd'           => 'draft',
        ]);

        $response = $this->actingAs($this->opdUser)->post(
            route('opd.lhr.tindak-lanjut.kirim', [$this->lhr->uuid, $this->tindakLanjut->id])
        );

        $response->assertRedirect(route('opd.lhr.show', $this->lhr->uuid));
        $response->assertSessionHas('success');

        $this->tindakLanjut->refresh();
        $this->assertSame('dikirim', $this->tindakLanjut->status_opd);
        $this->assertNotNull($this->tindakLanjut->dikirim_pada);

        $this->lhr->refresh();
        $this->assertSame(LhrStatus::TindakLanjut, $this->lhr->status);
    }

    public function test_opd_can_submit_all_drafts_at_once()
    {
        $this->tindakLanjut->update([
            'uraian_tindak_lanjut' => 'Penyesuaian bab 4 telah tuntas',
            'status_opd'           => 'draft',
        ]);

        $response = $this->actingAs($this->opdUser)->post(
            route('opd.lhr.tindak-lanjut.kirim-semua', $this->lhr->uuid)
        );

        $response->assertRedirect(route('opd.lhr.show', $this->lhr->uuid));
        $response->assertSessionHas('success');

        $this->tindakLanjut->refresh();
        $this->assertSame('dikirim', $this->tindakLanjut->status_opd);
    }

    public function test_unauthorized_opd_cannot_upload_to_other_opd_lhr()
    {
        $otherUnit = UnitDiperiksa::create(['nama_unit' => 'Dinas Pendidikan', 'kategori' => 'OPD']);
        $otherOpd = User::factory()->create(['name' => 'Operator Disdik']);
        $otherOpd->assignRole(User::ROLE_OPD);
        $otherOpd->opdUnits()->attach($otherUnit->id);

        $response = $this->actingAs($otherOpd)->post(
            route('opd.lhr.tindak-lanjut.upload', [$this->lhr->uuid, $this->tindakLanjut->id]),
            [
                'uraian_tindak_lanjut' => 'Hacker mencoba mengirim tindak lanjut',
            ]
        );

        $response->assertNotFound();
    }
}
