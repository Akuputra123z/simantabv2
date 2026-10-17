<?php

namespace Tests\Feature;

use App\Models\UnitDiperiksa;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PegawaiUuidRoutingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pegawaiInspektorat;
    private User $pegawaiOpd;
    private UnitDiperiksa $unitOpd;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $this->pegawaiInspektorat = User::factory()->create([
            'name' => 'Auditor Inspektorat',
            'email' => 'auditor@inspektorat.test',
            'unit_kerja' => 'Irban I',
            'jabatan' => 'Auditor Ahli Pertama',
        ]);
        $this->pegawaiInspektorat->assignRole(User::ROLE_ANGGOTA);

        $this->unitOpd = UnitDiperiksa::create([
            'nama_unit' => 'Dinas Kesehatan',
            'kategori' => 'OPD',
        ]);

        $this->pegawaiOpd = User::factory()->create([
            'name' => 'Pegawai OPD Dinkes',
            'email' => 'opd@dinkes.test',
        ]);
        $this->pegawaiOpd->assignRole(User::ROLE_OPD);
        $this->pegawaiOpd->opdUnits()->sync([$this->unitOpd->id]);
    }

    public function test_user_automatically_receives_uuid(): void
    {
        $this->assertNotEmpty($this->pegawaiInspektorat->uuid);
        $this->assertTrue(Str::isUuid($this->pegawaiInspektorat->uuid));
        $this->assertNotEmpty($this->pegawaiOpd->uuid);
        $this->assertTrue(Str::isUuid($this->pegawaiOpd->uuid));
    }

    public function test_route_generation_uses_uuid_instead_of_id(): void
    {
        $showInspektorat = route('pegawai.inspektorat.show', $this->pegawaiInspektorat);
        $showOpd = route('pegawai.opd.show', $this->pegawaiOpd);
        $editInspektorat = route('pegawai.inspektorat.edit', $this->pegawaiInspektorat);
        $editOpd = route('pegawai.opd.edit', $this->pegawaiOpd);

        $this->assertStringContainsString('/pegawai/inspektorat/' . $this->pegawaiInspektorat->uuid, $showInspektorat);
        $this->assertStringNotContainsString('/pegawai/inspektorat/' . $this->pegawaiInspektorat->id . '/', rtrim($showInspektorat, '/') . '/');

        $this->assertStringContainsString('/pegawai/opd/' . $this->pegawaiOpd->uuid, $showOpd);
        $this->assertStringNotContainsString('/pegawai/opd/' . $this->pegawaiOpd->id . '/', rtrim($showOpd, '/') . '/');

        $this->assertStringContainsString('/pegawai/inspektorat/' . $this->pegawaiInspektorat->uuid . '/edit', $editInspektorat);
        $this->assertStringNotContainsString('/pegawai/inspektorat/' . $this->pegawaiInspektorat->id . '/edit', $editInspektorat);

        $this->assertStringContainsString('/pegawai/opd/' . $this->pegawaiOpd->uuid . '/edit', $editOpd);
        $this->assertStringNotContainsString('/pegawai/opd/' . $this->pegawaiOpd->id . '/edit', $editOpd);
    }

    public function test_authenticated_user_can_access_pegawai_inspektorat_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pegawai.inspektorat.show', $this->pegawaiInspektorat));

        $response->assertStatus(200);
        $response->assertSee($this->pegawaiInspektorat->name);
    }

    public function test_accessing_pegawai_inspektorat_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/pegawai/inspektorat/' . $this->pegawaiInspektorat->id);

        $response->assertRedirect(route('pegawai.inspektorat.show', $this->pegawaiInspektorat));
        $response->assertStatus(301);
    }

    public function test_authenticated_user_can_access_pegawai_opd_by_uuid(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pegawai.opd.show', $this->pegawaiOpd));

        $response->assertStatus(200);
        $response->assertSee($this->pegawaiOpd->name);
        $response->assertSee($this->unitOpd->nama_unit);
    }

    public function test_accessing_pegawai_opd_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/pegawai/opd/' . $this->pegawaiOpd->id);

        $response->assertRedirect(route('pegawai.opd.show', $this->pegawaiOpd));
        $response->assertStatus(301);
    }

    public function test_accessing_pegawai_edit_with_numeric_id_redirects_to_uuid_url(): void
    {
        $response = $this->actingAs($this->admin)->get('/pegawai/inspektorat/' . $this->pegawaiInspektorat->id . '/edit');

        $response->assertRedirect(route('pegawai.inspektorat.edit', $this->pegawaiInspektorat));
        $response->assertStatus(301);
    }

    public function test_accessing_non_existent_uuid_returns_404(): void
    {
        $fakeUuid = (string) Str::uuid();
        $response = $this->actingAs($this->admin)->get('/pegawai/inspektorat/' . $fakeUuid);

        $response->assertStatus(404);
    }

    public function test_users_table_does_not_contain_phone_column(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'phone'));
        $this->assertTrue(Schema::hasColumn('users', 'uuid'));
    }

    public function test_store_pegawai_inspektorat_without_phone(): void
    {
        $response = $this->actingAs($this->admin)->post(route('pegawai.inspektorat.store'), [
            'name' => 'Pegawai Baru',
            'email' => 'pegawai.baru@inspektorat.test',
            'nip' => '199001012020011001',
            'role' => User::ROLE_ANGGOTA,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'unit_kerja' => 'Irban I',
            'jabatan' => 'Auditor',
            'jenis_kelamin' => 'L',
        ]);

        $response->assertRedirect(route('pegawai.inspektorat.index'));
        $createdUser = User::where('email', 'pegawai.baru@inspektorat.test')->first();
        $this->assertNotNull($createdUser);
        $this->assertNotEmpty($createdUser->uuid);
    }
}
