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
use Database\Seeders\LhrShsdSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LhrIndexSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $opdUser;
    private UnitDiperiksa $unit;
    private Lhr $lhr;
    private Recommendation $rekom1;
    private Recommendation $rekom2;
    private TindakLanjut $tl1;
    private TindakLanjut $tl2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(LhrShsdSeeder::class);

        $this->admin = User::factory()->create(['jenis_kelamin' => 'L']);
        $this->admin->assignRole(User::ROLE_SUPER_ADMIN);

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
            'nomor_surat'             => '000.1.2.3/999/REVIU/2026',
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
            'nomor_lhr'              => '700/01/REVIU-SYNC/2026',
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

        $this->rekom1 = Recommendation::create([
            'lhr_catatan_id' => $catatan->id,
            'uraian_rekom'   => 'Lakukan penyesuaian target indikator pada Bab IV',
            'status'         => 'belum_ditindaklanjuti',
        ]);

        $this->rekom2 = Recommendation::create([
            'lhr_catatan_id' => $catatan->id,
            'uraian_rekom'   => 'Harmonisasikan pagu program pendukung prioritas',
            'status'         => 'belum_ditindaklanjuti',
        ]);

        $this->tl1 = $this->rekom1->tindakLanjuts()->first();
        $this->tl2 = $this->rekom2->tindakLanjuts()->first();
    }

    public function test_lhr_index_initial_published_state_shows_accurate_recommendations(): void
    {
        $response = $this->actingAs($this->admin)->get(route('lhr.index'));

        $response->assertOk();
        $response->assertSee('700/01/REVIU-SYNC/2026');
        $response->assertSee('000.1.2.3/999/REVIU/2026');
        $response->assertSee('Irban I');
        $response->assertSee('Tuntas: 0/2');
        $response->assertSee('0%');
        $response->assertSee('Reviu: Selesai 100%');
        $response->assertSee('Menunggu Tindak Lanjut OPD');
        $response->assertSee('Terbit Resmi');
    }

    public function test_lhr_index_synchronizes_when_opd_submits_revisi(): void
    {
        // 1. OPD menyimpan draf revisi
        $this->tl1->update([
            'keterangan_pendukung_opd' => 'Sudah kami sesuaikan matriksnya',
            'status_opd'               => 'draft',
        ]);

        $responseDraft = $this->actingAs($this->admin)->get(route('lhr.index'));
        $responseDraft->assertOk();
        $responseDraft->assertSee('1 Draf OPD');

        // 2. OPD mengirim revisi ke Inspektorat
        $this->tl1->update([
            'status_opd'       => 'dikirim',
            'dikirim_pada'     => now(),
            'status_verifikasi'=> 'menunggu_verifikasi',
        ]);

        // Simulasikan action OPD mengirim revisi yang otomatis memicu status LHR
        $this->lhr->refreshStatistik();

        $this->lhr->refresh();
        $this->assertSame(LhrStatus::TindakLanjut, $this->lhr->status);

        $responseKirim = $this->actingAs($this->admin)->get(route('lhr.index'));
        $responseKirim->assertOk();
        $responseKirim->assertSee('1 Revisi OPD Masuk');
        $responseKirim->assertSee('Ada Revisi OPD');
        $responseKirim->assertSee('Dalam Tindak Lanjut');
    }

    public function test_lhr_index_synchronizes_when_revisions_are_completed(): void
    {
        // Kedua rekomendasi diverifikasi lunas / selesai
        $this->tl1->update([
            'status_opd'        => 'dikirim',
            'status_verifikasi' => 'lunas',
        ]);
        $this->rekom1->syncStatus();

        $this->tl2->update([
            'status_opd'        => 'dikirim',
            'status_verifikasi' => 'lunas',
        ]);
        $this->rekom2->syncStatus();

        $this->lhr->refreshStatistik();
        $this->lhr->refresh();

        $this->assertSame(LhrStatus::Selesai, $this->lhr->status);

        $response = $this->actingAs($this->admin)->get(route('lhr.index'));
        $response->assertOk();
        $response->assertSee('Tuntas: 2/2');
        $response->assertSee('100%');
        $response->assertSee('Tuntas Diverifikasi');
        $response->assertSee('Selesai');
    }

    public function test_lhr_index_for_draft_lhr_shows_penyusunan_stage(): void
    {
        $this->lhr->update(['status' => LhrStatus::Draft]);

        $response = $this->actingAs($this->admin)->get(route('lhr.index'));
        $response->assertOk();
        $response->assertSee('Penyusunan KKP');
        $response->assertSee('Rekomendasi Dirumuskan:');
        $response->assertSee('Draft / Disusun');
    }
}
