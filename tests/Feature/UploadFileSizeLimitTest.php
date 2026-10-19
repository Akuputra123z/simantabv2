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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadFileSizeLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $opdUser;
    private TindakLanjut $tindakLanjut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Storage::fake('local');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $unit = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Kesehatan',
            'kategori' => 'OPD',
        ]);

        $this->opdUser = User::factory()->create();
        $this->opdUser->assignRole('opd');
        $this->opdUser->opdUnits()->attach($unit->id);

        $program = AuditProgram::create([
            'nama_program' => 'Audit 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Audit Keuangan',
            'jenis_kegiatan' => 'Audit',
            'tim' => 'Irban I',
            'anggaran' => 1000000,
            'status' => 'aktif',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->admin->id,
            'nomor_surat' => 'ST/01/2026',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);

        $lhp = Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'nomor_lhp' => '700/01/2026',
            'tanggal_lhp' => now()->toDateString(),
            'semester' => 1,
            'status' => 'final',
            'unit_diperiksa_id' => $unit->id,
            'created_by' => $this->admin->id,
        ]);

        $kodeTemuan = KodeTemuan::create([
            'kode' => '1.01.01',
            'kode_numerik' => '10101',
            'kel' => 1,
            'sub_kel' => 1,
            'jenis' => 1,
            'kelompok' => 'Kel 1',
            'sub_kelompok' => 'Sub 1',
            'deskripsi' => 'Kerugian',
        ]);

        $kodeRekom = KodeRekomendasi::create([
            'kode' => '01',
            'kode_numerik' => 1,
            'deskripsi' => 'Setor Kas',
        ]);

        $temuan = Temuan::create([
            'lhp_id' => $lhp->id,
            'kode_temuan_id' => $kodeTemuan->id,
            'kondisi' => 'Kelebihan',
            'sebab' => 'Lalai',
            'akibat' => 'Rugi',
            'created_by' => $this->admin->id,
        ]);

        $rekom = Recommendation::create([
            'temuan_id' => $temuan->id,
            'kode_rekomendasi_id' => $kodeRekom->id,
            'jenis_rekomendasi' => 'uang',
            'uraian_rekom' => 'Setor kas',
            'nilai_rekom' => 10000000,
            'status' => 'belum_tindak_lanjut',
            'created_by' => $this->admin->id,
        ]);

        $this->tindakLanjut = TindakLanjut::create([
            'recommendation_id' => $rekom->id,
            'status_verifikasi' => 'menunggu_verifikasi',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_opd_upload_accepts_file_up_to_15mb(): void
    {
        // 14.5 MB file (14.5 * 1024 KB = 14848 KB)
        $file14MB = UploadedFile::fake()->create('bukti_14mb.pdf', 14848, 'application/pdf');

        $response = $this->actingAs($this->opdUser)
            ->post(route('opd.tindak-lanjut.upload', $this->tindakLanjut), [
                'jenis_penyelesaian' => 'setor_kas',
                'nilai_tindak_lanjut' => 5000000,
                'keterangan_pendukung' => 'Bukti setor 14MB',
                'attachments' => [$file14MB],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_opd_upload_rejects_file_exceeding_15mb(): void
    {
        // 16 MB file (16 * 1024 KB = 16384 KB)
        $file16MB = UploadedFile::fake()->create('bukti_16mb.pdf', 16384, 'application/pdf');

        $response = $this->actingAs($this->opdUser)
            ->post(route('opd.tindak-lanjut.upload', $this->tindakLanjut), [
                'jenis_penyelesaian' => 'setor_kas',
                'nilai_tindak_lanjut' => 5000000,
                'keterangan_pendukung' => 'Bukti setor 16MB',
                'attachments' => [$file16MB],
            ]);

        $response->assertSessionHasErrors(['attachments.0']);
    }

    public function test_import_unit_diperiksa_accepts_file_up_to_15mb(): void
    {
        $csvContent = "nama_unit,kategori,nama_kecamatan,alamat,telepon,keterangan\n";
        $csvContent .= "Dinas Sosial,OPD,Rembang,Jl. Pemuda,081,Ket\n";
        $file = UploadedFile::fake()->createWithContent('unit.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('unit-diperiksa.import'), [
                'file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
