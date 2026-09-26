<?php

namespace Tests\Unit;

use App\Actions\Lhr\CreateLhrFromAssignment;
use App\Enums\SimpulanStatus;
use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\LhrCatatan;
use App\Models\Lhr\LhrKategoriCatatan;
use App\Models\UnitDiperiksa;
use App\Models\User;
use App\Services\Lhr\LhrLinter;
use Carbon\Carbon;
use Database\Seeders\LhrShsdSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LhrLinterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(LhrShsdSeeder::class);
    }

    public function test_linter_detects_unassessed_pengujian(): void
    {
        $ketua = User::factory()->create(['jenis_kelamin' => 'L']);
        $unit = UnitDiperiksa::create(['nama_unit' => 'Dinas Pendidikan']);

        $program = AuditProgram::create([
            'nama_program' => 'PKPT 2026',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);
        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu SHSD',
            'jenis_kegiatan' => 'Reviu',
            'personil' => '3 Orang',
            'tingkat_resiko' => 'Sedang',
            'tim' => 'Irban I',
        ]);
        $spt = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $ketua->id,
            'nomor_surat' => '000.1.2.3/001/REVIU/INSP/2026',
            'tanggal_mulai' => Carbon::parse('2026-06-01'),
            'tanggal_selesai' => Carbon::parse('2026-06-10'),
            'status' => 'berjalan',
        ]);

        $jenis = JenisDokumenReviu::where('kode', 'SHSD')->firstOrFail();
        $action = new CreateLhrFromAssignment;
        $lhr = $action->execute(
            assignment: $spt,
            unit: $unit,
            jenisDokumen: $jenis,
            tahunAnggaran: 2027,
            creator: $ketua
        );

        $linter = new LhrLinter;
        $result = $linter->lint($lhr);

        $this->assertFalse($result->passes());
        $this->assertTrue($result->hasErrors());

        // L-02 error should be triggered for unassessed items
        $l02Errors = array_filter($result->errors, fn ($e) => $e['code'] === 'L-02');
        $this->assertCount(6, $l02Errors);
    }

    public function test_linter_detects_catatan_without_rekomendasi(): void
    {
        $ketua = User::factory()->create(['jenis_kelamin' => 'L']);
        $unit = UnitDiperiksa::create(['nama_unit' => 'Dinas Kesehatan']);

        $program = AuditProgram::create([
            'nama_program' => 'PKPT 2026',
            'tahun' => 2026,
            'kategori' => 'Reviu Dokumen',
            'status' => 'disetujui',
        ]);
        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Reviu SHSD',
            'jenis_kegiatan' => 'Reviu',
            'personil' => '3 Orang',
            'tingkat_resiko' => 'Sedang',
            'tim' => 'Irban I',
        ]);
        $spt = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'nama_tim' => 'Tim Reviu',
            'jenis_pengawasan' => 'reviu',
            'ketua_tim_id' => $ketua->id,
            'nomor_surat' => '000.1.2.3/001/REVIU/INSP/2026',
            'tanggal_mulai' => Carbon::parse('2026-06-01'),
            'tanggal_selesai' => Carbon::parse('2026-06-10'),
            'status' => 'berjalan',
        ]);

        $jenis = JenisDokumenReviu::where('kode', 'SHSD')->firstOrFail();
        $action = new CreateLhrFromAssignment;
        $lhr = $action->execute(
            assignment: $spt,
            unit: $unit,
            jenisDokumen: $jenis,
            tahunAnggaran: 2027,
            creator: $ketua
        );

        // Mark all pengujian as sesuai except one
        foreach ($lhr->pengujians as $peng) {
            $peng->update(['status_simpulan' => SimpulanStatus::Sesuai]);
        }

        $targetPeng = $lhr->pengujians->first();
        $targetPeng->update(['status_simpulan' => SimpulanStatus::SebagianSesuai]);

        $kat = LhrKategoriCatatan::first();
        // Create catatan WITHOUT rekomendasi
        LhrCatatan::create([
            'lhr_pengujian_id' => $targetPeng->id,
            'kategori_id' => $kat->id,
            'isi' => 'Catatan tanpa rekomendasi tindak lanjut.',
            'created_by' => $ketua->id,
        ]);

        $linter = new LhrLinter;
        $result = $linter->lint($lhr);

        $this->assertFalse($result->passes());
        $l03Errors = array_filter($result->errors, fn ($e) => $e['code'] === 'L-03');
        $this->assertNotEmpty($l03Errors);
    }
}
