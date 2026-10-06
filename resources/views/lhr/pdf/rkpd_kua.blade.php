{{--
    LHR Reviu Rankir RKPD — template terstruktur (data-driven)

    Variabel dari controller (sama seperti sebelumnya):
      $lhr, $unit, $assignment, $team, $signer, $pengujians, $attachments, $isPdf
    Variabel opsional baru:
      $pdfEngine   : 'dompdf' (default) | 'chrome'  → menentukan cara footer & nomor halaman
      $konsistensi : array baris tabel RPJMD vs RKPD (jika null dipakai data default di bawah)
      $lhr->link_lampiran, $lhr->tanggal_mulai_reviu : menggantikan nilai hardcode
--}}
@php
    /* ───────────── 1. VARIABEL DASAR ───────────── */
    $isPdf  = !empty($isPdf);
    $engine = $isPdf ? ($pdfEngine ?? 'dompdf') : 'browser';
    $team   = $team ?? [];

    $fmtTgl = fn($d, $fallback) => $d ? \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D MMMM Y') : $fallback;

    $tahun             = $lhr->tahun_anggaran ?: 2027;
    $tahunTerbit       = $lhr->tanggal_lhr ? \Carbon\Carbon::parse($lhr->tanggal_lhr)->format('Y') : '2026';
    $nomorLhr          = $lhr->nomor_lhr ?: '700/026/REVIU/INSP/2026';
    $tanggalLhr        = $fmtTgl($lhr->tanggal_lhr, '11 Mei 2026');
    $namaUnit          = $unit->nama_unit ?? 'BADAN PERENCANAAN PEMBANGUNAN DAERAH KABUPATEN REMBANG';
    $nomorSpt          = $assignment->nomor_spt ?? '000.1.2.3/020/INSP/2026';
    $tanggalSpt        = $fmtTgl($assignment->tanggal_mulai ?? null, '29 April 2026');
    $tanggalMulaiReviu = $fmtTgl($lhr->tanggal_mulai_reviu ?? null, '30 April 2026');
    $nomorPermohonan   = $lhr->permohonan_nomor ?: '000.7/250/2026';
    $tanggalPermohonan = $fmtTgl($lhr->permohonan_tanggal, '28 April 2026');
    $footerText        = "Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {$tahun}";
    $showBackCover     = true;

    $logoPath = public_path('images/logos/rembang.png');
    $logoSrc  = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : 'https://upload.wikimedia.org/wikipedia/commons/3/30/Lambang_Kabupaten_Rembang.png';

    $link = $lhr->link_lampiran ?? 'https://bit.ly/Lampiran-CHRRankirRKPD-2027';
    $lnk  = '<a href="' . e($link) . '">' . e($link) . '</a>';

    /* ───────────── 2. TIM (fallback → pindahkan ke config/lhr.php bila perlu) ───────────── */
    $defaultTeam = [
        'penanggung_jawab' => ['nama' => 'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.', 'nip' => '19741128 199903 2 003', 'jabatan' => 'Inspektur Daerah'],
        'pengendali_teknis' => ['nama' => 'SRI SURYANDARI, S.Sos.', 'nip' => '19690827 199603 2 005', 'jabatan' => 'Inspektur Pembantu I'],
        'ketua_tim' => ['nama' => 'SITI AMINAH, S.E.', 'nip' => '19920714 201902 2 002', 'jabatan' => 'Auditor Ahli Pertama'],
        'anggota' => [
            ['nama' => 'PARIJAN, S.E., M.M.', 'nip' => '19680314 199103 1 014', 'jabatan' => 'Pengawas Pemerintahan Urusan Pemerintah Daerah Ahli Madya'],
            ['nama' => 'AGUNG TRI PRASTIYO, S.E.', 'nip' => '19820821 200501 1 006', 'jabatan' => 'Auditor Ahli Muda'],
            ['nama' => 'IKA PERTIWI, S.H.', 'nip' => '19860908 202203 2 002', 'jabatan' => 'Auditor Ahli Pertama'],
            ['nama' => 'TAUFIQUR RACHMAN, S.E.', 'nip' => '19940326 202203 1 016', 'jabatan' => 'Auditor Ahli Pertama'],
            ['nama' => 'SIHONO, S.I.P.', 'nip' => '19960703 202203 1 007', 'jabatan' => 'Pengawas Pemerintahan Urusan Pemerintah Daerah Ahli Pertama'],
            ['nama' => 'WAHYU ANJAR, S.Tr.I.P', 'nip' => '20000727 202409 2 001', 'jabatan' => 'Pengawas Pemerintahan Urusan Pemerintah Daerah Ahli Pertama'],
            ['nama' => 'DEWI ZAHRO WAHYUNINGTYAS, S.T.', 'nip' => '20010524 202505 2 004', 'jabatan' => 'Calon Auditor'],
        ],
    ];
    $pick = function ($key, $field) use ($team, $defaultTeam) {
        $v = data_get($team, "$key.$field");
        return ($v && $v !== '-') ? $v : data_get($defaultTeam, "$key.$field");
    };
    $single = fn($key) => ['nama' => $pick($key, 'nama'), 'nip' => $pick($key, 'nip'), 'jabatan' => $pick($key, 'jabatan')];
    $anggota = !empty($team['anggota']) ? $team['anggota'] : $defaultTeam['anggota'];
    $blokTim = [
        'PENANGGUNGJAWAB'   => [$single('penanggung_jawab')],
        'PENGENDALI TEKNIS' => [$single('pengendali_teknis')],
        'KETUA TIM'         => [$single('ketua_tim')],
        'ANGGOTA TIM'       => $anggota,
    ];

    /* ───────────── 3. TEKS STATIS ───────────── */
    $dasarHukum = [
        'Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang Rencana Pembangunan Jangka Panjang Daerah dan Rencana Pembangunan Jangka Menengah Daerah, Serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah, dan Rencana Kerja Pemerintah Daerah;',
        'Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 10 Tahun 2018 tentang Reviu atas Dokumen Perencanaan Pembangunan dan Anggaran Daerah Tahunan;',
        'Peraturan Menteri Dalam Negeri Nomor 14 Tahun 2025 tentang Pedoman Penyusunan Anggaran Pendapatan dan Belanja Daerah Tahun Anggaran 2026;',
        'Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 3 Tahun 2026 tentang Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah;',
        'Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Perubahan Kedua atas Peraturan Daerah Kabupaten Rembang Nomor 5 Tahun 2016 tentang Pembentukan dan Susunan Perangkat daerah Kabupaten Rembang;',
        'Peraturan Bupati Rembang Nomor 54 Tahun 2021 tentang Kedudukan Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Inspektorat Kabupaten Rembang;',
        'Peraturan Bupati Rembang Nomor 4 Tahun 2026 tentang Perubahan atas Peraturan Bupati Rembang Nomor 110 Tahun 2025 tentang Penjabaran APBD Tahun Anggaran 2026;',
        "Surat dari Inspektorat Jenderal Kementerian Dalam Negeri Republik Indonesia Nomor: 700.1.1.1/1642/IJ perihal Reviu Rancangan Akhir Rencana Kerja Pemerintah Daerah Tahun Anggaran {$tahun};",
        'Keputusan Bupati Rembang Nomor: 100.3.3.2/0282/2026 tentang Program Kerja Pengawasan Tahunan Berbasis Risiko Inspektorat Daerah Kabupaten Rembang Tahun 2026;',
        "Surat dari Kepala Badan Perencanaan Pembangunan Daerah Kabupaten Rembang Nomor: {$nomorPermohonan} tanggal {$tanggalPermohonan} tentang Permohonan Reviu Dokumen Rancangan Akhir RKPD Kabupaten Rembang Tahun {$tahun};",
        "Surat Perintah Tugas Inspektur Daerah Kabupaten Rembang Nomor: {$nomorSpt} tanggal {$tanggalSpt} dengan susunan Tim:",
    ];

    $ruangLingkupSubstansi = [
        'Pengujian atas Kelengkapan Dokumen Pendukung Rancangan Akhir RKPD;',
        'Pengujian atas Analisis Isi BAB Rancangan Akhir RKPD;',
        'Pengujian atas Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan dalam RKPD dan RPJMD;',
        'Kesesuaian Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rencana Pembangunan Jangka Menengah Daerah (RPJMD) dengan Rancangan Akhir RKPD;',
        'Kewajaran proyeksi pendanaan pada rancangan akhir RKPD;',
        [
            'Kesesuaian Informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional',
            [
                'Kesesuaian urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil rapat koordinasi teknis pembangunan (Rakortekbang) dengan urusan pada rancangan akhir RKPD;',
                'Kesesuaian penetapan target Indikator Makro Pembangunan pada rancangan akhir RKPD berdasarkan Rencana Kerja Pemerintah (RKP);',
                'Kesesuaian dan ketepatan dukungan pelaksanaan ProSN pada rancangan akhir RKPD;',
                'Memastikan Program, Kegiatan dan Sub Kegiatan Standar Pelayanan Minimal (SPM) tersedia pada rancangan akhir RKPD;',
                'Memastikan kesesuaian dukungan Sub Kegiatan Tematik Pembangunan pada rancangan akhir RKPD;',
            ],
        ],
        'Kesesuaian Informasi pada rancangan akhir RKPD dengan tata cara dan kaidah Perencanaan Pembangunan Daerah lainnya (kesesuaian pengusulan pokok-pokok pikiran Dewan Perwakilan Rakyat Daerah (DPRD) sesuai dengan ketentuan peraturan perundang-undangan);',
        'Pengujian atas Konsistensi Pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran dalam Rancangan Akhir RKPD dan RPJMD.',
    ];

    $gambaranUmum = [
        'Pemerintah Kabupaten Rembang telah menyusun Peraturan Daerah Nomor 3 Tahun 2024 tentang Rencana Pembangunan Jangka Panjang Daerah Kabupaten Rembang Tahun 2025-2045;',
        'Pemerintah Kabupaten Rembang telah menyusun Peraturan Daerah Nomor 7 tahun 2025 tentang Rencana Pembangunan Jangka Menengah Daerah Kabupaten Rembang tahun 2025-2029 sebagai acuan dalam menentukan arah kebijakan pembangunan setiap tahunnya dengan membuat Rancangan Rencana Kerja Pemerintah Daerah Kabupaten Rembang;',
        "Sebagai penjabaran Rencana Pembangunan Jangka Menengah Daerah Kabupaten Rembang 2025-2029, kedudukan RKPD Kabupaten Rembang Tahun {$tahun} dalam pelaksanaan pembangunan di Kabupaten Rembang adalah menjembatani antara perencanaan strategis jangka menengah dengan perencanaan dan penganggaran tahunan yang memuat rancangan kerangka ekonomi daerah, prioritas pembangunan daerah, rencana kerja dan pendanaan yang disusun berdasarkan pada orientasi proses dengan pendekatan teknokratik, partisipatif, politis dan top-down, bottom-up, serta berdasarkan pada orientasi substansi dengan pendekatan secara holistik, integratif, tematik dan spasial, yang selanjutnya sebagai pedoman dalam penyusunan Kebijakan Umum Anggaran serta penentuan Prioritas Plafon Anggaran Sementara yang menjadi dasar penyusunan Rancangan Anggaran Pendapatan dan Belanja Daerah (RAPBD) Tahun {$tahun};",
        "Inspektorat Daerah Kabupaten Rembang berdasarkan kewenangannya melakukan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {$tahun}.",
    ];

    /* ───────────── 4. TABEL PERBANDINGAN FORMAT (Permendagri 86/2017 vs Rankir) ───────────── */
    // [no, permendagri, rankir, isBab]
    $formatRows = [
        ['1.', 'BAB I PENDAHULUAN', 'BAB I PENDAHULUAN', 1],
        ['1.1.', 'Latar Belakang', 'Latar Belakang'],
        ['1.2.', 'Dasar Hukum Penyusunan', 'Dasar Hukum'],
        ['1.3.', 'Hubungan Antar Dokumen', 'Hubungan Antara Dokumen'],
        ['1.4.', 'Maksud dan Tujuan', 'Maksud dan Tujuan'],
        ['1.5.', 'Sistematika Dokumen RKPD', "Sistematika Dokumen RKPD Tahun {$tahun}"],
        ['2.', 'BAB II GAMBARAN UMUM KONDISI DAERAH', 'BAB II GAMBARAN UMUM KONDISI DAERAH', 1],
        ['2.1.', 'Kondisi Umum Daerah', 'Kondisi Umum Daerah'],
        ['2.1.1.', 'Aspek Geografi dan Demografi', 'Aspek Geografi dan Demografi'],
        ['2.1.2.', 'Aspek Kesejahteraan Masyarakat', 'Aspek Kesejahteraan Masyarakat'],
        ['2.1.3.', 'Aspek Pelayanan Umum', 'Aspek Daya Saing Daerah'],
        ['2.1.4.', 'Aspek Daya Saing Daerah', 'Aspek Pelayanan Umum'],
        ['2.2.', 'Evaluasi Pelaksanaan Program dan Kegiatan RKPD sampai Tahun Berjalan dan Realisasi RPJMD', 'Evaluasi Pelaksanaan Kinerja RKPD sampai Tahun 2025 dan Realisasi RPJMD'],
        ['2.2.1', '-', 'Hasil Evaluasi Indikator Makro Daerah Tahun 2021-2025'],
        ['2.2.2', '-', 'Hasil Evaluasi Capaian Kinerja Tahun 2025'],
        ['2.3.', 'Permasalahan Pembangunan Daerah', 'Permasalahan Pembangunan Daerah'],
        ['2.3.1.', 'Permasalahan daerah yang berhubungan dengan prioritas dan sasaran pembangunan daerah', 'Permasalahan daerah yang berhubungan dengan prioritas dan sasaran pembangunan daerah'],
        ['2.3.2.', 'Identifikasi Permasalahan penyelenggaraan urusan pemerintah daerah', 'Identifikasi Permasalahan penyelenggaraan urusan pemerintah daerah'],
        ['3.', 'BAB III KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH', 'BAB III KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH', 1],
        ['3.1.', 'Arah Kebijakan Ekonomi Daerah', 'Arah Kebijakan Ekonomi Daerah'],
        ['3.2.', 'Arah Kebijakan Keuangan Daerah', 'Arah Kebijakan Keuangan Daerah'],
        ['3.2.1.', '-', 'Arah Kebijakan Pendapatan Daerah'],
        ['3.2.2.', '-', 'Arah Kebijakan Belanja Daerah'],
        ['3.2.3.', '-', 'Arah Kebijakan Pembiayaan Daerah'],
        ['3.2.4.', '-', 'Proyeksi Pendapatan dan Belanja'],
        ['4.', 'BAB IV SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH', "BAB IV SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH TAHUN {$tahun}", 1],
        ['4.1.', 'Tujuan dan Sasaran Pembangunan', 'Tujuan dan Sasaran Pembangunan'],
        ['4.2.', 'Arah Kebijakan Pembangunan', 'Arah Kebijakan Pembangunan'],
        ['4.3.', "Prioritas dan Sasaran Pembangunan Tahun {$tahun}", 'Prioritas Pembangunan'],
        ['4.3.1.', '-', "Prioritas Pembangunan Nasional Tahun {$tahun}"],
        ['4.3.2.', '-', "Prioritas Pembangunan Provinsi Jawa Tengah Tahun {$tahun}"],
        ['4.3.3.', '-', "Prioritas Pembangunan Kabupaten Rembang Tahun {$tahun}"],
        ['4.3.4.', '-', "Keterkaitan Prioritas Nasional, Prioritas Jawa Tengah dan Prioritas Kabupaten Rembang Tahun {$tahun}"],
        ['4.3.5.', '-', 'Penyelarasan Target Indikator Makro dan Tematik Berdasarkan Hasil Rakortekrenbang Provinsi'],
        ['4.4.', 'Pokok-Pokok Pikiran DPRD', 'Pokok-Pokok Pikiran DPRD'],
        ['4.5.', 'Inovasi Kebijakan Pembangunan', 'Inovasi Kebijakan Pembangunan'],
        ['5.', 'BAB V RENCANA KERJA DAN PENDANAAN DAERAH', 'BAB V RENCANA KERJA DAN PENDANAAN DAERAH', 1],
        ['5.1.', '-', 'Program Prioritas Daerah yang mendukung Prioritas Nasional dan Provinsi Jawa Tengah'],
        ['5.2.', '-', 'Program Perangkat Daerah'],
        ['5.2.1.', '-', 'Urusan Wajib Pemerintah Daerah'],
        ['5.2.2.', '-', 'Urusan Pemerintahan Wajib yang tidak berkaitan dengan Pelayanan Dasar'],
        ['5.2.3.', '-', 'Urusan Pemerintahan Pilihan'],
        ['5.2.4.', '-', 'Urusan Penunjang Pemerintah'],
        ['5.3.', '-', "Kerangka Pendanaan Tahun {$tahun}"],
        ['6.', 'BAB VI KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH', 'BAB VI KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH', 1],
        ['6.1.', 'Indikator Kinerja Utama Daerah', 'Indikator Kinerja Utama Daerah'],
        ['6.2.', 'Indikator Kinerja Daerah', 'Indikator Kinerja Daerah'],
        ['7.', 'BAB VII PENUTUP', 'BAB VII PENUTUP', 1],
    ];

    /* ───────────── 5. TABEL KONSISTENSI RPJMD vs RKPD ─────────────
       ['misi', 'judul misi']  atau
       [sasaran|null, ind RPJMD, satuan, target RPJMD, ind RKPD, satuan, target RKPD]
       Baris yang target RPJMD ≠ target RKPD otomatis diberi tanda (class "diff"). */
    $konsistensiRows = $konsistensi ?? [
        ['misi', 'Misi 1: Meningkatkan Kualitas Sumber Daya Manusia yang Unggul, Sehat dan Berdaya Saing'],
        ['Terwujudnya kualitas sumber daya manusia yang berdaya saing dan produktif', 'Indeks Daya Saing Daerah', 'Angka', '3,78', 'Indeks Daya Saing Daerah', 'Angka', '3,78'],
        ['Meningkatnya akses terhadap pendidikan dan kesehatan yang berkualitas, serta kesetaraan gender', 'Indeks Pembangunan Manusia', 'Tahun', '74,38', 'Indeks Pembangunan Manusia', 'Tahun', '73,70'],
        [null, 'Indeks Ketimpangan Gender (IUP 21)', 'Tahun', '0,246 / 0,22', 'Indeks Ketimpangan Gender (IUP 21)', 'Tahun', '0,246 / 2,22'],
        ['misi', 'Misi 2: Mewujudkan Pertumbuhan Ekonomi Daerah yang Berkelanjutan, Inklusif Berbasis Potensi Unggul, serta Mampu Mengurangi Kemiskinan'],
        ['Meningkatnya pertumbuhan ekonomi yang berdampak pada penurunan kemiskinan', 'Pertumbuhan Ekonomi', '%', '5,90 - 7,75', 'Pertumbuhan Ekonomi', '%', '5,40 - 6,20'],
        ['Meningkatnya nilai investasi, penyerapan tenaga kerja, kontribusi sektor unggulan dalam perekonomian, dan ketahanan pangan', 'Pembentukan Modal Tetap Bruto (PMTB) dalam PDRB (IUP 32)', '%', '24,02', 'Pembentukan Modal Tetap Bruto (PMTB) dalam PDRB', '%', '24,02'],
        [null, 'Produk Domestik Regional Bruto (PDRB) Per Kapita Kabupaten/Kota (IUP 33)', 'Juta Rp.', '57,81', 'Produk Domestik Regional Bruto (PDRB) Per Kapita Kabupaten/Kota', 'Juta Rp.', '57,81'],
        [null, 'Tingkat Pengangguran Terbuka (IUP 29)', '%', '2,45 / 2,15', 'Tingkat Pengangguran Terbuka', '%', '2,45 / 2,15'],
        ['-', 'Tingkat Kemiskinan (IUP 12)', '%', '12,01 / 11,70', 'Tingkat Kemiskinan', '%', '12,73 / 12,43'],
        [null, 'Indeks Ketahanan Pangan (IUP 53)', 'Angka', '85,34', 'Indeks Ketahanan Pangan (IKP)', 'Angka', '85,34'],
        ['misi', 'Misi 3: Mewujudkan Tata Kelola Pemerintahan dan Pelayanan Publik yang Profesional'],
        ['Meningkatnya tata kelola pemerintahan yang profesional dan berintegritas', 'Indeks Reformasi Birokrasi (IUP 39)<br>- RB General<br>- RB Tematik', 'Angka<br>Angka<br>Angka', '89,66<br>79,05<br>10,61', 'Indeks Reformasi Birokrasi<br>- RB General<br>- RB Tematik', 'Angka<br>Angka<br>Angka', '89,66<br>79,05<br>10,61'],
        ['misi', 'Misi 4: Mewujudkan Pembangunan Infrastruktur yang Berkualitas dan Berkelanjutan'],
        ['Meningkatnya pemerataan pembangunan infrastruktur yang tangguh', 'Indeks Infrastruktur', 'Angka', '70,31', 'Indeks Infrastruktur', 'Angka', '70,31'],
        ['1. Meningkatnya kinerja layanan infrastruktur konektivitas wilayah', 'Indeks Infrastruktur Konektivitas Wilayah', 'Angka', '68,18', 'Indeks Infrastruktur Konektivitas Wilayah', 'Angka', '68,18'],
        ['2. Meningkatnya pemenuhan permukiman Layak dan Berkelanjutan', 'Indeks Kinerja Infrastruktur Permukiman', '%', '72,44', 'Indeks Kinerja Infrastruktur Permukiman', '%', '72,44'],
        ['misi', 'Misi 5: Meningkatkan Kualitas Lingkungan Hidup dan Pengelolaan Sumber Daya Alam yang Berkelanjutan'],
        ['Meningkatnya kualitas lingkungan hidup berkelanjutan', 'Indeks Kualitas Lingkungan Hidup (IKLH)', 'Angka', '76,23', 'Indeks Kualitas Lingkungan Hidup (IKLH)', 'Angka', '76,23'],
        ['Meningkatnya pengelolaan lingkungan hidup dan Ketahanan Bencana Daerah', 'Kontribusi penurunan emisi GRK kumulatif (IUP 49)', 'TonCO2 eq', '1.815.353,68', 'Kontribusi penurunan emisi GRK kumulatif', 'TonCO2 eq', '1.815.353,68'],
        [null, 'Indeks Risiko Bencana (IRB)', 'Angka', '107,82 - 106,8', 'Indeks Risiko Bencana', 'Angka', '107,82 - 106,8'],
    ];

    /* ───────────── 6. HELPER DATA PENGUJIAN ───────────── */
    $pKeyed = ($pengujians ?? $lhr->pengujians)->keyBy('kode');

    $getSimpulan = function ($kode, $fallback) use ($pKeyed) {
        $val = $pKeyed->get($kode)?->teks_simpulan;
        return !empty(trim($val ?? '')) ? $val : $fallback;
    };
    $getCatatans = function ($kode) use ($pKeyed) {
        $p = $pKeyed->get($kode);
        if (!$p) return collect();
        $cats = $p->catatans ?? collect();
        if ($p->relationLoaded('children') && $p->children && $p->children->isNotEmpty()) {
            foreach ($p->children as $child) $cats = $cats->concat($child->catatans ?? collect());
        }
        return $cats;
    };
    $getRekomendasis = function ($kode) use ($pKeyed) {
        $p = $pKeyed->get($kode);
        if (!$p) return collect();
        $reks = ($p->catatans ?? collect())->pluck('rekomendasis')->flatten()->filter();
        if ($p->relationLoaded('children') && $p->children && $p->children->isNotEmpty()) {
            foreach ($p->children as $child) {
                $reks = $reks->concat(($child->catatans ?? collect())->pluck('rekomendasis')->flatten()->filter());
            }
        }
        return $reks;
    };

    // Render catatan/rekomendasi: data DB bila ada, kalau kosong → default.
    $catatanHtml = function ($kode, $default = '') use ($getCatatans) {
        $cats = $getCatatans($kode);
        if ($cats->isEmpty()) return $default;
        return $cats->map(function ($c) {
            $ref = $c->referensi_bagian ? '<span class="ref">' . e($c->referensi_bagian) . '</span><br>' : '';
            return '<div class="note">' . $ref . $c->isi . '</div>';
        })->implode('');
    };
    $rekomHtml = function ($kode, $default = '') use ($getRekomendasis) {
        $reks = $getRekomendasis($kode);
        if ($reks->isEmpty()) return $default;
        return $reks->map(fn($r) => '<div class="note">' . $r->uraian_rekom . '</div>')->implode('');
    };
    $ol = fn(array $items, $type = 'a') => '<ol type="' . $type . '">' . implode('', array_map(fn($i) => "<li>{$i}</li>", $items)) . '</ol>';
    $ul = fn(array $items) => '<ul>' . implode('', array_map(fn($i) => "<li>{$i}</li>", $items)) . '</ul>';

    /* ───────────── 7. DEFINISI PENGUJIAN (tambah/ubah pengujian cukup di sini) ───────────── */
    $dokumenPendukung = [
        'RPJMN tahun 2025-2029',
        'RPJMD Prov. Jateng tahun 2025-2029',
        "Rancangan RKPD Prov. Jateng tahun {$tahun}",
        'RPJMD Kab. Rembang tahun 2025-2029',
        'Renstra Perangkat Daerah tahun 2025-2029',
        'Hasil Evaluasi RKPD tahun 2025',
        "Rancangan Akhir RKPD tahun {$tahun}",
        "Berita Acara FKP RKPD tahun {$tahun}",
        "Berita Acara Musrenbang RKPD tahun {$tahun}",
        "SK Tim Penyusun RKPD Kab. Rembang tahun {$tahun}",
        "Lampiran Rancangan Akhir RKPD tahun {$tahun} (Rekapitulasi Rumusan Rencana Program, Kegiatan dan Sub Kegiatan Perangkat Daerah Tahun {$tahun})",
    ];
    $dokumenKurang = [
        'Hasil Evaluasi RKPD tahun 2026 (Triwulan 1);',
        'Laporan hasil pengendalian kebijakan penyusunan RKPD Formulir E.35 Pengendalian dan Evaluasi terhadap Kebijakan Perencanaan Pembangunan Tahunan Daerah (Cek List: 27 komponen);',
        "Jadwal / Agenda Kerja Tim Penyusunan RKPD tahun {$tahun}; dan",
        "Rekapitulasi Pokok-pokok Pikiran DPRD tahun {$tahun}.",
    ];
    $tema = "&quot;Pengembangan Perekonomian Daerah termasuk Ekonomi Syariah dan Pariwisata, Didukung SDM Produktif, Tata Kelola Pemerintahan yang Responsif dan Infrastruktur yang Mantap&quot;";
    $lampSipd = "Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {$tahun}) Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun}";

    $format = [
        'kode' => 'RKPD.F',
        'judul' => 'Kesesuaian Format Rancangan Akhir RKPD',
        'simpulan' => "Format/sistematika dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} kurang sesuai dengan yang diatur dalam Lampiran Peraturan Menteri Dalam Negeri Nomor 86 tahun 2017 (halaman 384).",
        'catatan' => "Format/sistematika dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} kurang sesuai dengan yang diatur dalam Lampiran Peraturan Menteri Dalam Negeri Nomor 86 tahun 2017 (halaman 384), sebagai berikut:",
        'rekom' => "Menyesuaikan judul: 1) Sub Bab 1.2, 2) Sub Bab 1.3, 3) Sub Bab 1.5, 4) Sub Bab 2.1.3, 5) Sub Bab 2.1.4, 6) Bab 4, 7) Sub Bab 4.3 Dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} dengan mempedomani Lampiran Peraturan Menteri Dalam Negeri nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah tentang Rencana Pembangunan Jangka Panjang Daerah dan Rencana Pembangunan Jangka Menengah Daerah, serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah dan Rencana Kerja Pemerintah Daerah (halaman 384);",
    ];

    // Baris default tabel Analisis Isi BAB (S2): bab, ref, catatan, rekomendasi
    $s2Default = [
        ['bab' => 'BAB II GAMBARAN UMUM KONDISI DAERAH', 'ref' => '2.1.4 Aspek Pelayanan Umum',
         'catatan' => $ol(['Belum menguraikan terkait Indikator Standar Pelayanan Minimum (SPM) sebagaimana tercantum dalam Peraturan Pemerintah Nomor 2 Tahun 2018; dan', 'Masih mencantumkan terkait anggaran, Sumber Daya Manusia dan sarana prasarana sebagai faktor penghambat kegagalan/ketidaktercapaian atas indikator kinerja tahun 2025.']),
         'rekom' => $ol(['Menambahkan uraian terkait Indikator Standar Pelayanan Minimum (SPM) sebagaimana tercantum dalam Peraturan Pemerintah Nomor 2 Tahun 2018; dan', 'Menghapus faktor penghambat kegagalan/ketidaktercapaian atas indikator kinerja tahun 2025 yang terkait dengan anggaran, Sumber Daya Manusia dan sarana prasarana.'])],
        ['bab' => 'BAB III. KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH', 'ref' => '3.1 Arah Kebijakan Ekonomi Daerah',
         'catatan' => 'Penjelasan terkait pertumbuhan ekonomi belum didukung dengan data pendukung yang disajikan pada tabel secara lengkap, yaitu:' . $ul(['PDRB Atas Dasar Harga Konstan Menurut Lapangan Usaha Kabupaten Rembang Tahun 2024-2025;', 'PDRB Atas Dasar Harga Berlaku (ADHB) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025;', 'PDRB Atas Dasar Harga Konstan (ADHK) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025; dan', 'Laju Inflasi.']),
         'rekom' => 'Melengkapi data pendukung pertumbuhan ekonomi yang meliputi:' . $ul(['PDRB Atas Dasar Harga Konstan Menurut Lapangan Usaha Kabupaten Rembang Tahun 2024-2025;', 'PDRB Atas Dasar Harga Berlaku (ADHB) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025;', 'PDRB Atas Dasar Harga Konstan (ADHK) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025; dan', 'Laju Inflasi.'])],
        ['bab' => 'BAB III. KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH', 'ref' => '3.2 Arah Kebijakan Keuangan Daerah',
         'catatan' => $ol([
            "Terdapat ketidaksesuaian angka proyeksi PAD dan belanja pada dokumen Rankir RKPD (tabel 3.5) dengan SIPD (tabel 3.5. Proyeksi Anggaran Pendapatan dan Belanja Daerah Kabupaten Rembang Tahun {$tahun});",
            'Kebijakan belanja belum memprioritaskan belanja untuk mendukung pelayanan dasar;',
            'Belum memproyeksikan belanja tidak terduga dan belanja transfer;',
            'Proyeksi belanja belum dirinci berdasarkan kategori rekening belanja;',
            'Terdapat proyeksi pembayaran Pembiayaan Utang Daerah sebesar Rp. 4.500.000.000,- yang tidak dijelaskan peruntukannya;',
            'Belum menginformasikan rasio ruang fiskal tahunan (grafik/tabel); dan',
            "Belum menginformasikan Kapasitas Riil Kemampuan Keuangan Daerah Kabupaten Rembang tahun {$tahun} (tabel & penjelasan).",
         ]),
         'rekom' => "Menyelaraskan proyeksi Pendapatan, Belanja dan Pembiayaan pada dokumen Rankir RKPD tahun {$tahun} (bab III) dengan Rankir RKPD tahun {$tahun} pada SIPD;<br><br>Dalam penetapan arah kebijakan belanja agar berdasarkan skala prioritas meliputi:" . $ul(['Wajib Mengikat', 'Pelayanan Dasar', 'Mandatory spending', 'Mendukung visi misi Kepala Daerah (RPJMD)']) .
            $ol(['Agar memproyeksikan belanja tidak terduga dan belanja transfer;', 'Proyeksi belanja agar dirinci berdasarkan rekening belanja;', 'Menambahkan penjelasan atas peruntukan pembayaran Pembiayaan Utang Daerah sebesar Rp. 4.500.000.000,-;', 'Menambahkan data rasio ruang fiskal tahunan (grafik/tabel); dan', "Menambahkan data Kapasitas Riil Kemampuan Keuangan Daerah Kabupaten Rembang tahun {$tahun} (tabel & penjelasan)."])],
        ['bab' => "BAB IV. SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH TAHUN {$tahun}", 'ref' => '4.2 Arah Kebijakan Pembangunan',
         'catatan' => "Arah kebijakan pembangunan RKPD Kabupaten Rembang yang tertuang dalam tabel 4.2 (halaman IV-8) belum difokuskan dalam mewujudkan tema pembangunan tahun {$tahun} {$tema}.",
         'rekom' => "Memfokuskan arah kebijakan pembangunan RKPD Kabupaten Rembang yang tertuang dalam tabel 4.2 (halaman IV-8) dalam mewujudkan tema pembangunan tahun {$tahun} {$tema}."],
        ['bab' => "BAB IV. SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH TAHUN {$tahun}", 'ref' => "4.3.3 Prioritas Pembangunan Kabupaten Rembang Tahun {$tahun}",
         'catatan' => 'Inventarisasi permasalahan di Bab II belum sepenuhnya menjadi prioritas yang ditangani.',
         'rekom' => 'Menambahkan prioritas pembangunan sesuai dengan permasalahan yang dituangkan dalam Bab II.'],
    ];

    $tabelPrioritas = '<table class="bordered"><thead><tr><th style="width:50%">Prioritas Pembangunan tahun ' . $tahun . '<br>(RPJMD 2025-2029)</th><th style="width:50%">Prioritas Pembangunan tahun ' . $tahun . '<br>(Rankir RKPD ' . $tahun . ')</th></tr></thead><tbody>'
        . implode('', array_map(fn($p) => "<tr><td>{$p}</td><td>{$p}</td></tr>", [
            '1. Peningkatan Pembangunan SDM', '2. Peningkatan Perekonomian Daerah', '3. Peningkatan Kualitas Pelayanan Publik',
            '4. Peningkatan Infrastruktur Mantap', '5. Peningkatan Daya Dukung Lingkungan Hidup']))
        . '</tbody></table>';

    $substansi = [
        ['kode' => 'RKPD.S1', 'judul' => 'Pengujian atas Kelengkapan Dokumen Pendukung RKPD',
         'simpulan' => "Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} telah didukung dengan dokumen perencanaan yang memadai yaitu:",
         'daftar' => $dokumenPendukung,
         'catatan' => 'Terdapat data/dokumen pendukung yang belum ada, yaitu:' . $ol($dokumenKurang),
         'rekom' => 'Melengkapi data/dokumen pendukung sebagai berikut:' . $ol($dokumenKurang)],

        ['kode' => 'RKPD.S2', 'judul' => 'Pengujian atas Analisis Isi BAB Rancangan Akhir RKPD',
         'simpulan' => "Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahun}, masih terdapat kekurangsesuaian data yang disajikan.",
         'tabel' => $s2Default],

        ['kode' => 'RKPD.S3', 'judul' => 'Pengujian atas Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan dalam RKPD dan RPJMD',
         'simpulan' => "Rumusan Prioritas dan Sasaran Pembangunan dalam Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahun} telah sesuai dengan Dokumen RPJMD Kabupaten Rembang tahun 2025-2029.",
         'catatan' => "Rumusan prioritas pembangunan daerah pada Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} (hal IV-16) telah selaras dengan prioritas pembangunan daerah yang telah ditetapkan pada RPJMD Kabupaten Rembang tahun 2025-2029 (hal III-143) sebagaimana penyandingan berikut:" . $tabelPrioritas,
         'rekom' => ''],

        ['kode' => 'RKPD.S4', 'judul' => 'Kesesuaian Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada RPJMD dengan Rancangan Akhir RKPD',
         'simpulan' => "Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahun} belum sesuai dengan Rencana Pembangunan Jangka Menengah Daerah (RPJMD) Kabupaten Rembang tahun 2025-2029.",
         'catatan' => $ol([
            "Terdapat ketidaksesuaian Indikator Kinerja, Target beserta Pagu Indikatif (Program) yang dicantumkan pada tabel 4.1 RPJMD Kabupaten Rembang tahun 2025-2029 (halaman IV-34) dengan Lampiran SIPD-RI Excel (Renja OPD Kabupaten Rembang tahun {$tahun}) Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun}, sebagaimana penyandingan pada link {$lnk};",
            "Seluruh Program pada Lampiran SIPD-RI PDF (Renja OPD Kabupaten Rembang tahun {$tahun}) Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} belum dilengkapi outcome, indikator, satuan, capaian (T-2) dan target;",
            "Terdapat ketidaksesuaian Indikator Kinerja, Target beserta Pagu Indikatif (Program) yang dicantumkan pada tabel 5.1 (halaman V-1 sampai halaman V-28) dan tabel 5.3 Rancangan Akhir RKPD tahun {$tahun} (halaman V-85 sampai halaman V-123) dengan {$lampSipd}, sebagaimana penyandingan pada link {$lnk};",
            "Terdapat beberapa Program yang termasuk Program Prioritas Nasional tetapi dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} kurang mendapatkan proporsi anggaran, sebagaimana catatan pada link {$lnk};",
            "Terdapat penetapan target kinerja &quot;Persentase terbangunnya sarana prasarana persampahan sesuai standar (%)&quot; namun tidak disertai dengan penetapan anggaran, padahal indikator kinerja yang digunakan sebagai tolok ukur menunjukkan pembangunan fisik/infrastruktur sebagaimana catatan pada link {$lnk};",
            "Terdapat kenaikan proporsi anggaran yang cukup signifikan pada beberapa Program di dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} dibandingkan dengan proporsi anggaran pada dokumen RPJMD Kabupaten Rembang tahun 2025-2029 sebagaimana catatan pada link {$lnk};",
            "Program Pengolahan dan Pemasaran Hasil Perikanan termasuk dalam program utama unggulan Bupati dan Wakil Bupati namun proporsi anggaran pada dokumen RPJMD Kabupaten Rembang tahun 2025-2029 dengan dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} selisih cukup besar dibanding program pendukung maupun bukan program pendukung sebagaimana catatan pada link {$lnk}; dan",
            "Terdapat beberapa program yang bukan termasuk program prioritas maupun nomenklatur program utama namun proporsi anggaran yang diberikan pada dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} cukup besar dibandingkan dengan anggaran pada program prioritas sebagaimana catatan pada link {$lnk}.",
         ]),
         'rekom' => $ol([
            "Menyesuaikan indikator kinerja, target beserta pagu indikatif (Program) yang dicantumkan tabel 5.1 (halaman V-1 sampai halaman V-28), tabel 5.3 Rancangan Akhir RKPD tahun {$tahun} (halaman V-85 sampai halaman V-123) dan {$lampSipd} dengan tabel 4.1 RPJMD Kabupaten Rembang tahun 2025-2029 (halaman IV-34);",
            "Seluruh Program pada Lampiran SIPD-RI PDF (Renja OPD Kabupaten Rembang tahun {$tahun}) Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} agar dilengkapi outcome, indikator, satuan, capaian (T-2), target, dan pagu indikatif;",
            "Memproporsikan anggaran program dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} yang termasuk Program Prioritas Nasional;",
            'Memproporsikan anggaran pada indikator kinerja &quot;Persentase terbangunnya sarana prasarana persampahan sesuai standar (%)&quot; yang digunakan sebagai tolok ukur pembangunan fisik/infrastruktur;',
            "Mengkaji kembali kenaikan proporsi anggaran yang cukup signifikan pada beberapa Program di dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} dibandingkan dengan proporsi anggaran pada dokumen RPJMD Kabupaten Rembang tahun 2025-2029;",
            'Memproporsikan anggaran untuk Program Pengolahan dan Pemasaran Hasil Perikanan yang merupakan program utama unggulan Bupati dan Wakil Bupati; dan',
            "Mengkaji kembali program yang bukan termasuk program prioritas maupun nomenklatur program utama namun proporsi anggaran yang diberikan pada dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} cukup besar dibandingkan dengan anggaran pada program prioritas.",
         ])],

        ['kode' => 'RKPD.S5', 'judul' => 'Kewajaran proyeksi pendanaan pada rancangan akhir RKPD',
         'simpulan' => "Proyeksi pendanaan pada Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahun} telah wajar.",
         'catatan' => $ol([
            "Data APBD tahun berjalan (tahun anggaran 2026) pada tabel 3.5 (halaman III-17) dokumen Rancangan Akhir RKPD Kabupaten Rembang belum menggunakan APBD berdasarkan Peraturan Bupati Rembang Nomor 4 tahun 2026 tentang Perubahan atas Peraturan Bupati Rembang Nomor 110 tahun 2025 tentang Penjabaran APBD tahun anggaran 2026 sebagaimana penyandingan pada link {$lnk};",
            "Data Realisasi Anggaran tahun 2025 unaudited pada tabel 3.2 (halaman III-10), tabel 3.3 (halaman III-15), dan tabel 3.4 (halaman III-16) Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} belum sesuai dengan LRA tahun 2025 unaudited terupdate sebagaimana penyandingan pada link {$lnk};",
            "Proyeksi APBD tahun anggaran {$tahun} pada tabel 3.5 Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} (halaman III-17) tidak sesuai dengan data proyeksi APBD tahun anggaran {$tahun} di SIPD-RI sebagaimana penyandingan pada link {$lnk}; dan",
            "Pada tabel 3.5 Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} (halaman III-17) belum memproyeksikan belanja tidak terduga dan belanja transfer.",
         ]),
         'rekom' => $ol([
            'Penyajian Data APBD tahun anggaran 2026 agar mempedomani Peraturan Bupati Rembang Nomor 4 tahun 2026 tentang Perubahan atas Peraturan Bupati Rembang Nomor 110 tahun 2025 tentang Penjabaran APBD tahun anggaran 2026;',
            "Penyajian Data Realisasi Anggaran tahun 2025 unaudited pada tabel 3.2 (halaman III-10), tabel 3.3 (halaman III-15), dan tabel 3.4 (halaman III-16) Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} agar disesuaikan dengan LRA tahun 2025 unaudited terupdate;",
            "Menyesuaikan penyajian Proyeksi APBD tahun anggaran {$tahun} pada tabel 3.5 Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} (halaman III-17) dengan data proyeksi APBD tahun anggaran {$tahun} di SIPD-RI; dan",
            "Pada tabel 3.5 Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} (halaman III-17) agar memproyeksikan belanja tidak terduga dan belanja transfer.",
         ])],

        ['kode' => 'RKPD.S6', 'judul' => 'Kesesuaian Informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional',
         'sub' => [
            ['kode' => 'RKPD.S6.a', 'judul' => 'Kesesuaian urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil rapat koordinasi teknis pembangunan (Rakortekbang) dengan urusan pada rancangan akhir RKPD:',
             'simpulan' => "Urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil rapat koordinasi teknis pembangunan (Rakortekrenbang Nasional tahun 2026) pada Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {$tahun}) Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} belum selaras.",
             'catatan' => $ul([
                "Terdapat ketidakselarasan pemetaan urusan pemerintah daerah terhadap Asta Cita berdasarkan RAKORTEKRENBANG Nasional Tahun 2026 sebagaimana penyandingan pada link {$lnk};",
                "Belum teridentifikasinya pemetaan dukungan subkegiatan terhadap outcome prioritas (program prioritas) pemerintah pusat sebagaimana tertuang pada link {$lnk};",
                "Terdapat subkegiatan dengan target kinerja nihil yang dimasukkan dalam pemetaan dukungan terhadap asta cita sebagaimana tertuang pada link {$lnk}.",
             ]),
             'rekom' => $ul(['Melakukan perbaikan pemetaan urusan pemerintah daerah terhadap dukungan Asta Cita;', 'Mengidentifikasi pemetaan subkegiatan yang mendukung outcome prioritas (program prioritas) pemerintah pusat; dan', 'Memperbaiki target subkegiatan yang tidak mendukung/kurang mendukung Asta Cita dan outcome prioritas pemerintah pusat.'])],
            ['kode' => 'RKPD.S6.b', 'judul' => 'Kesesuaian penetapan target Indikator Makro Pembangunan pada rancangan akhir RKPD berdasarkan Rencana Kerja Pemerintah (RKP):',
             'simpulan' => "Rencana Kerja Pemerintah (RKP) tahun {$tahun} belum tersedia.",
             'catatan' => "Rencana Kerja Pemerintah (RKP) tahun {$tahun} belum tersedia.",
             'rekom' => ''],
            ['kode' => 'RKPD.S6.c', 'judul' => 'Kesesuaian dan ketepatan dukungan pelaksanaan ProSN pada rancangan akhir RKPD:',
             'simpulan' => "Program Strategis Nasional (ProSN) dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} belum seluruhnya terpetakan dalam {$lampSipd}.",
             'catatan' => "Subkegiatan yang mendukung pelaksanaan ProSN belum seluruhnya terpetakan dalam {$lampSipd} sebagaimana penyandingan pada link {$lnk}.",
             'rekom' => 'Penyusunan dokumen perencanaan Kabupaten Rembang agar diselaraskan dan dipetakan sesuai dengan Program Strategis Nasional.'],
            ['kode' => 'RKPD.S6.d', 'judul' => 'Memastikan Program, Kegiatan dan Sub Kegiatan Standar Pelayanan Minimal (SPM) tersedia pada rancangan akhir RKPD:',
             'simpulan' => "Program, kegiatan dan subkegiatan Standar Pelayanan Minimal (SPM) pada Rancangan Akhir RKPD belum seluruhnya terpetakan dalam {$lampSipd}.",
             'catatan' => $ul([
                "Terdapat subkegiatan Standar Pelayanan Minimal (SPM) yang belum terpetakan dalam {$lampSipd}; dan",
                "Terdapat beberapa subkegiatan yang termasuk subkegiatan Standar Pelayanan Minimal (SPM) tetapi dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} tidak mendapatkan proporsi anggaran, sebagaimana catatan pada link {$lnk}.",
             ]),
             'rekom' => $ul(['Penetapan subkegiatan dalam dokumen perencanaan Kabupaten Rembang agar diselaraskan dan dipetakan dengan semua subkegiatan Standar Pelayanan Minimal (SPM);', 'Memproporsikan anggaran untuk semua indikator kinerja subkegiatan Standar Pelayanan Minimal (SPM).'])],
            ['kode' => 'RKPD.S6.e', 'judul' => 'Memastikan kesesuaian dukungan Sub Kegiatan Tematik Pembangunan pada rancangan akhir RKPD:',
             'simpulan' => "Sub Kegiatan Tematik Pembangunan pada Rancangan Akhir RKPD belum seluruhnya terpetakan dalam {$lampSipd}.",
             'catatan' => $ul([
                "Terdapat subkegiatan Tematik Pembangunan yang belum terpetakan dalam {$lampSipd}; dan",
                "Terdapat beberapa subkegiatan yang termasuk Tematik Pembangunan tetapi dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} tidak mendapatkan proporsi anggaran, sebagaimana catatan pada link {$lnk}.",
             ]),
             'rekom' => $ul(['Penetapan subkegiatan dalam dokumen perencanaan Kabupaten Rembang agar diselaraskan dan dipetakan dengan semua subkegiatan Tematik Pembangunan / Asta Cita Nasional;', 'Memproporsikan anggaran untuk semua indikator kinerja subkegiatan Tematik Pembangunan / Asta Cita Nasional.'])],
         ]],

        ['kode' => 'RKPD.S7', 'judul' => 'Kesesuaian Informasi pada rancangan akhir RKPD dengan tata cara dan kaidah Perencanaan Pembangunan Daerah lainnya (Pokok-Pokok Pikiran DPRD)',
         'simpulan' => "Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahun} belum dilengkapi dengan rekapitulasi data pokok-pokok pikiran Dewan Perwakilan Rakyat Daerah (DPRD) Kabupaten Rembang tahun {$tahun}.",
         'catatan' => "Bab IV Sasaran dan Prioritas Pembangunan Daerah Tahun {$tahun} telah menjelaskan pokok-pokok pikiran DPRD (halaman IV-33 sampai dengan halaman IV-34), namun belum dilengkapi dengan rekapitulasi data pokok-pokok pikiran DPRD yang berisi: Program/Kegiatan/Sub Kegiatan/Aktivitas, Indikator, Volume, Kelompok Sasaran, Lokasi dan Perangkat Daerah terkait.",
         'rekom' => "Penjelasan Pokok-Pokok Pikiran DPRD pada Bab IV Sasaran dan Prioritas Pembangunan Daerah Tahun {$tahun} agar dilengkapi dengan rekapitulasi data pokok-pokok pikiran DPRD yang berisi: Program/Kegiatan/Sub Kegiatan/Aktivitas, Indikator, Volume, Kelompok Sasaran, Lokasi dan Perangkat Daerah terkait."],

        ['kode' => 'RKPD.S8', 'judul' => 'Pengujian atas Konsistensi Pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran dalam Rancangan Akhir RKPD dan RPJMD',
         'simpulan' => "Target Kinerja Tujuan dan Sasaran dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} belum konsisten dengan Dokumen RPJMD Kabupaten Rembang Tahun 2025-2029.",
         'catatan' => "Terdapat ketidakkonsistenan target Indikator Kinerja Tujuan dan Sasaran yang dicantumkan pada tabel 3.6 RPJMD Kabupaten Rembang tahun 2025-2029 dengan tabel 4.1 Rancangan Akhir RKPD tahun {$tahun}, sebagaimana penyandingan sebagai berikut:",
         'rekom' => "Mengkaji kembali penetapan target indikator kinerja Tujuan dan Sasaran yang tercantum pada tabel 3.6 Misi, Tujuan Dan Sasaran Pembangunan Kabupaten Rembang Beserta Indikator dan Target Kinerja Tahun 2025-2029 dan tabel 3.9 Teknik Perumusan Program Prioritas Pembangunan Daerah Rancangan Akhir RPJMD tahun 2025-2029 dengan Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahun} yang tidak konsisten."],
    ];

    // Rekomendasi: hanya tampilkan pengujian yang benar-benar punya rekomendasi
    $nonEmpty = fn($h) => trim(strip_tags((string) $h)) !== '';
    $rekomItems = [];
    foreach ($substansi as $n => $u) {
        if (!empty($u['tabel'])) { $rekomItems[] = ['n' => $n + 1, 'u' => $u, 'type' => 'tabel']; continue; }
        if (!empty($u['sub'])) {
            $subs = [];
            foreach ($u['sub'] as $i => $s) {
                $h = $rekomHtml($s['kode'], $s['rekom'] ?? '');
                if ($nonEmpty($h)) $subs[] = ['l' => chr(97 + $i), 'judul' => $s['judul'], 'h' => $h];
            }
            if ($subs) $rekomItems[] = ['n' => $n + 1, 'u' => $u, 'type' => 'sub', 'subs' => $subs];
            continue;
        }
        $h = $rekomHtml($u['kode'], $u['rekom'] ?? '');
        if ($nonEmpty($h)) $rekomItems[] = ['n' => $n + 1, 'u' => $u, 'type' => 'single', 'h' => $h];
    }

    $attList = $attachments ?? $lhr->attachments;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahun }}</title>
    <style>
        /* ================= DASAR (semua mode) ================= */
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9.5pt; line-height: 1.35; color: #000; margin: 0; padding: 0; }
        h3, h4 { margin: 10px 0 4px; page-break-after: avoid; break-after: avoid; }
        p { margin: 4px 0; }
        ol, ul { margin: 2px 0 5px; padding-left: 18px; }
        li { margin-bottom: 3px; text-align: justify; }
        a { color: #000; word-break: break-all; }

        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-right { text-align: right; }
        .indent  { margin-left: 15px; }
        .indent2 { margin-left: 30px; }
        .nobreak { page-break-inside: avoid; break-inside: avoid; }
        .new-page { page-break-before: always; break-before: page; }
        .sec-title { font-weight: bold; margin: 10px 0 2px; page-break-after: avoid; break-after: avoid; }
        .item { margin: 6px 0; }
        .item > .note, .note { margin: 4px 0 6px; text-align: justify; }
        .ref { font-weight: bold; background: #eee; padding: 1px 4px; font-size: 8.5pt; }
        .sub-rekom { margin: 3px 0 3px 18px; text-align: justify; }

        /* tabel */
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 8.5pt; }
        table th, table td { padding: 3px 5px; vertical-align: top; }
        table.plain th, table.plain td { border: 0; }
        table.bordered, table.bordered th, table.bordered td { border: 1px solid #000; }
        table.bordered th { background: #f2f2f2; text-align: center; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; break-inside: avoid; }
        td.c { text-align: center; }
        td.grp, td.misi { background: #e6e6e6; font-weight: bold; }
        td.cont { border-top-color: transparent !important; }
        td.diff { background: #fde8e8; font-weight: bold; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 5px 6px; }
        table.data-table th { background: #f1f5f9; }
        .mono { font-family: monospace; font-size: 8pt; background: #e2e8f0; padding: 1px 5px; }

        .person td { padding: 2px; font-size: 9pt; }
        .person td:first-child { width: 100px; }
        .person-sep td { padding: 2px; }
        .surat-meta td { padding: 1px 4px; font-size: 9pt; }

        /* kop */
        .header-kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 4px; margin-bottom: 10px; }
        .header-kop h3 { margin: 0; font-size: 11pt; }
        .header-kop h2 { margin: 0; font-size: 13pt; }
        .header-kop p { margin: 1px 0; font-size: 8pt; }

        /* cover & penutup */
        .bleed { background: #f7b267; text-align: center; page-break-after: always; break-after: page; }
        .bleed:last-of-type { page-break-after: auto; break-after: auto; }
        .cover-logo { width: 100px; margin: 0 auto 10px; display: block; }
        .cover-header { font-weight: bold; font-size: 13pt; }
        .cover-title { font-size: 13.5pt; font-weight: bold; margin: 20px 0 15px; line-height: 1.3; }
        .cover-meta { text-align: left; margin: 20px auto; width: 85%; font-weight: bold; font-size: 10pt; }
        .cover-year { font-weight: bold; font-size: 14pt; margin-top: 40px; }

        /* tanda tangan */
        .ttd { width: 100%; margin-top: 30px; }
        .ttd td { border: 0; }

        @if($engine === 'dompdf')
        /* ================= DOMPDF ================= */
        @page { size: 210mm 297mm; margin: 15mm 15mm 20mm 20mm; }
        .preview-toolbar { display: none !important; }
        .running-footer { position: fixed; left: 0; right: 0; bottom: -12mm; border-top: 1px solid #ccc; padding-top: 3px; font-size: 8pt; font-style: italic; z-index: -10; }
        .running-footer table { margin: 0; font-size: 8pt; }
        .running-footer td { border: 0; padding: 0; }
        .pagenum:before { content: counter(page); }
        .bleed { margin: -15mm -15mm -20mm -20mm; width: 210mm; height: 262mm; overflow: hidden; padding: 20mm 15mm 15mm; position: relative; z-index: 10; }
        .cover-title { font-size: 13.5pt; font-weight: bold; margin: 15px 0 10px; line-height: 1.3; }
        .cover-meta { text-align: left; margin: 15px auto; width: 85%; font-weight: bold; font-size: 10pt; }
        .cover-year { font-weight: bold; font-size: 14pt; margin-top: 25px; }
        @elseif($engine === 'chrome')
        /* ================= CHROME / BROWSERSHOT ================= */
        @page { size: A4; margin: 15mm 15mm 20mm 20mm;
            @bottom-left  { content: "{{ $footerText }}"; font: italic 8pt Arial; border-top: 1px solid #ccc; width: 100%; vertical-align: top; padding-top: 3px; }
            @bottom-right { content: counter(page); font: italic 8pt Arial; border-top: 1px solid #ccc; vertical-align: top; padding-top: 3px; }
        }
        @page bleed { margin: 0; @bottom-left { content: none; border: 0 } @bottom-right { content: none; border: 0 } }
        .preview-toolbar { display: none !important; }
        .bleed { page: bleed; height: 297mm; padding: 25mm 20mm 20mm; }
        @else
        /* ================= PREVIEW LAYAR ================= */
        @page { size: A4; margin: 15mm 15mm 20mm 20mm;
            @bottom-left  { content: "{{ $footerText }}"; font: italic 8pt Arial; }
            @bottom-right { content: counter(page); font: italic 8pt Arial; }
        }
        @page bleed { margin: 0; @bottom-left { content: none } @bottom-right { content: none } }
        body { background: #525659; }
        .sheet { width: 210mm; max-width: 100%; margin: 15px auto; background: #fff; padding: 15mm 15mm 15mm 20mm; box-shadow: 0 4px 15px rgba(0,0,0,.25); }
        .bleed { page: bleed; width: 210mm; max-width: 100%; min-height: 297mm; margin: 15px auto; padding: 25mm 20mm 20mm; box-shadow: 0 4px 15px rgba(0,0,0,.25); }
        .running-footer { display: none; }
        .preview-toolbar { position: sticky; top: 0; z-index: 9999; background: #1e293b; color: #fff; padding: 10px 20px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,.3); }
        .preview-toolbar a, .preview-toolbar button { font: bold 12px Arial, sans-serif; padding: 6px 14px; border-radius: 6px; text-decoration: none; cursor: pointer; border: 0; color: #fff; display: inline-block; }
        .btn-back { background: #475569; } .btn-print { background: #2563eb; } .btn-pdf { background: #dc2626; } .btn-word { background: #2563eb; }
        @media print {
            body { background: none; }
            .sheet { width: auto; margin: 0; padding: 0; box-shadow: none; }
            .bleed { width: 100%; height: 297mm; margin: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }
        @endif
    </style>
</head>
<body>

@if($engine === 'dompdf')
    {{-- harus pertama di body agar cover menimpanya --}}
    <div class="running-footer">
        <table><tr>
            <td style="text-align:left">{{ $footerText }}</td>
            <td style="text-align:right"><span class="pagenum"></span></td>
        </tr></table>
    </div>
@endif

@if($engine === 'browser')
    <div class="preview-toolbar no-print">
        <div>
            <a href="{{ route('lhr.show', $lhr->uuid) }}" class="btn-back">&larr; Kembali ke Ruang Kerja LHR</a>
            <span style="font-size:13px;font-weight:bold;color:#cbd5e1;margin-left:8px">Dokumen LHR: {{ $nomorLhr }}</span>
        </div>
        <div>
            <button type="button" class="btn-print" onclick="window.print()">&#128438; Cetak / Print Browser</button>
            <a href="{{ route('lhr.pdf', $lhr->uuid) }}" class="btn-pdf">&#128190; Unduh PDF</a>
            <a href="{{ route('lhr.word', $lhr->uuid) }}" class="btn-word">&#128196; Unduh Word (.docx)</a>
        </div>
    </div>
@endif

{{-- ==================== COVER ==================== --}}
<section class="bleed">
    <div class="cover-header">
        <p style="margin:0">KABUPATEN REMBANG</p>
        <p style="margin:0">INSPEKTORAT DAERAH KABUPATEN REMBANG</p>
    </div>
    <div style="margin-top:25px">
        <img src="{{ $logoSrc }}" alt="Logo Rembang" class="cover-logo">
        <p style="font-weight:bold;font-size:10pt;margin:5px 0">BerAKHLAK</p>
        <p style="font-size:8pt;margin:0">bangga melayani bangsa</p>
    </div>
    <div class="cover-title">
        LAPORAN HASIL REVIU<br>
        DOKUMEN RANCANGAN AKHIR<br>
        RENCANA KERJA PEMERINTAH DAERAH (RKPD) KABUPATEN REMBANG<br>
        TAHUN {{ $tahun }}<br>
        <span style="font-size:12pt;text-transform:uppercase">{{ $namaUnit }}</span>
    </div>
    <div class="cover-meta">
        <table class="plain" style="font-size:10pt">
            <tr><td style="width:100px">Nomor</td><td>: {{ $nomorLhr }}</td></tr>
            <tr><td>Tanggal</td><td>: {{ $tanggalLhr }}</td></tr>
        </table>
    </div>
    <div class="cover-year">{{ $tahunTerbit }}</div>
</section>

{{-- ==================== ISI LAPORAN (mengalir otomatis antar halaman) ==================== --}}
<main class="sheet">

    {{-- Surat pengantar --}}
    <div class="header-kop">
        <h3>PEMERINTAH KABUPATEN REMBANG</h3>
        <h2>INSPEKTORAT DAERAH</h2>
        <p>JL. RAYA REMBANG LASEM KM. 1.1 REMBANG TELP. (0295) 691320</p>
        <p>Fax. (0295) 693525 Rembang - 59219 Email: Inspektorat@rembangkab.go.id</p>
    </div>

    <table class="plain surat-meta">
        <tr>
            <td style="width:15%">Nomor</td><td style="width:2%">:</td>
            <td style="width:48%">{{ $nomorLhr }}</td>
            <td style="width:35%">Rembang, {{ $tanggalLhr }}</td>
        </tr>
        <tr><td>Lampiran</td><td>:</td><td>-</td><td></td></tr>
        <tr>
            <td>Perihal</td><td>:</td>
            <td>Hasil Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahun }}</td>
            <td>Kepada Yth.<br>Kepala {{ $namaUnit }}<br>di - REMBANG</td>
        </tr>
    </table>

    <div class="text-justify" style="margin-top:12px">
        <p>Inspektorat Daerah Kabupaten Rembang sebagai Aparat Pengawasan Intern Pemerintah telah melakukan reviu atas Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahun }} berupa pengujian terbatas terhadap substansi yang termuat pada Rancangan Akhir Rencana Kerja Pemerintah Daerah. Reviu ditujukan untuk membantu terlaksananya penyusunan dan penyajikan dokumen Rencana Kerja Pemerintah Daerah (RKPD) serta memberikan keyakinan terbatas mengenai kepatuhan dan keabsahan informasi yang disajikan telah sesuai dengan kaidah-kaidah perencanaan, sehingga dapat menghasilkan dokumen Rencana Kerja Pemerintah Daerah (RKPD) yang berkualitas.</p>
        <p>Dalam pelaksanaan reviu, kami telah melakukan serangkaian aktivitas untuk menguji proses penyusunan atas penyajian dan substansi dalam dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Tahun {{ $tahun }} dengan dokumen pendukung lainnya, permintaan keterangan mengenai proses penyusunan untuk mengetahui hubungan dan hal-hal yang tidak terdapat dalam dokumen penyajian. Bersama ini kami sampaikan Laporan Hasil Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahun }}. Uraian rinci atas pelaksanaan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahun }} adalah sebagai berikut:</p>
    </div>

    {{-- A. Pernyataan --}}
    <h4 class="new-page">A. Pernyataan Reviu</h4>
    <p class="text-justify">Kami telah mereviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahun }} dengan mempedomani Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 3 Tahun 2026 tentang Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah. Semua informasi yang dimuat dalam laporan adalah penyajian manajemen Badan Perencanaan Pembangunan Daerah Kabupaten Rembang dan Aplikasi Sistem Informasi Pemerintahan Daerah Kementerian Dalam Negeri Republik Indonesia.</p>
    <p class="text-justify">Reviu dilaksanakan sesuai dengan Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 3 Tahun 2026 tentang Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah, yaitu tertera dalam:</p>
    <table class="plain text-justify">
        <tr><td style="width:100px;font-weight:bold">Pasal 2 ayat (1)</td><td>: APIP Daerah melaksanakan Reviu terhadap: a. dokumen Perencanaan Pembangunan Daerah; dan b. dokumen Keuangan Daerah;</td></tr>
        <tr><td style="font-weight:bold">Pasal 3 ayat (1)</td><td>: Dokumen Perencanaan Pembangunan Daerah sebagaimana dimaksud dalam Pasal 2 ayat (1) huruf a, terdiri atas: a. rancangan akhir RPJMD; b. rancangan akhir Renstra Perangkat Daerah; dan c. rancangan akhir RKPD.</td></tr>
        <tr><td style="font-weight:bold">Pasal 8</td><td>: Ketentuan lebih lanjut mengenai pedoman Reviu dokumen Perencanaan Pembangunan Daerah dan dokumen Keuangan Daerah tercantum dalam Lampiran yang merupakan bagian tidak terpisahkan dari Peraturan Menteri ini.</td></tr>
    </table>
    <p class="text-justify">Reviu mempunyai lingkup yang jauh lebih sempit dibandingkan dengan lingkup audit yang dilakukan sesuai dengan peraturan terkait dengan tujuan audit.</p>

    {{-- B. Dasar hukum + tim --}}
    <h4>B. Dasar Hukum Reviu</h4>
    <ol class="text-justify">
        @foreach($dasarHukum as $dh)<li>{{ $dh }}</li>@endforeach
    </ol>

    <div class="indent">
        @foreach($blokTim as $judulBlok => $orang)
            <div class="nobreak">
                <p style="font-weight:bold;margin:8px 0 2px">&#10148; {{ $judulBlok }}</p>
                @foreach($orang as $o)
                    <table class="plain person nobreak" style="margin:0 0 4px">
                        <tr><td>Nama</td><td>: {{ $o['nama'] }}</td></tr>
                        <tr><td>NIP</td><td>: {{ $o['nip'] }}</td></tr>
                        <tr><td>Jabatan</td><td>: {{ $o['jabatan'] }}</td></tr>
                    </table>
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- C–G --}}
    <h4>C. Tujuan Reviu</h4>
    <p class="text-justify">Tujuan Reviu rancangan akhir Rencana Kerja Pemerintah Daerah (RKPD) yaitu untuk memberikan keyakinan terbatas bahwa Rencana Kerja Pemerintah Daerah telah disusun berpedoman kepada RPJMD, Rencana Kerja Pemerintah, Program Strategis Nasional, pedoman penyusunan RKPD dan kaidah-kaidah perencanaan pembangunan lainnya.</p>

    <h4>D. Ruang Lingkup Reviu</h4>
    <p class="text-justify">Ruang lingkup reviu adalah pengujian atas penyusunan Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahun }} yang bersifat tahunan berupa Dokumen RKPD Tahun {{ $tahun }}.</p>
    <p>Ruang lingkup reviu mencakup pengujian terbatas atas:</p>
    <p style="font-weight:bold;margin-bottom:0">&#10148; FORMAT</p>
    <p style="margin:0 0 6px 15px">Kesesuaian Format Rancangan Akhir RKPD.</p>
    <p style="font-weight:bold;margin-bottom:0">&#10148; SUBSTANSI</p>
    <ol class="text-justify">
        @foreach($ruangLingkupSubstansi as $rl)
            @if(is_array($rl))
                <li>{{ $rl[0] }}
                    <ol type="a">@foreach($rl[1] as $sub)<li>{{ $sub }}</li>@endforeach</ol>
                </li>
            @else
                <li>{{ $rl }}</li>
            @endif
        @endforeach
    </ol>

    <h4>E. Objek Reviu</h4>
    <p class="text-justify">Objek reviu adalah Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahun }}.</p>

    <h4>F. Pelaksanaan Reviu</h4>
    <p class="text-justify">Pelaksanaan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {{ $tahun }} dilaksanakan mulai tanggal {{ $tanggalMulaiReviu }} sampai dengan selesai.</p>

    <h4>G. Metodologi Reviu</h4>
    <p class="text-justify">Reviu dilaksanakan dengan menggunakan metodologi mengumpulkan dan melakukan penelaahan atas Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {{ $tahun }}, konfirmasi, analisis dan prosedur lainnya terkait proses penyusunan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {{ $tahun }}.</p>

    <h4>H. Gambaran Umum</h4>
    <ol class="text-justify">
        @foreach($gambaranUmum as $g)<li>{{ $g }}</li>@endforeach
    </ol>

    {{-- I. Uraian hasil --}}
    <h4>I. Uraian Hasil Reviu</h4>
    <p class="text-justify">Hasil pelaksanaan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {{ $tahun }} dapat dijelaskan sebagai berikut:</p>

    {{-- 1. Simpulan --}}
    <div class="sec-title">1. Simpulan</div>
    <div class="indent">
        <div class="sec-title">a. Format</div>
        <div class="indent text-justify">
            <b>{{ $format['judul'] }}</b><br>
            {{ $getSimpulan($format['kode'], $format['simpulan']) }}
        </div>

        <div class="sec-title">b. Substansi</div>
        @foreach($substansi as $n => $u)
            <div class="item indent text-justify">
                <b>{{ $n + 1 }}) {{ $u['judul'] }}</b>
                @if(!empty($u['sub']))
                    <ol type="a">
                        @foreach($u['sub'] as $s)
                            <li><b>{{ $s['judul'] }}</b> {{ $getSimpulan($s['kode'], $s['simpulan']) }}</li>
                        @endforeach
                    </ol>
                @else
                    <br>{{ $getSimpulan($u['kode'], $u['simpulan']) }}
                    @if(!empty($u['daftar']))
                        <ol type="a">@foreach($u['daftar'] as $d)<li>{{ $d }}</li>@endforeach</ol>
                    @endif
                @endif
            </div>
        @endforeach
    </div>

    {{-- 2. Catatan --}}
    <div class="sec-title">2. Catatan</div>
    <p class="text-justify">Berdasarkan reviu dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahun }} terdapat catatan sebagai berikut:</p>
    <div class="indent">
        <div class="sec-title">a. Format</div>
        <div class="indent text-justify">
            <b>{{ $format['judul'] }}</b>
            {!! $catatanHtml($format['kode'], '<div class="note">' . $format['catatan'] . '</div>') !!}
        </div>
        <table class="bordered" style="margin-left:15px;width:calc(100% - 15px)">
            <thead>
                <tr>
                    <th style="width:10%">BAB</th>
                    <th style="width:45%">Lampiran Permendagri No. 86 tahun 2017</th>
                    <th style="width:45%">Rankir RKPD tahun {{ $tahun }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($formatRows as $r)
                    @php $b = !empty($r[3]); @endphp
                    <tr>
                        <td>{{ $r[0] }}</td>
                        <td>{!! $b ? '<b>'.e($r[1]).'</b>' : e($r[1]) !!}</td>
                        <td>{!! $b ? '<b>'.e($r[2]).'</b>' : e($r[2]) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="sec-title">b. Substansi</div>
        @foreach($substansi as $n => $u)
            <div class="item indent text-justify">
                <b>{{ $n + 1 }}) {{ $u['judul'] }}</b>

                @if(!empty($u['sub']))
                    <ol type="a">
                        @foreach($u['sub'] as $s)
                            <li><b>{{ $s['judul'] }}</b>{!! $catatanHtml($s['kode'], $s['catatan'] ?? '') !!}</li>
                        @endforeach
                    </ol>

                @elseif(!empty($u['tabel']))
                    @php $cats = $getCatatans($u['kode'])->values(); @endphp
                    <table class="bordered">
                        <thead><tr><th style="width:8%">NO</th><th>CATATAN</th></tr></thead>
                        <tbody>
                        @if($cats->isNotEmpty())
                            @foreach($cats as $i => $cat)
                                <tr>
                                    <td class="c">{{ $i + 1 }}.</td>
                                    <td>
                                        @if($cat->referensi_bagian)<b>{{ $cat->referensi_bagian }}</b><br>
                                        @elseif($cat->kategori)<b>{{ $cat->kategori->nama }}</b><br>@endif
                                        {!! $cat->isi !!}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @php $babNow = null; $no = 0; @endphp
                            @foreach($u['tabel'] as $r)
                                @if($r['bab'] !== $babNow)
                                    @php $babNow = $r['bab']; $no = 0; @endphp
                                    <tr><td colspan="2" class="grp">{{ $babNow }}</td></tr>
                                @endif
                                @php $no++; @endphp
                                <tr><td class="c">{{ $no }}.</td><td><b>{{ $r['ref'] }}</b><br>{!! $r['catatan'] !!}</td></tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>

                @else
                    {!! $catatanHtml($u['kode'], $u['catatan'] ?? '') !!}
                @endif

                {{-- Tabel konsistensi RPJMD vs RKPD tepat di bawah catatan S8 --}}
                @if($u['kode'] === 'RKPD.S8')
                    <table class="bordered" style="font-size:8pt">
                        <thead>
                            <tr>
                                <th rowspan="3" style="width:20%">Misi/Tujuan/Sasaran</th>
                                <th colspan="3">Rankir RPJMD tahun 2025-2029</th>
                                <th colspan="3">Rankir RKPD tahun {{ $tahun }}</th>
                            </tr>
                            <tr>
                                <th colspan="3">Tabel 3.6</th>
                                <th colspan="3">Tabel 3.9 — Teknik Perumusan Program Prioritas Pembangunan Daerah</th>
                            </tr>
                            <tr>
                                <th>Indikator Tujuan/Sasaran</th><th>Satuan</th><th>Target {{ $tahun }}</th>
                                <th>Indikator Tujuan/Sasaran</th><th>Satuan</th><th>Target {{ $tahun }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($konsistensiRows as $row)
                            @if($row[0] === 'misi')
                                <tr><td colspan="7" class="misi">{{ $row[1] }}</td></tr>
                            @else
                                @php $beda = trim(strip_tags($row[3])) !== trim(strip_tags($row[6])); @endphp
                                <tr>
                                    <td class="{{ $row[0] === null ? 'cont' : '' }}">{{ $row[0] }}</td>
                                    <td>{!! $row[1] !!}</td>
                                    <td class="c">{!! $row[2] !!}</td>
                                    <td class="c">{!! $row[3] !!}</td>
                                    <td>{!! $row[4] !!}</td>
                                    <td class="c">{!! $row[5] !!}</td>
                                    <td class="c {{ $beda ? 'diff' : '' }}">{!! $row[6] !!}</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach
    </div>

    {{-- 3. Rekomendasi --}}
    <div class="sec-title">3. Rekomendasi</div>
    <p class="text-justify">Berdasarkan hal tersebut, demi tersusunnya dokumen RKPD Kabupaten Rembang tahun {{ $tahun }} yang andal, akurat dan sebagai pedoman dalam penyusunan dokumen KUA-PPAS tahun {{ $tahun }} direkomendasikan beberapa hal sebagai berikut:</p>
    <div class="indent">
        <div class="sec-title">a. Format</div>
        <div class="indent text-justify">
            <b>{{ $format['judul'] }}</b>
            {!! $rekomHtml($format['kode'], '<div class="note">' . $format['rekom'] . '</div>') !!}
        </div>

        <div class="sec-title">b. Substansi</div>
        @foreach($rekomItems as $ri)
            <div class="item indent text-justify">
                <b>{{ $ri['n'] }}) {{ $ri['u']['judul'] }}</b>

                @if($ri['type'] === 'single')
                    {!! $ri['h'] !!}

                @elseif($ri['type'] === 'sub')
                    @foreach($ri['subs'] as $s)
                        <div class="sub-rekom"><b>{{ $s['l'] }}. {{ $s['judul'] }}</b>{!! $s['h'] !!}</div>
                    @endforeach

                @else {{-- tabel catatan | rekomendasi --}}
                    @php $rekoms = $getRekomendasis($ri['u']['kode'])->values(); @endphp
                    <table class="bordered">
                        <thead><tr><th style="width:8%">NO</th><th style="width:46%">CATATAN</th><th style="width:46%">REKOMENDASI</th></tr></thead>
                        <tbody>
                        @if($rekoms->isNotEmpty())
                            @foreach($rekoms as $i => $r)
                                <tr>
                                    <td class="c">{{ $i + 1 }}.</td>
                                    <td>{!! $r->lhrCatatan?->isi ?? '-' !!}</td>
                                    <td>{!! $r->uraian_rekom !!}</td>
                                </tr>
                            @endforeach
                        @else
                            @php $babNow = null; $no = 0; @endphp
                            @foreach($ri['u']['tabel'] as $r)
                                @if($r['bab'] !== $babNow)
                                    @php $babNow = $r['bab']; $no = 0; @endphp
                                    <tr><td colspan="3" class="grp">{{ $babNow }}</td></tr>
                                @endif
                                @php $no++; @endphp
                                <tr>
                                    <td class="c">{{ $no }}.</td>
                                    <td><b>{{ $r['ref'] }}</b><br>{!! $r['catatan'] !!}</td>
                                    <td>{!! $r['rekom'] !!}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach
    </div>

    {{-- J. Apresiasi + TTD --}}
    <div class="nobreak">
        <h4>J. Apresiasi</h4>
        <p class="text-justify">Demikian disampaikan hasil reviu atas rancangan akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {{ $tahun }}. Kami menyampaikan terima kasih atas bantuan dan kerjasama dari seluruh pejabat/pegawai pada Badan Perencanaan Pembangunan Daerah Kabupaten Rembang atas kesediaannya memberikan data/dokumen yang diperlukan, sehingga dapat mendukung terlaksananya kegiatan reviu Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) tahun {{ $tahun }}. Kami mengharapkan agar Saudara beserta seluruh jajaran untuk menindaklanjuti rekomendasi yang telah kami sampaikan.</p>

        <table class="ttd">
            <tr>
                <td style="width:50%"></td>
                <td style="width:50%;text-align:center">
                    <p style="margin-bottom:60px">INSPEKTUR DAERAH<br>KABUPATEN REMBANG</p>
                    <p style="font-weight:bold;text-decoration:underline;margin:0">{{ $signer?->name ?? $defaultTeam['penanggung_jawab']['nama'] }}</p>
                    <p style="margin:0">Pembina Utama Muda</p>
                    <p style="margin:0">NIP. {{ $signer?->nip ?? $defaultTeam['penanggung_jawab']['nip'] }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- Lampiran dokumen pendukung --}}
    @if($attList && $attList->isNotEmpty())
        <div class="new-page">
            <table class="plain" style="border-bottom:2px solid #1e3a8a;margin-bottom:12px">
                <tr>
                    <td style="width:50px;vertical-align:middle"><img src="{{ $logoSrc }}" alt="Logo" style="height:42px"></td>
                    <td style="vertical-align:middle;padding-left:10px">
                        <div style="font-size:11pt;font-weight:bold;color:#1e3a8a;text-transform:uppercase">INSPEKTORAT DAERAH KABUPATEN REMBANG</div>
                        <div style="font-size:8.5pt;color:#475569">Lampiran Dokumen Pendukung Hasil Reviu RKPD Tahun {{ $tahun }}</div>
                    </td>
                </tr>
            </table>
            <h3 class="text-center" style="font-size:11pt;text-transform:uppercase">Daftar Lampiran Dokumen Pendukung</h3>
            <p class="text-justify">Berikut adalah daftar berkas dan dokumen pendukung perencanaan yang dihimpun, diverifikasi, dan ditelaah oleh Tim Reviu Inspektorat Daerah Kabupaten Rembang selama pelaksanaan reviu atas Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahun }}:</p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:5%">No</th>
                        <th style="width:38%;text-align:left">Nama Dokumen / Berkas</th>
                        <th style="width:32%;text-align:left">Keterangan / Uraian</th>
                        <th style="width:12%">Format</th>
                        <th style="width:13%;text-align:right">Ukuran</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($attList as $i => $att)
                    <tr style="background:{{ $i % 2 ? '#f8fafc' : '#fff' }}">
                        <td class="c">{{ $i + 1 }}</td>
                        <td><strong>{{ $att->file_name }}</strong></td>
                        <td>{{ $att->keterangan ?: '-' }}</td>
                        <td class="c"><span class="mono">{{ strtoupper(pathinfo($att->file_name, PATHINFO_EXTENSION) ?: 'BERKAS') }}</span></td>
                        <td class="text-right">{{ $att->file_size_formatted }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p style="font-size:8pt;color:#64748b;font-style:italic">* Seluruh berkas lampiran di atas tersimpan secara aman dan terenkripsi pada repositori arsip digital Sistem Informasi Pengawasan Terpadu dan Akuntabel (SIAPTUNTAS) Inspektorat Daerah Kabupaten Rembang.</p>
        </div>
    @endif
</main>

{{-- ==================== PENUTUP ==================== --}}
<section class="bleed">
    <div style="padding-top:60mm">
        <img src="{{ $logoSrc }}" alt="Logo Rembang" style="width:150px;margin-bottom:20px">
        <h2 style="font-size:18pt;margin:0 0 5px">INSPEKTORAT DAERAH</h2>
        <h3 style="font-size:16pt;margin:0">KABUPATEN REMBANG</h3>
        <p style="margin-top:40px;font-weight:bold">{{ $tahunTerbit }}</p>
    </div>
</section>

@if($showBackCover)
<section class="bleed"></section>
@endif

</body>
</html>