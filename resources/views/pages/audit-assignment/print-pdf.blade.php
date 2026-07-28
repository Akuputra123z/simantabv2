<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $assignment->nomor_surat }}</title>
    <style>
        @page {
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
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .kop-text h1 {
            font-size: 13pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }

        .kop-text h2 {
            font-size: 16pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
            line-height: 1.2;
        }

        .kop-text p {
            font-size: 9pt;
            margin: 2px 0 0 0;
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
        }

        .content {
            margin-left: 100px;
            text-align: left;
        }

        .memerintahkan {
            text-align: center;
            font-weight: bold;
            letter-spacing: 4px;
            margin: 20px 0;
            font-size: 12pt;
            page-break-inside: avoid;
        }

        /* ===== Tabel Tim ===== */
        table.tim {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        table.tim th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            text-align: left;
            padding: 5px 8px;
            font-size: 10pt;
            text-transform: uppercase;
            font-weight: bold;
        }

        table.tim td {
            padding: 5px 8px;
            vertical-align: top;
            border-bottom: 1px solid #ccc;
        }

        table.tim tr:last-child td {
            border-bottom: 1px solid black;
        }

        /* ===== List "Untuk" ===== */
        .list-isi {
            margin: 0;
            padding: 0;
        }

        .item {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            text-align: justify;
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

        .ttd-wrapper {
            width: 100%;
            margin-top: 30px;
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
            font-size: 10pt;
            margin-bottom: 10px;
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
            margin-bottom: 50px;
            text-transform: uppercase;
        }

        .ttd-signature {
            text-align: center;
        }

        .ttd-signature img {
            height: 70px;
            margin-bottom: 5px;
        }

        .ttd-nama {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            font-size: 10.5pt;
            white-space: nowrap;
        }

        .ttd-nip {
            text-align: center;
            margin: 0;
            font-size: 10pt;
            line-height: 1.2;
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
    <div class="page-margin">
        <div class="container">

            <div class="kop-surat">
                <div class="kop-text">
                    <h1>Pemerintah Kabupaten Rembang</h1>
                    <h2>Inspektorat Daerah</h2>
                    <p>Jl. Raya Rembang – Lasem Km 1.1 Telp. ( 0295 ) 691320 Fax. (0295) 693525</p>
                    <p class="sub-p">Rembang 59219 Email : inspektorat@rembangkab.go.id</p>
                </div>
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
                            <li>Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Perubahan Kedua atas Peraturan Daerah Nomor 5 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Kabupaten Rembang.</li>
                            <li>Peraturan Daerah Kabupaten Rembang Nomor 2 Tahun 2024 tentang Anggaran Pendapatan dan Belanja Daerah Kabupaten Rembang Tahun Anggaran 2025.</li>
                            <li>Peraturan Bupati Rembang Nomor 54 Tahun 2021 tentang Kedudukan Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Inspektorat Kabupaten Rembang.</li>
                            <li>Peraturan Bupati Rembang Nomor 2 Tahun 2024 tentang Pembinaan dan Pengawasan Perangkat Daerah dan Desa.</li>
                            <li>Keputusan Bupati Rembang Nomor 100.3.3.2/0132/2025 tanggal 9 Januari 2025 tentang Program Kerja Pengawasan Tahunan Berbasis Resiko Inspektorat Kabupaten Rembang Tahun 2025.</li>
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
                            @php $nomor = 0; @endphp

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
                            @endif

                            @if($assignment->members && $assignment->members->isNotEmpty())
                                @foreach($assignment->members as $member)
                                    @php $nomor++; @endphp
                                    <tr>
                                        <td style="text-align: center;">{{ $nomor }}.</td>
                                        <td>
                                            <strong>{{ $member->name }}</strong><br>
                                            <span style="font-size: 10pt;">NIP. {{ $member->nip ?? '-' }}</span>
                                        </td>
                                        <td>{{ $member->pivot->jabatan_tim ?? 'Anggota Tim' }}</td>
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
                                Melaksanakan {{ $assignment->jenis_pengawasan }} pada :

                                @if($assignment->unitDiperiksas && $assignment->unitDiperiksas->isNotEmpty())
                                    @foreach($assignment->unitDiperiksas as $unit)
                                        <div class="sub-item">- {{ $unit->nama_unit }}</div>
                                        <div class="sub-kecamatan">{{ $unit->nama_kecamatan }}</div>
                                    @endforeach
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
                                {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->translatedFormat('d F') }}
                                s.d
                                {{ \Carbon\Carbon::parse($assignment->tanggal_selesai)->translatedFormat('d F Y') }}.
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

            {{-- TANDA TANGAN --}}
            <div class="signature-group">
                <div class="ttd-wrapper">
                    <div class="ttd-container">
                        <table class="table-ttd-meta">
                            <tr>
                                <td style="width: 40%;">Ditetapkan di</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 55%;">Rembang</td>
                            </tr>
                            <tr>
                                <td>Pada tanggal</td>
                                <td>:</td>
                                <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
                            </tr>
                        </table>

                        <div class="ttd-jabatan">
                            INSPEKTUR DAERAH<br>
                            KABUPATEN REMBANG
                        </div>

                        @if($assignment->isSigned() && $assignment->signer)
                            @if($signatureBase64)
                                <div class="ttd-signature">
                                    <img src="{{ $signatureBase64 }}" alt="Tanda Tangan">
                                </div>
                            @endif
                            <p class="ttd-nama">{{ $assignment->signer->name }}</p>
                            <p class="ttd-nip">
                                {{ $assignment->signer->jabatan ?? '-' }}<br>
                                NIP. {{ $assignment->signer->nip ?? '-' }}
                            </p>
                        @else
                            <p class="ttd-nama">
                                IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE
                            </p>
                            <p class="ttd-nip">
                                Pembina<br>
                                NIP. 197411281999032003
                            </p>
                        @endif
                    </div>
                </div>

                <div class="footer-note">
                    Dilarang meminta dan atau menerima pemberian dalam bentuk apapun dari siapapun.
                </div>
            </div>

        </div>
    </div>
</body>
</html>