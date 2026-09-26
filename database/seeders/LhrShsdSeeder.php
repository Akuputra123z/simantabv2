<?php

namespace Database\Seeders;

use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\LhrKategoriCatatan;
use App\Models\Lhr\LhrTemplate;
use App\Models\Lhr\LhrTemplateDasarHukum;
use App\Models\Lhr\LhrTemplatePengujian;
use App\Models\Lhr\LhrTemplateSection;
use App\Models\NomorFormat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LhrShsdSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Format Penomoran
        $formats = [
            [
                'jenis' => 'lhr',
                'pola' => '700/{urut:3}{.sufiks}/REVIU/INSP/{tahun}',
                'keterangan' => 'Format penomoran Laporan Hasil Reviu (LHR)',
                'aktif' => true,
            ],
            [
                'jenis' => 'lhtl',
                'pola' => '700/{urut:3}/REVIU/INSP/{tahun}',
                'keterangan' => 'Format penomoran Laporan Hasil Tindak Lanjut (LHTL)',
                'aktif' => true,
            ],
            [
                'jenis' => 'spt_reviu',
                'pola' => '000.1.2.3/{urut:3}/REVIU/INSP/{tahun}',
                'keterangan' => 'Format penomoran Surat Perintah Tugas Reviu',
                'aktif' => true,
            ],
            [
                'jenis' => 'spt_tl',
                'pola' => '000.1.2.3/{urut:3}/REVIU/INSP/{tahun}',
                'keterangan' => 'Format penomoran Surat Perintah Tugas Tindak Lanjut Reviu',
                'aktif' => true,
            ],
        ];

        foreach ($formats as $fmt) {
            NomorFormat::updateOrCreate(['jenis' => $fmt['jenis']], $fmt);
        }

        // 2. Kategori Catatan
        $kategoris = [
            ['kode' => 'KEWAJARAN_HARGA',    'nama' => 'Kewajaran & Kepatutan Harga'],
            ['kode' => 'STANDAR_REGIONAL',   'nama' => 'Kesesuaian Standar Harga Satuan Regional'],
            ['kode' => 'SIPD_KESESUAIAN',    'nama' => 'Implementasi Perencanaan Anggaran SIPD-RI'],
            ['kode' => 'KELENGKAPAN_DOKUMEN', 'nama' => 'Kelengkapan Dokumen Pendukung & SOP'],
            ['kode' => 'FORMAT_DOKUMEN',     'nama' => 'Format & Sistematika Dokumen'],
        ];

        foreach ($kategoris as $kat) {
            LhrKategoriCatatan::updateOrCreate(['kode' => $kat['kode']], $kat);
        }

        // 3. Jenis Dokumen Reviu SHSD
        $jenisShsd = JenisDokumenReviu::updateOrCreate(
            ['kode' => 'SHSD'],
            [
                'uuid' => (string) Str::uuid(),
                'nama' => 'Standar Harga Satuan Daerah (SHSD)',
                'gaya_penomoran_bagian' => 'alfabet',
                'tata_letak_hasil' => 'tabel_dua_kolom',
                'sla_tindak_lanjut_hari' => 30,
                'aktif' => true,
            ]
        );

        // 4. Template SHSD Versi 1 (Terbit)
        $template = LhrTemplate::updateOrCreate(
            [
                'jenis_dokumen_reviu_id' => $jenisShsd->id,
                'jenis_laporan' => 'LHR',
                'versi' => 1,
            ],
            [
                'status' => 'terbit',
                'berlaku_mulai' => '2026-01-01',
                'dasar_perubahan' => 'Permendagri Nomor 3 Tahun 2026',
            ]
        );

        // 5. Template Dasar Hukum
        $dasarHukums = [
            'Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah sebagaimana telah diubah beberapa kali terakhir dengan Undang-Undang Nomor 6 Tahun 2023 tentang Penetapan Peraturan Pemerintah Pengganti Undang-Undang Nomor 2 Tahun 2022 tentang Cipta Kerja Menjadi Undang-Undang.',
            'Peraturan Pemerintah Nomor 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah.',
            'Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah.',
            'Peraturan Presiden Nomor 72 Tahun 2025 tentang Standar Harga Satuan Regional.',
            'Peraturan Menteri Dalam Negeri Nomor 3 Tahun 2026 tentang Pedoman Pengawasan Penyelenggaraan Pemerintahan Daerah.',
            'Standar Operasional Prosedur (SOP) Penyusunan Standar Harga Satuan Daerah Pemerintah Kabupaten Rembang.',
        ];

        $template->dasarHukums()->delete();
        foreach ($dasarHukums as $idx => $teks) {
            LhrTemplateDasarHukum::create([
                'lhr_template_id' => $template->id,
                'urutan' => $idx + 1,
                'teks' => $teks,
            ]);
        }

        // 6. Template Sections (Naratif)
        $sections = [
            [
                'kunci' => 'pernyataan',
                'judul' => 'Pernyataan Reviu',
                'isi_default' => 'Kami telah mereviu Rancangan Standar Harga Satuan Daerah (SHSD) Tahun Anggaran {{tahun}} sesuai dengan Standar Audit Intern Pemerintah Indonesia (SAIPI). Reviu bertujuan untuk memberikan keyakinan terbatas mengenai kepatuhan terhadap ketentuan penyusunan dan kewajaran harga.',
                'urutan' => 1,
            ],
            [
                'kunci' => 'dasar',
                'judul' => 'Dasar Reviu',
                'isi_default' => 'Reviu dilaksanakan berdasarkan Surat Perintah Tugas Inspektur Kabupaten Rembang Nomor {{nomor_spt}} tanggal {{tanggal_spt}}.',
                'urutan' => 2,
            ],
            [
                'kunci' => 'tujuan',
                'judul' => 'Tujuan dan Sasaran Reviu',
                'isi_default' => 'Tujuan reviu adalah memberikan keyakinan terbatas atas kepatuhan proses penyusunan SHSD terhadap ketentuan perundang-undangan dan kewajaran harga yang ditetapkan.',
                'urutan' => 3,
            ],
            [
                'kunci' => 'ruang_lingkup',
                'judul' => 'Ruang Lingkup Reviu',
                'isi_default' => 'Ruang lingkup reviu mencakup pengujian kesesuaian SOP, kesesuaian Standar Harga Regional, kepatutan harga pasar, indeks kemahalan daerah, dan implementasi pada SIPD-RI.',
                'urutan' => 4,
            ],
            [
                'kunci' => 'objek',
                'judul' => 'Objek Reviu',
                'isi_default' => 'Objek reviu adalah Dokumen Rancangan Standar Harga Satuan Daerah (SHSD) Tahun Anggaran {{tahun}} yang disusun oleh {{opd}}.',
                'urutan' => 5,
            ],
            [
                'kunci' => 'waktu',
                'judul' => 'Waktu Pelaksanaan',
                'isi_default' => 'Reviu dilaksanakan mulai tanggal {{tanggal_mulai}} sampai dengan {{tanggal_selesai}}.',
                'urutan' => 6,
            ],
            [
                'kunci' => 'simpulan_rekomendasi',
                'judul' => 'Simpulan dan Rekomendasi',
                'isi_default' => 'Berdasarkan hasil pengujian yang telah dilaksanakan, uraian simpulan dan rekomendasi disajikan pada tabel terlampir.',
                'urutan' => 7,
            ],
            [
                'kunci' => 'apresiasi',
                'judul' => 'Apresiasi',
                'isi_default' => 'Kami menyampaikan terima kasih dan apresiasi yang setinggi-tingginya kepada pimpinan dan staf {{opd}} atas kerja sama dan keterbukaan data selama pelaksanaan reviu.',
                'urutan' => 8,
            ],
            [
                'kunci' => 'penutup',
                'judul' => 'Penutup',
                'isi_default' => 'Demikian Laporan Hasil Reviu (LHR) ini disampaikan untuk dapat dipergunakan dan ditindaklanjuti sebagaimana mestinya.',
                'urutan' => 9,
            ],
        ];

        $template->sections()->delete();
        foreach ($sections as $sec) {
            LhrTemplateSection::create(array_merge(['lhr_template_id' => $template->id, 'wajib' => true], $sec));
        }

        // 7. Template Pengujian (6 Butir SHSD)
        $pengujians = [
            [
                'kode' => 'SHSD.S1',
                'judul' => 'Proses penyusunan (kesesuaian dengan SOP)',
                'grup' => 'substansi',
                'urutan' => 1,
                'teks_simpulan_default' => 'Proses penyusunan rancangan SHSD telah dilaksanakan sesuai dengan SOP penyusunan yang berlaku.',
            ],
            [
                'kode' => 'SHSD.S2',
                'judul' => 'Kesesuaian dengan Standar Harga Satuan Regional',
                'grup' => 'substansi',
                'urutan' => 2,
                'teks_simpulan_default' => 'Terdapat beberapa komponen belanja yang belum sepenuhnya menyesuaikan batasan pagu tertinggi pada Standar Harga Satuan Regional.',
            ],
            [
                'kode' => 'SHSD.S3',
                'judul' => 'Kewajaran & kepatutan harga berdasarkan harga pasar',
                'grup' => 'substansi',
                'urutan' => 3,
                'teks_simpulan_default' => 'Sebagian besar harga barang dan jasa telah didukung bukti survei harga pasar yang memadai.',
            ],
            [
                'kode' => 'SHSD.S4',
                'judul' => 'Kewajaran & kepatutan berdasarkan kondisi geografis',
                'grup' => 'substansi',
                'urutan' => 4,
                'teks_simpulan_default' => 'Komponen ongkos angkut dan distribusi wilayah tertentu telah mempertimbangkan kondisi geografis daerah.',
            ],
            [
                'kode' => 'SHSD.S5',
                'judul' => 'Kewajaran & kepatutan berdasarkan indeks kemahalan daerah',
                'grup' => 'substansi',
                'urutan' => 5,
                'teks_simpulan_default' => 'Penyesuaian koefisien indeks kemahalan konstruksi telah sejalan dengan publikasi BPS terkini.',
            ],
            [
                'kode' => 'SHSD.S6',
                'judul' => 'Implementasi SHSD dalam perencanaan anggaran pada SIPD-RI',
                'grup' => 'substansi',
                'urutan' => 6,
                'teks_simpulan_default' => 'Data komponen SHSD telah terpetakan dengan akun kodefikasi belanja pada modul e-Standar Harga SIPD-RI.',
            ],
        ];

        $template->pengujians()->delete();
        foreach ($pengujians as $peng) {
            LhrTemplatePengujian::create(array_merge(['lhr_template_id' => $template->id], $peng));
        }
    }
}
