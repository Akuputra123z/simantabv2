<?php

namespace Tests\Feature;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\KodeTemuan;
use App\Models\KodeRekomendasi;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TindakLanjutUuidRoutingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $opdUser;
    private UnitDiperiksa $unit;
    private Lhp $lhp;
    private Recommendation $recommendation;
    private TindakLanjut $tindakLanjut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $this->unit = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Kesehatan',
            'kategori' => 'OPD',
        ]);

        $this->opdUser = User::factory()->create();
        $this->opdUser->assignRole('opd');
        $this->opdUser->opdUnits()->attach($this->unit->id);

        $program = AuditProgram::create([
            'nama_program' => 'Audit Kepatuhan TA 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Audit Pengadaan Dinkes',
            'jenis_kegiatan' => 'Audit Ketaatan',
            'tim' => 'Irban I',
            'anggaran' => 15000000,
            'status' => 'aktif',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->admin->id,
            'nomor_surat' => 'ST/700/DINKES/2026',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $this->lhp = Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => '700/01/LHP/2026',
            'tanggal_lhp' => now()->toDateString(),
            'status' => 'final',
            'unit_diperiksa_id' => $this->unit->id,
            'created_by' => $this->admin->id,
        ]);

        $kodeTemuan = KodeTemuan::create([
            'kode' => '1.01.01',
            'kode_numerik' => '10101',
            'kel' => 1,
            'sub_kel' => 1,
            'jenis' => 1,
            'kelompok' => 'Kelompok 1',
            'sub_kelompok' => 'Sub Kelompok 1',
            'deskripsi' => 'Kelebihan Pembayaran',
        ]);

        $kodeRekom = KodeRekomendasi::create([
            'kode' => '01',
            'kode_numerik' => 1,
            'deskripsi' => 'Menyetorkan ke Kas Daerah',
        ]);

        $temuan = Temuan::create([
            'lhp_id' => $this->lhp->id,
            'kode_temuan_id' => $kodeTemuan->id,
            'kondisi' => 'Terdapat kelebihan pembayaran pengadaan obat',
            'sebab' => 'Kurang teliti dalam verifikasi dokumen',
            'akibat' => 'Potensi kerugian keuangan daerah',
            'created_by' => $this->admin->id,
        ]);

        $this->recommendation = Recommendation::create([
            'temuan_id' => $temuan->id,
            'kode_rekomendasi_id' => $kodeRekom->id,
            'jenis_rekomendasi' => 'uang',
            'uraian_rekom' => 'Setorkan kelebihan pembayaran ke kas daerah',
            'nilai_rekom' => 10000000,
            'status' => 'belum_tindak_lanjut',
            'created_by' => $this->admin->id,
        ]);

        $this->tindakLanjut = TindakLanjut::create([
            'recommendation_id' => $this->recommendation->id,
            'jenis_penyelesaian' => 'setor_kas',
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
            'status_verifikasi' => 'menunggu_verifikasi',
            'nilai_tindak_lanjut' => 5000000,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_it_automatically_generates_uuid_on_create(): void
    {
        $this->assertNotNull($this->tindakLanjut->uuid);
        $this->assertTrue(Str::isUuid($this->tindakLanjut->uuid));
    }

    public function test_admin_can_access_show_via_uuid(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('tindak-lanjuts.show', $this->tindakLanjut));

        $response->assertOk();
    }

    public function test_admin_can_access_edit_via_uuid(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('tindak-lanjuts.edit', $this->tindakLanjut));

        $response->assertOk();
    }

    public function test_legacy_numeric_id_redirects_301_to_uuid_on_show(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/tindak-lanjuts/' . $this->tindakLanjut->id);

        $response->assertStatus(301);
        $response->assertRedirect(route('tindak-lanjuts.show', $this->tindakLanjut));
    }

    public function test_legacy_numeric_id_redirects_301_to_uuid_on_edit(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/tindak-lanjuts/' . $this->tindakLanjut->id . '/edit');

        $response->assertStatus(301);
        $response->assertRedirect(route('tindak-lanjuts.edit', $this->tindakLanjut));
    }

    public function test_opd_can_access_show_via_uuid(): void
    {
        $response = $this->actingAs($this->opdUser)
            ->get(route('opd.tindak-lanjut.show', $this->tindakLanjut));

        $response->assertOk();
    }

    public function test_legacy_numeric_id_redirects_301_to_uuid_on_opd_show(): void
    {
        $response = $this->actingAs($this->opdUser)
            ->get('/opd/tindak-lanjut/' . $this->tindakLanjut->id);

        $response->assertStatus(301);
        $response->assertRedirect(route('opd.tindak-lanjut.show', $this->tindakLanjut));
    }

    public function test_invalid_uuid_returns_404(): void
    {
        $randomUuid = (string) Str::uuid();

        $response = $this->actingAs($this->admin)
            ->get('/tindak-lanjuts/' . $randomUuid);

        $response->assertNotFound();
    }

    public function test_bulk_inserted_stubs_have_valid_uuid(): void
    {
        // Buat rekomendasi baru tanpa tindak lanjut
        $newRekom = Recommendation::create([
            'temuan_id' => $this->recommendation->temuan_id,
            'kode_rekomendasi_id' => $this->recommendation->kode_rekomendasi_id,
            'jenis_rekomendasi' => 'administrasi',
            'uraian_rekom' => 'Perbaiki SOP',
            'nilai_rekom' => 0,
            'status' => 'belum_tindak_lanjut',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->opdUser)
            ->get(route('opd.tindak-lanjut.index'));

        $stub = TindakLanjut::where('recommendation_id', $newRekom->id)->first();
        $this->assertNotNull($stub);
        $this->assertNotNull($stub->uuid);
        $this->assertTrue(Str::isUuid($stub->uuid));
    }
}
