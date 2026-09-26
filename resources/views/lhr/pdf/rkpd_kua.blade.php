<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $lhr->tahun_anggaran ?? 2027 }}</title>
    @if(!empty($isPdf))
    <style>
        @page {
            size: 210mm 297mm;
            margin: 15mm 15mm 15mm 20mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #000;
            font-size: 9.5pt;
            line-height: 1.35;
        }
        .preview-toolbar {
            display: none !important;
        }
        .page {
            width: 100%;
            page-break-after: always;
            clear: both;
            padding: 0;
            margin: 0;
            min-height: auto;
            box-shadow: none;
            background: transparent;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        .cover-page {
            margin: -15mm -15mm -15mm -20mm;
            padding: 25mm 20mm 20mm 20mm;
            background-color: #f7b267;
            text-align: center;
            width: 210mm;
        }
        .page-footer {
            margin-top: 15px;
            padding-top: 4px;
            border-top: 1px solid #ccc;
            font-size: 8pt;
            font-style: italic;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            font-style: italic;
        }
        .footer-table td {
            padding: 0;
            border: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 8.5pt;
        }
        table.bordered, table.bordered th, table.bordered td {
            border: 1px solid #000;
        }
        table th, table td {
            padding: 3px 5px;
            vertical-align: top;
        }
        table th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .header-kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        .header-kop h3 { margin: 0; font-size: 11pt; }
        .header-kop h2 { margin: 0; font-size: 13pt; }
        .header-kop p { margin: 1px 0; font-size: 8pt; }
        .cover-logo { width: 100px; margin: 0 auto 10px; display: block; }
        .cover-header { font-weight: bold; font-size: 13pt; }
        .cover-title { font-size: 13.5pt; font-weight: bold; margin: 20px 0 15px; line-height: 1.3; }
        .cover-meta { text-align: left; margin: 20px auto; width: 85%; font-weight: bold; font-size: 10pt; }
        .surat-meta td { padding: 1px 4px; font-size: 9pt; }
        ol, ul { margin-top: 0; margin-bottom: 5px; padding-left: 18px; }
        li { margin-bottom: 3px; text-align: justify; }
        p { margin-top: 4px; margin-bottom: 4px; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
    </style>
    @else
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #525659;
            color: #000;
            font-size: 11pt;
            line-height: 1.4;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 15mm 20mm 20mm;
            margin: 10px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            position: relative;
            page-break-after: always;
            break-after: page;
        }
        .page:last-child {
            page-break-after: auto;
            break-after: auto;
        }
        @media print {
            body {
                background: none;
            }
            .page {
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
        .preview-toolbar {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: #1e293b;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .preview-toolbar button, .preview-toolbar a {
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: bold;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-pdf { background: #dc2626; color: #fff; margin-left: 8px; }
        .btn-pdf:hover { background: #b91c1c; }
        .btn-back { background: #475569; color: #fff; margin-right: 8px; }
        .btn-back:hover { background: #334155; }
        .page-footer {
            position: absolute;
            bottom: 15mm;
            left: 20mm;
            right: 15mm;
            font-size: 9pt;
            font-style: italic;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 9pt;
            font-style: italic;
        }
        .footer-table td {
            padding: 0;
            border: none;
        }
        .cover-page {
            background-color: #f7b267;
            text-align: center;
            padding: 25mm 20mm 20mm 20mm;
        }
        .cover-logo {
            width: 120px;
            margin: 0 auto 10px;
            display: block;
        }
        .cover-header {
            font-weight: bold;
            font-size: 14pt;
        }
        .cover-title {
            font-size: 15pt;
            font-weight: bold;
            margin: 25px 0 20px;
            line-height: 1.3;
        }
        .cover-meta {
            text-align: left;
            margin: 25px auto;
            width: 80%;
            font-weight: bold;
        }
        h1, h2, h3, h4 { margin-top: 0; color: #000; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .header-kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .header-kop h3 { margin: 0; font-size: 12pt; }
        .header-kop h2 { margin: 0; font-size: 14pt; }
        .header-kop p { margin: 2px 0; font-size: 8.5pt; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9.5pt;
        }
        table.bordered, table.bordered th, table.bordered td {
            border: 1px solid #000;
        }
        table th, table td {
            padding: 5px;
            vertical-align: top;
        }
        table th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .surat-meta td { padding: 2px 5px; }
        ol, ul { margin-top: 0; padding-left: 20px; }
        li { margin-bottom: 5px; text-align: justify; }
    </style>
    @endif
</head>
<body>

    @php
        $logoPath = public_path('images/logos/rembang.png');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : 'https://upload.wikimedia.org/wikipedia/commons/3/30/Lambang_Kabupaten_Rembang.png';
            
        $nomorLhr = $lhr->nomor_lhr ?: '700/026/REVIU/INSP/2026';
        $tanggalLhr = $lhr->tanggal_lhr 
            ? \Carbon\Carbon::parse($lhr->tanggal_lhr)->locale('id')->isoFormat('D MMMM Y') 
            : '11 Mei 2026';
        $tahunAnggaran = $lhr->tahun_anggaran ?: 2027;
        $tahunTerbit = $lhr->tanggal_lhr ? \Carbon\Carbon::parse($lhr->tanggal_lhr)->format('Y') : '2026';
        $namaUnit = $unit->nama_unit ?? 'BADAN PERENCANAAN PEMBANGUNAN DAERAH KABUPATEN REMBANG';
        $nomorSpt = $assignment->nomor_spt ?? '000.1.2.3/020/INSP/2026';
        $tanggalSpt = $assignment->tanggal_mulai 
            ? \Carbon\Carbon::parse($assignment->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y') 
            : '29 April 2026';
        $nomorPermohonan = $lhr->permohonan_nomor ?: '000.7/250/2026';
        $tanggalPermohonan = $lhr->permohonan_tanggal 
            ? \Carbon\Carbon::parse($lhr->permohonan_tanggal)->locale('id')->isoFormat('D MMMM Y') 
            : '28 April 2026';

        $pKeyed = ($pengujians ?? $lhr->pengujians)->keyBy('kode');

        $getSimpulan = function($kode, $fallback) use ($pKeyed) {
            $val = $pKeyed->get($kode)?->teks_simpulan;
            return !empty(trim($val ?? '')) ? $val : $fallback;
        };

        $getCatatans = function($kode) use ($pKeyed) {
            $p = $pKeyed->get($kode);
            if (!$p) return collect();
            $cats = $p->catatans ?? collect();
            if ($p->relationLoaded('children') && $p->children && $p->children->isNotEmpty()) {
                foreach ($p->children as $child) {
                    $cats = $cats->concat($child->catatans ?? collect());
                }
            }
            return $cats;
        };

        $getRekomendasis = function($kode) use ($pKeyed) {
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
    @endphp

    @if(empty($isPdf))
    <!-- Screen Preview Toolbar -->
    <div class="preview-toolbar no-print">
        <div>
            <a href="{{ route('lhr.show', $lhr->uuid) }}" class="btn-back">
                &larr; Kembali ke Ruang Kerja LHR
            </a>
            <span style="font-size:13px; font-weight:bold; color:#cbd5e1;">
                Dokumen LHR: {{ $nomorLhr }} | Mode: Template Resmi 26 Halaman (Rembang)
            </span>
        </div>
        <div>
            <button type="button" class="btn-print" onclick="window.print()">
                &#128438; Cetak / Print Browser
            </button>
            <a href="{{ route('lhr.pdf', $lhr->uuid) }}" class="btn-pdf">
                &#128190; Unduh PDF Asli
            </a>
            <a href="{{ route('lhr.word', $lhr->uuid) }}" class="btn-pdf" style="background:#2563eb; color:#fff; text-decoration:none; margin-left:6px;">
                &#128196; Unduh Word (.docx)
            </a>
        </div>
    </div>
    @endif

    <!-- ==================== HALAMAN 1: COVER ==================== -->
    <section class="page cover-page">
        <div>
            <div class="cover-header">
                <p style="margin:0;">KABUPATEN REMBANG</p>
                <p style="margin:0;">INSPEKTORAT DAERAH KABUPATEN REMBANG</p>
            </div>
            <div style="margin-top: 25px;">
                <img src="{{ $logoSrc }}" alt="Logo Rembang" class="cover-logo">
                <p style="font-weight:bold; font-size:10pt; margin:5px 0;">BerAKHLAK</p>
                <p style="font-size:8pt; margin:0;">bangga melayani bangsa</p>
            </div>
            <div class="cover-title">
                LAPORAN HASIL REVIU<br>
                DOKUMEN RANCANGAN AKHIR<br>
                RENCANA KERJA PEMERINTAH DAERAH (RKPD) KABUPATEN REMBANG<br>
                TAHUN {{ $tahunAnggaran }}<br>
                <span style="font-size:12pt; text-transform:uppercase;">{{ $namaUnit }}</span>
            </div>
        </div>

        <div class="cover-meta">
            <table style="width:100%; border:none;">
                <tr>
                    <td style="width:100px; border:none; padding:3px;">Nomor</td>
                    <td style="border:none; padding:3px;">: {{ $nomorLhr }}</td>
                </tr>
                <tr>
                    <td style="border:none; padding:3px;">Tanggal</td>
                    <td style="border:none; padding:3px;">: {{ $tanggalLhr }}</td>
                </tr>
            </table>
        </div>

        <div style="font-weight:bold; font-size:14pt; margin-top: 40px;">
            {{ $tahunTerbit }}
        </div>
    </section>

    <!-- ==================== HALAMAN 2: SURAT PENGANTAR ==================== -->
    <section class="page">
        <div class="header-kop">
            <h3>PEMERINTAH KABUPATEN REMBANG</h3>
            <h2>INSPEKTORAT DAERAH</h2>
            <p>JL. RAYA REMBANG LASEM KM. 1.1 REMBANG TELP. (0295) 691320</p>
            <p>Fax. (0295) 693525 Rembang - 59219 Email: Inspektorat@rembangkab.go.id</p>
        </div>

        <table class="surat-meta" style="width:100%; border:none;">
            <tr>
                <td style="width:15%; border:none;">Nomor</td>
                <td style="width:2%; border:none;">:</td>
                <td style="width:48%; border:none;">{{ $nomorLhr }}</td>
                <td style="width:35%; border:none;">Rembang, {{ $tanggalLhr }}</td>
            </tr>
            <tr>
                <td style="border:none;">Lampiran</td>
                <td style="border:none;">:</td>
                <td style="border:none;">-</td>
                <td style="border:none;"></td>
            </tr>
            <tr>
                <td style="border:none;">Perihal</td>
                <td style="border:none;">:</td>
                <td style="border:none;">Hasil Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                <td style="border:none;">Kepada Yth.<br>Kepala {{ $namaUnit }}<br>di - REMBANG</td>
            </tr>
        </table>

        <br>
        <div class="text-justify">
            <p>Inspektorat Daerah Kabupaten Rembang sebagai Aparat Pengawasan Intern Pemerintah telah melakukan reviu atas Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahunAnggaran }} berupa pengujian terbatas terhadap substansi yang termuat pada Rancangan Akhir Rencana Kerja Pemerintah Daerah. Reviu ditujukan untuk membantu terlaksananya penyusunan dan penyajikan dokumen Rencana Kerja Pemerintah Daerah (RKPD) serta memberikan keyakinan terbatas mengenai kepatuhan dan keabsahan informasi yang disajikan telah sesuai dengan kaidah-kaidah perencanaan, sehingga dapat menghasilkan dokumen Rencana Kerja Pemerintah Daerah (RKPD) yang berkualitas.</p>
            <p>Dalam pelaksanaan reviu, kami telah melakukan serangkaian aktivitas untuk menguji proses penyusunan atas penyajian dan substansi dalam dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Tahun {{ $tahunAnggaran }} dengan dokumen pendukung lainnya, permintaan keterangan mengenai proses penyusunan untuk mengetahui hubungan dan hal-hal yang tidak terdapat dalam dokumen penyajian. Bersama ini kami sampaikan Laporan Hasil Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahunAnggaran }}. Uraian rinci atas pelaksanaan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahunAnggaran }} adalah sebagai berikut:</p>
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">1</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 3: PERNYATAAN REVIU & DASAR HUKUM (1) ==================== -->
    <section class="page">
        <h4>A. Pernyataan Reviu</h4>
        <p class="text-justify">Kami telah mereviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahunAnggaran }} dengan mempedomani Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 3 Tahun 2026 tentang Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah. Semua informasi yang dimuat dalam laporan adalah penyajian manajemen Badan Perencanaan Pembangunan Daerah Kabupaten Rembang dan Aplikasi Sistem Informasi Pemerintahan Daerah Kementerian Dalam Negeri Republik Indonesia.</p>
        <p class="text-justify">Reviu dilaksanakan sesuai dengan Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 3 Tahun 2026 tentang Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah, yaitu tertera dalam:</p>
        
        <table style="width:100%; border:none;">
            <tr>
                <td style="width:120px; font-weight:bold; border:none;">Pasal 2 ayat (1)</td>
                <td style="border:none;">: APIP Daerah melaksanakan Reviu terhadap: a. dokumen Perencanaan Pembangunan Daerah; dan b. dokumen Keuangan Daerah;</td>
            </tr>
            <tr>
                <td style="font-weight:bold; border:none;">Pasal 3 ayat (1)</td>
                <td style="border:none;">: Dokumen Perencanaan Pembangunan Daerah sebagaimana dimaksud dalam Pasal 2 ayat (1) huruf a, terdiri atas: a. rancangan akhir RPJMD; b. rancangan akhir Renstra Perangkat Daerah; dan c. rancangan akhir RKPD.</td>
            </tr>
            <tr>
                <td style="font-weight:bold; border:none;">Pasal 8</td>
                <td style="border:none;">: Ketentuan lebih lanjut mengenai pedoman Reviu dokumen Perencanaan Pembangunan Daerah dan dokumen Keuangan Daerah tercantum dalam Lampiran yang merupakan bagian tidak terpisahkan dari Peraturan Menteri ini.</td>
            </tr>
        </table>
        
        <p class="text-justify">Reviu mempunyai lingkup yang jauh lebih sempit dibandingkan dengan lingkup audit yang dilakukan sesuai dengan peraturan terkait dengan tujuan audit.</p>

        <h4>B. Dasar Hukum Reviu</h4>
        <ol class="text-justify">
            <li>Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang Rencana Pembangunan Jangka Panjang Daerah dan Rencana Pembangunan Jangka Menengah Daerah, Serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah, dan Rencana Kerja Pemerintah Daerah;</li>
            <li>Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 10 Tahun 2018 tentang Reviu atas Dokumen Perencanaan Pembangunan dan Anggaran Daerah Tahunan;</li>
            <li>Peraturan Menteri Dalam Negeri Nomor 14 Tahun 2025 tentang Pedoman Penyusunan Anggaran Pendapatan dan Belanja Daerah Tahun Anggaran 2026;</li>
        </ol>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">2</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 4: DASAR HUKUM (2) & TIM PENGAWASAN ==================== -->
    <section class="page">
        <ol start="4" class="text-justify">
            <li>Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 3 Tahun 2026 tentang Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah;</li>
            <li>Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Perubahan Kedua atas Peraturan Daerah Kabupaten Rembang Nomor 5 Tahun 2016 tentang Pembentukan dan Susunan Perangkat daerah Kabupaten Rembang;</li>
            <li>Peraturan Bupati Rembang Nomor 54 Tahun 2021 tentang Kedudukan Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Inspektorat Kabupaten Rembang;</li>
            <li>Peraturan Bupati Rembang Nomor 4 Tahun 2026 tentang Perubahan atas Peraturan Bupati Rembang Nomor 110 Tahun 2025 tentang Penjabaran APBD Tahun Anggaran 2026;</li>
            <li>Surat dari Inspektorat Jenderal Kementerian Dalam Negeri Republik Indonesia Nomor: 700.1.1.1/1642/IJ perihal Reviu Rancangan Akhir Rencana Kerja Pemerintah Daerah Tahun Anggaran {{ $tahunAnggaran }};</li>
            <li>Keputusan Bupati Rembang Nomor: 100.3.3.2/0282/2026 tentang Program Kerja Pengawasan Tahunan Berbasis Risiko Inspektorat Daerah Kabupaten Rembang Tahun 2026;</li>
            <li>Surat dari Kepala Badan Perencanaan Pembangunan Daerah Kabupaten Rembang Nomor: {{ $nomorPermohonan }} tanggal {{ $tanggalPermohonan }} tentang Permohonan Reviu Dokumen Rancangan Akhir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }};</li>
            <li>Surat Perintah Tugas Inspektur Daerah Kabupaten Rembang Nomor: {{ $nomorSpt }} tanggal {{ $tanggalSpt }} dengan susunan Tim:</li>
        </ol>

        <div style="margin-left: 20px;">
            <p style="font-weight:bold; margin-bottom:2px;">➤ PENANGGUNGJAWAB</p>
            <table style="width:100%; border:none; margin-top:0;">
                <tr><td style="width:100px; border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: 19741128 199903 2 003</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: Inspektur Daerah</td></tr>
            </table>

            <p style="font-weight:bold; margin-bottom:2px;">➤ PENGENDALI TEKNIS</p>
            <table style="width:100%; border:none; margin-top:0;">
                <tr><td style="width:100px; border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: {{ $team['pengendali_teknis']['nama'] !== '-' ? $team['pengendali_teknis']['nama'] : 'SRI SURYANDARI, S.Sos.' }}</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: {{ $team['pengendali_teknis']['nip'] !== '-' ? $team['pengendali_teknis']['nip'] : '19690827 199603 2 005' }}</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: {{ $team['pengendali_teknis']['jabatan'] ?: 'Inspektur Pembantu I' }}</td></tr>
            </table>

            <p style="font-weight:bold; margin-bottom:2px;">➤ KETUA TIM</p>
            <table style="width:100%; border:none; margin-top:0;">
                <tr><td style="width:100px; border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: {{ $team['ketua_tim']['nama'] !== '-' ? $team['ketua_tim']['nama'] : 'SITI AMINAH, S.E.' }}</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: {{ $team['ketua_tim']['nip'] !== '-' ? $team['ketua_tim']['nip'] : '19920714 201902 2 002' }}</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: {{ $team['ketua_tim']['jabatan'] ?: 'Auditor Ahli Pertama' }}</td></tr>
            </table>
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">3</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 5: ANGGOTA TIM & TUJUAN / RUANG LINGKUP ==================== -->
    <section class="page">
        <p style="font-weight:bold; margin-bottom:2px; margin-left:20px;">➤ ANGGOTA TIM</p>
        <table style="width:95%; margin-left:20px; border:none;">
            @if(!empty($team['anggota']) && count($team['anggota']) > 0)
                @foreach($team['anggota'] as $agt)
                <tr><td style="width:100px; border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: {{ $agt['nama'] }}</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: {{ $agt['nip'] }}</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: {{ $agt['jabatan'] }}</td></tr>
                @endforeach
            @else
                <tr><td style="width:100px; border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: PARIJAN, S.E., M.M.</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: 19680314 199103 1 014</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: Pengawas Pemerintahan Urusan Pemerintah Daerah Ahli Madya</td></tr>
                
                <tr><td style="border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: AGUNG TRI PRASTIYO, S.E.</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: 19820821 200501 1 006</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: Auditor Ahli Muda</td></tr>

                <tr><td style="border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: IKA PERTIWI, S.H.</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: 19860908 202203 2 002</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: Auditor Ahli Pertama</td></tr>

                <tr><td style="border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: TAUFIQUR RACHMAN, S.E.</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: 19940326 202203 1 016</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: Auditor Ahli Pertama</td></tr>

                <tr><td style="border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: SIHONO, S.I.P.</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: 19960703 202203 1 007</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: Pengawas Pemerintahan Urusan Pemerintah Daerah Ahli Pertama</td></tr>

                <tr><td style="border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: WAHYU ANJAR, S.Tr.I.P</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: 20000727 202409 2 001</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: Pengawas Pemerintahan Urusan Pemerintah Daerah Ahli Pertama</td></tr>

                <tr><td style="border:none; padding:2px;">Nama</td><td style="border:none; padding:2px;">: DEWI ZAHRO WAHYUNINGTYAS, S.T.</td></tr>
                <tr><td style="border:none; padding:2px;">NIP</td><td style="border:none; padding:2px;">: 20010524 202505 2 004</td></tr>
                <tr><td style="border:none; padding:2px;">Jabatan</td><td style="border:none; padding:2px;">: Calon Auditor</td></tr>
            @endif
        </table>

        <h4>C. Tujuan Reviu</h4>
        <p class="text-justify">Tujuan Reviu rancangan akhir Rencana Kerja Pemerintah Daerah (RKPD) yaitu untuk memberikan keyakinan terbatas bahwa Rencana Kerja Pemerintah Daerah telah disusun berpedoman kepada RPJMD, Rencana Kerja Pemerintah, Program Strategis Nasional, pedoman penyusunan RKPD dan kaidah-kaidah perencanaan pembangunan lainnya.</p>

        <h4>D. Ruang Lingkup Reviu</h4>
        <p class="text-justify">Ruang lingkup reviu adalah pengujian atas penyusunan Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahunAnggaran }} yang bersifat tahunan berupa Dokumen RKPD Tahun {{ $tahunAnggaran }}.</p>
        <p>Ruang lingkup reviu mencakup pengujian terbatas atas:</p>
        <p style="font-weight:bold; margin-bottom:0;">➤ FORMAT</p>
        <p style="margin-top:0; margin-left:15px;">Kesesuaian Format Rancangan Akhir RKPD.</p>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">4</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 6: RUANG LINGKUP SUBSTANSI, OBJEK & GAMBARAN UMUM ==================== -->
    <section class="page">
        <p style="font-weight:bold; margin-bottom:0;">➤ SUBSTANSI</p>
        <ol class="text-justify">
            <li>Pengujian atas Kelengkapan Dokumen Pendukung Rancangan Akhir RKPD;</li>
            <li>Pengujian atas Analisis Isi BAB Rancangan Akhir RKPD;</li>
            <li>Pengujian atas Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan dalam RKPD dan RPJMD;</li>
            <li>Kesesuaian Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rencana Pembangunan Jangka Menengah Daerah (RPJMD) dengan Rancangan Akhir RKPD;</li>
            <li>Kewajaran proyeksi pendanaan pada rancangan akhir RKPD;</li>
            <li>Kesesuaian Informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional
                <ol type="a">
                    <li>Kesesuaian urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil rapat koordinasi teknis pembangunan (Rakortekbang) dengan urusan pada rancangan akhir RKPD;</li>
                    <li>Kesesuaian penetapan target Indikator Makro Pembangunan pada rancangan akhir RKPD berdasarkan Rencana Kerja Pemerintah (RKP);</li>
                    <li>Kesesuaian dan ketepatan dukungan pelaksanaan ProSN pada rancangan akhir RKPD;</li>
                    <li>Memastikan Program, Kegiatan dan Sub Kegiatan Standar Pelayanan Minimal (SPM) tersedia pada rancangan akhir RKPD;</li>
                    <li>Memastikan kesesuaian dukungan Sub Kegiatan Tematik Pembangunan pada rancangan akhir RKPD;</li>
                </ol>
            </li>
            <li>Kesesuaian Informasi pada rancangan akhir RKPD dengan tata cara dan kaidah Perencanaan Pembangunan Daerah lainnya (kesesuaian pengusulan pokok-pokok pikiran Dewan Perwakilan Rakyat Daerah (DPRD) sesuai dengan ketentuan peraturan perundang-undangan);</li>
            <li>Pengujian atas Konsistensi Pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran dalam Rancangan Akhir RKPD dan RPJMD.</li>
        </ol>

        <h4>E. Objek Reviu</h4>
        <p class="text-justify">Objek reviu adalah Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {{ $tahunAnggaran }}.</p>

        <h4>F. Pelaksanaan Reviu</h4>
        <p class="text-justify">Pelaksanaan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {{ $tahunAnggaran }} dilaksanakan pada mulai tanggal 30 April 2026 sampai dengan selesai.</p>

        <h4>G. Metodologi Reviu</h4>
        <p class="text-justify">Reviu dilaksanakan dengan menggunakan metodologi mengumpulkan dan melakukan penelaahan atas Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {{ $tahunAnggaran }}, konfirmasi, analisis dan prosedur lainnya terkait proses penyusunan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {{ $tahunAnggaran }}.</p>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">5</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 7: GAMBARAN UMUM & URAIAN HASIL REVIU (SIMPULAN 1) ==================== -->
    <section class="page">
        <h4>H. Gambaran Umum</h4>
        <ol class="text-justify">
            <li>Pemerintah Kabupaten Rembang telah menyusun Peraturan Daerah Nomor 3 Tahun 2024 tentang Rencana Pembangunan Jangka Panjang Daerah Kabupaten Rembang Tahun 2025-2045;</li>
            <li>Pemerintah Kabupaten Rembang telah menyusun Peraturan Daerah Nomor 7 tahun 2025 tentang Rencana Pembangunan Jangka Menengah Daerah Kabupaten Rembang tahun 2025-2029 sebagai acuan dalam menentukan arah kebijakan pembangunan setiap tahunnya dengan membuat Rancangan Rencana Kerja Pemerintah Daerah Kabupaten Rembang;</li>
            <li>Sebagai penjabaran Rencana Pembangunan Jangka Menengah Daerah Kabupaten Rembang 2025-2029, kedudukan RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }} dalam pelaksanaan pembangunan di Kabupaten Rembang adalah menjembatani antara perencanaan strategis jangka menengah dengan perencanaan dan penganggaran tahunan yang memuat rancangan kerangka ekonomi daerah, prioritas pembangunan daerah, rencana kerja dan pendanaan yang disusun berdasarkan pada orientasi proses dengan pendekatan teknokratik, partisipatif, politis dan top-down, bottom-up, serta berdasarkan pada orientasi substansi dengan pendekatan secara holistik, integratif, tematik dan spasial, yang selanjutnya sebagai pedoman dalam penyusunan Kebijakan Umum Anggaran serta penentuan Prioritas Plafon Anggaran Sementara yang menjadi dasar penyusunan Rancangan Anggaran Pendapatan dan Belanja Daerah (RAPBD) Tahun {{ $tahunAnggaran }};</li>
            <li>Inspektorat Daerah Kabupaten Rembang berdasarkan kewenangannya melakukan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {{ $tahunAnggaran }}.</li>
        </ol>

        <h4>I. Uraian Hasil Reviu</h4>
        <p class="text-justify">Hasil pelaksanaan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {{ $tahunAnggaran }} dapat dijelaskan sebagai berikut:</p>
        <p style="font-weight:bold; margin-bottom:2px;">1. Simpulan</p>
        
        <div style="margin-left: 15px;">
            <p style="font-weight:bold; margin-bottom:2px;">a. Format</p>
            <p style="margin-top:0;" class="text-justify"><b>Kesesuaian Format Rancangan Akhir RKPD</b><br>
            {{ $getSimpulan('RKPD.F', "Format/sistematika dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} kurang sesuai dengan yang diatur dalam Lampiran Peraturan Menteri Dalam Negeri Nomor 86 tahun 2017 (halaman 384).") }}</p>

            <p style="font-weight:bold; margin-bottom:2px;">b. Substansi</p>
            <p style="margin-top:0;" class="text-justify"><b>1) Pengujian atas Kelengkapan Dokumen Pendukung RKPD</b><br>
            {{ $getSimpulan('RKPD.S1', "Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} telah didukung dengan dokumen perencanaan yang memadai yaitu:") }}</p>
            <ol type="a" style="margin-top:0;">
                <li>RPJMN tahun 2025-2029</li>
                <li>RPJMD Prov. Jateng tahun 2025-2029</li>
                <li>Rancangan RKPD Prov. Jateng tahun {{ $tahunAnggaran }}</li>
                <li>RPJMD Kab. Rembang tahun 2025-2029</li>
                <li>Renstra Perangkat Daerah tahun 2025-2029</li>
                <li>Hasil Evaluasi RKPD tahun 2025</li>
            </ol>
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">6</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 8: SIMPULAN SUBSTANSI (2) ==================== -->
    <section class="page">
        <div style="margin-left: 30px;">
            <ol type="a" start="7" style="margin-top:0;">
                <li>Rancangan Akhir RKPD tahun {{ $tahunAnggaran }}</li>
                <li>Berita Acara FKP RKPD tahun {{ $tahunAnggaran }}</li>
                <li>Berita Acara Musrenbang RKPD tahun {{ $tahunAnggaran }}</li>
                <li>SK Tim Penyusun RKPD Kab. Rembang tahun {{ $tahunAnggaran }}</li>
                <li>Lampiran Rancangan Akhir RKPD tahun {{ $tahunAnggaran }} (Rekapitulasi Rumusan Rencana Program, Kegiatan dan Sub Kegiatan Perangkat Daerah Tahun {{ $tahunAnggaran }})</li>
            </ol>

            <p class="text-justify"><b>2) Pengujian atas Analisis Isi BAB Rancangan Akhir RKPD</b><br>
            {{ $getSimpulan('RKPD.S2', "Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran}, masih terdapat kekurangsesuaian data yang disajikan.") }}</p>

            <p class="text-justify"><b>3) Pengujian atas Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan dalam RKPD dan RPJMD</b><br>
            {{ $getSimpulan('RKPD.S3', "Rumusan Prioritas dan Sasaran Pembangunan dalam Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran} telah sesuai dengan Dokumen RPJMD Kabupaten Rembang tahun 2025-2029.") }}</p>

            <p class="text-justify"><b>4) Kesesuaian Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rencana Pembangunan Jangka Menengah Daerah (RPJMD) dengan rancangan akhir RKPD</b><br>
            {{ $getSimpulan('RKPD.S4', "Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran} belum sesuai dengan Rencana Pembangunan Jangka Menengah Daerah (RPJMD) Kabupaten Rembang tahun 2025-2029.") }}</p>

            <p class="text-justify"><b>5) Kewajaran proyeksi pendanaan pada rancangan akhir RKPD</b><br>
            {{ $getSimpulan('RKPD.S5', "Proyeksi pendanaan pada Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran} telah wajar.") }}</p>

            <p class="text-justify"><b>6) Kesesuaian Informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional</b></p>
            <ol type="a">
                <li><b>Kesesuaian urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil rapat koordinasi teknis pembangunan (Rakortekbang) dengan urusan pada rancangan akhir RKPD:</b> {{ $getSimpulan('RKPD.S6.a', "Urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil rapat koordinasi teknis pembangunan (Rakortekrenbang Nasional tahun 2026) pada Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {$tahunAnggaran}) Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} belum selaras.") }}</li>
                <li><b>Kesesuaian penetapan target Indikator Makro Pembangunan pada rancangan akhir RKPD berdasarkan Rencana Kerja Pemerintah (RKP):</b> {{ $getSimpulan('RKPD.S6.b', "Rencana Kerja Pemerintah (RKP) tahun {$tahunAnggaran} belum tersedia.") }}</li>
            </ol>
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">7</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 9: SIMPULAN SUBSTANSI (3) & CATATAN FORMAT ==================== -->
    <section class="page">
        <div style="margin-left: 45px;">
            <ol type="a" start="3">
                <li class="text-justify"><b>Kesesuaian dan ketepatan dukungan pelaksanaan ProSN pada rancangan akhir RKPD:</b> {{ $getSimpulan('RKPD.S6.c', "Program Strategis Nasional (ProSN) dalam dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran} belum seluruhnya terpetakan dalam Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {$tahunAnggaran}) Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran}.") }}</li>
                <li class="text-justify"><b>Memastikan Program, Kegiatan dan Sub Kegiatan Standar Pelayanan Minimal (SPM) tersedia pada rancangan akhir RKPD:</b> {{ $getSimpulan('RKPD.S6.d', "Program, kegiatan dan subkegiatan Standar Pelayanan Minimal (SPM) pada Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD), belum seluruhnya terpetakan dalam Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {$tahunAnggaran}) Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran}.") }}</li>
                <li class="text-justify"><b>Memastikan kesesuaian dukungan Sub Kegiatan Tematik Pembangunan pada rancangan akhir RKPD:</b> {{ $getSimpulan('RKPD.S6.e', "Sub Kegiatan Tematik Pembangunan pada Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD), belum seluruhnya terpetakan dalam Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {$tahunAnggaran}) Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran}.") }}</li>
            </ol>
        </div>

        <div style="margin-left: 30px;">
            <p class="text-justify"><b>7) Kesesuaian Informasi pada rancangan akhir RKPD dengan tata cara dan kaidah Perencanaan Pembangunan Daerah lainnya:</b> {{ $getSimpulan('RKPD.S7', "Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran} belum dilengkapi dengan rekapitulasi data pokok-pokok pikiran Dewan Perwakilan Rakyat Daerah (DPRD) Kabupaten Rembang tahun {$tahunAnggaran}.") }}</p>
            <p class="text-justify"><b>8) Pengujian atas Konsistensi Pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran dalam Rancangan Akhir RKPD dan RPJMD:</b> {{ $getSimpulan('RKPD.S8', "Target Kinerja Tujuan dan Sasaran dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} belum konsisten dengan Dokumen RPJMD Kabupaten Rembang Tahun 2025-2029.") }}</p>
        </div>

        <p style="font-weight:bold; margin-bottom:2px;">2. Catatan</p>
        <p style="margin-top:0;" class="text-justify">Berdasarkan reviu dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} terdapat catatan sebagai berikut:</p>
        <p style="font-weight:bold; margin-left:15px; margin-bottom:2px;">a. Format</p>
        <p style="margin-top:0; margin-left:15px;" class="text-justify"><b>Kesesuaian Format Rancangan Akhir RKPD</b><br>
        @php $catsF = $getCatatans('RKPD.F'); @endphp
        @if($catsF->isNotEmpty())
            @foreach($catsF as $cat)
                <div style="margin-top: 4px; margin-bottom: 8px;">
                    @if($cat->referensi_bagian)
                        <span style="font-weight:bold; background:#eee; padding:1px 4px; font-size:8.5pt;">{{ $cat->referensi_bagian }}</span><br>
                    @endif
                    {!! $cat->isi !!}
                </div>
            @endforeach
        @else
            Format/sistematika dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} kurang sesuai dengan yang diatur dalam Lampiran Peraturan Menteri Dalam Negeri Nomor 86 tahun 2017 (halaman 384), sebagai berikut:
        @endif
        </p>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">8</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 10: TABEL PERBANDINGAN FORMAT RKPD ==================== -->
    <section class="page">
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width:10%;">BAB</th>
                    <th style="width:45%;">Lampiran Permendagri No. 86 tahun 2017</th>
                    <th style="width:45%;">Rankir RKPD tahun {{ $tahunAnggaran }}</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>1.</td><td><b>BAB I PENDAHULUAN</b></td><td><b>BAB I PENDAHULUAN</b></td></tr>
                <tr><td>1.1.</td><td>Latar Belakang</td><td>Latar Belakang</td></tr>
                <tr><td>1.2.</td><td>Dasar Hukum Penyusunan</td><td>Dasar Hukum</td></tr>
                <tr><td>1.3.</td><td>Hubungan Antar Dokumen</td><td>Hubungan Antara Dokumen</td></tr>
                <tr><td>1.4.</td><td>Maksud dan Tujuan</td><td>Maksud dan Tujuan</td></tr>
                <tr><td>1.5.</td><td>Sistematika Dokumen RKPD</td><td>Sistematika Dokumen RKPD Tahun {{ $tahunAnggaran }}</td></tr>
                
                <tr><td>2.</td><td><b>BAB II GAMBARAN UMUM KONDISI DAERAH</b></td><td><b>BAB II GAMBARAN UMUM KONDISI DAERAH</b></td></tr>
                <tr><td>2.1.</td><td>Kondisi Umum Daerah</td><td>Kondisi Umum Daerah</td></tr>
                <tr><td>2.1.1.</td><td>Aspek Geografi dan Demografi</td><td>Aspek Geografi dan Demografi</td></tr>
                <tr><td>2.1.2.</td><td>Aspek Kesejahteraan Masyarakat</td><td>Aspek Kesejahteraan Masyarakat</td></tr>
                <tr><td>2.1.3.</td><td>Aspek Pelayanan Umum</td><td>Aspek Daya Saing Daerah</td></tr>
                <tr><td>2.1.4.</td><td>Aspek Daya Saing Daerah</td><td>Aspek Pelayanan Umum</td></tr>
                <tr><td>2.2.</td><td>Evaluasi Pelaksanaan Program dan Kegiatan RKPD sampai Tahun Berjalan dan Realisasi RPJMD</td><td>Evaluasi Pelaksanaan Kinerja RKPD sampai Tahun 2025 dan Realisasi RPJMD</td></tr>
                <tr><td>2.2.1</td><td>-</td><td>Hasil Evaluasi Indikator Makro Daerah Tahun 2021-2025</td></tr>
                <tr><td>2.2.2</td><td>-</td><td>Hasil Evaluasi Capaian Kinerja Tahun 2025</td></tr>
                <tr><td>2.3.</td><td>Permasalahan Pembangunan Daerah</td><td>Permasalahan Pembangunan Daerah</td></tr>
                <tr><td>2.3.1.</td><td>Permasalahan daerah yang berhubungan dengan prioritas dan sasaran pembangunan daerah</td><td>Permasalahan daerah yang berhubungan dengan prioritas dan sasaran pembangunan daerah</td></tr>
                <tr><td>2.3.2.</td><td>Identifikasi Permasalahan penyelenggaraan urusan pemerintah daerah</td><td>Identifikasi Permasalahan penyelenggaraan urusan pemerintah daerah</td></tr>
            </tbody>
        </table>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">9</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 11: TABEL PERBANDINGAN FORMAT RKPD (LANJUTAN) ==================== -->
    <section class="page">
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width:10%;">BAB</th>
                    <th style="width:45%;">Lampiran Permendagri No. 86 tahun 2017</th>
                    <th style="width:45%;">Rankir RKPD tahun {{ $tahunAnggaran }}</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>3.</td><td><b>BAB III KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH</b></td><td><b>BAB III KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH</b></td></tr>
                <tr><td>3.1.</td><td>Arah Kebijakan Ekonomi Daerah</td><td>Arah Kebijakan Ekonomi Daerah</td></tr>
                <tr><td>3.2.</td><td>Arah Kebijakan Keuangan Daerah</td><td>Arah Kebijakan Keuangan Daerah</td></tr>
                <tr><td>3.2.1.</td><td>-</td><td>Arah Kebijakan Pendapatan Daerah</td></tr>
                <tr><td>3.2.2.</td><td>-</td><td>Arah Kebijakan Belanja Daerah</td></tr>
                <tr><td>3.2.3.</td><td>-</td><td>Arah Kebijakan Pembiayaan Daerah</td></tr>
                <tr><td>3.2.4.</td><td>-</td><td>Proyeksi Pendapatan dan Belanja</td></tr>

                <tr><td>4.</td><td><b>BAB IV SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH</b></td><td><b>BAB IV SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH TAHUN {{ $tahunAnggaran }}</b></td></tr>
                <tr><td>4.1.</td><td>Tujuan dan Sasaran Pembangunan</td><td>Tujuan dan Sasaran Pembangunan</td></tr>
                <tr><td>4.2.</td><td>Arah Kebijakan Pembangunan</td><td>Arah Kebijakan Pembangunan</td></tr>
                <tr><td>4.3.</td><td>Prioritas dan Sasaran Pembangunan Tahun {{ $tahunAnggaran }}</td><td>Prioritas Pembangunan</td></tr>
                <tr><td>4.3.1.</td><td>-</td><td>Prioritas Pembangunan Nasional Tahun {{ $tahunAnggaran }}</td></tr>
                <tr><td>4.3.2.</td><td>-</td><td>Prioritas Pembangunan Provinsi Jawa Tengah Tahun {{ $tahunAnggaran }}</td></tr>
                <tr><td>4.3.3.</td><td>-</td><td>Prioritas Pembangunan Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td></tr>
                <tr><td>4.3.4.</td><td>-</td><td>Keterkaitan Prioritas Nasional, Prioritas Jawa Tengah dan Prioritas Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td></tr>
                <tr><td>4.3.5.</td><td>-</td><td>Penyelarasan Target Indikator Makro dan Tematik Berdasarkan Hasil Rakortekrenbang Provinsi</td></tr>
                <tr><td>4.4.</td><td>Pokok-Pokok Pikiran DPRD</td><td>Pokok-Pokok Pikiran DPRD</td></tr>
                <tr><td>4.5.</td><td>Inovasi Kebijakan Pembangunan</td><td>Inovasi Kebijakan Pembangunan</td></tr>

                <tr><td>5.</td><td><b>BAB V RENCANA KERJA DAN PENDANAAN DAERAH</b></td><td><b>BAB V RENCANA KERJA DAN PENDANAAN DAERAH</b></td></tr>
                <tr><td>5.1.</td><td>-</td><td>Program Prioritas Daerah yang mendukung Prioritas Nasional dan Provinsi Jawa Tengah</td></tr>
                <tr><td>5.2.</td><td>-</td><td>Program Perangkat Daerah</td></tr>
                <tr><td>5.2.1.</td><td>-</td><td>Urusan Wajib Pemerintah Daerah</td></tr>
                <tr><td>5.2.2.</td><td>-</td><td>Urusan Pemerintahan Wajib yang tidak berkaitan dengan Pelayanan Dasar</td></tr>
                <tr><td>5.2.3.</td><td>-</td><td>Urusan Pemerintahan Pilihan</td></tr>
                <tr><td>5.2.4.</td><td>-</td><td>Urusan Penunjang Pemerintah</td></tr>
                <tr><td>5.3.</td><td>-</td><td>Kerangka Pendanaan Tahun {{ $tahunAnggaran }}</td></tr>

                <tr><td>6.</td><td><b>BAB VI KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH</b></td><td><b>BAB VI KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH</b></td></tr>
                <tr><td>6.1.</td><td>Indikator Kinerja Utama Daerah</td><td>Indikator Kinerja Utama Daerah</td></tr>
                <tr><td>6.2.</td><td>Indikator Kinerja Daerah</td><td>Indikator Kinerja Daerah</td></tr>

                <tr><td>7.</td><td><b>BAB VII PENUTUP</b></td><td><b>BAB VII PENUTUP</b></td></tr>
            </tbody>
        </table>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">10</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 12: CATATAN SUBSTANSI (1 & 2) ==================== -->
    <section class="page">
        <p style="font-weight:bold; margin-left:15px; margin-bottom:2px;">b. Substansi</p>
        <div style="margin-left: 15px;">
            <p class="text-justify"><b>1) Pengujian atas Kelengkapan Dokumen Pendukung RKPD</b><br>
            @php $catsS1 = $getCatatans('RKPD.S1'); @endphp
            @if($catsS1->isNotEmpty())
                @foreach($catsS1 as $cat)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $cat->isi !!}
                    </div>
                @endforeach
            @else
                Terdapat data/dokumen pendukung yang belum ada, yaitu:
                <ol type="a" style="margin-top:0;">
                    <li>Hasil Evaluasi RKPD tahun 2026 (Triwulan 1);</li>
                    <li>Laporan hasil pengendalian kebijakan penyusunan RKPD Formulir E.35 Pengendalian dan Evaluasi terhadap Kebijakan Perencanaan Pembangunan Tahunan Daerah (Cek List: 27 komponen);</li>
                    <li>Jadwal / Agenda Kerja Tim Penyusunan RKPD tahun {{ $tahunAnggaran }}; dan</li>
                    <li>Rekapitulasi Pokok-pokok Pikiran DPRD tahun {{ $tahunAnggaran }}.</li>
                </ol>
            @endif
            </p>

            <p class="text-justify"><b>2) Pengujian atas Analisis Isi BAB RKPD</b></p>
            @php $catsS2 = $getCatatans('RKPD.S2'); @endphp
            @if($catsS2->isNotEmpty())
                <table class="bordered">
                    <thead>
                        <tr>
                            <th style="width:8%;">NO</th>
                            <th>CATATAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($catsS2 as $idx => $cat)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}.</td>
                                <td>
                                    @if($cat->referensi_bagian)
                                        <b>{{ $cat->referensi_bagian }}</b><br>
                                    @elseif($cat->kategori)
                                        <b>{{ $cat->kategori->nama }}</b><br>
                                    @endif
                                    {!! $cat->isi !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
            <table class="bordered">
                <thead>
                    <tr>
                        <th style="width:8%;">NO</th>
                        <th>CATATAN</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2" style="background:#f9f9f9; font-weight:bold;">BAB II GAMBARAN UMUM KONDISI DAERAH</td>
                    </tr>
                    <tr>
                        <td class="text-center">1.</td>
                        <td>
                            <b>2.1.4 Aspek Pelayanan Umum</b>
                            <ol type="a" style="padding-left:15px; margin-bottom:0;">
                                <li>Belum menguraikan terkait Indikator Standar Pelayanan Minimum (SPM) sebagaimana tercantum dalam Peraturan Pemerintah Nomor 2 Tahun 2018; dan</li>
                                <li>Masih mencantumkan terkait anggaran, Sumber Daya Manusia dan sarana prasarana sebagai faktor penghambat kegagalan/ketidaktercapaian atas indikator kinerja tahun 2025.</li>
                            </ol>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="background:#f9f9f9; font-weight:bold;">BAB III. KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH</td>
                    </tr>
                    <tr>
                        <td class="text-center">1.</td>
                        <td>
                            <b>3.1 Arah Kebijakan Ekonomi Daerah</b><br>
                            Penjelasan terkait pertumbuhan ekonomi belum didukung dengan data pendukung yang disajikan pada tabel secara lengkap, yaitu:
                            <ul style="padding-left:15px; margin-bottom:0;">
                                <li>PDRB Atas Dasar Harga Konstan Menurut Lapangan Usaha Kabupaten Rembang Tahun 2024-2025;</li>
                                <li>PDRB Atas Dasar Harga Berlaku (ADHB) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025;</li>
                                <li>PDRB Atas Dasar Harga Konstan (ADHK) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025; dan</li>
                                <li>Laju Inflasi.</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">2.</td>
                        <td>
                            <b>3.2 Arah Kebijakan Keuangan Daerah</b>
                            <ol type="a" style="padding-left:15px; margin-bottom:0;">
                                <li>Terdapat ketidaksesuaian angka proyeksi PAD dan belanja pada dokumen Rankir RKPD (tabel 3.5) dengan SIPD (tabel 3.5. Proyeksi Anggaran Pendapatan dan Belanja Daerah Kabupaten Rembang Tahun {{ $tahunAnggaran }});</li>
                                <li>Kebijakan belanja belum memprioritaskan belanja untuk mendukung pelayanan dasar;</li>
                                <li>Belum memproyeksikan belanja tidak terduga dan belanja transfer;</li>
                                <li>Proyeksi belanja belum dirinci berdasarkan kategori rekening belanja;</li>
                                <li>Terdapat proyeksi pembayaran Pembiayaan Utang Daerah sebesar Rp. 4.500.000.000,- yang tidak dijelaskan peruntukannya;</li>
                                <li>Belum menginformasikan rasio ruang fiskal tahunan (grafik/tabel); dan</li>
                                <li>Belum menginformasikan Kapasitas Riil Kemampuan Keuangan Daerah Kabupaten Rembang tahun {{ $tahunAnggaran }} (tabel & penjelasan).</li>
                            </ol>
                        </td>
                    </tr>
                </tbody>
            </table>
            @endif
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">11</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 13: CATATAN SUBSTANSI (2 LANJUTAN & 3, 4) ==================== -->
    <section class="page">
        <div style="margin-left: 15px;">
            @if($catsS2->isEmpty())
            <table class="bordered">
                <thead>
                    <tr>
                        <th style="width:8%;">NO</th>
                        <th>CATATAN</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2" style="background:#f9f9f9; font-weight:bold;">BAB IV. SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH TAHUN {{ $tahunAnggaran }}</td>
                    </tr>
                    <tr>
                        <td class="text-center">1.</td>
                        <td>
                            <b>4.2 Arah Kebijakan Pembangunan</b><br>
                            Arah kebijakan pembangunan RKPD Kabupaten Rembang yang tertuang dalam tabel 4.2 (halaman IV-8) belum difokuskan dalam mewujudkan tema pembangunan tahun {{ $tahunAnggaran }} "Pengembangan Perekonomian Daerah termasuk Ekonomi Syariah dan Pariwisata, Didukung SDM Produktif, Tata Kelola Pemerintahan yang Responsif dan Infrastruktur yang Mantap".
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">2.</td>
                        <td>
                            <b>4.3 Prioritas Pembangunan</b><br>
                            <b>4.3.3 Prioritas Pembangunan Kabupaten Rembang Tahun {{ $tahunAnggaran }}</b><br>
                            Inventarisasi permasalahan di Bab II belum sepenuhnya menjadi prioritas yang ditangani.
                        </td>
                    </tr>
                </tbody>
            </table>
            @endif

            <p class="text-justify"><b>3) Pengujian atas Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan dalam RKPD dan RPJMD</b><br>
            @php $catsS3 = $getCatatans('RKPD.S3'); @endphp
            @if($catsS3->isNotEmpty())
                @foreach($catsS3 as $cat)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $cat->isi !!}
                    </div>
                @endforeach
            @else
            Rumusan prioritas pembangunan daerah pada Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} (hal IV-16) telah selaras dengan prioritas pembangunan daerah yang telah ditetapkan pada RPJMD Kabupaten Rembang tahun 2025-2029 (hal III-143) sebagaimana penyandingan berikut:</p>

            <table class="bordered">
                <thead>
                    <tr>
                        <th style="width:50%;">Prioritas Pembangunan tahun {{ $tahunAnggaran }}<br>(RPJMD 2025-2029)</th>
                        <th style="width:50%;">Prioritas Pembangunan tahun {{ $tahunAnggaran }}<br>(Rankir RKPD {{ $tahunAnggaran }})</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>1. Peningkatan Pembangunan SDM</td><td>1. Peningkatan Pembangunan SDM</td></tr>
                    <tr><td>2. Peningkatan Perekonomian Daerah</td><td>2. Peningkatan Perekonomian Daerah</td></tr>
                    <tr><td>3. Peningkatan Kualitas Pelayanan Publik</td><td>3. Peningkatan Kualitas Pelayanan Publik</td></tr>
                    <tr><td>4. Peningkatan Infrastruktur Mantap</td><td>4. Peningkatan Infrastruktur Mantap</td></tr>
                    <tr><td>5. Peningkatan Daya Dukung Lingkungan Hidup</td><td>5. Peningkatan Daya Dukung Lingkungan Hidup</td></tr>
                </tbody>
            </table>
            @endif

            <p class="text-justify"><b>4) Kesesuaian Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rencana Pembangunan Jangka Menengah Daerah (RPJMD) dengan rancangan akhir RKPD</b></p>
            @php $catsS4 = $getCatatans('RKPD.S4'); @endphp
            @if($catsS4->isNotEmpty())
                @foreach($catsS4 as $cat)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $cat->isi !!}
                    </div>
                @endforeach
            @else
            <ol type="a" class="text-justify" style="margin-top:0;">
                <li>Terdapat ketidaksesuaian Indikator Kinerja, Target beserta Pagu Indikatif (Program) yang dicantumkan pada tabel 4.1 RPJMD Kabupaten Rembang tahun 2025-2029 (halaman IV-34) dengan Lampiran SIPD-RI Excel (Renja OPD Kabupaten Rembang tahun {{ $tahunAnggaran }}) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }}, sebagaimana penyandingan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>;</li>
                <li>Seluruh Program pada Lampiran SIPD-RI PDF (Renja OPD Kabupaten Rembang tahun {{ $tahunAnggaran }}) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} belum dilengkapi outcome, indikator, satuan, capaian (T-2) dan target;</li>
            </ol>
            @endif
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">12</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 14: CATATAN SUBSTANSI (4 LANJUTAN & 5) ==================== -->
    <section class="page">
        <div style="margin-left: 15px;">
            @if($catsS4->isEmpty())
            <ol type="a" start="3" class="text-justify" style="margin-top:0;">
                <li>Terdapat ketidaksesuaian Indikator Kinerja, Target beserta Pagu Indikatif (Program) yang dicantumkan pada tabel 5.1 (halaman V-1 sampai halaman V-28) dan tabel 5.3 Rancangan Akhir RKPD tahun {{ $tahunAnggaran }} (halaman V-85 sampai halaman V-123) dengan Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {{ $tahunAnggaran }}) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }}, sebagaimana penyandingan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>;</li>
                <li>Terdapat beberapa Program yang termasuk Program Prioritas Nasional tetapi dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} kurang mendapatkan proporsi anggaran, sebagaimana catatan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>;</li>
                <li>Terdapat penetapan target kinerja "Persentase terbangunnya sarana prasarana persampahan sesuai standar (%)" namun tidak disertai dengan penetapan anggaran, padahal indikator kinerja yang digunakan sebagai tolok ukur menunjukkan pembangunan fisik/infrastruktur sebagaimana catatan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>;</li>
                <li>Terdapat kenaikan proporsi anggaran yang cukup signifikan pada beberapa Program di dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} dibandingkan dengan proporsi anggaran pada dokumen RPJMD Kabupaten Rembang tahun 2025-2029 sebagaimana catatan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>;</li>
                <li>Program Pengolahan dan Pemasaran Hasil Perikanan termasuk dalam program utama unggulan Bupati dan Wakil Bupati namun proporsi anggaran pada dokumen RPJMD Kabupaten Rembang tahun 2025-2029 dengan dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} selisih cukup besar dibanding program pendukung maupun bukan program pendukung sebagaimana catatan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>; dan</li>
                <li>Terdapat beberapa program yang bukan termasuk program prioritas maupun nomenklatur program utama namun proporsi anggaran yang diberikan pada dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} cukup besar dibandingkan dengan anggaran pada program prioritas sebagaimana catatan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>.</li>
            </ol>
            @endif

            <p class="text-justify"><b>5) Kewajaran proyeksi pendanaan pada rancangan akhir RKPD</b></p>
            @php $catsS5 = $getCatatans('RKPD.S5'); @endphp
            @if($catsS5->isNotEmpty())
                @foreach($catsS5 as $cat)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $cat->isi !!}
                    </div>
                @endforeach
            @else
            <ol type="a" class="text-justify" style="margin-top:0;">
                <li>Data APBD tahun berjalan (tahun anggaran 2026) pada tabel 3.5 (halaman III-17) dokumen Rancangan Akhir RKPD Kabupaten Rembang belum menggunakan APBD berdasarkan Peraturan Bupati Rembang Nomor 4 tahun 2026 tentang Perubahan atas Peraturan Bupati Rembang Nomor 110 tahun 2025 tentang Penjabaran APBD tahun anggaran 2026 sebagaimana penyandingan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>;</li>
            </ol>
            @endif
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">13</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 15: CATATAN SUBSTANSI (5 LANJUTAN & 6, 7) ==================== -->
    <section class="page">
        <div style="margin-left: 15px;">
            @if($catsS5->isEmpty())
            <ol type="a" start="2" class="text-justify" style="margin-top:0;">
                <li>Data Realisasi Anggaran tahun 2025 unaudited pada tabel 3.2 (halaman III-10), tabel 3.3 (halaman III-15), dan tabel 3.4 (halaman III-16) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} belum sesuai dengan LRA tahun 2025 unaudited terupdate sebagaimana penyandingan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>;</li>
                <li>Proyeksi APBD tahun anggaran {{ $tahunAnggaran }} pada tabel 3.5 Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} (halaman III-17) tidak sesuai dengan data proyeksi APBD tahun anggaran {{ $tahunAnggaran }} di SIPD-RI sebagaimana penyandingan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>; dan</li>
                <li>Pada tabel 3.5 Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} (halaman III-17) belum memproyeksikan belanja tidak terduga dan belanja transfer.</li>
            </ol>
            @endif

            <p class="text-justify"><b>6) Kesesuaian Informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional</b></p>
            @php
                $catsS6 = $getCatatans('RKPD.S6');
                $catsS6a = $getCatatans('RKPD.S6.a');
                $catsS6b = $getCatatans('RKPD.S6.b');
                $catsS6c = $getCatatans('RKPD.S6.c');
                $catsS6d = $getCatatans('RKPD.S6.d');
                $catsS6e = $getCatatans('RKPD.S6.e');
            @endphp
            <ol type="a" class="text-justify" style="margin-top:0;">
                <li><b>Kesesuaian urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil rapat koordinasi teknis pembangunan (Rakortekbang) dengan urusan pada rancangan akhir RKPD:</b>
                    @if($catsS6a->isNotEmpty())
                        @foreach($catsS6a as $cat)
                            <div style="margin-top: 4px; margin-bottom: 6px;">{!! $cat->isi !!}</div>
                        @endforeach
                    @else
                    <ul style="padding-left:15px;">
                        <li>Terdapat ketidakselarasan pemetaan urusan pemerintah daerah terhadap Asta Cita berdasarkan RAKORTEKRENBANG Nasional Tahun 2026 sebagaimana penyandingan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>;</li>
                        <li>Belum teridentifikasinya pemetaan dukungan subkegiatan terhadap outcome prioritas (program prioritas) pemerintah pusat sebagaimana tertuang pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>;</li>
                        <li>Terdapat subkegiatan dengan target kinerja nihil yang dimasukkan dalam pemetaan dukungan terhadap asta cita sebagaimana tertuang pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>.</li>
                    </ul>
                    @endif
                </li>
                <li><b>Kesesuaian penetapan target Indikator Makro Pembangunan pada rancangan akhir RKPD berdasarkan Rencana Kerja Pemerintah (RKP):</b>
                    @if($catsS6b->isNotEmpty())
                        @foreach($catsS6b as $cat)
                            <div style="margin-top: 4px; margin-bottom: 6px;">{!! $cat->isi !!}</div>
                        @endforeach
                    @else
                        Rencana Kerja Pemerintah (RKP) tahun {{ $tahunAnggaran }} belum tersedia.
                    @endif
                </li>
                <li><b>Kesesuaian dan ketepatan dukungan pelaksanaan ProSN pada rancangan akhir RKPD:</b>
                    @if($catsS6c->isNotEmpty())
                        @foreach($catsS6c as $cat)
                            <div style="margin-top: 4px; margin-bottom: 6px;">{!! $cat->isi !!}</div>
                        @endforeach
                    @else
                        Subkegiatan yang mendukung pelaksanaan ProSN belum seluruhnya terpetakan dalam Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {{ $tahunAnggaran }}) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} sebagaimana penyandingan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>.
                    @endif
                </li>
            </ol>
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">14</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 16: CATATAN SUBSTANSI (6 LANJUTAN & 7, 8) ==================== -->
    <section class="page">
        <div style="margin-left: 15px;">
            <ol type="a" start="4" class="text-justify" style="margin-top:0;">
                <li><b>Memastikan Program, Kegiatan dan Sub Kegiatan Standar Pelayanan Minimal (SPM) tersedia pada rancangan akhir RKPD:</b>
                    @if($catsS6d->isNotEmpty())
                        @foreach($catsS6d as $cat)
                            <div style="margin-top: 4px; margin-bottom: 6px;">{!! $cat->isi !!}</div>
                        @endforeach
                    @else
                    <ul style="padding-left:15px;">
                        <li>Terdapat subkegiatan Standar Pelayanan Minimal (SPM) yang belum terpetakan dalam Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {{ $tahunAnggaran }}) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }}; dan</li>
                        <li>Terdapat beberapa subkegiatan yang termasuk subkegiatan Standar Pelayanan Minimal (SPM) tetapi dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} tidak mendapatkan proporsi anggaran, sebagaimana catatan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>.</li>
                    </ul>
                    @endif
                </li>
                <li><b>Memastikan kesesuaian dukungan Sub Kegiatan Tematik Pembangunan pada rancangan akhir RKPD:</b>
                    @if($catsS6e->isNotEmpty())
                        @foreach($catsS6e as $cat)
                            <div style="margin-top: 4px; margin-bottom: 6px;">{!! $cat->isi !!}</div>
                        @endforeach
                    @else
                    <ul style="padding-left:15px;">
                        <li>Terdapat subkegiatan Tematik Pembangunan yang belum terpetakan dalam Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {{ $tahunAnggaran }}) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }}; dan</li>
                        <li>Terdapat beberapa subkegiatan yang termasuk Tematik Pembangunan tetapi dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} tidak mendapatkan proporsi anggaran, sebagaimana catatan pada link <a href="https://bit.ly/Lampiran-CHRRankirRKPD-2027">https://bit.ly/Lampiran-CHRRankirRKPD-2027</a>.</li>
                    </ul>
                    @endif
                </li>
            </ol>

            <p class="text-justify"><b>7) Pengujian atas Pokok-Pokok Pikiran DPRD</b><br>
            @php $catsS7 = $getCatatans('RKPD.S7'); @endphp
            @if($catsS7->isNotEmpty())
                @foreach($catsS7 as $cat)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $cat->isi !!}
                    </div>
                @endforeach
            @else
            Bab IV Sasaran dan Prioritas Pembangunan Daerah Tahun {{ $tahunAnggaran }} telah menjelaskan pokok-pokok pikiran DPRD (halaman IV-33 sampai dengan halaman IV-34), namun belum dilengkapi dengan rekapitulasi data pokok-pokok pikiran DPRD yang berisi: Program/Kegiatan/Sub Kegiatan/Aktivitas, Indikator, Volume, Kelompok Sasaran, Lokasi dan Perangkat Daerah terkait.
            @endif
            </p>

            <p class="text-justify"><b>8) Pengujian atas Konsistensi pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran dalam Rancangan Akhir RKPD dan RPJMD</b><br>
            @php $catsS8 = $getCatatans('RKPD.S8'); @endphp
            @if($catsS8->isNotEmpty())
                @foreach($catsS8 as $cat)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $cat->isi !!}
                    </div>
                @endforeach
            @else
            Terdapat ketidakkonsistenan target Indikator Kinerja Tujuan dan Sasaran yang dicantumkan pada tabel 3.6 RPJMD Kabupaten Rembang tahun 2025-2029 dengan tabel 4.1 Rancangan Akhir RKPD tahun {{ $tahunAnggaran }}, sebagaimana penyandingan sebagai berikut:
            @endif
            </p>
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">15</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 17: TABEL KONSISTENSI RPJMD VS RKPD (MISI 1 & 2) ==================== -->
    <section class="page">
        <table class="bordered" style="font-size: 8.5pt;">
            <thead>
                <tr>
                    <th rowspan="3" style="width:20%;">Misi/Tujuan/Sasaran</th>
                    <th colspan="3">Rankir RPJMD tahun 2025-2029</th>
                    <th colspan="3">Rankir RKPD tahun {{ $tahunAnggaran }}</th>
                </tr>
                <tr>
                    <th colspan="3">Tabel 3.6</th>
                    <th>Tabel 3.9</th>
                    <th colspan="2">Teknik Perumusan Program Prioritas Pembangunan Daerah</th>
                </tr>
                <tr>
                    <th>Indikator Tujuan/ Indikator Sasaran</th>
                    <th>Satuan</th>
                    <th>Target {{ $tahunAnggaran }}</th>
                    <th>Indikator Tujuan/ Indikator Sasaran</th>
                    <th>Satuan</th>
                    <th>Target {{ $tahunAnggaran }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" style="background:#e6e6e6; font-weight:bold;">Misi 1: Meningkatkan Kualitas Sumber Daya Manusia yang Unggul, Sehat dan Berdaya Saing</td>
                </tr>
                <tr>
                    <td>Terwujudnya kualitas sumber daya manusia yang berdaya saing dan produktif</td>
                    <td>Indeks Daya Saing Daerah</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">3,78</td>
                    <td>Indeks Daya Saing Daerah</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">3,78</td>
                </tr>
                <tr>
                    <td rowspan="2">Meningkatnya akses terhadap pendidikan dan kesehatan yang berkualitas, serta kesetaraan gender</td>
                    <td>Indeks Pembangunan Manusia</td>
                    <td class="text-center">Tahun</td>
                    <td class="text-center">74,38</td>
                    <td>Indeks Pembangunan Manusia</td>
                    <td class="text-center">Tahun</td>
                    <td class="text-center">73,70</td>
                </tr>
                <tr>
                    <td>Indeks Ketimpangan Gender (IUP 21)</td>
                    <td class="text-center">Tahun</td>
                    <td class="text-center">0,246 / 0,22</td>
                    <td>Indeks Ketimpangan Gender (IUP 21)</td>
                    <td class="text-center">Tahun</td>
                    <td class="text-center">0,246 / 2,22</td>
                </tr>
                <tr>
                    <td colspan="7" style="background:#e6e6e6; font-weight:bold;">Misi 2: Mewujudkan Pertumbuhan Ekonomi Daerah yang Berkelanjutan, Inklusif Berbasis Potensi Unggul, serta Mampu Mengurangi Kemiskinan</td>
                </tr>
                <tr>
                    <td>Meningkatnya pertumbuhan ekonomi yang berdampak pada penurunan kemiskinan</td>
                    <td>Pertumbuhan Ekonomi</td>
                    <td class="text-center">%</td>
                    <td class="text-center">5,90 - 7,75</td>
                    <td>Pertumbuhan Ekonomi</td>
                    <td class="text-center">%</td>
                    <td class="text-center">5,40 - 6,20</td>
                </tr>
                <tr>
                    <td rowspan="3">Meningkatnya nilai investasi, penyerapan tenaga kerja, kontribusi sektor unggulan dalam perekonomian, dan ketahanan pangan</td>
                    <td>Pembentukan Modal Tetap Bruto (PMTB) dalam PDRB (IUP 32)</td>
                    <td class="text-center">%</td>
                    <td class="text-center">24,02</td>
                    <td>Pembentukan Modal Tetap Bruto (PMTB) dalam PDRB</td>
                    <td class="text-center">%</td>
                    <td class="text-center">24,02</td>
                </tr>
                <tr>
                    <td>Produk Domestik Regional Bruto (PDRB) Per Kapita Kabupaten/Kota (IUP 33)</td>
                    <td class="text-center">Juta Rp.</td>
                    <td class="text-center">57,81</td>
                    <td>Produk Domestik Regional Bruto (PDRB) Per Kapita Kabupaten/Kota</td>
                    <td class="text-center">Juta Rp.</td>
                    <td class="text-center">57,81</td>
                </tr>
                <tr>
                    <td>Tingkat Pengangguran Terbuka (IUP 29)</td>
                    <td class="text-center">%</td>
                    <td class="text-center">2,45 / 2,15</td>
                    <td>Tingkat Pengangguran Terbuka</td>
                    <td class="text-center">%</td>
                    <td class="text-center">2,45 / 2,15</td>
                </tr>
                <tr>
                    <td rowspan="2">-</td>
                    <td>Tingkat Kemiskinan (IUP 12)</td>
                    <td class="text-center">%</td>
                    <td class="text-center">12,01 / 11,70</td>
                    <td>Tingkat Kemiskinan</td>
                    <td class="text-center">%</td>
                    <td class="text-center">12,73 / 12,43</td>
                </tr>
                <tr>
                    <td>Indeks Ketahanan Pangan (IUP 53)</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">85,34</td>
                    <td>Indeks Ketahanan Pangan (IKP)</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">85,34</td>
                </tr>
            </tbody>
        </table>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">16</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 18: TABEL KONSISTENSI RPJMD VS RKPD (MISI 3, 4, 5) ==================== -->
    <section class="page">
        <table class="bordered" style="font-size: 8.5pt;">
            <thead>
                <tr>
                    <th rowspan="3" style="width:20%;">Misi/Tujuan/Sasaran</th>
                    <th colspan="3">Rankir RPJMD tahun 2025-2029</th>
                    <th colspan="3">Rankir RKPD tahun {{ $tahunAnggaran }}</th>
                </tr>
                <tr>
                    <th colspan="3">Tabel 3.6</th>
                    <th>Tabel 3.9</th>
                    <th colspan="2">Teknik Perumusan Program Prioritas Pembangunan Daerah</th>
                </tr>
                <tr>
                    <th>Indikator Tujuan/ Indikator Sasaran</th>
                    <th>Satuan</th>
                    <th>Target {{ $tahunAnggaran }}</th>
                    <th>Indikator Tujuan/ Indikator Sasaran</th>
                    <th>Satuan</th>
                    <th>Target {{ $tahunAnggaran }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" style="background:#e6e6e6; font-weight:bold;">Misi 3: Mewujudkan Tata Kelola Pemerintahan dan Pelayanan Publik yang Profesional</td>
                </tr>
                <tr>
                    <td>Meningkatnya tata kelola pemerintahan yang profesional dan berintegritas</td>
                    <td>Indeks Reformasi Birokrasi (IUP 39)<br>- RB General<br>- RB Tematik</td>
                    <td class="text-center">Angka<br>Angka<br>Angka</td>
                    <td class="text-center">89,66<br>79,05<br>10,61</td>
                    <td>Indeks Reformasi Birokrasi<br>- RB General<br>- RB Tematik</td>
                    <td class="text-center">Angka<br>Angka<br>Angka</td>
                    <td class="text-center">89,66<br>79,05<br>10,61</td>
                </tr>
                <tr>
                    <td colspan="7" style="background:#e6e6e6; font-weight:bold;">Misi 4: Mewujudkan Pembangunan Infrastruktur yang Berkualitas dan Berkelanjutan</td>
                </tr>
                <tr>
                    <td>Meningkatnya pemerataan pembangunan infrastruktur yang tangguh</td>
                    <td>Indeks Infrastruktur</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">70,31</td>
                    <td>Indeks Infrastruktur</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">70,31</td>
                </tr>
                <tr>
                    <td>1. Meningkatnya kinerja layanan infrastruktur konektivitas wilayah</td>
                    <td>Indeks Infrastruktur Konektivitas Wilayah</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">68,18</td>
                    <td>Indeks Infrastruktur Konektivitas Wilayah</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">68,18</td>
                </tr>
                <tr>
                    <td>2. Meningkatnya pemenuhan permukiman Layak dan Berkelanjutan</td>
                    <td>Indeks Kinerja Infrastruktur Permukiman</td>
                    <td class="text-center">%</td>
                    <td class="text-center">72,44</td>
                    <td>Indeks Kinerja Infrastruktur Permukiman</td>
                    <td class="text-center">%</td>
                    <td class="text-center">72,44</td>
                </tr>
                <tr>
                    <td colspan="7" style="background:#e6e6e6; font-weight:bold;">Misi 5: Meningkatkan Kualitas Lingkungan Hidup dan Pengelolaan Sumber Daya Alam yang Berkelanjutan</td>
                </tr>
                <tr>
                    <td>Meningkatnya kualitas lingkungan hidup berkelanjutan</td>
                    <td>Indeks Kualitas Lingkungan Hidup (IKLH)</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">76,23</td>
                    <td>Indeks Kualitas Lingkungan Hidup (IKLH)</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">76,23</td>
                </tr>
                <tr>
                    <td rowspan="2">Meningkatnya pengelolaan lingkungan hidup dan Ketahanan Bencana Daerah</td>
                    <td>Kontribusi penurunan emisi GRK kumulatif (IUP 49)</td>
                    <td class="text-center">TonCO2 eq</td>
                    <td class="text-center">1.815.353,68</td>
                    <td>Kontribusi penurunan emisi GRK kumulatif</td>
                    <td class="text-center">TonCO2 eq</td>
                    <td class="text-center">1.815.353,68</td>
                </tr>
                <tr>
                    <td>Indeks Risiko Bencana (IRB)</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">107,82 - 106,8</td>
                    <td>Indeks Risiko Bencana</td>
                    <td class="text-center">Angka</td>
                    <td class="text-center">107,82 - 106,8</td>
                </tr>
            </tbody>
        </table>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">17</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 19: REKOMENDASI FORMAT & SUBSTANSI (1) ==================== -->
    <section class="page">
        <p style="font-weight:bold; margin-bottom:2px;">3. Rekomendasi</p>
        <p style="margin-top:0;" class="text-justify">Berdasarkan hal tersebut, demi tersusunnya dokumen RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} yang andal, akurat dan sebagai pedoman dalam penyusunan dokumen KUPA PPAS tahun {{ $tahunAnggaran }} direkomendasikan beberapa hal sebagai berikut:</p>
        
        <p style="font-weight:bold; margin-left:15px; margin-bottom:2px;">a. Format</p>
        <p style="margin-top:0; margin-left:15px;" class="text-justify"><b>Kesesuaian Format Rancangan Akhir RKPD</b><br>
        @php $rekomF = $getRekomendasis('RKPD.F'); @endphp
        @if($rekomF->isNotEmpty())
            @foreach($rekomF as $r)
                <div style="margin-top: 4px; margin-bottom: 6px;">
                    {!! $r->uraian_rekom !!}
                </div>
            @endforeach
        @else
            Menyesuaikan judul: 1) Sub Bab 1.2, 2) Sub Bab 1.3, 3) Sub Bab 1.5, 4) Sub Bab 2.1.3, 5) Sub Bab 2.1.4, 6) Bab 4, 7) Sub Bab 4.3 Dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} dengan mempedomani Lampiran Peraturan Menteri Dalam Negeri nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah tentang Rencana Pembangunan Jangka Panjang Daerah dan Rencana Pembangunan Jangka Menengah Daerah, serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah dan Rencana Kerja Pemerintah Daerah (halaman 384);
        @endif
        </p>

        <p style="font-weight:bold; margin-left:15px; margin-bottom:2px;">b. Substansi</p>
        <div style="margin-left: 15px;">
            <p class="text-justify"><b>1) Pengujian atas Kelengkapan Dokumen Pendukung RKPD Tahun {{ $tahunAnggaran }}</b><br>
            @php $rekomS1 = $getRekomendasis('RKPD.S1'); @endphp
            @if($rekomS1->isNotEmpty())
                @foreach($rekomS1 as $r)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $r->uraian_rekom !!}
                    </div>
                @endforeach
            @else
                Melengkapi data/dokumen pendukung sebagai berikut:
                <ol type="a" style="margin-top:0;">
                    <li>Hasil Evaluasi RKPD tahun 2026 (Triwulan 1);</li>
                    <li>Laporan hasil pengendalian kebijakan penyusunan RKPD Formulir E.35 Pengendalian dan Evaluasi terhadap Kebijakan Perencanaan Pembangunan Tahunan Daerah (Cek List: 27 komponen);</li>
                    <li>Jadwal / Agenda Kerja Tim Penyusunan RKPD tahun {{ $tahunAnggaran }}; dan</li>
                    <li>Rekapitulasi Pokok-pokok Pikiran DPRD tahun {{ $tahunAnggaran }}.</li>
                </ol>
            @endif
            </p>

            <p class="text-justify"><b>2) Pengujian atas Analisis Isi BAB RKPD</b></p>
            @php $rekomS2 = $getRekomendasis('RKPD.S2'); @endphp
            @if($rekomS2->isNotEmpty())
                <table class="bordered">
                    <thead>
                        <tr>
                            <th style="width:8%;">NO</th>
                            <th style="width:46%;">CATATAN</th>
                            <th style="width:46%;">REKOMENDASI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekomS2 as $idx => $r)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}.</td>
                                <td>{!! $r->lhrCatatan?->isi ?? '-' !!}</td>
                                <td>{!! $r->uraian_rekom !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">18</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 20: TABEL REKOMENDASI ANALISIS ISI BAB (1) ==================== -->
    <section class="page">
        @if($rekomS2->isEmpty())
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width:8%;">NO</th>
                    <th style="width:46%;">CATATAN</th>
                    <th style="width:46%;">REKOMENDASI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" style="background:#f9f9f9; font-weight:bold;">BAB II GAMBARAN UMUM KONDISI DAERAH</td>
                </tr>
                <tr>
                    <td class="text-center">1.</td>
                    <td>
                        <b>2.1.4 Aspek Pelayanan Umum</b>
                        <ol type="a" style="padding-left:12px; margin:0;">
                            <li>Belum menguraikan terkait Indikator Standar Pelayanan Minimum (SPM) sebagaimana tercantum dalam Peraturan Pemerintah Nomor 2 Tahun 2018; dan</li>
                            <li>Masih mencantumkan terkait anggaran, Sumber Daya Manusia dan sarana prasarana sebagai faktor penghambat kegagalan/ketidaktercapaian atas indikator kinerja tahun 2025.</li>
                        </ol>
                    </td>
                    <td>
                        <br>
                        Menambahkan uraian terkait Indikator Standar Pelayanan Minimum (SPM) sebagaimana tercantum dalam Peraturan Pemerintah Nomor 2 Tahun 2018; dan<br><br>
                        Menghapus faktor penghambat kegagalan/ketidaktercapaian atas indikator kinerja tahun 2025 yang terkait dengan anggaran, Sumber Daya Manusia dan sarana prasarana.
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="background:#f9f9f9; font-weight:bold;">BAB III. KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH</td>
                </tr>
                <tr>
                    <td class="text-center">1.</td>
                    <td>
                        <b>3.1 Arah Kebijakan Ekonomi Daerah</b><br>
                        Penjelasan terkait pertumbuhan ekonomi belum didukung dengan data pendukung yang disajikan pada tabel secara lengkap, yaitu:
                        <ul style="padding-left:12px; margin:0;">
                            <li>PDRB Atas Dasar Harga Konstan Menurut Lapangan Usaha Kabupaten Rembang Tahun 2024 - 2025;</li>
                            <li>PDRB Atas Dasar Harga Berlaku (ADHB) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025;</li>
                            <li>PDRB Atas Dasar Harga Konstan (ADHK) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025; dan</li>
                            <li>Laju Inflasi.</li>
                        </ul>
                    </td>
                    <td>
                        Melengkapi data pendukungan pertumbuhan ekonomi yang meliputi:
                        <ul style="padding-left:12px; margin:0;">
                            <li>PDRB Atas Dasar Harga Konstan Menurut Lapangan Usaha Kabupaten Rembang Tahun 2024 - 2025;</li>
                            <li>PDRB Atas Dasar Harga Berlaku (ADHB) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025;</li>
                            <li>PDRB Atas Dasar Harga Konstan (ADHK) Menurut Pengeluaran Kabupaten Rembang Tahun 2024 - 2025; dan</li>
                            <li>Laju Inflasi.</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td class="text-center">2.</td>
                    <td>
                        <b>3.2 Arah Kebijakan Keuangan Daerah</b>
                        <ol type="a" style="padding-left:12px; margin:0;">
                            <li>Terdapat ketidaksesuaian angka proyeksi PAD dan belanja pada dokumen Rankir RKPD (tabel 3.5) dengan SIPD (tabel 3.5. Proyeksi Anggaran Pendapatan dan Belanja Daerah Kabupaten Rembang Tahun {{ $tahunAnggaran }});</li>
                            <li>Kebijakan Belanja belum memprioritaskan belanja untuk mendukung pelayanan dasar;</li>
                            <li>Belum memproyeksikan belanja tidak terduga dan belanja transfer;</li>
                            <li>Proyeksi Belanja belum dirinci berdasarkan kategori rekening belanja;</li>
                            <li>Terdapat proyeksi pembayaran Pembiayaan Utang Daerah sebesar Rp. 4.500.000.000,- yang tidak dijelaskan peruntukannya;</li>
                        </ol>
                    </td>
                    <td>
                        <br>
                        Menyelaraskan proyeksi Pendapatan, Belanja dan Pembiayaan pada dokumen Rankir RKPD tahun {{ $tahunAnggaran }} (bab III) dengan Rankir RKPD tahun {{ $tahunAnggaran }} pada SIPD;<br><br>
                        Dalam penetapan arah kebijakan belanja agar berdasarkan skala prioritas meliputi:<br>
                        - Wajib Mengikat<br>- Pelayanan Dasar<br>- Mandatory spending<br>- Mendukung visi misi Kepala Daerah (RPJMD)<br>
                        Agar memproyeksikan belanja tidak terduga dan belanja transfer;<br>
                        Proyeksi Belanja agar dirinci berdasarkan rekening belanja;<br>
                        Menambahkan penjelasan atas peruntukan pembayaran Pembiayaan Utang Daerah sebesar Rp. 4.500.000.000,-
                    </td>
                </tr>
            </tbody>
        </table>
        @endif

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">19</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 21: TABEL REKOMENDASI ANALISIS ISI BAB (2) ==================== -->
    <section class="page">
        @if($rekomS2->isEmpty())
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width:8%;">NO</th>
                    <th style="width:46%;">CATATAN</th>
                    <th style="width:46%;">REKOMENDASI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">f.</td>
                    <td>Belum menginformasikan rasio ruang fiskal tahunan (grafik/tabel); dan</td>
                    <td>Menambahkan data rasio ruang fiskal tahunan (grafik/tabel); dan</td>
                </tr>
                <tr>
                    <td class="text-center">g.</td>
                    <td>Belum menginformasikan Kapasitas Riil Kemampuan Keuangan Daerah Kabupaten Rembang tahun {{ $tahunAnggaran }} (tabel & penjelasan).</td>
                    <td>Menambahkan data Kapasitas Riil Kemampuan Keuangan Daerah Kabupaten Rembang tahun {{ $tahunAnggaran }} (tabel & penjelasan).</td>
                </tr>
                <tr>
                    <td colspan="3" style="background:#f9f9f9; font-weight:bold;">BAB IV. SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH TAHUN {{ $tahunAnggaran }}</td>
                </tr>
                <tr>
                    <td class="text-center">1.</td>
                    <td>
                        <b>4.2 Arah Kebijakan Pembangunan</b><br>
                        Arah kebijakan pembangunan RKPD Kabupaten Rembang yang tertuang dalam tabel 4.2 (halaman IV-8) belum difokuskan dalam mewujudkan tema pembangunan tahun {{ $tahunAnggaran }} "Pengembangan Perekonomian Daerah termasuk Ekonomi Syariah dan Pariwisata, Didukung SDM Produktif, Tata Kelola Pemerintahan yang Responsif dan Infrastruktur yang Mantap".
                    </td>
                    <td>
                        Memfokuskan arah kebijakan pembangunan RKPD Kabupaten Rembang yang tertuang dalam tabel 4.2 (halaman IV-8) dalam mewujudkan tema pembangunan tahun {{ $tahunAnggaran }} "Pengembangan Perekonomian Daerah termasuk Ekonomi Syariah dan Pariwisata, Didukung SDM Produktif, Tata Kelola Pemerintahan yang Responsif dan Infrastruktur yang Mantap".
                    </td>
                </tr>
                <tr>
                    <td class="text-center">2.</td>
                    <td>
                        <b>4.3 Prioritas Pembangunan</b><br>
                        <b>4.3.3 Prioritas Pembangunan Kabupaten Rembang Tahun {{ $tahunAnggaran }}</b><br>
                        Inventarisasi permasalahan di Bab II belum sepenuhnya menjadi prioritas yang ditangani.
                    </td>
                    <td>
                        <br><br>
                        Menambahkan prioritas pembangunan sesuai dengan permasalahan yang dituangkan dalam Bab II.
                    </td>
                </tr>
            </tbody>
        </table>
        @endif

        <div style="margin-left: 15px;">
            @php $rekomS3 = $getRekomendasis('RKPD.S3'); @endphp
            @if($rekomS3->isNotEmpty())
                <p class="text-justify"><b>Pengujian atas Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan dalam RKPD dan RPJMD</b></p>
                @foreach($rekomS3 as $r)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $r->uraian_rekom !!}
                    </div>
                @endforeach
            @endif

            <p class="text-justify"><b>3) Kesesuaian Program/Outcome dan Indikator pada Rencana Pembangunan Jangka Menengah Daerah (RPJMD) dengan rancangan akhir RKPD</b></p>
            @php $rekomS4 = $getRekomendasis('RKPD.S4'); @endphp
            @if($rekomS4->isNotEmpty())
                @foreach($rekomS4 as $r)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $r->uraian_rekom !!}
                    </div>
                @endforeach
            @else
            <ol type="a" class="text-justify" style="margin-top:0;">
                <li>Menyesuaikan indikator kinerja, target beserta pagu indikatif (Program) yang dicantumkan tabel 5.1 (halaman V-1 sampai halaman V-28), tabel 5.3 Rancangan Akhir RKPD tahun {{ $tahunAnggaran }} (halaman V-85 sampai halaman V-123) dan Lampiran SIPD-RI PDF dan Excel (Renja OPD Kabupaten Rembang tahun {{ $tahunAnggaran }}) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} dengan tabel 4.1 RPJMD Kabupaten Rembang tahun 2025-2029 (halaman IV-34);</li>
                <li>Seluruh Program pada Lampiran SIPD-RI PDF (Renja OPD Kabupaten Rembang tahun {{ $tahunAnggaran }}) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} agar dilengkapi outcome, indikator, satuan, capaian (T-2), target, dan pagu indikatif;</li>
                <li>Memproporsikan anggaran program dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} yang termasuk Program Prioritas Nasional;</li>
                <li>Memproporsikan anggaran pada indikator kinerja "Persentase terbangunnya sarana prasarana persampahan sesuai standar (%)" yang digunakan sebagai tolok ukur pembangunan fisik/infrastruktur;</li>
            </ol>
            @endif
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">20</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 22: REKOMENDASI SUBSTANSI (3 LANJUTAN & 4, 5) ==================== -->
    <section class="page">
        <div style="margin-left: 15px;">
            @if($rekomS4->isEmpty())
            <ol type="a" start="5" class="text-justify" style="margin-top:0;">
                <li>Mengkaji kembali kenaikan proporsi anggaran yang cukup signifikan pada beberapa Program di dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} dibandingkan dengan proporsi anggaran pada dokumen RPJMD Kabupaten Rembang tahun 2025-2029;</li>
                <li>Memproporsikan anggaran untuk Program Pengolahan dan Pemasaran Hasil Perikanan yang merupakan program utama unggulan Bupati dan Wakil Bupati; dan</li>
                <li>Mengkaji kembali yang bukan termasuk program prioritas maupun nomenklatur program utama namun proporsi anggaran yang diberikan pada dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} cukup besar dibandingkan dengan anggaran pada program prioritas.</li>
            </ol>
            @endif

            <p class="text-justify"><b>4) Kewajaran proyeksi pendanaan pada rancangan akhir RKPD</b></p>
            @php $rekomS5 = $getRekomendasis('RKPD.S5'); @endphp
            @if($rekomS5->isNotEmpty())
                @foreach($rekomS5 as $r)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $r->uraian_rekom !!}
                    </div>
                @endforeach
            @else
            <ol type="a" class="text-justify" style="margin-top:0;">
                <li>Penyajian Data APBD tahun anggaran 2026 agar mempedomani Peraturan Bupati Rembang Nomor 4 tahun 2026 tentang Perubahan atas Peraturan Bupati Rembang Nomor 110 tahun 2025 tentang Penjabaran APBD tahun anggaran 2026;</li>
                <li>Penyajian Data Realisasi Anggaran tahun 2025 unaudited pada tabel 3.2 (halaman III-10), tabel 3.3 (halaman III-15), dan tabel 3.4 (halaman III-16) Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} agar disesuaikan dengan LRA tahun 2025 unaudited terupdate;</li>
                <li>Menyesuaikan penyajian Proyeksi APBD tahun anggaran {{ $tahunAnggaran }} pada tabel 3.5 Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} (halaman III-17) dengan data proyeksi APBD tahun anggaran {{ $tahunAnggaran }} di SIPD-RI; dan</li>
                <li>Pada tabel 3.5 Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} (halaman III-17) agar memproyeksikan belanja tidak terduga dan belanja transfer.</li>
            </ol>
            @endif

            <p class="text-justify"><b>5) Kesesuaian Informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional</b></p>
            @php
                $rekomS6a = $getRekomendasis('RKPD.S6.a');
                $rekomS6c = $getRekomendasis('RKPD.S6.c');
                $rekomS6d = $getRekomendasis('RKPD.S6.d');
                $rekomS6e = $getRekomendasis('RKPD.S6.e');
            @endphp
            <ol type="a" class="text-justify" style="margin-top:0;">
                <li><b>Kesesuaian target subkegiatan yang mendukung pelaksanaan Asta Cita berdasarkan hasil rapat koordinasi teknis pembangunan (Rakortekbang) dengan target sub kegiatan pada rancangan akhir RKPD:</b>
                    @if($rekomS6a->isNotEmpty())
                        @foreach($rekomS6a as $r)
                            <div style="margin-top: 4px; margin-bottom: 6px;">{!! $r->uraian_rekom !!}</div>
                        @endforeach
                    @else
                    <ul style="padding-left:15px;">
                        <li>Melakukan perbaikan pemetaan urusan pemerintah daerah terhadap dukungan Asta Cita;</li>
                        <li>Mengidentifikasi pemetaan subkegiatan yang mendukung outcome prioritas (program prioritas) pemerintah pusat; dan</li>
                        <li>Memperbaiki target subkegiatan yang tidak mendukung/kurang mendukung Asta Cita dan outcome prioritas pemerintah pusat.</li>
                    </ul>
                    @endif
                </li>
            </ol>
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">21</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 23: REKOMENDASI SUBSTANSI (5 LANJUTAN & 6, 7) ==================== -->
    <section class="page">
        <div style="margin-left: 15px;">
            <ol type="a" start="2" class="text-justify" style="margin-top:0;">
                <li><b>Kesesuaian dan ketepatan dukungan pelaksanaan ProSN pada rancangan akhir RKPD:</b><br>
                    @if($rekomS6c->isNotEmpty())
                        @foreach($rekomS6c as $r)
                            <div style="margin-top: 4px; margin-bottom: 6px;">{!! $r->uraian_rekom !!}</div>
                        @endforeach
                    @else
                        Penyusunan dokumen perencanaan Kabupaten Rembang agar diselaraskan dan dipetakan sesuai dengan Program Strategis Nasional.
                    @endif
                </li>
                <li><b>Memastikan Program, Kegiatan dan Sub Kegiatan Standar Pelayanan Minimal (SPM) tersedia pada rancangan akhir RKPD:</b><br>
                    @if($rekomS6d->isNotEmpty())
                        @foreach($rekomS6d as $r)
                            <div style="margin-top: 4px; margin-bottom: 6px;">{!! $r->uraian_rekom !!}</div>
                        @endforeach
                    @else
                        - Penetapan subkegiatan dalam dokumen perencanaan Kabupaten Rembang agar diselaraskan dan dipetakan dengan semua subkegiatan Standar Pelayanan Minimal (SPM);<br>
                        - Memproporsikan anggaran untuk semua indikator kinerja subkegiatan Standar Pelayanan Minimal (SPM).
                    @endif
                </li>
                <li><b>Memastikan kesesuaian dukungan Sub Kegiatan Tematik Pembangunan pada rancangan akhir RKPD:</b><br>
                    @if($rekomS6e->isNotEmpty())
                        @foreach($rekomS6e as $r)
                            <div style="margin-top: 4px; margin-bottom: 6px;">{!! $r->uraian_rekom !!}</div>
                        @endforeach
                    @else
                        - Penetapan subkegiatan dalam dokumen perencanaan Kabupaten Rembang agar diselaraskan dan dipetakan dengan semua subkegiatan Tematik Pembangunan / Asta Cita Nasional;<br>
                        - Memproporsikan anggaran untuk semua indikator kinerja subkegiatan Tematik Pembangunan / Asta Cita Nasional.
                    @endif
                </li>
            </ol>

            <p class="text-justify"><b>6) Kesesuaian Informasi pada rancangan akhir RKPD dengan tata cara dan kaidah Perencanaan Pembangunan Daerah lainnya (kesesuaian pengusulan pokok-pokok pikiran Dewan Perwakilan Rakyat Daerah (DPRD) sesuai dengan ketentuan peraturan perundang-undangan)</b><br>
            @php $rekomS7 = $getRekomendasis('RKPD.S7'); @endphp
            @if($rekomS7->isNotEmpty())
                @foreach($rekomS7 as $r)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $r->uraian_rekom !!}
                    </div>
                @endforeach
            @else
                Penjelasan Pokok-Pokok Pikiran DPRD pada Bab IV Sasaran dan Prioritas Pembangunan Daerah Tahun agar dilengkapi dengan rekapitulasi data pokok-pokok pikiran DPRD yang berisi: Program/Kegiatan/Sub Kegiatan/Aktivitas, Indikator, Volume, Kelompok Sasaran, Lokasi dan Perangkat Daerah terkait.
            @endif
            </p>

            <p class="text-justify"><b>7) Pengujian atas Konsistensi Pencantuman Indikator serta Target Kinerja Sasaran dan Program dalam Rancangan Akhir RKPD dan RPJMD</b><br>
            @php $rekomS8 = $getRekomendasis('RKPD.S8'); @endphp
            @if($rekomS8->isNotEmpty())
                @foreach($rekomS8 as $r)
                    <div style="margin-top: 4px; margin-bottom: 6px;">
                        {!! $r->uraian_rekom !!}
                    </div>
                @endforeach
            @else
                Mengkaji kembali atas penetapan target indikator kinerja Tujuan dan Sasaran yang tercantum pada tabel 3.6 Misi, Tujuan Dan Sasaran Pembangunan Kabupaten Rembang Beserta Indikator dan Target Kinerja Tahun 2025-2029 dan tabel 3.9. Teknik Perumusan Program Prioritas Pembangunan Daerah Rancangan Akhir RPJMD tahun 2025-2029 dengan Rancangan Akhir RKPD Kabupaten Rembang tahun {{ $tahunAnggaran }} yang tidak konsisten.
            @endif
            </p>
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">22</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 24: APRESIASI & TANDA TANGAN ==================== -->
    <section class="page">
        <h4>J. Apresiasi</h4>
        <p class="text-justify">Demikian disampaikan hasil reviu atas rancangan akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {{ $tahunAnggaran }}. Kami menyampaikan terima kasih atas bantuan dan kerjasama dari seluruh pejabat/pegawai pada Badan Perencanaan Pembangunan Daerah Kabupaten Rembang atas kesediaannya memberikan data/dokumen yang diperlukan, sehingga dapat mendukung terlaksananya kegiatan reviu Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) tahun {{ $tahunAnggaran }}. Kami mengharapkan agar Saudara beserta seluruh jajaran untuk menindaklanjuti rekomendasi yang telah kami sampaikan.</p>

        <br><br>
        <table style="width:100%; border:none;">
            <tr>
                <td style="width:50%; border:none;"></td>
                <td style="width:50%; border:none; text-align:center;">
                    <p style="margin-bottom:2px;">INSPEKTUR DAERAH<br>KABUPATEN REMBANG</p>
                    <br><br><br><br>
                    <p style="font-weight:bold; text-decoration:underline; margin-bottom:0;">{{ $signer?->name ?? 'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.' }}</p>
                    <p style="margin-top:0; margin-bottom:0;">Pembina Utama Muda</p>
                    <p style="margin-top:0;">NIP. {{ $signer?->nip ?? '19741128 199903 2 003' }}</p>
                </td>
            </tr>
        </table>

        <div class="page-footer" style="clear:both;">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">23</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 25: COLOPHON / HALAMAN PENUTUP ==================== -->
    <section class="page" style="background-color: #f7b267;">
        <div style="padding-top: 75mm; text-align: center;">
            <img src="{{ $logoSrc }}" alt="Logo Rembang" style="width: 150px; margin-bottom: 20px; display:inline-block;">
            <h2 style="font-size: 18pt; margin-bottom: 5px;">INSPEKTORAT DAERAH</h2>
            <h3 style="font-size: 16pt; margin-top: 0;">KABUPATEN REMBANG</h3>
            <p style="margin-top: 40px; font-weight: bold;">{{ $tahunTerbit }}</p>
        </div>

        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">24</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- ==================== HALAMAN 26: HALAMAN SAMPUL BELAKANG (POLOS) ==================== -->
    <section class="page" style="background-color: #f7b267;">
        <div style="min-height: 240mm;"></div>
        <div class="page-footer">
            <table class="footer-table">
                <tr>
                    <td style="text-align:left;">Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {{ $tahunAnggaran }}</td>
                    <td style="text-align:right;">25</td>
                </tr>
            </table>
        </div>
    </section>

</body>
</html>
