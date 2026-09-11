<?php

namespace Tests\Feature;

use App\Models\UnitDiperiksa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UnitDiperiksaImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create superadmin user for authentication
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_can_download_unit_diperiksa_import_template(): void
    {
        $response = $this->get(route('unit-diperiksa.download-template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename=template_import_unit_diperiksa.xlsx');
    }

    public function test_can_import_unit_diperiksa_csv_file(): void
    {
        $csvContent = "nama_unit,kategori,nama_kecamatan,alamat,telepon,keterangan\n";
        $csvContent .= "Dinas Kesehatan Kab Rembang,OPD,,Jl. Pemuda 10,081234567,Dinas Kesehatan\n";
        $csvContent .= "Desa Ronggomulyo,Desa,Sumber,Jl. Raya Sumber,,Pemerintah Desa\n";

        $file = UploadedFile::fake()->createWithContent('import_unit.csv', $csvContent);

        $response = $this->post(route('unit-diperiksa.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('unit-diperiksa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('unit_diperiksas', [
            'nama_unit' => 'Dinas Kesehatan Kab Rembang',
            'kategori'  => 'OPD',
        ]);

        $this->assertDatabaseHas('unit_diperiksas', [
            'nama_unit'      => 'Desa Ronggomulyo',
            'kategori'       => 'Desa',
            'nama_kecamatan' => 'Sumber',
        ]);
    }

    public function test_import_updates_existing_unit_diperiksa(): void
    {
        $existing = UnitDiperiksa::create([
            'nama_unit'      => 'Desa Logung',
            'kategori'       => 'Desa',
            'nama_kecamatan' => 'Sumber',
            'alamat'         => 'Alamat Lama',
        ]);

        $csvContent = "nama_unit,kategori,nama_kecamatan,alamat,telepon,keterangan\n";
        $csvContent .= "Desa Logung,Desa,Sumber,Alamat Baru,08999999,Diperbarui via Import\n";

        $file = UploadedFile::fake()->createWithContent('import_update.csv', $csvContent);

        $response = $this->post(route('unit-diperiksa.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('unit-diperiksa.index'));

        $this->assertDatabaseHas('unit_diperiksas', [
            'id'         => $existing->id,
            'nama_unit'  => 'Desa Logung',
            'alamat'     => 'Alamat Baru',
            'telepon'    => '08999999',
            'keterangan' => 'Diperbarui via Import',
        ]);
    }
}
