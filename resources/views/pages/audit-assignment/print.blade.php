<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tugas - {{ $assignment->nomor_surat }}</title>
    <style id="page-style">
        @page {
            size: {{ (isset($paper) && $paper === 'a4') ? 'A4' : '215mm 330mm' }};
            margin: 20mm 15mm 15mm 20mm;
        }
    </style>
    <style>
        /* ===== Reset & Base Typography ===== */
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.45;
            margin: 0;
            padding: 0;
            color: #000;
            background-color: #f1f5f9;
        }
        table, td, th {
            font-family: Arial, Helvetica, sans-serif;
        }

        /* ===== Toolbar Atas (User Friendly Screen UI) ===== */
        .toolbar-wrapper {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #cbd5e1;
            box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.08);
            padding: 10px 20px;
        }
        .toolbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .toolbar-left, .toolbar-center, .toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 10pt;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease-in-out;
            font-family: inherit;
        }
        .btn-back {
            background: #fff;
            color: #475569;
            border-color: #cbd5e1;
        }
        .btn-back:hover {
            background: #f8fafc;
            color: #1e293b;
        }
        .btn-print-primary {
            background: #2563eb;
            color: #fff;
            border-color: #1d4ed8;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .btn-print-primary:hover {
            background: #1d4ed8;
        }
        .btn-pdf {
            background: #dc2626;
            color: #fff;
            border-color: #b91c1c;
        }
        .btn-pdf:hover {
            background: #b91c1c;
        }
        .btn-sign {
            background: #16a34a;
            color: #fff;
            border-color: #15803d;
        }
        .btn-sign:hover {
            background: #15803d;
        }
        .btn-unsign {
            background: #f59e0b;
            color: #fff;
            border-color: #d97706;
        }
        .btn-unsign:hover {
            background: #d97706;
        }

        /* Paper Size Selector Pill */
        .paper-selector {
            display: inline-flex;
            background: #e2e8f0;
            padding: 3px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }
        .paper-btn {
            padding: 6px 14px;
            font-size: 9.5pt;
            font-weight: bold;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            background: transparent;
            color: #475569;
            transition: all 0.15s ease;
        }
        .paper-btn.active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }

        .badge-status-signed {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 9pt;
            font-weight: bold;
        }

        /* ===== Canvas & Paper Preview ===== */
        .preview-canvas {
            padding: 24px 16px 60px;
            display: flex;
            justify-content: center;
        }
        .paper-sheet {
            background: #ffffff;
            color: #000;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0,0,0,0.04);
            padding: 20mm 15mm 15mm 20mm;
            box-sizing: border-box;
            transition: width 0.2s ease, min-height 0.2s ease;
            position: relative;
        }

        /* Dimensi Fisik Kertas Screen */
        .paper-sheet.sheet-f4 {
            width: 215mm;
            min-height: 330mm;
        }
        .paper-sheet.sheet-a4 {
            width: 210mm;
            min-height: 297mm;
        }

        /* ===== Kop Surat ===== */
        .kop-surat {
            border-bottom: 4px double #000;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }
        .table-kop {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin: 0;
            padding: 0;
        }
        .table-kop td {
            border: none !important;
            padding: 0;
            vertical-align: middle;
        }
        .kop-logo-cell {
            width: 75px;
            text-align: center;
        }
        .kop-spacer-cell {
            width: 75px;
        }
        .kop-logo {
            width: 70px;
            height: auto;
            max-height: 90px;
            display: inline-block;
        }
        .kop-text-cell {
            text-align: center;
        }
        .kop-text h1 {
            font-size: 13pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-text h2 {
            font-size: 17pt;
            margin: 2px 0;
            text-transform: uppercase;
            font-weight: bold;
            line-height: 1.2;
            letter-spacing: 0.8px;
        }
        .kop-text p {
            font-size: 9.5pt;
            margin: 2px 0 0 0;
            line-height: 1.3;
        }
        .kop-text .sub-p {
            font-size: 8.5pt;
            font-style: italic;
        }

        /* ===== Judul Surat ===== */
        .judul-surat {
            text-align: center;
            margin-bottom: 20px;
        }
        .judul-surat h3 {
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .judul-surat p {
            margin: 4px 0 0 0;
            font-size: 11pt;
        }

        /* ===== Sections (Dasar, Kepada, Untuk) ===== */
        .section {
            width: 100%;
            margin-bottom: 10px;
        }
        .section::after {
            content: '';
            display: block;
            clear: both;
        }
        .label {
            float: left;
            width: 95px;
            font-weight: bold;
        }
        .content {
            margin-left: 100px;
            text-align: left;
        }
        ol.list-dinas {
            margin: 0;
            padding-left: 30px;
        }
        ol.list-dinas li {
            margin-bottom: 4px;
            text-align: justify;
        }
        .memerintahkan {
            text-align: center;
            font-weight: bold;
            letter-spacing: 4px;
            margin: 18px 0;
            font-size: 11pt;
            page-break-inside: avoid;
        }
        table.tim {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 10px;
            font-size: 10.5pt;
        }
        table.tim th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 5px 8px;
            font-size: 10.5pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        table.tim td {
            padding: 5px 8px;
            vertical-align: top;
            border-bottom: 1px solid #d1d5db;
            font-size: 10.5pt;
        }
        table.tim tr:last-child td {
            border-bottom: 1px solid #000;
        }
        .list-isi {
            margin: 0;
            padding: 0;
            font-size: 11pt;
        }
        .item {
            margin-bottom: 5px;
            font-size: 11pt;
            text-align: justify;
        }

        /* ===== AREA TANDA TANGAN & QR CODE (REVISI ELEGAN & TIDAK BERDEKATAN) ===== */
        .ttd-section {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .table-ttd-layout {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin: 0;
            padding: 0;
        }
        .table-ttd-layout td {
            border: none !important;
            padding: 0;
        }

        /* Kolom QR Code (Kiri Bawah) */
        .col-qr {
            width: 44%;
            vertical-align: bottom;
            text-align: left;
            padding-bottom: 4px;
        }
        .qr-card {
            display: inline-block;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 8px 10px;
            border-radius: 6px;
            max-width: 290px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .table-qr-inner {
            border-collapse: collapse;
            border: none;
            margin: 0;
            padding: 0;
        }
        .table-qr-inner td {
            border: none !important;
            padding: 0;
            vertical-align: middle;
        }
        .qr-code-img {
            width: 66px;
            height: 66px;
            display: block;
            border-radius: 2px;
        }
        .qr-meta-text {
            padding-left: 8px;
            font-size: 7.5pt;
            line-height: 1.35;
            color: #334155;
        }
        .qr-meta-title {
            font-weight: bold;
            font-size: 8pt;
            color: #0f172a;
            display: block;
            letter-spacing: 0.2px;
            margin-bottom: 1px;
        }
        .qr-meta-sub {
            color: #64748b;
            font-size: 7pt;
            font-style: italic;
            display: block;
            margin-top: 2px;
        }

        /* Kolom Spacer (Jarak Pemisah Bebas Tabrakan) */
        .col-spacer {
            width: 12%;
            border: none !important;
        }

        /* Kolom Tanda Tangan (Kanan) */
        .col-ttd {
            width: 44%;
            vertical-align: top;
            text-align: right;
        }
        .ttd-container {
            display: inline-block;
            text-align: center;
            width: 310px;
        }
        .table-ttd-meta {
            width: 250px;
            margin-left: auto;
            margin-right: auto;
            font-size: 11pt;
            margin-bottom: 8px;
            text-align: left;
            border-collapse: collapse;
        }
        .table-ttd-meta td {
            padding: 1px 0;
            vertical-align: top;
            border: none !important;
        }
        .ttd-jabatan {
            text-align: center;
            font-weight: bold;
            line-height: 1.3;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-size: 11pt;
        }
        .ttd-img-wrap {
            margin: 4px 0;
            text-align: center;
        }
        .ttd-img {
            height: 80px;
            max-height: 85px;
            width: auto;
            display: inline-block;
        }
        .ttd-space-blank {
            height: 75px;
        }
        .ttd-nama {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            font-size: 11pt;
        }
        .ttd-nip {
            text-align: center;
            margin: 2px 0 0 0;
            font-size: 10pt;
            line-height: 1.3;
        }

        .footer-note {
            border: 1px dashed #000;
            padding: 7px 10px;
            text-align: center;
            font-style: italic;
            font-size: 9pt;
            margin-top: 30px;
            font-weight: bold;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* ===== Mode Cetak (Print Stylesheet) ===== */
        @media print {
            .no-print, .toolbar-wrapper {
                display: none !important;
            }
            body {
                background: #fff !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .preview-canvas {
                padding: 0 !important;
                margin: 0 !important;
            }
            .paper-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
            }
            .qr-card {
                border-color: #94a3b8 !important;
                background: #f8fafc !important;
            }
        }
    </style>
</head>
<body>

    @php
        if (empty($logoBase64)) {
            $logoPath = public_path('images/logo/image.png');
            if (!file_exists($logoPath)) {
                $logoPath = public_path('images/logos/rembang.png');
            }
            $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
        }
        $currentPaper = isset($paper) && in_array(strtolower($paper), ['a4', 'f4', 'folio'], true) ? strtolower($paper) : 'f4';
        if ($currentPaper === 'folio') { $currentPaper = 'f4'; }
        $canSign = auth()->check() && (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('kepala_inspektorat'));
    @endphp

    {{-- ===== TOOLBAR HEADER NAVIGASI USER FRIENDLY ===== --}}
    <div class="toolbar-wrapper no-print">
        <div class="toolbar-container">
            <div class="toolbar-left">
                <a href="{{ route('audit-assignment.show', $assignment) }}" class="btn-toolbar btn-back">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
                <span style="font-size: 10pt; font-weight: bold; color: #1e293b; margin-left: 4px;">
                    {{ $assignment->nomor_surat }}
                </span>
            </div>

            {{-- Selector Kertas Interaktif --}}
            <div class="toolbar-center">
                <span style="font-size: 9.5pt; font-weight: 600; color: #475569;">Ukuran Kertas:</span>
                <div class="paper-selector">
                    <button type="button" class="paper-btn {{ $currentPaper === 'f4' ? 'active' : '' }}" onclick="switchPaperSize('f4')">
                        F4 / Folio (215×330 mm)
                    </button>
                    <button type="button" class="paper-btn {{ $currentPaper === 'a4' ? 'active' : '' }}" onclick="switchPaperSize('a4')">
                        A4 (210×297 mm)
                    </button>
                </div>
            </div>

            <div class="toolbar-right">
                @if($assignment->isSigned())
                    <span class="badge-status-signed">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Ditandatangani Elektronik
                    </span>
                    @if($canSign)
                        <form action="{{ route('audit-assignment.batal-sign', $assignment) }}" method="POST" style="display:inline;" onsubmit="return confirm('Batalkan tanda tangan Surat Tugas ini?')">
                            @csrf
                            <input type="hidden" name="from" value="preview">
                            <button type="submit" class="btn-toolbar btn-unsign">Batal TTD</button>
                        </form>
                    @endif
                @else
                    @if($canSign)
                        <form action="{{ route('audit-assignment.sign', $assignment) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="from" value="preview">
                            <button type="submit" class="btn-toolbar btn-sign" onclick="return confirm('Tandatangani Surat Tugas ini secara resmi?')">
                                ✓ Tandatangani Surat Tugas
                            </button>
                        </form>
                    @else
                        <span style="font-size: 9pt; color: #64748b; font-style: italic;">(Draf Surat Tugas)</span>
                    @endif
                @endif

                <a id="btn-download-pdf" href="{{ route('audit-assignment.print', ['auditAssignment' => $assignment, 'paper' => $currentPaper]) }}" target="_blank" class="btn-toolbar btn-pdf">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh PDF (<span id="pdf-paper-label">{{ strtoupper($currentPaper) }}</span>)
                </a>

                <button type="button" onclick="window.print()" class="btn-toolbar btn-print-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Surat Tugas
                </button>
            </div>
        </div>
    </div>

    {{-- ===== CANVAS LEMBAR KERTAS FISIK ===== --}}
    <div class="preview-canvas">
        <div id="paper-sheet" class="paper-sheet {{ $currentPaper === 'a4' ? 'sheet-a4' : 'sheet-f4' }}">
            
            {{-- KOP SURAT PEMKAB REMBANG --}}
            <div class="kop-surat">
                <table class="table-kop">
                    <tr>
                        <td class="kop-logo-cell">
                            @if(!empty($logoBase64))
                                <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Lambang Kabupaten Rembang" class="kop-logo">
                            @endif
                        </td>
                        <td class="kop-text-cell">
                            <div class="kop-text">
                                <h1>Pemerintah Kabupaten Rembang</h1>
                                <h2>Inspektorat Daerah</h2>
                                <p>Jl. Raya Rembang – Lasem Km 1.1 Telp. ( 0295 ) 691320 Fax. (0295) 693525</p>
                                <p class="sub-p">Rembang 59219 Email : inspektorat@rembangkab.go.id</p>
                            </div>
                        </td>
                        <td class="kop-spacer-cell"></td>
                    </tr>
                </table>
            </div>

            {{-- JUDUL SURAT --}}
            <div class="judul-surat">
                <h3>Surat Perintah Tugas</h3>
                <p>Nomor : {{ $assignment->nomor_surat }}</p>
            </div>

            {{-- DASAR HUKUM --}}
            <div class="section">
                <div class="label">DASAR :</div>
                <div class="content">
                    <ol class="list-dinas">
                        @php
                            $dasarList = ($assignment->dasarPeraturans && $assignment->dasarPeraturans->isNotEmpty())
                                ? $assignment->dasarPeraturans
                                : $assignment->effective_dasar_peraturans;
                        @endphp
                        @forelse($dasarList as $dasar)
                            <li>{{ $dasar->nama }}</li>
                        @empty
                            <li>Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Perubahan Kedua atas Peraturan Daerah Nomor 5 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Kabupaten Rembang.</li>
                            <li>Peraturan Daerah Kabupaten Rembang Nomor 2 Tahun 2024 tentang Anggaran Pendapatan dan Belanja Daerah Kabupaten Rembang Tahun Anggaran 2025.</li>
                            <li>Peraturan Bupati Rembang Nomor 54 Tahun 2021 tentang Kedudukan Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Inspektorat Kabupaten Rembang.</li>
                            <li>Peraturan Bupati Rembang Nomor 2 Tahun 2024 tentang Pembinaan dan Pengawasan Perangkat Daerah dan Desa.</li>
                            <li>Keputusan Bupati Rembang Nomor 100.3.3.2/0132/2025 tanggal 9 Januari 2025 tentang Program Kerja Pengawasan Tahunan Berbasis Resiko Inspektorat Kabupaten Rembang Tahun 2025.</li>
                        @endforelse
                    </ol>
                </div>
            </div>

            <div class="memerintahkan">MEMERINTAHKAN</div>

            {{-- KEPADA (SUSUNAN TIM) --}}
            <div class="section">
                <div class="label">KEPADA :</div>
                <div class="content">
                    <table class="tim">
                        <thead>
                            <tr>
                                <th style="width: 8%; text-align: center;">No</th>
                                <th style="width: 52%;">Nama / NIP</th>
                                <th style="width: 40%;">Jabatan Dalam Tim</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $nomor = 0;
                                $penanggungJawab = \App\Models\User::find(2) 
                                    ?? \App\Models\User::where('jabatan', 'like', '%Inspektur%')->where('jabatan', 'not like', '%Pembantu%')->first();

                                $excludeIds = array_filter([
                                    $penanggungJawab?->id,
                                    $assignment->pengendali_teknis_id,
                                    $assignment->ketua_tim_id,
                                ]);

                                $anggotaList = $assignment->members 
                                    ? $assignment->members->reject(fn($m) => in_array($m->id, $excludeIds)) 
                                    : collect();
                            @endphp

                            {{-- 1. Penanggung Jawab --}}
                            @if($penanggungJawab)
                                @php $nomor++; @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $nomor }}.</td>
                                    <td>
                                        <strong>{{ $penanggungJawab->name }}</strong><br>
                                        <span style="font-size: 10.5pt; color: #333;">NIP. {{ $penanggungJawab->nip ?? '-' }}</span>
                                    </td>
                                    <td>Penanggung Jawab</td>
                                </tr>
                            @endif

                            {{-- 2. Pengendali Teknis --}}
                            @if($assignment->pengendaliTeknis)
                                @php $nomor++; @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $nomor }}.</td>
                                    <td>
                                        <strong>{{ $assignment->pengendaliTeknis->name }}</strong><br>
                                        <span style="font-size: 10.5pt; color: #333;">NIP. {{ $assignment->pengendaliTeknis->nip ?? '-' }}</span>
                                    </td>
                                    <td>Pengendali Teknis</td>
                                </tr>
                            @elseif($assignment->pengendali_teknis)
                                @php $nomor++; @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $nomor }}.</td>
                                    <td>
                                        <strong>{{ $assignment->pengendali_teknis }}</strong><br>
                                        <span style="font-size: 10.5pt; color: #333;">NIP. -</span>
                                    </td>
                                    <td>Pengendali Teknis</td>
                                </tr>
                            @endif

                            {{-- 3. Ketua Tim --}}
                            @if($assignment->ketuaTim)
                                @php $nomor++; @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $nomor }}.</td>
                                    <td>
                                        <strong>{{ $assignment->ketuaTim->name }}</strong><br>
                                        <span style="font-size: 10.5pt; color: #333;">NIP. {{ $assignment->ketuaTim->nip ?? '-' }}</span>
                                    </td>
                                    <td>Ketua Tim</td>
                                </tr>
                            @endif

                            {{-- 4. Anggota Tim --}}
                            @if($anggotaList->isNotEmpty())
                                @foreach($anggotaList as $member)
                                    @php $nomor++; @endphp
                                    <tr>
                                        <td style="text-align: center;">{{ $nomor }}.</td>
                                        <td>
                                            <strong>{{ $member->name }}</strong><br>
                                            <span style="font-size: 10.5pt; color: #333;">NIP. {{ $member->nip ?? '-' }}</span>
                                        </td>
                                        <td>{{ $member->pivot->jabatan_tim ?: 'Anggota' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- UNTUK (TUGAS & WAKTU PELAKSANAAN) --}}
            <div class="section" style="margin-top: 15px; page-break-inside: avoid;">
                <div class="label">UNTUK :</div>
                <div class="content">
                    <div class="list-isi">
                        <div class="item">
                            <span style="display:inline-block; width:18px;">1.</span>
                            @php
                                $subProgram = trim($assignment->auditProgramDetail->nama_detail_program ?? '');
                                $jenis = trim($assignment->jenis_pengawasan ?: ($assignment->auditProgramDetail->jenis_kegiatan ?? ''));

                                if (!empty($subProgram)) {
                                    if (preg_match('/^(audit|reviu|review|evaluasi|monitoring|pengawasan|pemeriksaan)\b/i', $subProgram)) {
                                        $uraianTugas = $subProgram;
                                    } elseif (!empty($jenis) && stripos($subProgram, $jenis) !== false) {
                                        $uraianTugas = $subProgram;
                                    } elseif (!empty($jenis)) {
                                        $uraianTugas = $jenis . ' ' . $subProgram;
                                    } else {
                                        $uraianTugas = $subProgram;
                                    }
                                } else {
                                    $uraianTugas = !empty($jenis) ? $jenis : 'Pengawasan';
                                }
                            @endphp
                            Melaksanakan {{ $uraianTugas }} pada :

                            @if($assignment->unitDiperiksas && $assignment->unitDiperiksas->isNotEmpty())
                                @foreach($assignment->unitDiperiksas as $unit)
                                    <div style="margin-left:18px;">
                                        - {{ $unit->nama_unit }}
                                    </div>
                                    @if(!empty($unit->nama_kecamatan))
                                    <div style="margin-left:35px; font-style:italic;">
                                        {{ $unit->nama_kecamatan }}
                                    </div>
                                    @endif
                                @endforeach
                            @elseif($assignment->auditProgramDetail && !empty($assignment->auditProgramDetail->objek_pengawasan))
                                <div style="margin-left:18px;">
                                    - {{ $assignment->auditProgramDetail->objek_pengawasan }}
                                </div>
                            @else
                                <div style="margin-left:18px;">
                                    -
                                </div>
                            @endif
                        </div>

                        <div class="item">
                            <span style="display:inline-block; width:18px;">2.</span>
                            Waktu pelaksanaan selama {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($assignment->tanggal_selesai)) + 1 }} hari kerja, mulai tanggal {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->locale('id')->translatedFormat('d F') }} s.d {{ \Carbon\Carbon::parse($assignment->tanggal_selesai)->locale('id')->translatedFormat('d F Y') }}.
                        </div>

                        <div class="item">
                            <span style="display:inline-block; width:18px;">3.</span>
                            Melaporkan Hasil Pelaksanaan Tugas Kepada Pejabat Pemberi Tugas.
                        </div>

                        <div class="item">
                            <span style="display:inline-block; width:18px;">4.</span>
                            Perintah ini Agar Dilaksanakan dengan Penuh Tanggung Jawab.
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== AREA TANDA TANGAN & QR CODE (TIDAK BERDEKATAN & PROPORSIONAL) ===== --}}
            <div class="ttd-section">
                <table class="table-ttd-layout">
                    <tr>
                        {{-- Kolom Kiri: QR Code Sertifikasi TTE (Bottom Aligned, Ramping & Formal) --}}
                        <td class="col-qr">
                            @if($assignment->isSigned() && !empty($qrBase64))
                                <div class="qr-card">
                                    <table class="table-qr-inner">
                                        <tr>
                                            <td style="width: 70px;">
                                                <img src="{{ $qrBase64 }}" class="qr-code-img" alt="QR Code Verifikasi">
                                            </td>
                                            <td class="qr-meta-text">
                                                <span class="qr-meta-title">DITANDATANGANI SECARA ELEKTRONIK</span>
                                                Inspektorat Daerah Kabupaten Rembang
                                                <span class="qr-meta-sub">Pindai QR untuk verifikasi keaslian dokumen</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        </td>

                        {{-- Kolom Tengah: Spacer Pemisah (Jarak Aman Bebas Tabrakan) --}}
                        <td class="col-spacer">&nbsp;</td>

                        {{-- Kolom Kanan: Tanda Tangan Pejabat (Top Aligned, Centered Block) --}}
                        <td class="col-ttd">
                            <div class="ttd-container">
                                <table class="table-ttd-meta">
                                    <tr>
                                        <td style="width: 42%;">Ditetapkan di</td>
                                        <td style="width: 6%; text-align: center;">:</td>
                                        <td style="width: 52%;">Rembang</td>
                                    </tr>
                                    <tr>
                                        <td>Pada tanggal</td>
                                        <td style="text-align: center;">:</td>
                                        <td>{{ ($assignment->created_at ?? now())->locale('id')->translatedFormat('d F Y') }}</td>
                                    </tr>
                                </table>

                                <div class="ttd-jabatan">
                                    INSPEKTUR DAERAH<br>KABUPATEN REMBANG
                                </div>

                                @if($assignment->isSigned() && !empty($signatureBase64))
                                    <div class="ttd-img-wrap">
                                        <img src="{{ $signatureBase64 }}" alt="Tanda Tangan" class="ttd-img">
                                    </div>
                                @elseif($assignment->isSigned() && $assignment->signer && $assignment->signer->signature)
                                    <div class="ttd-img-wrap">
                                        <img src="{{ Storage::url($assignment->signer->signature) }}" alt="Tanda Tangan" class="ttd-img">
                                    </div>
                                @else
                                    <div class="ttd-space-blank"></div>
                                @endif

                                @php
                                    $defaultInspektur = \App\Models\User::find(2);
                                @endphp
                                @if($assignment->isSigned() && $assignment->signer)
                                    <p class="ttd-nama">{{ $assignment->signer->name }}</p>
                                    <p class="ttd-nip">
                                        {{ $assignment->signer->pangkat_gol ?? ($defaultInspektur?->pangkat_gol ?? 'Pembina Utama Ahli Muda (IV/c)') }}<br>
                                        NIP. {{ $assignment->signer->nip ?? '-' }}
                                    </p>
                                @else
                                    <p class="ttd-nama">{{ $defaultInspektur?->name ?? 'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.' }}</p>
                                    <p class="ttd-nip">{{ $defaultInspektur?->pangkat_gol ?? 'Pembina Utama Ahli Muda (IV/c)' }}<br>NIP. {{ $defaultInspektur?->nip ?? '197411281999032003' }}</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- CATATAN KAKI RESMI INTEGRITAS --}}
            <div class="footer-note">
                Dilarang meminta dan atau menerima pemberian dalam bentuk apapun dari siapapun.
            </div>

        </div>
    </div>

    {{-- Script Beralih Ukuran Kertas Interaktif --}}
    <script>
        function switchPaperSize(size) {
            const paperSheet = document.getElementById('paper-sheet');
            const pageStyle = document.getElementById('page-style');
            const pdfLink = document.getElementById('btn-download-pdf');
            const pdfLabel = document.getElementById('pdf-paper-label');
            const buttons = document.querySelectorAll('.paper-btn');

            buttons.forEach(btn => btn.classList.remove('active'));

            if (size === 'a4') {
                paperSheet.classList.remove('sheet-f4');
                paperSheet.classList.add('sheet-a4');
                pageStyle.innerHTML = '@page { size: A4; margin: 20mm 15mm 15mm 20mm; }';
                buttons[1].classList.add('active');
                if (pdfLink) {
                    pdfLink.href = "{{ route('audit-assignment.print', $assignment) }}?paper=a4";
                }
                if (pdfLabel) {
                    pdfLabel.innerText = 'A4';
                }
            } else {
                paperSheet.classList.remove('sheet-a4');
                paperSheet.classList.add('sheet-f4');
                pageStyle.innerHTML = '@page { size: 215mm 330mm; margin: 20mm 15mm 15mm 20mm; }';
                buttons[0].classList.add('active');
                if (pdfLink) {
                    pdfLink.href = "{{ route('audit-assignment.print', $assignment) }}?paper=f4";
                }
                if (pdfLabel) {
                    pdfLabel.innerText = 'F4';
                }
            }
        }
    </script>
</body>
</html>