<?php

namespace Tests\Feature;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\KodeRekomendasi;
use App\Models\KodeTemuan;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LhpAndTindakLanjutEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected AuditAssignment $assignment;
    protected UnitDiperiksa $unit;
    protected Lhp $lhp;
    protected Recommendation $rekom;
    protected TindakLanjut $tindakLanjut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $program = AuditProgram::create([
            'nama_program' => 'Program Audit Reguler',
            'tahun' => 2026,
            'kategori' => 'PKPT',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Sub Program 1',
            'jenis_kegiatan' => 'Audit Operasional',
            'tim' => 'Irban I',
        ]);

        $this->unit = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan',
            'kode_unit' => 'DISDIK-01',
            'kategori' => 'OPD',
        ]);

        $this->assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->admin->id,
            'nomor_surat' => 'ST/001/2026',
            'tanggal_surat' => now(),
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(14),
            'status' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);
        $this->assignment->unitDiperiksas()->attach($this->unit->id);

        $this->seed(\Database\Seeders\KodeTemuanSeeder::class);
        $this->seed(\Database\Seeders\KodeRekomendasiSeeder::class);

        $kodeTemuan = KodeTemuan::first();
        $kodeRekom = KodeRekomendasi::where('is_active', true)->first();

        $this->lhp = Lhp::create([
            'audit_assignment_id' => $this->assignment->id,
            'unit_diperiksa_id' => $this->unit->id,
            'nomor_lhp' => '700.1.2.1/001/LHP/2026',
            'tanggal_lhp' => now(),
            'catatan_umum' => "Catatan Baris 1\nCatatan Baris 2 dengan kutip 'tunggal' dan \"ganda\"\nBaris 3.",
            'is_nihil' => false,
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        $this->lhp->unitDiperiksas()->attach($this->unit->id);

        $temuan = Temuan::create([
            'lhp_id' => $this->lhp->id,
            'kode_temuan_id' => $kodeTemuan->id,
            'kondisi' => 'Terdapat kelebihan bayar belanja modal.',
            'nilai_temuan' => 10000000,
            'nilai_kerugian_daerah' => 10000000,
            'status_tl' => 'belum_ditindaklanjuti',
        ]);

        $this->rekom = Recommendation::create([
            'temuan_id' => $temuan->id,
            'kode_rekomendasi_id' => $kodeRekom->id,
            'uraian_rekom' => 'Setorkan kelebihan pembayaran sebesar Rp 10.000.000 ke Kas Daerah.',
            'jenis_rekomendasi' => 'uang',
            'nilai_rekom' => 10000000,
            'nilai_sisa' => 10000000,
            'status' => 'belum_ditindaklanjuti',
            'created_by' => $this->admin->id,
        ]);

        $this->tindakLanjut = TindakLanjut::create([
            'recommendation_id' => $this->rekom->id,
            'jenis_penyelesaian' => 'setor_kas',
            'nilai_tindak_lanjut' => 5000000,
            'tanggal_jatuh_tempo' => now()->addDays(30),
            'status_verifikasi' => 'berjalan',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_lhp_edit_page_renders_cleanly_with_multiline_catatan_umum()
    {
        $response = $this->actingAs($this->admin)->get(route('lhps.edit', $this->lhp));

        $response->assertStatus(200);
        $content = $response->getContent();
        // Ensure the blade JSON encoding was used and didn't crash
        $this->assertStringContainsString('lhpEditWizard', $content);
        $this->assertStringContainsString('Catatan Baris 1', $content);
    }

    public function test_lhp_update_via_ajax_returns_json()
    {
        $updatePayload = [
            'audit_assignment_id' => $this->assignment->id,
            'unit_diperiksa_ids' => [$this->unit->id],
            'unit_diperiksa_id' => $this->unit->id,
            'nomor_lhp' => '700.1.2.1/001-REV/LHP/2026',
            'tanggal_lhp' => now()->toDateString(),
            'catatan_umum' => "Catatan yang telah diperbarui.\nBaris kedua.",
            'is_nihil' => 0,
            'temuans' => [
                [
                    'id' => $this->lhp->temuans->first()->id,
                    'kode_temuan_id' => $this->lhp->temuans->first()->kode_temuan_id,
                    'kondisi' => 'Kondisi diperbarui.',
                    'nilai_kerugian_daerah' => '10000000',
                    'recommendations' => [
                        [
                            'id' => $this->rekom->id,
                            'kode_rekomendasi_id' => $this->rekom->kode_rekomendasi_id,
                            'uraian_rekom' => 'Rekomendasi diperbarui.',
                            'jenis_rekomendasi' => 'uang',
                            'nilai_rekom' => '10000000',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders(['Accept' => 'application/json'])
            ->put(route('lhps.update', $this->lhp), $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->lhp->refresh();
        $this->assertEquals('700.1.2.1/001-REV/LHP/2026', $this->lhp->nomor_lhp);
    }

    public function test_tindak_lanjut_edit_page_renders_cleanly()
    {
        $response = $this->actingAs($this->admin)->get(route('tindak-lanjuts.edit', $this->tindakLanjut));

        $response->assertStatus(200);
        $response->assertSee('Edit Tindak Lanjut');
        $response->assertSee($this->lhp->nomor_lhp);
    }

    public function test_tindak_lanjut_update_recalculates_and_sets_verification_metadata()
    {
        $verifier = User::factory()->create();

        $updatePayload = [
            'recommendation_id' => $this->rekom->id,
            'jenis_penyelesaian' => 'langsung',
            'nilai_tindak_lanjut' => '10000000',
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
            'status_verifikasi' => 'lunas',
            'diverifikasi_oleh' => $verifier->id,
            'catatan_tl' => 'Telah disetor lunas seluruhnya.',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('tindak-lanjuts.update', $this->tindakLanjut), $updatePayload);

        $response->assertRedirect(route('tindak-lanjuts.index'));

        $this->tindakLanjut->refresh();
        $this->assertEquals('lunas', $this->tindakLanjut->status_verifikasi);
        $this->assertEquals(10000000, (float)$this->tindakLanjut->total_terbayar);
        $this->assertEquals(0, (float)$this->tindakLanjut->sisa_belum_bayar);
        $this->assertEquals($verifier->id, $this->tindakLanjut->diverifikasi_oleh);
        $this->assertNotNull($this->tindakLanjut->diverifikasi_pada);

        // Recommendation status should cascade to 'sudah_ditindaklanjuti' (lunas)
        $this->rekom->refresh();
        $this->assertEquals(Recommendation::STATUS_SELESAI, $this->rekom->status);
        $this->assertEquals(0, (float)$this->rekom->nilai_sisa);
    }

    public function test_tindak_lanjut_update_to_menunggu_verifikasi_resets_total_terbayar()
    {
        // First set to lunas
        $this->tindakLanjut->update([
            'nilai_tindak_lanjut' => 10000000,
            'status_verifikasi' => 'lunas',
            'total_terbayar' => 10000000,
            'sisa_belum_bayar' => 0,
        ]);

        // Now edit back to menunggu_verifikasi
        $updatePayload = [
            'recommendation_id' => $this->rekom->id,
            'jenis_penyelesaian' => 'langsung',
            'nilai_tindak_lanjut' => '10000000',
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
            'status_verifikasi' => 'menunggu_verifikasi',
            'diverifikasi_oleh' => null,
            'catatan_tl' => 'Kembali ke menunggu verifikasi.',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('tindak-lanjuts.update', $this->tindakLanjut), $updatePayload);

        $response->assertRedirect(route('tindak-lanjuts.index'));

        $this->tindakLanjut->refresh();
        $this->assertEquals('menunggu_verifikasi', $this->tindakLanjut->status_verifikasi);
        $this->assertEquals(0, (float)$this->tindakLanjut->total_terbayar);
        $this->assertEquals(10000000, (float)$this->tindakLanjut->sisa_belum_bayar);
        $this->assertNull($this->tindakLanjut->diverifikasi_pada);
    }
}
