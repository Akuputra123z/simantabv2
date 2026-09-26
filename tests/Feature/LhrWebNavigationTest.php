<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LhrShsdSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LhrWebNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(LhrShsdSeeder::class);
    }

    public function test_super_admin_can_access_lhr_and_master_menus(): void
    {
        $admin = User::where('email', 'admin@inspektorat.go.id')->first();
        if (! $admin) {
            $admin = User::factory()->create(['jenis_kelamin' => 'L']);
            $admin->assignRole(User::ROLE_SUPER_ADMIN);
        }

        $this->actingAs($admin);

        // 1. Index LHR
        $resLhr = $this->get(route('lhr.index'));
        $resLhr->assertStatus(200);
        $resLhr->assertSee('Laporan Hasil Reviu');
        $resLhr->assertSee('Buat LHR Baru');

        // 2. Create LHR wizard
        $resCreate = $this->get(route('lhr.create'));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('Surat Perintah Tugas (SPT)');

        // 3. Master Data Jenis Dokumen
        $resJenis = $this->get(route('jenis-dokumen-reviu.index'));
        $resJenis->assertStatus(200);
        $resJenis->assertSee('SHSD');

        // 4. Master Data Kategori Catatan
        $resKat = $this->get(route('lhr-kategori-catatans.index'));
        $resKat->assertStatus(200);
        $resKat->assertSee('KEWAJARAN_HARGA');

        // 5. Master Data Format Penomoran
        $resNomor = $this->get(route('nomor-formats.index'));
        $resNomor->assertStatus(200);
        $resNomor->assertSee('700/{urut:3}{.sufiks}/REVIU/INSP/{tahun}');
    }

    public function test_opd_user_cannot_access_lhr_module(): void
    {
        $opdUser = User::factory()->create(['jenis_kelamin' => 'L']);
        $opdUser->assignRole(User::ROLE_OPD);

        $this->actingAs($opdUser);

        $response = $this->get(route('lhr.index'));
        $response->assertStatus(403);

        $responseCreate = $this->get(route('lhr.create'));
        $responseCreate->assertStatus(403);
    }
}
