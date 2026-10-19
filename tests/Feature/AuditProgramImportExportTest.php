<?php

namespace Tests\Feature;

use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditProgramImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AuditProgram $program;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(User::ROLE_SUPER_ADMIN);

        $this->user = User::factory()->create();
        $this->user->assignRole(User::ROLE_SUPER_ADMIN);

        $this->program = AuditProgram::create([
            'nama_program' => 'Program Kerja Pengawasan Tahunan 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'berjalan',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_can_download_sub_program_excel_template(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('audit-program-detail.download-template'));

        $response->assertOk();
        $this->assertTrue(
            str_contains($response->headers->get('content-disposition') ?? '', 'template_sub_program.xlsx')
        );
    }

    public function test_can_export_sub_program_excel(): void
    {
        AuditProgramDetail::create([
            'audit_program_id' => $this->program->id,
            'nama_detail_program' => 'Audit Pengelolaan Keuangan Desa',
            'jenis_kegiatan' => 'Audit Ketaatan',
            'objek_pengawasan' => 'Pemerintah Desa',
            'ruang_lingkup' => 'Dana Desa TA 2026',
            'personil' => '5',
            'tujuan' => 'Ketaatan regulasi',
            'anggaran' => 5000000,
            'tingkat_resiko' => 'Tinggi',
            'laporan_akhir' => '1 LHP',
            'jadwal' => '01/03/2026',
            'tim' => 'Irban III',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('audit-program.export-excel', $this->program->id));

        $response->assertOk();
        $this->assertTrue(
            str_contains($response->headers->get('content-disposition') ?? '', '.xlsx')
        );
    }

    public function test_can_export_sub_program_pdf(): void
    {
        AuditProgramDetail::create([
            'audit_program_id' => $this->program->id,
            'nama_detail_program' => 'Audit Pengelolaan Aset',
            'jenis_kegiatan' => 'Audit Operasional',
            'objek_pengawasan' => 'BPKAD',
            'anggaran' => 3000000,
            'status' => 'rencana',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('audit-program.export-pdf', $this->program->id));

        $response->assertOk();
        $this->assertTrue(
            str_contains($response->headers->get('content-type') ?? '', 'application/pdf')
        );
    }

    public function test_can_import_sub_program_csv_with_mode_add(): void
    {
        $csvContent = implode("\n", [
            'Nama Detail Program,Jenis Kegiatan,Objek Pengawasan,Ruang Lingkup,Jumlah Personil,Tujuan Pengawasan,Anggaran (Rp),Tingkat Risiko,Target Laporan,Jadwal Pelaksanaan,Tim Pelaksana,Status',
            '"Audit Dana BOS","Audit Ketaatan","Dinas Pendidikan","Dana BOS 2026","4","Uji petik akuntabilitas","Rp 4.500.000,00","Sedang","1 LHP","15/02/2026","IRBAN II","aktif"',
            '"Reviu SPM","Reviu","Dinkes","SPM Kesehatan","3","Kesesuaian standar","2.500.000","Rendah","1 LHR","Maret 2026","Irban I","rencana"',
        ]);

        $file = UploadedFile::fake()->createWithContent('sub_program.csv', $csvContent);

        $response = $this->actingAs($this->user)->post(route('audit-program-detail.import'), [
            'file' => $file,
            'audit_program_id' => $this->program->id,
            'mode' => 'add',
        ]);

        $response->assertRedirect(route('audit-program.show', $this->program));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('audit_program_details', [
            'audit_program_id' => $this->program->id,
            'nama_detail_program' => 'Audit Dana BOS',
            'anggaran' => 4500000,
            'tim' => 'Irban II',
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('audit_program_details', [
            'audit_program_id' => $this->program->id,
            'nama_detail_program' => 'Reviu SPM',
            'anggaran' => 2500000,
            'tingkat_resiko' => 'Rendah',
            'tim' => 'Irban I',
            'status' => 'rencana',
        ]);
    }

    public function test_import_empty_file_fails_gracefully(): void
    {
        $csvContent = "Nama Detail Program,Jenis Kegiatan,Objek Pengawasan\n"; // Header only, no rows
        $file = UploadedFile::fake()->createWithContent('empty.csv', $csvContent);

        $response = $this->actingAs($this->user)->post(route('audit-program-detail.import'), [
            'file' => $file,
            'audit_program_id' => $this->program->id,
            'mode' => 'add',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_import_replace_mode_replaces_old_records(): void
    {
        // Data lama
        AuditProgramDetail::create([
            'audit_program_id' => $this->program->id,
            'nama_detail_program' => 'Program Lama Yang Harus Diganti',
            'jenis_kegiatan' => 'Audit Ketaatan',
            'anggaran' => 1000000,
            'status' => 'rencana',
        ]);

        $csvContent = implode("\n", [
            'Nama Detail Program,Jenis Kegiatan,Objek Pengawasan,Ruang Lingkup,Jumlah Personil,Tujuan Pengawasan,Anggaran (Rp),Tingkat Risiko,Target Laporan,Jadwal Pelaksanaan,Tim Pelaksana,Status',
            '"Program Baru Pengganti","Audit Investigasi","Inspektorat","Ruang Lingkup Baru","2","Tujuan Baru","7500000","Tinggi","1 LHP","01/05/2026","Irbansus","aktif"',
        ]);

        $file = UploadedFile::fake()->createWithContent('replace.csv', $csvContent);

        $response = $this->actingAs($this->user)->post(route('audit-program-detail.import'), [
            'file' => $file,
            'audit_program_id' => $this->program->id,
            'mode' => 'replace',
        ]);

        $response->assertRedirect(route('audit-program.show', $this->program));
        $response->assertSessionHas('success');

        // Data lama harus terhapus
        $this->assertDatabaseMissing('audit_program_details', [
            'nama_detail_program' => 'Program Lama Yang Harus Diganti',
        ]);

        // Data baru harus ada
        $this->assertDatabaseHas('audit_program_details', [
            'nama_detail_program' => 'Program Baru Pengganti',
            'tim' => 'Irbansus',
            'anggaran' => 7500000,
        ]);
    }
}
