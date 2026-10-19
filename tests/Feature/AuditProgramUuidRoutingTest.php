<?php

namespace Tests\Feature;

use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditProgramUuidRoutingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AuditProgram $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $this->program = AuditProgram::create([
            'nama_program' => 'Audit Kepatuhan TA 2026',
            'tahun' => 2026,
            'kategori' => 'PKPT',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_audit_program_automatically_receives_uuid(): void
    {
        $this->assertNotEmpty($this->program->uuid);
        $this->assertTrue(Str::isUuid($this->program->uuid));
    }

    public function test_route_generation_uses_uuid_instead_of_id(): void
    {
        $showUrl = route('audit-program.show', $this->program);
        $editUrl = route('audit-program.edit', $this->program);
        $previewUrl = route('audit-program.preview', $this->program);

        $this->assertStringContainsString('/audit-program/' . $this->program->uuid, $showUrl);
        $this->assertStringNotContainsString('/audit-program/' . $this->program->id . '/', rtrim($showUrl, '/') . '/');

        $this->assertStringContainsString('/audit-program/' . $this->program->uuid . '/edit', $editUrl);
        $this->assertStringNotContainsString('/audit-program/' . $this->program->id . '/edit', $editUrl);

        $this->assertStringContainsString('/audit-program/' . $this->program->uuid . '/preview', $previewUrl);
        $this->assertStringNotContainsString('/audit-program/' . $this->program->id . '/preview', $previewUrl);
    }

    public function test_authenticated_user_can_access_audit_program_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->get(route('audit-program.show', $this->program));

        $response->assertStatus(200);
        $response->assertSee($this->program->nama_program);
    }

    public function test_accessing_show_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/audit-program/' . $this->program->id);

        $response->assertRedirect(route('audit-program.show', $this->program));
        $response->assertStatus(301);
    }

    public function test_accessing_edit_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/audit-program/' . $this->program->id . '/edit');

        $response->assertRedirect(route('audit-program.edit', $this->program));
        $response->assertStatus(301);
    }

    public function test_accessing_preview_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/audit-program/' . $this->program->id . '/preview');

        $response->assertRedirect(route('audit-program.preview', $this->program));
        $response->assertStatus(301);
    }

    public function test_accessing_non_existent_uuid_returns_404(): void
    {
        $fakeUuid = (string) Str::uuid();
        $response = $this->actingAs($this->admin)->get('/audit-program/' . $fakeUuid);

        $response->assertStatus(404);
    }

    public function test_can_delete_audit_program_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('audit-program.destroy', $this->program));

        $response->assertRedirect(route('audit-program.index'));
        $this->assertSoftDeleted('audit_programs', ['id' => $this->program->id]);
    }

    public function test_create_page_renders_without_pkpt_category_option(): void
    {
        $response = $this->actingAs($this->admin)->get(route('audit-program.create'));

        $response->assertStatus(200);
        $response->assertSee('id="kategori"', false);
        $response->assertDontSee('value="PKPT"', false);
        $response->assertSee('value="BPK"', false);
        $response->assertSee('value="BPKP"', false);
        $response->assertSee('value="ITPROV"', false);
        $response->assertSee('value="ITDA"', false);
        $response->assertSee('value="LAINNYA"', false);
    }

    public function test_can_store_audit_program_with_selected_kategori(): void
    {
        $response = $this->actingAs($this->admin)->post(route('audit-program.store'), [
            'nama_program' => 'Audit BPK TA 2027',
            'tahun' => 2027,
            'kategori' => 'BPK',
        ]);

        $response->assertRedirect(route('audit-program.index'));
        $this->assertDatabaseHas('audit_programs', [
            'nama_program' => 'Audit BPK TA 2027',
            'tahun' => 2027,
            'kategori' => 'BPK',
        ]);
    }

    public function test_edit_page_renders_without_pkpt_category_option(): void
    {
        $response = $this->actingAs($this->admin)->get(route('audit-program.edit', $this->program));

        $response->assertStatus(200);
        $response->assertSee('id="kategori"', false);
        $response->assertDontSee('value="PKPT"', false);
        $response->assertSee('value="BPK"', false);
        $response->assertSee('value="BPKP"', false);
        $response->assertSee('value="ITPROV"', false);
        $response->assertSee('value="ITDA"', false);
        $response->assertSee('value="LAINNYA"', false);
    }

    public function test_can_update_audit_program_with_selected_kategori(): void
    {
        $response = $this->actingAs($this->admin)->put(route('audit-program.update', $this->program), [
            'nama_program' => 'Audit BPKP Updated TA 2026',
            'tahun' => 2026,
            'kategori' => 'BPKP',
            'status' => 'berjalan',
        ]);

        $this->assertDatabaseHas('audit_programs', [
            'id' => $this->program->id,
            'kategori' => 'BPKP',
            'status' => 'berjalan',
        ]);
    }

    public function test_audit_program_index_filter_does_not_contain_pkpt_option(): void
    {
        $response = $this->actingAs($this->admin)->get(route('audit-program.index'));

        $response->assertStatus(200);
        $response->assertSee('name="kategori"', false);
        $response->assertDontSee('<option value="PKPT"', false);
        $response->assertSee('<option value="BPK"', false);
        $response->assertSee('<option value="BPKP"', false);
    }

    public function test_audit_program_show_renders_per_page_options(): void
    {
        $response = $this->actingAs($this->admin)->get(route('audit-program.show', $this->program));

        $response->assertStatus(200);
        $response->assertSee('name="per_page"', false);
        $response->assertSee('value="20"', false);
        $response->assertSee('value="50"', false);
        $response->assertSee('value="100"', false);
    }

    public function test_audit_program_show_applies_per_page_pagination(): void
    {
        // Create 25 sub-programs
        for ($i = 1; $i <= 25; $i++) {
            \App\Models\AuditProgramDetail::create([
                'audit_program_id' => $this->program->id,
                'nama_detail_program' => "Sub Program {$i}",
                'jenis_kegiatan' => 'Audit',
                'anggaran' => 1000000,
                'status' => 'draft',
            ]);
        }

        // With per_page=20, page 1 should have 20 items
        $response20 = $this->actingAs($this->admin)->get(route('audit-program.show', [
            $this->program,
            'per_page' => 20,
        ]));
        $response20->assertStatus(200);
        $this->assertCount(20, $response20->viewData('details')->items());

        // With per_page=50, page 1 should have 25 items
        $response50 = $this->actingAs($this->admin)->get(route('audit-program.show', [
            $this->program,
            'per_page' => 50,
        ]));
        $response50->assertStatus(200);
        $this->assertCount(25, $response50->viewData('details')->items());
    }
}
