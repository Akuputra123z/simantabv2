<?php

namespace App\Services;

use App\Models\Lhp;
use App\Models\Temuan;
use Illuminate\Support\Collection;

class LaporanKepatuhanService
{
    /**
     * Menganalisis riwayat temuan & rekomendasi berulang untuk objek pemeriksaan (OPD) yang sama,
     * serta menghasilkan kesimpulan evaluasi tajam per temuan, per rekomendasi, dan untuk OPD secara menyeluruh.
     */
    public function analisisTemuanBerulang(Lhp $lhp): array
    {
        $opdName = $lhp->unitDiperiksa?->nama_unit ?? $lhp->nama_unit ?? 'OPD Terperiksa';

        if ($lhp->is_nihil || $lhp->temuans->isEmpty() || ! $lhp->unit_diperiksa_id) {
            return [
                'has_berulang'      => false,
                'total_berulang'    => 0,
                'total_rekom_ulang' => 0,
                'tingkat_risiko'    => 'Rendah',
                'badge_color'       => 'emerald',
                'daftar_berulang'   => [],
                'temuans_map'       => [],
                'catatan_ringkas'   => 'LHP bebas temuan (Nihil) atau tidak memiliki riwayat temuan serupa.',
                'kesimpulan_opd'    => [
                    'status'    => 'Kepatuhan Prima & Nihil Pengulangan',
                    'tipe'      => 'prima',
                    'ringkasan' => "Objek Pemeriksaan ({$opdName}) menunjukkan kinerja tata kelola yang sangat baik tanpa adanya temuan berulang dari tahun lalu maupun periode pemeriksaan sebelumnya.",
                    'penegasan' => "Pimpinan OPD diapresiasi atas komitmen pengendalian intern dan diinstruksikan untuk terus mempertahankan standar kepatuhan ini secara konsisten.",
                ],
            ];
        }

        // Ambil ID LHP lain milik unit kerja / OPD yang sama
        $otherLhpIds = Lhp::where('unit_diperiksa_id', $lhp->unit_diperiksa_id)
            ->where('id', '!=', $lhp->id)
            ->pluck('id');

        if ($otherLhpIds->isEmpty()) {
            return [
                'has_berulang'      => false,
                'total_berulang'    => 0,
                'total_rekom_ulang' => 0,
                'tingkat_risiko'    => 'Rendah',
                'badge_color'       => 'blue',
                'daftar_berulang'   => [],
                'temuans_map'       => [],
                'catatan_ringkas'   => 'Pemeriksaan pertama/tunggal pada entitas ini dalam basis data pengawasan.',
                'kesimpulan_opd'    => [
                    'status'    => 'Pemeriksaan Awal / Kasus Baru',
                    'tipe'      => 'baru',
                    'ringkasan' => "Seluruh temuan dan rekomendasi pada LHP ini merupakan kasus pertama yang teridentifikasi pada Objek Pemeriksaan ({$opdName}).",
                    'penegasan' => "Tindak lanjut yang telah tuntas 100% harus diiringi dengan mitigasi pencegahan agar kelemahan serupa tidak menjadi temuan berulang pada audit berikutnya.",
                ],
            ];
        }

        // Ambil temuan historis dari LHP lain milik OPD ini beserta rekomendasinya
        $historicalTemuans = Temuan::with([
            'lhp:id,nomor_lhp,tanggal_lhp',
            'kodeTemuan',
            'recommendations.kodeRekomendasi',
        ])
        ->whereIn('lhp_id', $otherLhpIds)
        ->get();

        $daftarBerulang = [];
        $temuansMap = [];
        $hasFinancialRepeat = false;
        $totalRekomUlang = 0;

        foreach ($lhp->temuans as $temuan) {
            $kodeId = $temuan->kode_temuan_id;
            $kodeStr = $temuan->kodeTemuan?->kode;
            $descTemuan = $temuan->kodeTemuan?->deskripsi ?? $temuan->judul ?? 'Temuan Pemeriksaan';

            // Temukan kecocokan pada riwayat LHP sebelumnya
            $matchedHistory = $historicalTemuans->filter(function ($ht) use ($kodeId, $kodeStr) {
                return ($kodeId && $ht->kode_temuan_id === $kodeId)
                    || ($kodeStr && $ht->kodeTemuan?->kode === $kodeStr);
            });

            $isTemuanBerulang = $matchedHistory->isNotEmpty();
            $nomorLhpLampau = [];
            $periodeLampau = [];

            if ($isTemuanBerulang) {
                foreach ($matchedHistory as $mh) {
                    if ($mh->lhp) {
                        $nomorLhpLampau[] = $mh->lhp->nomor_lhp;
                        $tgl = $mh->lhp->tanggal_lhp ? \Carbon\Carbon::parse($mh->lhp->tanggal_lhp)->translatedFormat('F Y') : 'Periode Lalu';
                        $periodeLampau[] = $tgl;
                    }
                }
                $nomorLhpLampau = array_values(array_unique(array_filter($nomorLhpLampau)));
                $periodeLampau = array_values(array_unique(array_filter($periodeLampau)));
            }

            $nilaiTemuan = ($temuan->nilai_kerugian_negara ?? 0)
                         + ($temuan->nilai_kerugian_daerah ?? 0)
                         + ($temuan->nilai_kerugian_desa ?? 0)
                         + ($temuan->nilai_kerugian_bos_blud ?? 0);

            if ($isTemuanBerulang && $nilaiTemuan > 0) {
                $hasFinancialRepeat = true;
            }

            // Analisis Rekomendasi di bawah temuan ini
            $rekomMap = [];
            foreach ($temuan->recommendations as $rekom) {
                $rekomKodeId = $rekom->kode_rekomendasi_id;
                $rekomKodeStr = $rekom->kodeRekomendasi?->kode;
                $descRekom = $rekom->kodeRekomendasi?->deskripsi ?? $rekom->uraian_rekom ?? '-';

                // Cek apakah rekomendasi serupa pernah diterbitkan di LHP lampau
                $matchedRekomHistory = $matchedHistory->flatMap->recommendations->filter(function ($hr) use ($rekomKodeId, $rekomKodeStr) {
                    return ($rekomKodeId && $hr->kode_rekomendasi_id === $rekomKodeId)
                        || ($rekomKodeStr && $hr->kodeRekomendasi?->kode === $rekomKodeStr);
                });

                $isRekomBerulang = $matchedRekomHistory->isNotEmpty();
                if ($isRekomBerulang) {
                    $totalRekomUlang++;
                }

                $kesimpulanRekomOpd = $isRekomBerulang
                    ? "KESIMPULAN UNTUK OPD: Rekomendasi [{$rekomKodeStr}] ini merupakan REKOMENDASI BERULANG dari pemeriksaan sebelumnya. Hal ini membuktikan bahwa instruksi perbaikan yang telah diterbitkan pada periode lampau belum diimplementasikan secara membudaya di internal OPD, dan perbaikan hanya dilakukan sebatas formalitas saat diperiksa."
                    : "KESIMPULAN UNTUK OPD: Rekomendasi ini merupakan kasus baru dan telah ditindaklanjuti 100%. OPD wajib memastikan bahwa prosedur tindak lanjut yang telah disetujui dijadikan pedoman kerja tetap agar tidak menjadi rekomendasi berulang di masa datang.";

                $rekomMap[$rekom->id] = [
                    'is_berulang'          => $isRekomBerulang,
                    'kode'                 => $rekomKodeStr ?? '-',
                    'deskripsi'            => $descRekom,
                    'kesimpulan_rekom_opd' => $kesimpulanRekomOpd,
                ];
            }

            // Kesimpulan khusus untuk temuan ini bagi OPD
            $periodeText = !empty($periodeLampau) ? implode(', ', $periodeLampau) : 'tahun/periode lalu';
            $kesimpulanTemuanOpd = $isTemuanBerulang
                ? "CATATAN EVALUASI KHUSUS UNTUK OPD: Temuan mengenai [{$descTemuan}] terbukti BERULANG dari pemeriksaan terdahulu ({$periodeText}). Kesimpulan Inspektorat: OPD {$opdName} belum memiliki komitmen perbaikan sistemik dan masih membiarkan pola kelalaian yang sama terulang kembali. Meskipun secara finansial/administrasi tindak lanjut saat ini telah lunas 100%, hal ini dinilai hanya sebagai tindakan penambalan (kuratif) tanpa adanya pencegahan permanen (preventif)."
                : "CATATAN EVALUASI UNTUK OPD: Temuan ini merupakan kasus pertama dan telah dituntaskan 100%. Pimpinan OPD diharapkan mengoptimalkan pengawasan melekat agar kelemahan ini tidak terulang pada tahun berikutnya.";

            $instruksiKhususOpd = $isTemuanBerulang
                ? "INSTRUKSI TEGAS UNTUK KEPALA OPD: Wajib menerbitkan Instruksi Tertulis Penegakan Disiplin kepada PPK/PPTK/Bendahara terkait, merombak alur verifikasi dokumen, dan melaporkan bukti pembenahan sistemik kepada Inspektorat Daerah maksimal 30 hari sejak laporan ini diterbitkan."
                : "INSTRUKSI UNTUK KEPALA OPD: Mempertahankan tertib verifikasi berjenjang sebelum realisasi anggaran.";

            $temuansMap[$temuan->id] = [
                'is_berulang'           => $isTemuanBerulang,
                'kode'                  => $kodeStr ?? '-',
                'deskripsi'             => $descTemuan,
                'lhp_lampau'            => $nomorLhpLampau,
                'periode_lampau'        => $periodeLampau,
                'frekuensi_lampau'      => $matchedHistory->count(),
                'nilai_terkait'         => $nilaiTemuan,
                'kesimpulan_temuan_opd' => $kesimpulanTemuanOpd,
                'instruksi_khusus_opd'  => $instruksiKhususOpd,
                'rekom_map'             => $rekomMap,
            ];

            if ($isTemuanBerulang) {
                $daftarBerulang[] = [
                    'temuan_id'        => $temuan->id,
                    'judul'            => $temuan->judul ?? 'Temuan Pemeriksaan',
                    'kode'             => $kodeStr ?? '-',
                    'deskripsi_kode'   => $descTemuan,
                    'kelompok'         => $temuan->kodeTemuan?->kelompok ?? '-',
                    'frekuensi_lampau' => $matchedHistory->count(),
                    'lhp_lampau'       => $nomorLhpLampau,
                    'nilai_terkait'    => $nilaiTemuan,
                ];
            }
        }

        $totalBerulang = count($daftarBerulang);
        $hasBerulang = $totalBerulang > 0;

        // Klasifikasi Rentang Keparahan Pengulangan (Severity Tiers)
        if ($totalBerulang === 0) {
            $rentangKeparahan = 'nol';
            $tingkatRisiko = 'Rendah';
            $badgeColor = 'emerald';
            $labelKeparahan = 'Kepatuhan Bersih & Konsisten';
        } elseif ($totalBerulang <= 2) {
            $rentangKeparahan = '1_sampai_2';
            $tingkatRisiko = $hasFinancialRepeat ? 'Tinggi' : 'Sedang';
            $badgeColor = $hasFinancialRepeat ? 'rose' : 'amber';
            $labelKeparahan = 'Pengulangan Ringan / Peringatan Prosedural (1 - 2 Temuan Berulang)';
        } elseif ($totalBerulang <= 5) {
            $rentangKeparahan = '3_sampai_5';
            $tingkatRisiko = 'Tinggi (Kelemahan Sistemik)';
            $badgeColor = 'rose';
            $labelKeparahan = 'Kelemahan Sistemik & Kegagalan Pengendalian (3 - 5 Temuan Berulang)';
        } elseif ($totalBerulang <= 10) {
            $rentangKeparahan = '6_sampai_10';
            $tingkatRisiko = 'Kritis (Maladministrasi Akut)';
            $badgeColor = 'red';
            $labelKeparahan = 'Maladministrasi Kronis & Pembiaran Pelanggaran (6 - 10 Temuan Berulang)';
        } else {
            $rentangKeparahan = 'diatas_10';
            $tingkatRisiko = 'Darurat Akuntabilitas';
            $badgeColor = 'red';
            $labelKeparahan = 'Darurat Tata Kelola & Kolaps Pengendalian (> 10 Temuan Berulang)';
        }

        // Catatan Ringkas & Kesimpulan Khusus untuk OPD Berdasarkan Rentang Keparahan
        if ($rentangKeparahan === '3_sampai_5') {
            $catatanRingkas = "PERINGATAN TINGKAT TINGGI: Terdeteksi {$totalBerulang} temuan dan {$totalRekomUlang} rekomendasi berulang dari tahun/bulan lalu pada OPD ini. Menunjukkan KELEMAHAN SISTEMIK (Systemic Failure) pada sistem pengendalian intern.";
            $kesimpulanOpd = [
                'status'            => 'Peringatan Tingkat Tinggi: Kelemahan Sistemik (3 - 5 Temuan Berulang)',
                'tipe'              => 'berulang_signifikan',
                'rentang'           => '3_sampai_5',
                'label_keparahan'   => $labelKeparahan,
                'ringkasan'         => "Objek Pemeriksaan ({$opdName}) tercatat mengulang {$totalBerulang} temuan dan {$totalRekomUlang} rekomendasi dari pemeriksaan tahun lalu / bulan lalu. Angka 3 s.d. 5 temuan berulang bukan lagi kelalaian biasa (*human error*), melainkan bukti kegagalan sistematis pengendalian intern (SPIP). Tindak lanjut 100% saat ini baru bersifat kuratif di atas kertas tanpa adanya kemauan perbaikan tata kelola yang sungguh-sungguh.",
                'penegasan'         => "INSTRUKSI DAN KONSEKUENSI BAGI KEPALA OPD:\n" .
                                       "1. Penerbitan Surat Peringatan Tertulis Keras dari Bupati/Sekretaris Daerah kepada Kepala OPD ({$opdName}).\n" .
                                       "2. Penurunan nilai maturitas SPIP dan pemotongan bobot SAKIP OPD minimal 5–10 poin.\n" .
                                       "3. Rekomendasi pemotongan TPP sebesar 10%–20% bagi PPK/PPTK/Bendahara terkait selama 3 bulan.\n" .
                                       "4. Penetapan status 'Atensi Pengawasan Khusus' (Special Watchlist) pada PKPT tahun berikutnya dengan audit sampling diperluas menjadi 100%.",
            ];
        } elseif ($rentangKeparahan === '6_sampai_10') {
            $catatanRingkas = "BAHAYA KRITIS: Terdeteksi {$totalBerulang} temuan berulang! Merupakan MALADMINISTRASI KRONIS dan pembiaran kelalaian (Gross Negligence) terhadap instruksi pengawasan APIP.";
            $kesimpulanOpd = [
                'status'            => 'Kondisi Kritis: Maladministrasi Akut & Pembiaran Kronis (6 - 10 Temuan Berulang)',
                'tipe'              => 'berulang_kritis',
                'rentang'           => '6_sampai_10',
                'label_keparahan'   => $labelKeparahan,
                'ringkasan'         => "Objek Pemeriksaan ({$opdName}) berada dalam KONDISI KRITIS dengan catatan {$totalBerulang} temuan dan {$totalRekomUlang} rekomendasi berulang dari pemeriksaan sebelumnya. Pengulangan 6 s.d. 10 temuan merupakan bukti pembiaran kelalaian berat (*gross negligence*) dan pembangkangan terhadap rekomendasi APIP. Tindak lanjut 100% yang dilaporkan terbukti hanya formalitas manipulatif tanpa adanya niat perbaikan nyata.",
                'penegasan'         => "REKOMENDASI TINDAKAN TEGAS BUPATI & APARAT PENEGAK HUKUM:\n" .
                                       "1. Rekomendasi Pencopotan/Bebas Tugas: Inspektur Daerah menerbitkan nota rekomendasi kepada Bupati Rembang untuk membebaskan dari jabatan bagi PPK, PPTK, dan Bendahara Pengeluaran serta evaluasi kelayakan jabatan Kepala OPD.\n" .
                                       "2. Pelaksanaan Pemeriksaan Khusus (Audit Investigasi Forensik APIP) guna mendalami indikasi kesengajaan, pemufakatan jahat, atau penyalahgunaan wewenang.\n" .
                                       "3. Penangguhan / pembekuan usulan penambahan pagu belanja operasional tertentu pada APBD Perubahan / RAPBD tahun berikutnya.\n" .
                                       "4. Penjatuhan Hukuman Disiplin Berat ASN (PP No. 94 Tahun 2021 Pasal 8 & 14): Penurunan pangkat setingkat lebih rendah selama 3 tahun atau pembebasan dari jabatan.\n" .
                                       "5. Pelimpahan Perkara: Jika terdapat kerugian negara/daerah yang berulang, langsung diproses ke Sidang Majelis TP-TGR dan diteruskan ke Kejaksaan Negeri / Kepolisian Resor Rembang.",
            ];
        } elseif ($rentangKeparahan === 'diatas_10') {
            $catatanRingkas = "DARURAT TATA KELOLA: Terdeteksi lebih dari 10 temuan berulang! Runtuhnya seluruh fungsi kepatuhan dan integritas pengelolaan keuangan OPD.";
            $kesimpulanOpd = [
                'status'            => 'Darurat Tata Kelola: Intervensi Melekat APIP (> 10 Temuan Berulang)',
                'tipe'              => 'berulang_darurat',
                'rentang'           => 'diatas_10',
                'label_keparahan'   => $labelKeparahan,
                'ringkasan'         => "Objek Pemeriksaan ({$opdName}) mengalami kolaps tata kelola akuntabilitas dengan lebih dari 10 temuan berulang. Seluruh fungsi verifikasi internal dan integritas tata kelola telah lumpuh.",
                'penegasan'         => "REKOMENDASI DARURAT: Pembekuan kewenangan otorisasi belanja mandiri, penempatan Tim Asistensi Melekat Inspektorat Daerah secara penuh, serta pergantian total seluruh jajaran pengelola keuangan pada OPD terkait.",
            ];
        } elseif ($hasBerulang) {
            $catatanRingkas = "PERINGATAN PROSEDURAL: Terdeteksi {$totalBerulang} temuan dan {$totalRekomUlang} rekomendasi berulang dari tahun lalu / periode sebelumnya pada OPD ini.";
            $kesimpulanOpd = [
                'status'            => 'Peringatan Prosedural: Rawan Pengulangan (1 - 2 Temuan Berulang)',
                'tipe'              => 'berulang',
                'rentang'           => '1_sampai_2',
                'label_keparahan'   => $labelKeparahan,
                'ringkasan'         => "Berdasarkan penelusuran riwayat pengawasan, Objek Pemeriksaan ({$opdName}) teridentifikasi memiliki {$totalBerulang} temuan dan {$totalRekomUlang} rekomendasi yang sama dari pemeriksaan tahun/bulan lalu. Meskipun secara administratif tindak lanjut saat ini telah mencapai 100%, kepatuhan ini masih rentan terulang kembali jika alur verifikasi tidak dibenahi.",
                'penegasan'         => "Pimpinan OPD diwajibkan melakukan pembenahan alur verifikasi dokumen dan pengawasan melekat secara berkala. Apabila pada audit berikutnya kembali terjadi pengulangan, status OPD akan dinaikkan ke tingkat Kelemahan Sistemik dengan sanksi penalti AKIP dan TPP.",
            ];
        } else {
            $catatanRingkas = "Seluruh temuan pada LHP ini merupakan kasus baru (tidak teridentifikasi pengulangan kode temuan lampau).";
            $kesimpulanOpd = [
                'status'            => 'Kepatuhan Sehat & Konsisten (Non-Recurrent)',
                'tipe'              => 'sehat',
                'rentang'           => 'nol',
                'label_keparahan'   => $labelKeparahan,
                'ringkasan'         => "Objek Pemeriksaan ({$opdName}) menyelesaikan seluruh tindak lanjut hingga 100% tanpa adanya catatan pengulangan temuan atau rekomendasi dari periode sebelumnya.",
                'penegasan'         => "Kepala OPD diapresiasi atas komitmen perbaikan dan diminta untuk menjaga integritas serta disiplin pengawasan internal secara berkesinambungan.",
            ];
        }

        return [
            'has_berulang'      => $hasBerulang,
            'total_berulang'    => $totalBerulang,
            'total_rekom_ulang' => $totalRekomUlang,
            'tingkat_risiko'    => $tingkatRisiko,
            'rentang_keparahan' => $rentangKeparahan,
            'label_keparahan'   => $labelKeparahan,
            'badge_color'       => $badgeColor,
            'daftar_berulang'   => $daftarBerulang,
            'temuans_map'       => $temuansMap,
            'catatan_ringkas'   => $catatanRingkas,
            'kesimpulan_opd'    => $kesimpulanOpd,
        ];
    }

