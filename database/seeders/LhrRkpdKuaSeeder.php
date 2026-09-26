<?php

namespace Database\Seeders;

use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\LhrKategoriCatatan;
use App\Models\Lhr\LhrTemplate;
use App\Models\Lhr\LhrTemplateDasarHukum;
use App\Models\Lhr\LhrTemplatePengujian;
use App\Models\Lhr\LhrTemplateSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LhrRkpdKuaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kategori Catatan Tambahan (Perencanaan & Anggaran)
        $kategoris = [
            ['kode' => 'KONSISTENSI_RPJMD',          'nama' => 'Konsistensi Terhadap RPJMD'],
            ['kode' => 'PROGRAM_PRIORITAS_NASIONAL', 'nama' => 'Kesesuaian Program Prioritas Nasional & Asta Cita'],
            ['kode' => 'POKOK_PIKIRAN_DPRD',          'nama' => 'Penelaahan Pokok-Pokok Pikiran DPRD'],
            ['kode' => 'PROYEKSI_FISKAL',             'nama' => 'Kewajaran Proyeksi Fiskal & Pendanaan'],
            ['kode' => 'PAGU_INDIKATIF',              'nama' => 'Keselarasan Pagu Indikatif & Alokasi Anggaran'],
            ['kode' => 'INDIKATOR_TARGET',            'nama' => 'Konsistensi Indikator & Target Kinerja'],
            ['kode' => 'SISTEMATIKA_BAB',             'nama' => 'Sistematika Dokumen & Dokumen Pendukung'],
        ];

        foreach ($kategoris as $kat) {
            LhrKategoriCatatan::updateOrCreate(['kode' => $kat['kode']], [
                'nama' => $kat['nama'],
                'aktif' => true,
            ]);
        }

        // 2. Seed Jenis Dokumen & Template RKPD
        $this->seedRkpd();

        // 3. Seed Jenis Dokumen & Template KUA-PPAS
        $this->seedKuaPpas();
    }

    private function seedRkpd(): void
    {
        $jenisRkpd = JenisDokumenReviu::updateOrCreate(
            ['kode' => 'RKPD'],
            [
                'uuid' => (string) Str::uuid(),
                'nama' => 'Rencana Kerja Pemerintah Daerah (RKPD)',
                'gaya_penomoran_bagian' => 'alfabet',
                'tata_letak_hasil' => 'tabel_dua_kolom',
                'sla_tindak_lanjut_hari' => 14,
                'aktif' => true,
            ]
        );

        $template = LhrTemplate::updateOrCreate(
            [
                'jenis_dokumen_reviu_id' => $jenisRkpd->id,
                'jenis_laporan' => 'LHR',
                'versi' => 1,
            ],
            [
                'status' => 'terbit',
                'berlaku_mulai' => '2026-01-01',
                'dasar_perubahan' => 'Permendagri Nomor 86 Tahun 2017 dan Permendagri Nomor 3 Tahun 2026',
            ]
        );

        // Dasar Hukum RKPD
        $dasarHukums = [
            'Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah sebagaimana telah diubah beberapa kali terakhir dengan Undang-Undang Nomor 6 Tahun 2023.',
            'Peraturan Pemerintah Nomor 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah.',
            'Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah.',
            'Peraturan Menteri Dalam Negeri Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah tentang RPJPD dan RPJMD, serta Tata Cara Perubahan RPJPD, RPJMD, dan RKPD.',
            'Peraturan Menteri Dalam Negeri Nomor 10 Tahun 2018 tentang Pedoman Penyusunan Rencana Kerja Pemerintah Daerah.',
            'Peraturan Menteri Dalam Negeri Nomor 14 Tahun 2025 tentang Pedoman Penyusunan RKPD Tahun Berjalan.',
            'Peraturan Menteri Dalam Negeri Nomor 3 Tahun 2026 tentang Pedoman Pengawasan Penyelenggaraan Pemerintahan Daerah.',
            'Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Pembentukan dan Susunan Perangkat Daerah Kabupaten Rembang.',
            'Peraturan Bupati Rembang Nomor 54 Tahun 2021 tentang Kedudukan, Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Inspektorat Daerah Kabupaten Rembang.',
        ];

        $template->dasarHukums()->delete();
        foreach ($dasarHukums as $idx => $teks) {
            LhrTemplateDasarHukum::create([
                'lhr_template_id' => $template->id,
                'urutan' => $idx + 1,
                'teks' => $teks,
            ]);
        }

        // Sections RKPD (A s/d J)
        $sections = [
            [
                'kunci' => 'pernyataan',
                'judul' => 'A. Pernyataan Reviu',
                'isi_default' => 'Kami telah mereviu Rancangan Akhir Rencana Kerja Pemerintah Daerah (Rankir RKPD) Kabupaten Rembang Tahun Anggaran {{tahun}} sesuai dengan Standar Audit Intern Pemerintah Indonesia (SAIPI). Reviu bertujuan untuk memberikan keyakinan terbatas bahwa perumusan RKPD telah sesuai dengan kaidah dan ketentuan peraturan perundang-undangan yang berlaku.',
                'urutan' => 1,
            ],
            [
                'kunci' => 'dasar_hukum',
                'judul' => 'B. Dasar Hukum Reviu',
                'isi_default' => 'Reviu dilaksanakan berdasarkan Surat Perintah Tugas Inspektur Kabupaten Rembang Nomor {{nomor_spt}} tanggal {{tanggal_spt}} dalam rangka pemenuhan tahapan perencanaan pembangunan daerah.',
                'urutan' => 2,
            ],
            [
                'kunci' => 'tujuan',
                'judul' => 'C. Tujuan Reviu',
                'isi_default' => 'Tujuan reviu adalah memberikan keyakinan terbatas mengenai kepatuhan proses penyusunan Rankir RKPD, konsistensi prioritas dan sasaran terhadap RPJMD, kelengkapan dokumen pendukung, dan keselarasan program prioritas nasional.',
                'urutan' => 3,
            ],
            [
                'kunci' => 'ruang_lingkup',
                'judul' => 'D. Ruang Lingkup Reviu',
                'isi_default' => 'Ruang lingkup reviu mencakup pengujian kesesuaian format, analisis isi bab, konsistensi program, outcome, indikator target kinerja dan pagu indikatif antara RPJMD dengan Rankir RKPD, proyeksi pendanaan, serta program prioritas nasional (Asta Cita/SPM).',
                'urutan' => 4,
            ],
            [
                'kunci' => 'objek',
                'judul' => 'E. Objek Reviu',
                'isi_default' => 'Objek reviu adalah Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (Rankir RKPD) Kabupaten Rembang Tahun {{tahun}} beserta lampiran dan dokumen pendukung yang disusun oleh {{opd}}.',
                'urutan' => 5,
            ],
            [
                'kunci' => 'pelaksanaan',
                'judul' => 'F. Pelaksanaan Reviu',
                'isi_default' => 'Reviu dilaksanakan mulai tanggal {{tanggal_mulai}} sampai dengan {{tanggal_selesai}} bertempat di Inspektorat Daerah Kabupaten Rembang dan {{opd}}.',
                'urutan' => 6,
            ],
            [
                'kunci' => 'metodologi',
                'judul' => 'G. Metodologi Reviu',
                'isi_default' => 'Metodologi reviu meliputi penelaahan dokumen (desk review), penelusuran data pada aplikasi SIPD-RI, konfirmasi, klarifikasi teknis dengan tim penyusun, dan analisis komparasi antar dokumen perencanaan.',
                'urutan' => 7,
            ],
            [
                'kunci' => 'gambaran_umum',
                'judul' => 'H. Gambaran Umum Dokumen',
                'isi_default' => 'Rankir RKPD Tahun {{tahun}} memuat prioritas pembangunan daerah, kerangka ekonomi makro, arah kebijakan fiskal, rencana kerja, dan pendanaan program daerah.',
                'urutan' => 8,
            ],
            [
                'kunci' => 'uraian_hasil',
                'judul' => 'I. Uraian Hasil Reviu',
                'isi_default' => 'Uraian hasil reviu terdiri atas simpulan kesesuaian pengujian, catatan hasil penelaahan, dan rekomendasi penyempurnaan yang disajikan secara terperinci.',
                'urutan' => 9,
            ],
            [
                'kunci' => 'apresiasi',
                'judul' => 'J. Apresiasi',
                'isi_default' => 'Kami menyampaikan penghargaan dan apresiasi setinggi-tingginya kepada seluruh tim penyusun atas kerja sama, keterbukaan data, dan komitmen penyempurnaan dokumen Rankir RKPD.',
                'urutan' => 10,
            ],
        ];

        $template->sections()->delete();
        foreach ($sections as $sec) {
            LhrTemplateSection::create(array_merge($sec, ['lhr_template_id' => $template->id]));
        }

        // Pengujian RKPD (termasuk sub-pengujian 6a s/d 6e)
        $template->pengujians()->delete();

        $items = [
            ['kode' => 'RKPD.F',  'grup' => 'Format',    'judul' => 'Kesesuaian format dan sistematika Rankir RKPD', 'urutan' => 1],
            ['kode' => 'RKPD.S1', 'grup' => 'Substansi', 'judul' => 'Kelengkapan dokumen pendukung perencanaan', 'urutan' => 2],
            ['kode' => 'RKPD.S2', 'grup' => 'Substansi', 'judul' => 'Analisis isi dan konsistensi narasi BAB dokumen RKPD', 'urutan' => 3],
            ['kode' => 'RKPD.S3', 'grup' => 'Substansi', 'judul' => 'Kesesuaian rumusan prioritas dan sasaran pembangunan RKPD terhadap RPJMD', 'urutan' => 4],
            ['kode' => 'RKPD.S4', 'grup' => 'Substansi', 'judul' => 'Kesesuaian program/outcome, indikator, target, dan pagu indikatif RPJMD dengan Rankir RKPD', 'urutan' => 5],
            ['kode' => 'RKPD.S5', 'grup' => 'Substansi', 'judul' => 'Kewajaran dan kepatutan proyeksi kerangka pendanaan daerah', 'urutan' => 6],
            ['kode' => 'RKPD.S6', 'grup' => 'Substansi', 'judul' => 'Kesesuaian dengan Program Prioritas Nasional & Kebijakan Strategis', 'urutan' => 7, 'is_parent' => true],
            ['kode' => 'RKPD.S7', 'grup' => 'Substansi', 'judul' => 'Kesesuaian dengan tata cara dan penelaahan Pokok-Pokok Pikiran DPRD', 'urutan' => 8],
            ['kode' => 'RKPD.S8', 'grup' => 'Substansi', 'judul' => 'Konsistensi indikator & target kinerja tujuan serta sasaran RKPD terhadap RPJMD', 'urutan' => 9],
        ];

        $subItemsS6 = [
            ['kode' => 'RKPD.S6.a', 'judul' => 'Urusan pendukung Asta Cita dan kesepakatan Rakortekbang', 'urutan' => 1],
            ['kode' => 'RKPD.S6.b', 'judul' => 'Penyelarasan target indikator makro daerah terhadap target RKP', 'urutan' => 2],
            ['kode' => 'RKPD.S6.c', 'judul' => 'Dukungan terhadap Program Strategis Nasional (ProSN)', 'urutan' => 3],
            ['kode' => 'RKPD.S6.d', 'judul' => 'Pemenuhan program, kegiatan, dan sub kegiatan Standar Pelayanan Minimal (SPM)', 'urutan' => 4],
            ['kode' => 'RKPD.S6.e', 'judul' => 'Alokasi dan pelaksanaan sub kegiatan tematik pembangunan prioritas', 'urutan' => 5],
        ];

        foreach ($items as $item) {
            $isParent = $item['is_parent'] ?? false;
            unset($item['is_parent']);

            $created = LhrTemplatePengujian::create([
                'lhr_template_id' => $template->id,
                'kode' => $item['kode'],
                'judul' => $item['judul'],
                'grup' => $item['grup'],
                'urutan' => $item['urutan'],
                'opsi_simpulan' => ['sesuai', 'belum_sesuai', 'tidak_sesuai'],
                'teks_simpulan_default' => "Berdasarkan hasil pengujian pada butir {$item['kode']}, perumusan dinyatakan sesuai dengan ketentuan.",
            ]);

            if ($isParent) {
                foreach ($subItemsS6 as $sub) {
                    LhrTemplatePengujian::create([
                        'lhr_template_id' => $template->id,
                        'parent_id' => $created->id,
                        'kode' => $sub['kode'],
                        'judul' => $sub['judul'],
                        'grup' => 'Substansi',
                        'urutan' => $sub['urutan'],
                        'opsi_simpulan' => ['sesuai', 'belum_sesuai', 'tidak_sesuai'],
                        'teks_simpulan_default' => "Pengujian butir {$sub['kode']} telah ditelaah dan selaras dengan prioritas nasional.",
                    ]);
                }
            }
        }
    }

    private function seedKuaPpas(): void
    {
        $jenisKua = JenisDokumenReviu::updateOrCreate(
            ['kode' => 'KUA_PPAS'],
            [
                'uuid' => (string) Str::uuid(),
                'nama' => 'Kebijakan Umum Anggaran dan PPAS (KUA-PPAS)',
                'gaya_penomoran_bagian' => 'alfabet',
                'tata_letak_hasil' => 'tabel_dua_kolom',
                'sla_tindak_lanjut_hari' => 14,
                'aktif' => true,
            ]
        );

        $template = LhrTemplate::updateOrCreate(
            [
                'jenis_dokumen_reviu_id' => $jenisKua->id,
                'jenis_laporan' => 'LHR',
                'versi' => 1,
            ],
            [
                'status' => 'terbit',
                'berlaku_mulai' => '2026-01-01',
                'dasar_perubahan' => 'PP Nomor 12 Tahun 2019 dan Permendagri Nomor 77 Tahun 2020',
            ]
        );

        // Dasar Hukum KUA-PPAS
        $dasarHukums = [
            'Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah sebagaimana telah diubah beberapa kali terakhir dengan Undang-Undang Nomor 6 Tahun 2023.',
            'Peraturan Pemerintah Nomor 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah.',
            'Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah.',
            'Peraturan Menteri Dalam Negeri Nomor 77 Tahun 2020 tentang Pedoman Teknis Pengelolaan Keuangan Daerah.',
            'Peraturan Menteri Dalam Negeri tentang Pedoman Penyusunan APBD Tahun Anggaran Berjalan.',
            'Peraturan Menteri Dalam Negeri Nomor 3 Tahun 2026 tentang Pedoman Pengawasan Penyelenggaraan Pemerintahan Daerah.',
            'Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Pembentukan dan Susunan Perangkat Daerah Kabupaten Rembang.',
            'Peraturan Bupati Rembang Nomor 54 Tahun 2021 tentang Kedudukan, Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Inspektorat Daerah Kabupaten Rembang.',
        ];

        $template->dasarHukums()->delete();
        foreach ($dasarHukums as $idx => $teks) {
            LhrTemplateDasarHukum::create([
                'lhr_template_id' => $template->id,
                'urutan' => $idx + 1,
                'teks' => $teks,
            ]);
        }

        // Sections KUA-PPAS
        $sections = [
            [
                'kunci' => 'pernyataan',
                'judul' => 'A. Pernyataan Reviu',
                'isi_default' => 'Kami telah mereviu Rancangan Kebijakan Umum Anggaran dan Prioritas Plafon Anggaran Sementara (KUA-PPAS) Kabupaten Rembang Tahun Anggaran {{tahun}} sesuai dengan Standar Audit Intern Pemerintah Indonesia (SAIPI). Reviu bertujuan untuk memberikan keyakinan terbatas atas konsistensi dan keselarasan dengan RKPD.',
                'urutan' => 1,
            ],
            [
                'kunci' => 'dasar_hukum',
                'judul' => 'B. Dasar Hukum Reviu',
                'isi_default' => 'Reviu dilaksanakan berdasarkan Surat Perintah Tugas Inspektur Kabupaten Rembang Nomor {{nomor_spt}} tanggal {{tanggal_spt}}.',
                'urutan' => 2,
            ],
            [
                'kunci' => 'tujuan',
                'judul' => 'C. Tujuan Reviu',
                'isi_default' => 'Tujuan reviu adalah meyakinkan konsistensi tujuan, sasaran strategis, proyeksi pendapatan, belanja, dan pembiayaan antara KUA-PPAS dengan RKPD dan RPJMD.',
                'urutan' => 3,
            ],
            [
                'kunci' => 'ruang_lingkup',
                'judul' => 'D. Ruang Lingkup Reviu',
                'isi_default' => 'Ruang lingkup reviu meliputi pengujian keselarasan kerangka ekonomi makro, kebijakan belanja daerah, pagu plafon anggaran sementara, dan pembiayaan daerah.',
                'urutan' => 4,
            ],
            [
                'kunci' => 'objek',
                'judul' => 'E. Objek Reviu',
                'isi_default' => 'Objek reviu adalah Dokumen Rancangan KUA dan Rancangan PPAS Kabupaten Rembang Tahun Anggaran {{tahun}} yang disusun oleh TAPD/{{opd}}.',
                'urutan' => 5,
            ],
            [
                'kunci' => 'pelaksanaan',
                'judul' => 'F. Pelaksanaan Reviu',
                'isi_default' => 'Reviu dilaksanakan mulai tanggal {{tanggal_mulai}} sampai dengan {{tanggal_selesai}}.',
                'urutan' => 6,
            ],
            [
                'kunci' => 'metodologi',
                'judul' => 'G. Metodologi Reviu',
                'isi_default' => 'Metodologi reviu meliputi rekonsiliasi data pagu, telaah komparatif KUA-PPAS vs RKPD, validasi SIPD-RI, dan konfirmasi teknis bersama TAPD.',
                'urutan' => 7,
            ],
            [
                'kunci' => 'gambaran_umum',
                'judul' => 'H. Gambaran Umum Dokumen',
                'isi_default' => 'Rancangan KUA-PPAS Tahun {{tahun}} memuat proyeksi pendapatan daerah, alokasi prioritas belanja, dan penyesuaian defisit/pembiayaan.',
                'urutan' => 8,
            ],
            [
                'kunci' => 'uraian_hasil',
                'judul' => 'I. Uraian Hasil Reviu',
                'isi_default' => 'Uraian hasil reviu disajikan dalam tabel simpulan kesesuaian pengujian, catatan hasil penelaahan, dan rekomendasi tindak lanjut.',
                'urutan' => 9,
            ],
            [
                'kunci' => 'apresiasi',
                'judul' => 'J. Apresiasi',
                'isi_default' => 'Kami menyampaikan apresiasi kepada TAPD dan jajaran {{opd}} atas kerja sama dan penyediaan data yang transparan.',
                'urutan' => 10,
            ],
        ];

        $template->sections()->delete();
        foreach ($sections as $sec) {
            LhrTemplateSection::create(array_merge($sec, ['lhr_template_id' => $template->id]));
        }

        // Pengujian KUA-PPAS (KP.S1 s/d KP.S6 dengan sub 6a s/d 6e)
        $template->pengujians()->delete();

        $itemsKua = [
            ['kode' => 'KP.S1', 'grup' => 'Substansi', 'judul' => 'Kesesuaian tujuan & sasaran strategis KUA-PPAS dengan RPJMD dan RKPD', 'urutan' => 1],
            ['kode' => 'KP.S2', 'grup' => 'Substansi', 'judul' => 'Kesesuaian rumusan prioritas pembangunan daerah antara KUA-PPAS dengan RKPD', 'urutan' => 2],
            ['kode' => 'KP.S3', 'grup' => 'Substansi', 'judul' => 'Kesesuaian proyeksi fiskal (pendapatan, belanja, dan pembiayaan) dengan RKPD', 'urutan' => 3],
            ['kode' => 'KP.S4', 'grup' => 'Substansi', 'judul' => 'Konsistensi indikator, target kinerja, program, kegiatan, dan sub kegiatan dengan RKPD', 'urutan' => 4],
            ['kode' => 'KP.S5', 'grup' => 'Substansi', 'judul' => 'Kesesuaian pagu indikatif dan plafon anggaran sementara program/kegiatan dengan RKPD', 'urutan' => 5],
            ['kode' => 'KP.S6', 'grup' => 'Substansi', 'judul' => 'Substansi narasi dan konsistensi antar bab dokumen KUA-PPAS', 'urutan' => 6, 'is_parent' => true],
        ];

        $subItemsKpS6 = [
            ['kode' => 'KP.S6.a', 'judul' => 'Bab Pendahuluan dan Keselarasan Arah Kebijakan Makro', 'urutan' => 1],
            ['kode' => 'KP.S6.b', 'judul' => 'Bab Kerangka Ekonomi Makro Daerah (PDRB, Inflasi, dan Pertumbuhan Ekonomi)', 'urutan' => 2],
            ['kode' => 'KP.S6.c', 'judul' => 'Bab Kebijakan Belanja Daerah, Prioritas Daerah, dan SPM', 'urutan' => 3],
            ['kode' => 'KP.S6.d', 'judul' => 'Bab Kebijakan Pembiayaan Daerah & Kapasitas Riil Keuangan Daerah', 'urutan' => 4],
            ['kode' => 'KP.S6.e', 'judul' => 'Bab Strategi Pencapaian Pendapatan dan Efisiensi Belanja', 'urutan' => 5],
        ];

        foreach ($itemsKua as $item) {
            $isParent = $item['is_parent'] ?? false;
            unset($item['is_parent']);

            $created = LhrTemplatePengujian::create([
                'lhr_template_id' => $template->id,
                'kode' => $item['kode'],
                'judul' => $item['judul'],
                'grup' => $item['grup'],
                'urutan' => $item['urutan'],
                'opsi_simpulan' => ['sesuai', 'belum_sesuai', 'tidak_sesuai'],
                'teks_simpulan_default' => "Berdasarkan hasil pengujian pada butir {$item['kode']}, perumusan KUA-PPAS dinyatakan telah selaras dengan RKPD.",
            ]);

            if ($isParent) {
                foreach ($subItemsKpS6 as $sub) {
                    LhrTemplatePengujian::create([
                        'lhr_template_id' => $template->id,
                        'parent_id' => $created->id,
                        'kode' => $sub['kode'],
                        'judul' => $sub['judul'],
                        'grup' => 'Substansi',
                        'urutan' => $sub['urutan'],
                        'opsi_simpulan' => ['sesuai', 'belum_sesuai', 'tidak_sesuai'],
                        'teks_simpulan_default' => "Penelaahan bab pada butir {$sub['kode']} telah konsisten dan mencantumkan data pendukung yang memadai.",
                    ]);
                }
            }
        }
    }
}
