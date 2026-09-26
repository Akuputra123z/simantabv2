<?php

namespace Database\Seeders;

use App\Enums\SimpulanStatus;
use App\Models\AuditAssignment;
use App\Models\KodeRekomendasi;
use App\Models\Lhr\Lhr;
use App\Models\Lhr\LhrCatatan;
use App\Models\Lhr\LhrKategoriCatatan;
use App\Models\Recommendation;
use App\Models\UnitDiperiksa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LhrRkpdDocumentSyncSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Cari atau Sinkronkan Dokumen LHR RKPD 2027
            $lhr = Lhr::where('uuid', '37853dbc-10e3-4774-93c8-9bb7fcc94e02')->first()
                ?? Lhr::whereHas('jenisDokumen', fn ($q) => $q->where('kode', 'RKPD'))->first();

            if (! $lhr) {
                return;
            }

            // Pastikan Unit BAPPEDA terhubung
            $bappeda = UnitDiperiksa::where('nama_unit', 'like', '%Perencanaan Pembangunan%')->first();
            $unitId = $bappeda?->id ?? 315;

            // Update Metadata LHR RKPD 2027
            $lhr->update([
                'nomor_lhr' => '700/026/REVIU/INSP/2026',
                'tanggal_lhr' => '2026-05-11',
                'tahun_anggaran' => 2027,
                'unit_diperiksa_id' => $unitId,
                'permohonan_nomor' => '000.7/250/2026',
                'permohonan_tanggal' => '2026-04-28',
                'tanggal_mulai' => '2026-04-30',
                'tanggal_selesai' => '2026-05-11',
            ]);

            // Sinkronkan data penugasan SPT ID 14 jika ada
            if ($lhr->audit_assignment_id) {
                $assignment = AuditAssignment::find($lhr->audit_assignment_id);
                if ($assignment) {
                    $assignment->update([
                        'nomor_surat' => '000.1.2.3/020/INSP/2026',
                        'tanggal_mulai' => '2026-04-29',
                        'tanggal_selesai' => '2026-05-11',
                    ]);
                }
            }

            // Ambil Kategori ID
            $katFormat = LhrKategoriCatatan::where('kode', 'FORMAT_DOKUMEN')->value('id') ?? 12;
            $katDokumen = LhrKategoriCatatan::where('kode', 'KELENGKAPAN_DOKUMEN')->value('id') ?? 11;
            $katBab = LhrKategoriCatatan::where('kode', 'SISTEMATIKA_BAB')->value('id') ?? 12;
            $katFiskal = LhrKategoriCatatan::where('kode', 'PROYEKSI_FISKAL')->value('id') ?? 9;
            $katPagu = LhrKategoriCatatan::where('kode', 'PAGU_INDIKATIF')->value('id') ?? 10;
            $katNasional = LhrKategoriCatatan::where('kode', 'PROGRAM_PRIORITAS_NASIONAL')->value('id') ?? 7;
            $katPokir = LhrKategoriCatatan::where('kode', 'POKOK_PIKIRAN_DPRD')->value('id') ?? 8;
            $katRpjmd = LhrKategoriCatatan::where('kode', 'KONSISTENSI_RPJMD')->value('id') ?? 6;

            // Ambil Kode Rekomendasi
            $kodeRk06 = KodeRekomendasi::where('kode', 'RK-06')->value('id');
            $kodeRk09 = KodeRekomendasi::where('kode', 'RK-09')->value('id');

            // 2. Daftar Simpulan & Temuan Riil dari Template Resmi Rembang
            $pengujianData = [
                'RKPD.F' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Format/sistematika dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun 2027 kurang sesuai dengan yang diatur dalam Lampiran Peraturan Menteri Dalam Negeri Nomor 86 tahun 2017 (halaman 384).',
                    'catatans' => [
                        [
                            'kategori_id' => $katFormat,
                            'ref' => 'Bab I s/d Bab VII (Lampiran Permendagri 86/2017)',
                            'catatan' => 'Format/sistematika dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun 2027 kurang sesuai dengan yang diatur dalam Lampiran Peraturan Menteri Dalam Negeri Nomor 86 tahun 2017 (halaman 384) pada Sub Bab 1.2, Sub Bab 1.3, Sub Bab 1.5, Sub Bab 2.1.3, Sub Bab 2.1.4, Bab 4, dan Sub Bab 4.3.',
                            'rekomendasi' => 'Menyesuaikan judul: 1) Sub Bab 1.2, 2) Sub Bab 1.3, 3) Sub Bab 1.5, 4) Sub Bab 2.1.3, 5) Sub Bab 2.1.4, 6) Bab 4, 7) Sub Bab 4.3 Dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun 2027 dengan mempedomani Lampiran Peraturan Menteri Dalam Negeri nomor 86 Tahun 2017 halaman 384.',
                            'kode_rek_id' => $kodeRk06,
                        ],
                    ],
                ],
                'RKPD.S1' => [
                    'status' => SimpulanStatus::Sesuai,
                    'simpulan' => 'Rancangan Akhir RKPD Kabupaten Rembang tahun 2027 telah didukung dengan dokumen perencanaan yang memadai (RPJMN 2025-2029, RPJMD Prov Jateng, Rankir RKPD Prov Jateng, RPJMD Kab Rembang, Renstra OPD, Hasil Evaluasi RKPD, BA FKP, BA Musrenbang, dan SK Tim Penyusun).',
                    'catatans' => [
                        [
                            'kategori_id' => $katDokumen,
                            'ref' => 'Dokumen Pendukung Perencanaan',
                            'catatan' => 'Terdapat data/dokumen pendukung yang belum ada, yaitu: a) Hasil Evaluasi RKPD tahun 2026 (Triwulan 1); b) Laporan hasil pengendalian kebijakan penyusunan RKPD Formulir E.35 (Cek List: 27 komponen); c) Jadwal / Agenda Kerja Tim Penyusunan RKPD tahun 2027; dan d) Rekapitulasi Pokok-pokok Pikiran DPRD tahun 2027.',
                            'rekomendasi' => 'Melengkapi data/dokumen pendukung: 1) Hasil Evaluasi RKPD tahun 2026 (TW 1); 2) Laporan hasil pengendalian kebijakan Formulir E.35; 3) Jadwal kerja Tim Penyusunan; dan 4) Rekapitulasi Pokok-pokok Pikiran DPRD tahun 2027.',
                            'kode_rek_id' => $kodeRk06,
                        ],
                    ],
                ],
                'RKPD.S2' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun 2027, masih terdapat kekurangsesuaian data yang disajikan pada Bab II, Bab III, dan Bab IV.',
                    'catatans' => [
                        [
                            'kategori_id' => $katBab,
                            'ref' => 'Bab II Sub 2.1.4 (Aspek Pelayanan Umum)',
                            'catatan' => 'Belum menguraikan terkait Indikator Standar Pelayanan Minimum (SPM) sebagaimana tercantum dalam Peraturan Pemerintah Nomor 2 Tahun 2018; dan masih mencantumkan terkait anggaran, SDM dan sarana prasarana sebagai faktor penghambat kegagalan/ketidaktercapaian atas indikator kinerja tahun 2025.',
                            'rekomendasi' => 'Menambahkan uraian terkait Indikator Standar Pelayanan Minimum (SPM) sebagaimana tercantum dalam Peraturan Pemerintah Nomor 2 Tahun 2018; dan menghapus faktor penghambat anggaran, SDM, dan sarana prasarana atas capaian kinerja.',
                            'kode_rek_id' => $kodeRk09,
                        ],
                        [
                            'kategori_id' => $katFiskal,
                            'ref' => 'Bab III Sub 3.1 (Arah Kebijakan Ekonomi Daerah)',
                            'catatan' => 'Penjelasan terkait pertumbuhan ekonomi belum didukung dengan data pendukung yang disajikan pada tabel secara lengkap: PDRB ADHK Menurut Lapangan Usaha 2024-2025; PDRB ADHB Menurut Pengeluaran 2024-2025; PDRB ADHK Menurut Pengeluaran 2024-2025; dan Laju Inflasi.',
                            'rekomendasi' => 'Melengkapi data pendukung pertumbuhan ekonomi yang meliputi tabel PDRB ADHK Lapangan Usaha, ADHB/ADHK Pengeluaran 2024-2025, dan Laju Inflasi Kabupaten Rembang.',
                            'kode_rek_id' => $kodeRk06,
                        ],
                        [
                            'kategori_id' => $katFiskal,
                            'ref' => 'Bab III Sub 3.2 (Arah Kebijakan Keuangan Daerah)',
                            'catatan' => 'Ketidaksesuaian angka proyeksi PAD dan belanja pada dokumen Rankir RKPD (tabel 3.5) dengan SIPD; kebijakan belanja belum memprioritaskan belanja pelayanan dasar; belum memproyeksikan belanja tidak terduga dan transfer; belanja belum dirinci per rekening; dan terdapat proyeksi pembayaran Utang Daerah sebesar Rp 4.500.000.000,- tanpa penjelasan peruntukannya.',
                            'rekomendasi' => 'Menyelaraskan proyeksi Pendapatan, Belanja dan Pembiayaan dokumen Rankir RKPD (Bab III) dengan SIPD; menetapkan skala prioritas belanja (wajib mengikat, pelayanan dasar, mandatory spending); memproyeksikan BTT dan transfer; merinci rekening belanja; serta menambahkan penjelasan peruntukan utang daerah Rp 4,5 Miliar.',
                            'kode_rek_id' => $kodeRk09,
                        ],
                        [
                            'kategori_id' => $katBab,
                            'ref' => 'Bab IV Sub 4.2 & 4.3 (Sasaran dan Prioritas)',
                            'catatan' => 'Arah kebijakan pembangunan tabel 4.2 belum difokuskan dalam mewujudkan tema pembangunan tahun 2027 dan inventarisasi permasalahan Bab II belum sepenuhnya menjadi prioritas yang ditangani.',
                            'rekomendasi' => 'Memfokuskan arah kebijakan pembangunan tabel 4.2 pada tema pembangunan 2027 dan menambahkan prioritas pembangunan sesuai permasalahan yang dituangkan dalam Bab II.',
                            'kode_rek_id' => $kodeRk09,
                        ],
                    ],
                ],
                'RKPD.S3' => [
                    'status' => SimpulanStatus::Sesuai,
                    'simpulan' => 'Rumusan Prioritas dan Sasaran Pembangunan dalam Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun 2027 telah sesuai dengan Dokumen RPJMD Kabupaten Rembang tahun 2025-2029 (5 Prioritas Pembangunan Daerah selaras).',
                    'catatans' => [],
                ],
                'RKPD.S4' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rancangan Akhir RKPD tahun 2027 belum sesuai dengan RPJMD tahun 2025-2029.',
                    'catatans' => [
                        [
                            'kategori_id' => $katPagu,
                            'ref' => 'Bab V dan Lampiran SIPD-RI (Renja OPD)',
                            'catatan' => 'Terdapat ketidaksesuaian Indikator Kinerja, Target beserta Pagu Indikatif tabel 4.1 RPJMD dengan Lampiran SIPD Excel/PDF; program SIPD PDF belum dilengkapi outcome/indikator/capaian; program prioritas nasional kurang proporsi anggaran; penetapan target sampah tanpa alokasi anggaran; kenaikan proporsi anggaran signifikan; dan selisih anggaran program perikanan unggulan Bupati.',
                            'rekomendasi' => 'Menyesuaikan indikator kinerja, target beserta pagu indikatif tabel 5.1 & 5.3 dengan tabel 4.1 RPJMD; melengkapi outcome dan capaian pada SIPD PDF; memproporsikan anggaran program prioritas nasional dan persampahan; mengkaji kenaikan anggaran; serta memproporsikan anggaran program perikanan unggulan Bupati.',
                            'kode_rek_id' => $kodeRk09,
                        ],
                    ],
                ],
                'RKPD.S5' => [
                    'status' => SimpulanStatus::Sesuai,
                    'simpulan' => 'Proyeksi pendanaan pada Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun 2027 telah wajar dengan rekomendasi penyelarasan data APBD Perbup 4/2026 dan SIPD.',
                    'catatans' => [
                        [
                            'kategori_id' => $katFiskal,
                            'ref' => 'Bab III Sub 3.2 (Proyeksi Kerangka Pendanaan)',
                            'catatan' => 'Data APBD 2026 pada tabel 3.5 belum menggunakan Perbup 4/2026 (Penjabaran APBD Perubahan); data Realisasi 2025 unaudited belum sesuai LRA terupdate; proyeksi APBD 2027 tidak sesuai data SIPD-RI; dan belum memproyeksikan belanja tidak terduga serta belanja transfer.',
                            'rekomendasi' => 'Penyajian data APBD 2026 agar mempedomani Perbup 4/2026; data Realisasi 2025 disesuaikan LRA unaudited terupdate; menyelaraskan proyeksi APBD 2027 dengan SIPD-RI; serta memproyeksikan belanja tidak terduga dan transfer.',
                            'kode_rek_id' => $kodeRk09,
                        ],
                    ],
                ],
                'RKPD.S6' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Kesesuaian informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional, Asta Cita, ProSN, SPM, dan Tematik masih memerlukan pemetaan komprehensif pada Renja OPD.',
                    'catatans' => [],
                ],
                'RKPD.S6.a' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil rapat koordinasi teknis pembangunan (Rakortekrenbang Nasional tahun 2026) pada Lampiran SIPD-RI belum selaras.',
                    'catatans' => [
                        [
                            'kategori_id' => $katNasional,
                            'ref' => 'Lampiran SIPD-RI (Asta Cita Rakortekrenbang)',
                            'catatan' => 'Terdapat ketidakselarasan pemetaan urusan terhadap Asta Cita hasil Rakortekrenbang 2026; belum teridentifikasinya pemetaan dukungan subkegiatan terhadap outcome prioritas pusat; dan terdapat subkegiatan dengan target nihil yang dimasukkan dalam pemetaan.',
                            'rekomendasi' => 'Melakukan perbaikan pemetaan urusan terhadap dukungan Asta Cita; mengidentifikasi pemetaan subkegiatan pendukung outcome prioritas; dan memperbaiki target subkegiatan yang kurang mendukung Asta Cita.',
                            'kode_rek_id' => $kodeRk09,
                        ],
                    ],
                ],
                'RKPD.S6.b' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Rencana Kerja Pemerintah (RKP) tahun 2027 belum tersedia saat pelaksanaan reviu.',
                    'catatans' => [],
                ],
                'RKPD.S6.c' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Program Strategis Nasional (ProSN) dalam dokumen Rankir RKPD belum seluruhnya terpetakan dalam Lampiran SIPD-RI (Renja OPD).',
                    'catatans' => [
                        [
                            'kategori_id' => $katNasional,
                            'ref' => 'Lampiran SIPD-RI (Dukungan ProSN)',
                            'catatan' => 'Subkegiatan yang mendukung pelaksanaan Program Strategis Nasional (ProSN) belum seluruhnya terpetakan dalam Lampiran SIPD-RI PDF dan Excel Rancangan Akhir RKPD tahun 2027.',
                            'rekomendasi' => 'Penyusunan dokumen perencanaan Kabupaten Rembang agar diselaraskan dan dipetakan sesuai dengan Program Strategis Nasional (ProSN).',
                            'kode_rek_id' => $kodeRk09,
                        ],
                    ],
                ],
                'RKPD.S6.d' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Program, kegiatan dan subkegiatan Standar Pelayanan Minimal (SPM) belum seluruhnya terpetakan dan belum beranggaran memadai.',
                    'catatans' => [
                        [
                            'kategori_id' => $katNasional,
                            'ref' => 'Renja OPD Standar Pelayanan Minimal (SPM)',
                            'catatan' => 'Terdapat subkegiatan SPM yang belum terpetakan dalam Lampiran SIPD-RI dan terdapat beberapa subkegiatan SPM yang tidak mendapatkan proporsi anggaran memadai pada Rankir RKPD 2027.',
                            'rekomendasi' => 'Penetapan subkegiatan agar diselaraskan dan dipetakan dengan semua subkegiatan SPM serta memproporsikan anggaran untuk semua indikator kinerja subkegiatan SPM.',
                            'kode_rek_id' => $kodeRk09,
                        ],
                    ],
                ],
                'RKPD.S6.e' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Sub Kegiatan Tematik Pembangunan belum seluruhnya terpetakan dalam Lampiran SIPD-RI Renja OPD.',
                    'catatans' => [
                        [
                            'kategori_id' => $katNasional,
                            'ref' => 'Renja OPD Subkegiatan Tematik Pembangunan',
                            'catatan' => 'Terdapat subkegiatan Tematik Pembangunan yang belum terpetakan dalam SIPD-RI dan sebagian tidak mendapatkan proporsi anggaran memadai.',
                            'rekomendasi' => 'Penetapan subkegiatan agar diselaraskan dengan semua subkegiatan Tematik Pembangunan/Asta Cita Nasional serta memproporsikan anggaran pelaksanaannya.',
                            'kode_rek_id' => $kodeRk09,
                        ],
                    ],
                ],
                'RKPD.S7' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Dokumen Rancangan Akhir RKPD tahun 2027 belum dilengkapi dengan rekapitulasi data pokok-pokok pikiran Dewan Perwakilan Rakyat Daerah (DPRD) Kabupaten Rembang tahun 2027.',
                    'catatans' => [
                        [
                            'kategori_id' => $katPokir,
                            'ref' => 'Bab IV Sub 4.4 (Pokok-Pokok Pikiran DPRD)',
                            'catatan' => 'Bab IV telah menjelaskan Pokir DPRD (hal IV-33 s/d IV-34), namun belum dilengkapi dengan rekapitulasi data Pokir DPRD yang berisi: Program/Kegiatan/Sub Kegiatan, Indikator, Volume, Kelompok Sasaran, Lokasi dan Perangkat Daerah pengampu.',
                            'rekomendasi' => 'Penjelasan Pokok-Pokok Pikiran DPRD pada Bab IV agar dilengkapi dengan rekapitulasi data pokok-pokok pikiran DPRD yang berisi: Program, Kegiatan, Subkegiatan, Indikator, Volume, Sasaran, Lokasi dan Perangkat Daerah terkait.',
                            'kode_rek_id' => $kodeRk06,
                        ],
                    ],
                ],
                'RKPD.S8' => [
                    'status' => SimpulanStatus::BelumSesuai,
                    'simpulan' => 'Target Kinerja Tujuan dan Sasaran dalam dokumen Rancangan Akhir RKPD tahun 2027 belum konsisten dengan Dokumen RPJMD Kabupaten Rembang Tahun 2025-2029 pada Misi 1 s/d Misi 5.',
                    'catatans' => [
                        [
                            'kategori_id' => $katRpjmd,
                            'ref' => 'Tabel 3.6 RPJMD vs Tabel 4.1 Rankir RKPD (Misi 1-5)',
                            'catatan' => 'Terdapat ketidakkonsistenan target Indikator Kinerja Tujuan dan Sasaran yang dicantumkan pada tabel 3.6 RPJMD 2025-2029 dengan tabel 4.1 RKPD 2027 (Indeks Daya Saing, IPM, IKG, Pertumbuhan Ekonomi, PMTB, PDRB Per Kapita, Pengangguran, Kemiskinan, IKP, RB General/Tematik, Infrastruktur, Konektivitas, Permukiman, IKLH, Emisi GRK, dan Risiko Bencana).',
                            'rekomendasi' => 'Mengkaji kembali atas penetapan target indikator kinerja Tujuan dan Sasaran yang tercantum pada tabel 3.6 RPJMD 2025-2029 dan tabel 3.9 Teknik Perumusan Prioritas dengan tabel 4.1 RKPD 2027 yang tidak konsisten.',
                            'kode_rek_id' => $kodeRk09,
                        ],
                    ],
                ],
            ];

            // 3. Eksekusi Pengkinian Pengujian, Catatan, dan Rekomendasi
            foreach ($pengujianData as $kode => $data) {
                $pengujian = $lhr->pengujians()->where('kode', $kode)->first();
                if (! $pengujian) {
                    continue;
                }

                $pengujian->update([
                    'status_simpulan' => $data['status'],
                    'teks_simpulan' => $data['simpulan'],
                    'selesai_pada' => now(),
                ]);

                // Bersihkan catatan lama pada pengujian ini
                foreach ($pengujian->catatans as $oldCat) {
                    $oldCat->rekomendasis()->delete();
                    $oldCat->delete();
                }

                // Tambahkan catatan & rekomendasi baru
                foreach ($data['catatans'] as $idx => $cat) {
                    $catatan = LhrCatatan::create([
                        'lhr_pengujian_id' => $pengujian->id,
                        'kategori_id' => $cat['kategori_id'],
                        'referensi_bagian' => $cat['ref'],
                        'isi' => $cat['catatan'],
                        'urutan' => $idx + 1,
                        'created_by' => 1,
                    ]);

                    Recommendation::create([
                        'lhr_catatan_id' => $catatan->id,
                        'kode_rekomendasi_id' => $cat['kode_rek_id'],
                        'uraian_rekom' => $cat['rekomendasi'],
                        'jenis_rekomendasi' => Recommendation::JENIS_ADMIN,
                        'batas_waktu' => Carbon::parse('2026-05-25'), // 14 hari kerja
                        'status' => Recommendation::STATUS_BELUM,
                        'status_tl' => Recommendation::STATUS_TL_BELUM_SELESAI,
                        'created_by' => 1,
                    ]);
                }
            }
        });
    }
}