    /**
     * Menghasilkan masukan manajerial dan rencana aksi spesifik berdasarkan master data kode temuan.
     */
    public function getMasukanManajerial(Lhp $lhp, array $analisisBerulang): array
    {
        $masukanList = [];

        if ($lhp->is_nihil || $lhp->temuans->isEmpty()) {
            return [
                [
                    'kategori' => 'Pertahankan Kualitas Tata Kelola (Good Governance)',
                    'poin'     => 'Mempertahankan standar kepatuhan administrasi dan internal control yang sudah berjalan baik.',
                    'tindakan' => 'Lakukan evaluasi berkala dan rotasi pengelola keuangan secara sehat untuk mencegah potensi kejenuhan operasional.',
                ],
            ];
        }

        // Periksa keberadaan kelompok temuan
        $kelompokCodes = $lhp->temuans->pluck('kodeTemuan.kode')->filter()->map(function ($k) {
            return substr($k, 0, 4); // misal "1.01", "1.02", "1.03", "1.04", "2.01", dst
        })->unique()->all();

        // 1. Kerugian Keuangan / Rekanan Wanprestasi / Volume Kurang (Kode 1.01)
        if (in_array('1.01', $kelompokCodes)) {
            $masukanList[] = [
                'kategori' => 'Pengadaan Barang/Jasa & Serah Terima Hasil Pekerjaan (Kode 1.01)',
                'poin'     => 'Kelemahan dalam pengendalian mutu pekerjaan rekanan dan keterlambatan realisasi fisik.',
                'tindakan' => 'Pejabat Pembuat Komitmen (PPK) dan Pejabat Pelaksana Teknis Kegiatan (PPTK) diwajibkan melakukan uji petik/opname lapangan fisik bersama Konsultan Pengawas sebelum menandatangani Berita Acara Serah Terima (BAST). Rekanan yang terlambat atau wanprestasi wajib dikenakan denda keterlambatan maksimal dan dipertimbangkan untuk dimasukkan dalam Daftar Hitam (Blacklist) penyedia.',
            ];
        }

        // 2. Potensi Kerugian / Piutang Daerah (Kode 1.02)
        if (in_array('1.02', $kelompokCodes)) {
            $masukanList[] = [
                'kategori' => 'Penyelamatan Aset & Piutang Daerah (Kode 1.02)',
                'poin'     => 'Adanya potensi kebocoran penerimaan atau pengeluaran kas yang berisiko menjadi kerugian definitif.',
                'tindakan' => 'Membentuk Tim Rekonsiliasi Internal OPD untuk menagih kewajiban pihak ketiga secara berkala serta mengamankan jaminan pelaksanaan/pemeliharaan perbankan yang masih berlaku.',
            ];
        }

        // 3. Kekurangan Penerimaan / Pajak (Kode 1.03)
        if (in_array('1.03', $kelompokCodes)) {
            $masukanList[] = [
                'kategori' => 'Kewajiban Perpajakan & Retribusi (Kode 1.03)',
                'poin'     => 'Keterlambatan pemotongan atau penyetoran Pajak Pusat (PPh/PPN) dan Pajak Daerah.',
                'tindakan' => 'Bendahara Pengeluaran wajib menerapkan prinsip "Potong Langsung Setor" dengan memvalidasi Bukti Penerimaan Negara (BPN/NTPN) sebagai lampiran mutlak pertanggungjawaban SP2D sebelum pencairan termin berikutnya.',
            ];
        }

        // 4. Administrasi & SPJ Belum Lengkap (Kode 1.04)
        if (in_array('1.04', $kelompokCodes)) {
            $masukanList[] = [
                'kategori' => 'Ketertiban Administrasi & Penatausahaan SPJ (Kode 1.04)',
                'poin'     => 'Kelemahan kelengkapan bukti pengeluaran riil, nota dinas, dan pengarsipan administrasi.',
                'tindakan' => 'Menetapkan standar operasional prosedur (SOP) verifikasi SPJ berjenjang oleh PPK/PPK-SKPD maksimal tanggal 10 bulan berikutnya. Melarang pembayaran beban belanja yang tidak disertai bukti riil dan sah sesuai perundang-undangan.',
            ];
        }

        // 5. Kelemahan Sistem Pengendalian Intern (Kode 2.xx)
        $hasSpi = collect($kelompokCodes)->contains(fn($k) => str_starts_with($k, '2.'));
        if ($hasSpi || empty($masukanList)) {
            $masukanList[] = [
                'kategori' => 'Penguatan Sistem Pengendalian Intern Pemerintah / SPIP (Kode 2.xx)',
                'poin'     => 'Kelemahan supervisi atasan langsung dan pengawasan berjenjang dalam siklus belanja anggaran.',
                'tindakan' => 'Kepala OPD wajib mengaktifkan fungsi Pengawasan Melekat (Waskat) secara berkala dan menyusun Daftar Mitigasi Risiko Pengendalian Intern untuk menutup celah human error maupun kelalaian manajerial.',
            ];
        }

        // Tambahan penegasan jika terdeteksi temuan berulang
        if ($analisisBerulang['has_berulang']) {
            $masukanList[] = [
                'kategori' => 'Peringatan Khusus: Pengentasan Temuan Berulang',
                'poin'     => "Unit kerja ini tercatat memiliki {$analisisBerulang['total_berulang']} temuan berulang dari LHP sebelumnya.",
                'tindakan' => 'Pimpinan OPD wajib menerbitkan Instruksi Tertulis kepada seluruh jajaran pejabat pengelola keuangan agar kelemahan yang sama tidak terulang pada tahun anggaran berikutnya, serta menyampaikan evaluasi sistemik tertulis kepada Inspektorat Daerah dalam waktu 30 hari.',
            ];
        }

        return $masukanList;
    }

