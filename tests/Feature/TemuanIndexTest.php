<?php

namespace Tests\Feature;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\KodeTemuan;
use App\Models\Lhp;
use App\Models\Temuan;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TemuanIndexTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private UnitDiperiksa $unit;
    private Lhp $lhp;
    private KodeTemuan $kodeTemuan;
    private Temuan $temuan;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin']);
        $this->user = User::factory()->create();
        $this->user->assignRole('super_admin');

        $this->unit = UnitDiperiksa::create([
            'nama_unit' => 'Pemerintah Desa Sumberjo',
            'kategori' => 'Desa',
            'nama_kecamatan' => 'Rembang',
        ]);

        $program = AuditProgram::create([
            'nama_program' => 'Audit Dana Desa 2026',
            'kategori' => 'PKPT',
            'tahun' => 2026,
            'status' => 'draft',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Pemeriksaan Dana Desa Sumberjo',
            'jenis_kegiatan' => 'Pemeriksaan',
            'tim' => 'Irban I',
            'nomor_lhp' => 'LHP/DD/2026/010',
            'ruang_lingkup' => 'Keuangan Desa',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->user->id,
            'nomor_surat' => 'ST/DD/2026/010',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(5),
            'status' => 'berjalan',
            'jenis_pengawasan' => 'Audit',
        ]);

        $this->lhp = Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => 'LHP/DD/2026/010',
            'tanggal_lhp' => now(),
            'unit_diperiksa_id' => $this->unit->id,
            'status' => 'final',
        ]);

        $this->kodeTemuan = KodeTemuan::create([
            'kode' => '1.01.01',
            'kode_numerik' => 10101,
            'kel' => 1,
            'sub_kel' => 1,
            'jenis' => 1,
            'kelompok' => 'Temuan Ketidakpatuhan Terhadap Peraturan',
            'sub_kelompok' => 'Kerugian Negara/daerah',
            'deskripsi' => 'Kelebihan pembayaran atas belanja modal fisik',
        ]);

        $this->temuan = Temuan::create([
            'lhp_id' => $this->lhp->id,
            'kode_temuan_id' => $this->kodeTemuan->id,
            'kondisi' => 'Terdapat kekurangan volume pada proyek pembangunan jalan desa.',
            'nilai_temuan' => 15000000,
            'nilai_kerugian_desa' => 15000000,
            'status_tl' => 'belum_ditindaklanjuti',
        ]);
    }

    public function test_temuan_index_loads_successfully_without_unknown_column_error(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('temuan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.temuan.index');
        $response->assertSee('Daftar Temuan');
        $response->assertSee('LHP/DD/2026/010');
        $response->assertSee('Pemerintah Desa Sumberjo');
        $response->assertSee('1.01.01');
    }

    public function test_clicking_unit_from_dashboard_matriks_navigates_to_temuan_index_with_search(): void
    {
        $this->actingAs($this->user);

        // Akses route temuan dengan search unit nama seperti di matriks dashboard
        $response = $this->get(route('temuan.index', ['search' => 'Pemerintah Desa Sumberjo']));

        $response->assertStatus(200);
        $response->assertSee('Pemerintah Desa Sumberjo');
        $response->assertSee('LHP/DD/2026/010');
        $response->assertSee('Terdapat kekurangan volume', false);
        $response->assertSee('Menampilkan hasil filter:');
    }

    public function test_search_filters_temuan_by_unit_diperiksa_or_nomor_lhp(): void
    {
        $this->actingAs($this->user);

        // Search match
        $match = $this->get(route('temuan.index', ['search' => 'Sumberjo']));
        $match->assertStatus(200);
        $match->assertSee('Pemerintah Desa Sumberjo');

        // Search no match
        $noMatch = $this->get(route('temuan.index', ['search' => 'Desa antah berantah 999']));
        $noMatch->assertStatus(200);
        $noMatch->assertSee('Belum ada data temuan yang tercatat');
    }
}
