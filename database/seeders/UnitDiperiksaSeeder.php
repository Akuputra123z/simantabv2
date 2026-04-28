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

            //OPD
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Bupati Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Wakil Bupati Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Sekretaris Daerah Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Sekretaris Dewan Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Pendidikan Pemuda dan Olah Raga Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Kebudayaan dan Pariwisata Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Perumahan dan Kawasan Permukiman Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Lingkungan Hidup Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Penanaman Modal Pelayanan Terpadu Satu Pintu Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Kesehatan'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Pekerjaan Umum dan Penataan Ruang Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Perhubungan Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Pemberdayaan Masyarakat dan Desa Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Kelautan dan Perikanan Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Badan Kesatuan Bangsa dan Politik Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Komunikasi dan Informatika Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Perdagangan, Koperasi dan UMKM Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Kependudukan dan Pencatatan Sipil Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Pertanian dan Pangan Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Sosial, Pemberdayaan Perempuan dan Keluarga Berencana Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Badan Perencanaan Pembangunan Daerah'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Badan Pendapatan, Pengelolaan Keuangan dan Aset Daerah Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Inspektur Inspektorat'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Satuan Polisi Pamong Praja Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Badan Kepegawaian Daerah Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Pelaksana Badan Penanggulangan Bencana Daerah Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Kearsipan dan Perpustakaan Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Plt. Direktur RSUD R. Soetrasno Kabupaten Rembang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Kepala Dinas Perindustrian dan Tenaga Kerja Kabupaten Rembang'],

            ['kategori' => 'OPD', 'nama_kecamatan' => 'Sluke', 'nama_unit' => 'Camat Sluke'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Sale', 'nama_unit' => 'Camat Sale'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Bulu', 'nama_unit' => 'Camat Bulu'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Lasem', 'nama_unit' => 'Camat Lasem'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Pancur', 'nama_unit' => 'Camat Pancur'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Sarang', 'nama_unit' => 'Camat Sarang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Sulang', 'nama_unit' => 'Camat Sulang'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Kragan', 'nama_unit' => 'Camat Kragan'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Sumber', 'nama_unit' => 'Camat Sumber'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Pamotan', 'nama_unit' => 'Camat Pamotan'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Gunem', 'nama_unit' => 'Camat Gunem'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Kaliori', 'nama_unit' => 'Camat Kaliori'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Sedan', 'nama_unit' => 'Camat Sedan'],
            ['kategori' => 'OPD', 'nama_kecamatan' => 'Rembang', 'nama_unit' => 'Camat Rembang'],


        ];

        // Insert bulk
        DB::table('unit_diperiksas')->insert($data);
    }
}