    /**
     * Mengambil protokol eskalasi sanksi dan konsekuensi hukum jika OPD melakukan temuan berulang.
     */
    public function getProtokolEskalasiSanksi(): array
    {
        return [
            [
                'tingkat'      => 'Tingkat I',
                'label'        => 'Peringatan & Evaluasi Sistemik',
                'syarat'       => 'Pengulangan temuan administratif atau SPI pada objek yang sama.',
                'dasar_hukum'  => 'PP No. 60 Tahun 2008 tentang SPIP & Permendagri Pengawasan Pemda',
                'tindakan'     => 'Penerbitan Surat Peringatan Pengawasan oleh Inspektur Daerah; OPD diwajibkan merevisi SOP dan dimasukkan ke dalam daftar High-Priority Monitoring pada audit PKPT tahun berikutnya.',
            ],
            [
                'tingkat'      => 'Tingkat II',
                'label'        => 'Penalti Kinerja & Peninjauan TPP',
                'syarat'       => 'Pengulangan temuan lebih dari 1 (satu) kali pemeriksaan atau pembiaran tindak lanjut.',
                'dasar_hukum'  => 'Perbup Rembang tentang Penilaian Kinerja & Tambahan Penghasilan Pegawai (TPP)',
                'tindakan'     => 'Pengurangan nilai Indikator Kinerja Pelaksanaan Anggaran (IKPA) dan AKIP Perangkat Daerah, serta rekomendasi pengurangan/penahanan alokasi TPP pejabat pengelola anggaran terkait.',
            ],
            [
                'tingkat'      => 'Tingkat III',
                'label'        => 'Sanksi Disiplin Pegawai Negeri Sipil',
                'syarat'       => 'Kekurangan volume berulang, kelalaian berulang pemotongan pajak, atau kelalaian fatal pejabat teknis.',
                'dasar_hukum'  => 'PP No. 94 Tahun 2021 tentang Disiplin Pegawai Negeri Sipil (Pasal 3 & 4)',
                'tindakan'     => 'Inspektorat merekomendasikan kepada Pejabat Pembina Kepegawaian (Bupati/Sekretaris Daerah) untuk menjatuhkan Hukuman Disiplin Sedang/Berat (penundaan kenaikan pangkat, pembebasan dari jabatan) kepada PPK/PPTK/Bendahara terkait.',
            ],
            [
                'tingkat'      => 'Tingkat IV',
                'label'        => 'Majelis TP-TGR & Pelimpahan ke APH',
                'syarat'       => 'Pengulangan temuan yang menimbulkan kerugian negara/daerah atau indikasi kesengajaan/pembiaran.',
                'dasar_hukum'  => 'UU No. 1 Tahun 2004 tentang Perbendaharaan Negara & UU No. 31/1999 jo UU No. 20/2001 (Tipikor)',
                'tindakan'     => 'Tidak diberikan toleransi administratif; langsung diajukan ke Sidang Majelis Pertimbangan Tuntutan Perbendaharaan dan Tuntutan Ganti Rugi (TP-TGR) Kabupaten Rembang serta pelimpahan berkas pengawasan kepada Aparat Penegak Hukum (Kejaksaan/Kepolisian).',
            ],
        ];
    }
}
