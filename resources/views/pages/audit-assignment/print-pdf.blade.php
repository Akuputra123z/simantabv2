<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $assignment->nomor_surat }}</title>
    <style>
        @page {
            size: {{ (isset($paper) && $paper === 'a4') ? '210mm 297mm' : '215mm 330mm' }};
            margin: 2cm 1.5cm 1.5cm 2cm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }

        table, td, th {
            font-family: Arial, Helvetica, sans-serif;
        }

        .page-margin {
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
        }

        /* ===== Kop Surat ===== */
        .kop-surat {
            border-bottom: 4px double black;
            padding-bottom: 8px;
            margin-bottom: 20px;
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
            width: 65px;
            height: auto;
            max-height: 85px;
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
            font-size: 16pt;
            margin: 2px 0;
            text-transform: uppercase;
            font-weight: bold;
            line-height: 1.2;
            letter-spacing: 1px;
        }

        .kop-text p {
            font-size: 9pt;
            margin: 2px 0 0 0;
            line-height: 1.3;
        }

        .kop-text .sub-p {
            font-size: 8pt;
            font-style: italic;
        }

        /* ===== Judul Surat ===== */
        .judul-surat {
            text-align: center;
            margin-bottom: 25px;
        }

        .judul-surat h3 {
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
        }

        .judul-surat p {
            margin: 5px 0 0 0;
            font-size: 11pt;
        }

        /* ===== Section: Dasar (table based) ===== */
        .section-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 11pt;
        }

        .section-table td {
            vertical-align: top;
        }

        .section-label {
            width: 90px;
            font-weight: bold;
        }

        .section-colon {
            width: 15px;
            text-align: center;
            font-weight: bold;
        }

        .section-content {
            text-align: justify;
        }

        ol.list-dinas {
            margin: 0;
            padding-left: 32px;
            font-size: 11pt;
        }

        ol.list-dinas li {
            margin-bottom: 4px;
        }

        /* ===== Section: Kepada / Untuk (float based) ===== */
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
            font-size: 11pt;
        }

        .content {
            margin-left: 100px;
            text-align: left;
            font-size: 11pt;
        }

        .memerintahkan {
            text-align: center;
            font-weight: bold;
            letter-spacing: 4px;
            margin: 20px 0;
            font-size: 11pt;
            page-break-inside: avoid;
        }

        /* ===== Tabel Tim ===== */
        table.tim {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 10px;
            font-size: 11pt;
        }

        table.tim th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            text-align: left;
            padding: 5px 8px;
            font-size: 11pt;
            text-transform: uppercase;
            font-weight: bold;
        }

        table.tim td {
            padding: 5px 8px;
            vertical-align: top;
            border-bottom: 1px solid #ccc;
            font-size: 11pt;
        }

        table.tim tr:last-child td {
            border-bottom: 1px solid black;
        }

        /* ===== List "Untuk" ===== */
        .list-isi {
            margin: 0;
            padding: 0;
            font-size: 11pt;
        }

        .item {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            text-align: justify;
            font-size: 11pt;
        }

        .item-num {
            display: table-cell;
            width: 20px;
            vertical-align: top;
        }

        .item-text {
            display: table-cell;
            vertical-align: top;
        }

        .sub-item {
            margin: 2px 0 0 0;
        }

        .sub-kecamatan {
            margin: 0 0 4px 13px;
            font-style: italic;
        }

        /* ===== Tanda Tangan ===== */
        .signature-group {
            page-break-inside: avoid !important;
            break-inside: avoid;
        }

        .table-ttd-layout {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 25px;
            page-break-inside: avoid !important;
        }
        .table-ttd-layout td {
            border: none !important;
            padding: 0;
            vertical-align: top;
        }

        .ttd-wrapper {
            width: 100%;
            margin-top: 40px;
            text-align: right;
            page-break-inside: avoid !important;
            break-inside: avoid;
        }

        .ttd-container {
            display: inline-block;
            text-align: left;
            width: 400px;
            page-break-inside: avoid !important;
            break-inside: avoid;
        }

        .table-ttd-meta {
            width: 100%;
            font-size: 11pt;
            margin-bottom: 12px;
            page-break-inside: avoid !important;
        }

        .table-ttd-meta td {
            padding: 1px 0;
            vertical-align: top;
        }

        .ttd-jabatan {
            text-align: center;
            font-weight: bold;
            line-height: 1.3;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 11pt;
        }

        .ttd-space {
            height: 75px;
        }

        .ttd-signature {
            text-align: center;
            margin: 6px 0;
        }

        .ttd-signature img {
            height: 85px;
            max-height: 95px;
            width: auto;
            margin-bottom: 5px;
        }

        .ttd-nama {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            font-size: 11pt;
            white-space: nowrap;
        }

        .ttd-nip {
            text-align: center;
            margin: 0;
            font-size: 11pt;
            line-height: 1.3;
        }

        .signature-placeholder {
            color: #999;
            font-style: italic;
            font-size: 11pt;
            margin: 15px 0;
            border-bottom: 1px dotted #ccc;
            display: inline-block;
            padding: 0 20px;
        }

        .footer-note {
            border: 1px dashed black;
            padding: 6px;
            text-align: center;
            font-style: italic;
            font-size: 8.5pt;
            margin-top: 40px;
            font-weight: bold;
            page-break-inside: avoid !important;
            break-inside: avoid;
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
    @endphp

    <div class="page-margin">
        <div class="container">

            <div class="kop-surat">
                <table class="table-kop">
                    <tr>
                        <td class="kop-logo-cell">
                            @if(!empty($logoBase64))
                                <img src="data:image/png;base64,{{ $logoBase64 }}" class="kop-logo" alt="Lambang Kabupaten Rembang">
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

            <div class="judul-surat">
                <h3>Surat Perintah Tugas</h3>
                <p>Nomor : {{ $assignment->nomor_surat }}</p>
            </div>

            {{-- DASAR --}}
            <table class="section-table">
                <tr>
                    <td class="section-label">DASAR</td>
                    <td class="section-colon">:</td>
                    <td class="section-content">
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
                    </td>
                </tr>
            </table>

            <div class="memerintahkan">MEMERINTAHKAN</div>

            {{-- KEPADA --}}
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

                            {{-- 1. Penanggung Jawab (Kepala Inspektorat / IMUNG) --}}
                            @if($penanggungJawab)
                                @php $nomor++; @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $nomor }}.</td>
                                    <td>
                                        <strong>{{ $penanggungJawab->name }}</strong><br>
                                        <span style="font-size: 10pt;">NIP. {{ $penanggungJawab->nip ?? '-' }}</span>
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
                                        <span style="font-size: 10pt;">NIP. {{ $assignment->pengendaliTeknis->nip ?? '-' }}</span>
                                    </td>
                                    <td>Pengendali Teknis</td>
                                </tr>
                            @elseif($assignment->pengendali_teknis)
                                @php $nomor++; @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $nomor }}.</td>
                                    <td>
                                        <strong>{{ $assignment->pengendali_teknis }}</strong><br>
                                        <span style="font-size: 10pt;">NIP. -</span>
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
                                        <span style="font-size: 10pt;">NIP. {{ $assignment->ketuaTim->nip ?? '-' }}</span>
                                    </td>
                                    <td>Ketua Tim</td>
                                </tr>
                            @endif

                            {{-- 4. Anggota --}}
                            @if($anggotaList->isNotEmpty())
                                @foreach($anggotaList as $member)
                                    @php $nomor++; @endphp
                                    <tr>
                                        <td style="text-align: center;">{{ $nomor }}.</td>
                                        <td>
                                            <strong>{{ $member->name }}</strong><br>
                                            <span style="font-size: 10pt;">NIP. {{ $member->nip ?? '-' }}</span>
                                        </td>
                                        <td>{{ $member->pivot->jabatan_tim ?: 'Anggota' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- UNTUK --}}
            <div class="section" style="margin-top: 15px; page-break-inside: avoid;">
                <div class="label">UNTUK :</div>
                <div class="content">
                    <div class="list-isi">
                        <div class="item">
                            <div class="item-num">1.</div>
                            <div class="item-text">
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
                                        <div class="sub-item">- {{ $unit->nama_unit }}</div>
                                        @if(!empty($unit->nama_kecamatan))
                                            <div class="sub-kecamatan">{{ $unit->nama_kecamatan }}</div>
                                        @endif
                                    @endforeach
                                @elseif($assignment->auditProgramDetail && !empty($assignment->auditProgramDetail->objek_pengawasan))
                                    <div class="sub-item">- {{ $assignment->auditProgramDetail->objek_pengawasan }}</div>
                                @else
                                    <div class="sub-item">-</div>
                                @endif
                            </div>
                        </div>

                        <div class="item">
                            <div class="item-num">2.</div>
                            <div class="item-text">
                                Waktu pelaksanaan selama
                                {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($assignment->tanggal_selesai)) + 1 }}
                                hari kerja, mulai tanggal
                                {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->locale('id')->translatedFormat('d F') }}
                                s.d
                                {{ \Carbon\Carbon::parse($assignment->tanggal_selesai)->locale('id')->translatedFormat('d F Y') }}.
                            </div>
                        </div>

                        <div class="item">
                            <div class="item-num">3.</div>
                            <div class="item-text">
                                Melaporkan Hasil Pelaksanaan Tugas Kepada Pejabat Pemberi Tugas.
                            </div>
                        </div>

                        <div class="item">
                            <div class="item-num">4.</div>
                            <div class="item-text">
                                Perintah ini Agar Dilaksanakan dengan Penuh Tanggung Jawab.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TANDA TANGAN & QR CODE --}}
            <div class="signature-group">
                <table class="table-ttd-layout">
                    <tr>
                        {{-- Kolom Kiri: QR Code Verifikasi (Rata Bawah agar sejajar & tidak bentrok dengan TTD) --}}
                        <td style="width: 44%; vertical-align: bottom; text-align: left; padding-bottom: 5px;">
                            @if($assignment->isSigned() && !empty($qrBase64))
                                <div style="display: inline-block; border: 1px solid #cbd5e1; background: #f8fafc; padding: 7px 10px; border-radius: 4px; width: 250px;">
                                    <table style="border-collapse: collapse; border: none; margin: 0; padding: 0; width: 100%;">
                                        <tr>
                                            <td style="width: 66px; vertical-align: middle; padding: 0 8px 0 0; border: none !important;">
                                                <img src="{{ $qrBase64 }}" width="64" height="64" style="width: 64px; height: 64px; display: block;" alt="QR Code Verifikasi">
                                            </td>
                                            <td style="vertical-align: middle; border: none !important; font-size: 7.5pt; line-height: 1.35; color: #334155;">
                                                <strong style="color: #0f172a; font-size: 8pt; display: block; margin-bottom: 2px;">DITANDATANGANI ELEKTRONIK</strong>
                                                <span style="font-size: 7.5pt; color: #1e293b;">Inspektorat Kab. Rembang</span><br>
                                                <span style="color: #64748b; font-size: 6.5pt;">Scan QR untuk validasi naskah dinas</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        </td>

                        {{-- Kolom Tengah: Spasi Pemisah --}}
                        <td style="width: 12%; border: none !important;">&nbsp;</td>

                        {{-- Kolom Kanan: Blok Tanda Tangan --}}
                        <td style="width: 44%; vertical-align: top; text-align: center;">
                            <div class="ttd-container" style="display: block; text-align: center; width: 100%;">
                                <table class="table-ttd-meta" style="margin: 0 auto 6px auto; width: 220px; font-size: 10pt;">
                                    <tr>
                                        <td style="width: 45%; text-align: left;">Ditetapkan di</td>
                                        <td style="width: 5%; text-align: center;">:</td>
                                        <td style="width: 50%; text-align: left;">Rembang</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left;">Pada tanggal</td>
                                        <td style="text-align: center;">:</td>
                                        <td style="text-align: left;">{{ ($assignment->created_at ?? now())->locale('id')->translatedFormat('d F Y') }}</td>
                                    </tr>
                                </table>

                                <div class="ttd-jabatan" style="font-size: 10.5pt; margin-bottom: 8px;">
                                    INSPEKTUR DAERAH<br>
                                    KABUPATEN REMBANG
                                </div>

                                @if($assignment->isSigned() && !empty($signatureBase64))
                                    <div class="ttd-signature" style="min-height: 70px; margin: 4px 0;">
                                        <img src="{{ $signatureBase64 }}" alt="Tanda Tangan" style="height: 68px; max-height: 75px; width: auto;">
                                    </div>
                                @else
                                    <div class="ttd-space" style="height: 65px;"></div>
                                @endif

                                @php
                                    $defaultInspektur = \App\Models\User::find(2);
                                @endphp
                                @if($assignment->isSigned() && $assignment->signer)
                                    <p class="ttd-nama" style="font-size: 10.5pt;">{{ $assignment->signer->name }}</p>
                                    <p class="ttd-nip" style="font-size: 10pt;">
                                        {{ $assignment->signer->pangkat_gol ?? ($defaultInspektur?->pangkat_gol ?? 'Pembina Utama Ahli Muda (IV/c)') }}<br>
                                        NIP. {{ $assignment->signer->nip ?? '-' }}
                                    </p>
                                @else
                                    <p class="ttd-nama" style="font-size: 10.5pt;">
                                        {{ $defaultInspektur?->name ?? 'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.' }}
                                    </p>
                                    <p class="ttd-nip" style="font-size: 10pt;">
                                        {{ $defaultInspektur?->pangkat_gol ?? 'Pembina Utama Ahli Muda (IV/c)' }}<br>
                                        NIP. {{ $defaultInspektur?->nip ?? '197411281999032003' }}
                                    </p>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="footer-note" style="margin-top: 25px;">
                    Dilarang meminta dan atau menerima pemberian dalam bentuk apapun dari siapapun.
                </div>
            </div>

        </div>
    </div>
</body>
</html>