<?php

namespace Tests\Feature\Security;

use App\Actions\Lhr\CreateLhrFromAssignment;
use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhp;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\Lhr;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Database\Seeders\LhrRkpdKuaSeeder;
use Database\Seeders\LhrShsdSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PdfPasswordSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Lhp $lhp;
    private Lhr $lhr;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(LhrShsdSeeder::class);
        $this->seed(LhrRkpdKuaSeeder::class);

        // Buat user dengan password yang diketahui
        $this->user = User::factory()->create([
            'password' => Hash::make('SecretPass123!'),
            'is_active' => true,
        ]);
        $this->user->assignRole(User::ROLE_SUPER_ADMIN);

        $program = AuditProgram::create([
            'nama_program' => 'Audit Keuangan 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
        ]);

        $detail = AuditProgramDetail::create([
            'audit_program_id' => $program->id,
            'nama_detail_program' => 'Pemeriksaan Rutin',
            'jenis_kegiatan' => 'Audit Operasional',
            'tim' => 'Irban I',
        ]);

        $assignment = AuditAssignment::create([
            'audit_program_detail_id' => $detail->id,
            'ketua_tim_id' => $this->user->id,
            'nomor_surat' => 'ST/TEST/001',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'status' => 'berjalan',
        ]);

        $unit = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Pendidikan',
            'kategori' => 'OPD',
        ]);

        $this->lhp = Lhp::create([
            'audit_assignment_id' => $assignment->id,
            'unit_diperiksa_id' => $unit->id,
            'nomor_lhp' => 'LHP/TEST/001',
            'tanggal_lhp' => now(),
            'created_by' => $this->user->id,
        ]);

        $jenisDokumen = JenisDokumenReviu::where('kode', 'RKPD')->firstOrFail();

        $action = app(CreateLhrFromAssignment::class);
        $this->lhr = $action->execute(
            assignment: $assignment,
            unit: $unit,
            jenisDokumen: $jenisDokumen,
            tahunAnggaran: 2026,
            creator: $this->user
        );
    }

    public function test_direct_access_to_lhp_preview_without_password_verification_is_forbidden(): void
    {
        $response = $this->actingAs($this->user)->get(route('laporan.preview-pdf-per-lhp', $this->lhp));
        $response->assertStatus(403);
    }

    public function test_direct_access_to_lhr_pdf_without_password_verification_is_forbidden(): void
    {
        $response = $this->actingAs($this->user)->get(route('lhr.pdf', $this->lhr->uuid));
        $response->assertStatus(403);
    }

    public function test_verify_password_fails_with_wrong_password(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('security.verify-pdf-access'), [
            'password' => 'WrongPassword!',
            'document_type' => 'lhp_preview',
            'document_id' => (string) $this->lhp->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_verify_password_succeeds_and_returns_signed_url_for_lhp(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('security.verify-pdf-access'), [
            'password' => 'SecretPass123!',
            'document_type' => 'lhp_preview',
            'document_id' => (string) $this->lhp->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'url',
            ]);

        $signedUrl = $response->json('url');
        $this->assertNotNull($signedUrl);
        $this->assertStringContainsString('signature=', $signedUrl);

        // Buka URL yang telah ditandatangani
        $pdfResponse = $this->actingAs($this->user)->get($signedUrl);
        $pdfResponse->assertStatus(200);
    }

    public function test_verify_password_succeeds_and_returns_signed_url_for_lhr(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('security.verify-pdf-access'), [
            'password' => 'SecretPass123!',
            'document_type' => 'lhr_pdf',
            'document_id' => $this->lhr->uuid,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $signedUrl = $response->json('url');
        $this->assertNotNull($signedUrl);
        $this->assertStringContainsString('signature=', $signedUrl);

        $pdfResponse = $this->actingAs($this->user)->get($signedUrl);
        $pdfResponse->assertStatus(200);
    }

    public function test_tampered_signature_is_rejected(): void
    {
        $signedUrl = URL::temporarySignedRoute('laporan.preview-pdf-per-lhp', now()->addMinutes(10), ['lhp' => $this->lhp->id]);

        // Manipulasi signature
        $tamperedUrl = $signedUrl.'tampered';

        $response = $this->actingAs($this->user)->get($tamperedUrl);
        $response->assertStatus(403);
    }

    public function test_direct_access_to_kepatuhan_pdf_without_password_verification_is_forbidden(): void
    {
        $this->lhp->update(['is_nihil' => true]);

        $response = $this->actingAs($this->user)->get(route('laporan.kepatuhan.pdf', $this->lhp->id));
        $response->assertStatus(403);
    }

    public function test_verify_password_succeeds_and_returns_signed_url_for_kepatuhan_pdf(): void
    {
        $this->lhp->update(['is_nihil' => true]);

        $response = $this->actingAs($this->user)->postJson(route('security.verify-pdf-access'), [
            'password' => 'SecretPass123!',
            'document_type' => 'kepatuhan_pdf',
            'document_id' => (string) $this->lhp->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $signedUrl = $response->json('url');
        $this->assertNotNull($signedUrl);
        $this->assertStringContainsString('signature=', $signedUrl);

        $pdfResponse = $this->actingAs($this->user)->get($signedUrl);
        $pdfResponse->assertStatus(200);
    }

    public function test_accessing_kepatuhan_show_with_numeric_id_redirects_to_uuid(): void
    {
        $this->lhp->update(['is_nihil' => true]);

        $response = $this->actingAs($this->user)->get('/laporan/kepatuhan/'.$this->lhp->id);

        $response->assertStatus(301);
        $response->assertRedirect('/laporan/kepatuhan/'.$this->lhp->uuid);
    }

    public function test_direct_access_to_kepatuhan_show_without_password_shows_locked_screen(): void
    {
        $this->lhp->update(['is_nihil' => true]);

        $response = $this->actingAs($this->user)->get('/laporan/kepatuhan/'.$this->lhp->uuid);

        $response->assertStatus(200);
        $response->assertSee('Dokumen Dilindungi Keamanan');
        $response->assertSee('Buka Dokumen');
    }

    public function test_verify_password_for_kepatuhan_show_unlocks_and_renders_document(): void
    {
        $this->lhp->update(['is_nihil' => true]);

        $response = $this->actingAs($this->user)->postJson(route('security.verify-pdf-access'), [
            'password' => 'SecretPass123!',
            'document_type' => 'kepatuhan_show',
            'document_id' => $this->lhp->uuid,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $signedUrl = $response->json('url');
        $this->assertNotNull($signedUrl);
        $this->assertStringContainsString('signature=', $signedUrl);

        $showResponse = $this->actingAs($this->user)->get($signedUrl);
        $showResponse->assertStatus(200);
        $showResponse->assertSee('PEMERINTAH KABUPATEN REMBANG');
        $showResponse->assertSee('Unduh PDF Resmi');
    }

    public function test_accessing_rekap_per_lhp_with_numeric_id_redirects_to_uuid(): void
    {
        $response = $this->actingAs($this->user)->get('/laporan/'.$this->lhp->id.'/rekap');

        $response->assertStatus(301);
        $response->assertRedirect('/laporan/'.$this->lhp->uuid.'/rekap');
    }

    public function test_direct_access_to_rekap_per_lhp_without_password_shows_locked_screen(): void
    {
        $response = $this->actingAs($this->user)->get('/laporan/'.$this->lhp->uuid.'/rekap');

        $response->assertStatus(200);
        $response->assertSee('Dokumen Rekap Dilindungi Keamanan');
        $response->assertSee('Buka Rekap');
    }

    public function test_verify_password_for_rekap_show_unlocks_and_renders_rekap(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('security.verify-pdf-access'), [
            'password' => 'SecretPass123!',
            'document_type' => 'rekap_show',
            'document_id' => $this->lhp->uuid,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $signedUrl = $response->json('url');
        $this->assertNotNull($signedUrl);
        $this->assertStringContainsString('signature=', $signedUrl);

        $showResponse = $this->actingAs($this->user)->get($signedUrl);
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Detail LHP');
        $showResponse->assertSee($this->lhp->nomor_lhp);
        $showResponse->assertSee('Informasi LHP');
    }

    public function test_accessing_download_excel_per_lhp_with_numeric_id_redirects_to_uuid(): void
    {
        $response = $this->actingAs($this->user)->get('/laporan/download/excel/'.$this->lhp->id);

        $response->assertStatus(301);
        $response->assertRedirect('/laporan/download/excel/'.$this->lhp->uuid);
    }
}
