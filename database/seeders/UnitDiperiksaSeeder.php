<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitDiperiksaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Mlatirejo'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Sendangmulyo'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Pondokrejo'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Warugunung'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Pinggan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Cabean Kidul'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Lambangan Kulon'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Lambangan Wetan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Sumbermulyo'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Karangasem'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Pasedan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Ngulaan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Jukung'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Bulu'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Mantingan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Desa Kadiwono'],

            // Sumber
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Ronggo Mulyo'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Logede'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Pelemsari'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Logung'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Krikilan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Kedungtulub'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Polbayem'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Jatihadi'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Sumber'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Jadi'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Grawan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Desa Randuagung'],

            // Gunem (contoh sebagian)
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Gunem', 'nama_unit' => 'Desa Kajar'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Gunem', 'nama_unit' => 'Desa Timbrangan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Gunem', 'nama_unit' => 'Desa Tegaldowo'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Gunem', 'nama_unit' => 'Desa Pasucen'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Gunem', 'nama_unit' => 'Desa Suntri'],

            // Sale
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sale', 'nama_unit' => 'Desa Bancang'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sale', 'nama_unit' => 'Desa Mrayun'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sale', 'nama_unit' => 'Desa Ngajaran'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sale', 'nama_unit' => 'Desa Tahunan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sale', 'nama_unit' => 'Desa Gading'],

            // Sarang
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sarang', 'nama_unit' => 'Desa Lodan Kulon'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sarang', 'nama_unit' => 'Desa Lodan Wetan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sarang', 'nama_unit' => 'Desa Bonjor'],

            // Sedan
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sedan', 'nama_unit' => 'Desa Ngulahan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sedan', 'nama_unit' => 'Desa Pacing'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sedan', 'nama_unit' => 'Desa Karas'],

            // Pamotan
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Pamotan', 'nama_unit' => 'Desa Megal'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Pamotan', 'nama_unit' => 'Desa Ngemplakrejo'],

            // Sulang
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sulang', 'nama_unit' => 'Desa Tanjung'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sulang', 'nama_unit' => 'Desa Pragu'],

            // Rembang
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Desa Kedungrejo'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Desa Turusgede'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kelurahan Magersari'],

            // Pancur
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Pancur', 'nama_unit' => 'Desa Jepeledok'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Pancur', 'nama_unit' => 'Desa Jeruk'],

            // Kragan
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Kragan', 'nama_unit' => 'Desa Tanjungsari'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Kragan', 'nama_unit' => 'Desa Sendangmulyo'],

            // Sluke
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sluke', 'nama_unit' => 'Desa Sanetan'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Sluke', 'nama_unit' => 'Desa Rakitan'],

            // Lasem
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Lasem', 'nama_unit' => 'Desa Karasgede'],
            ['kategori' => 'Desa', 'nama_kecamatan' => 'Lasem', 'nama_unit' => 'Desa Jolotundo'],
        ];

        // Insert bulk
        DB::table('unit_diperiksas')->insert($data);
    }
}