<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $assignment->nomor_surat }}</title>
    <style>
        @page { size: A4; margin: 2cm 1.5cm 1.5cm 2cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: 0; color: #000; }
        .container { width: 100%; }
        
        /* Kop Surat */
        .kop-surat { border-bottom: 4px double black; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        .kop-text h1 { font-size: 14pt; margin: 0; text-transform: uppercase; font-weight: bold; }
        .kop-text h2 { font-size: 18pt; margin: 0; text-transform: uppercase; font-weight: bold; line-height: 1.2; }
        .kop-text p { font-size: 10pt; margin: 2px 0 0 0; font-style: normal; }
        .kop-text .sub-p { font-size: 9pt; font-style: italic; }
        
        /* Judul Surat */
        .judul-surat { text-align: center; margin-bottom: 25px; }
        .judul-surat h3 { text-decoration: underline; text-transform: uppercase; margin: 0; font-size: 14pt; font-weight: bold; letter-spacing: 0.5px; }
        .judul-surat p { margin: 5px 0 0 0; font-size: 12pt; }

        .section { width: 100%; margin-bottom: 10px; }
        .section::after { content: ''; display: block; clear: both; }
        .label { float: left; width: 95px; font-weight: bold; }
        .content { margin-left: 100px; text-align: left; }
        ol.list-dinas { margin: 0; padding-left: 32px; }
        ol.list-dinas li { margin-bottom: 4px; }
        .memerintahkan { text-align: center; font-weight: bold; letter-spacing: 4px; margin: 20px 0; font-size: 12pt; page-break-inside: avoid; }
        table.tim { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; }
        table.tim th { border-top: 1px solid black; border-bottom: 1px solid black; text-align: left; padding: 5px 8px; font-size: 10pt; text-transform: uppercase; font-weight: bold; }
        table.tim td { padding: 5px 8px; vertical-align: top; border-bottom: 1px solid #ccc; }
        table.tim tr:last-child td { border-bottom: 1px solid black; }
        table.unit-list { width: 100%; border-collapse: collapse; margin: 6px 0 4px 0; font-size: 11pt; }
        table.unit-list td { padding: 3px 12px; vertical-align: top; width: 50%; }
        table.unit-list td:first-child { border-right: 1px solid #ccc; }
        .ttd-wrapper { width: 100%; margin-top: 30px; text-align: right; }
        .ttd-container { display: inline-block; text-align: left; width: 400px; }
        .table-ttd-meta { width: 100%; font-size: 11pt; margin-bottom: 10px; }
        .table-ttd-meta td { padding: 1px 0; vertical-align: top; }
        .ttd-jabatan { text-align: center; font-weight: bold; line-height: 1.3; margin-bottom: 50px; text-transform: uppercase; }
        .ttd-nama { text-align: center; font-weight: bold; text-decoration: underline; margin: 0; font-size: 12pt; }
        .ttd-nip { text-align: center; margin: 0; font-size: 11pt; line-height: 1.2; }
        .footer-note { border: 1px dashed black; padding: 8px; text-align: center; font-style: italic; font-size: 9.5pt; margin-top: 40px; font-weight: bold; page-break-inside: avoid; }
        
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

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

        <div class="section">
            <div class="label">DASAR :</div>
            <div class="content">
                <ol class="list-dinas">
                    <li>Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Perubahan Kedua atas Peraturan Daerah Nomor 5 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Kabupaten Rembang.</li>
                    <li>Peraturan Daerah Kabupaten Rembang Nomor 2 Tahun 2024 tentang Anggaran Pendapatan dan Belanja Daerah Kabupaten Rembang Tahun Anggaran 2025.</li>
                    <li>Peraturan Bupati Rembang Nomor 54 Tahun 2021 tentang Kedudukan Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Inspektorat Kabupaten Rembang.</li>
                    <li>Peraturan Bupati Rembang Nomor 2 Tahun 2024 tentang Pembinaan dan Pengawasan Perangkat Daerah dan Desa.</li>
                    <li>Keputusan Bupati Rembang Nomor 100.3.3.2/0132/2025 tanggal 9 Januari 2025 tentang Program Kerja Pengawasan Tahunan Berbasis Resiko Inspektorat Kabupaten Rembang Tahun 2025.</li>
                </ol>
            </div>
        </div>

        <div class="memerintahkan">MEMERINTAHKAN</div>

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
                        {{-- 1. Tampilkan Ketua Tim --}}
                        @if($assignment->ketuaTim)
                        <tr>
                            <td style="text-align: center;">1.</td>
                            <td>
                                <strong>{{ $assignment->ketuaTim->name }}</strong><br>
                                <span style="font-size: 11pt; color: #333;">NIP. {{ $assignment->ketuaTim->nip ?? '-' }}</span>
                            </td>
                            <td>Ketua Tim</td>
                        </tr>
                        @endif

                        {{-- 2. Tampilkan Anggota dari Relasi members() --}}
                        @if($assignment->members && $assignment->members->isNotEmpty())
                            @foreach($assignment->members as $index => $member)
                            <tr>
                                <td style="text-align: center;">{{ $index + 2 }}.</td>
                                <td>
                                    <strong>{{ $member->name }}</strong><br>
                                    <span style="font-size: 11pt; color: #333;">NIP. {{ $member->nip ?? '-' }}</span>
                                </td>
                                <td>{{ $member->pivot->jabatan_tim ?? 'Anggota Tim' }}</td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section" style="margin-top: 15px;">
            <div class="label">UNTUK :</div>
            <div class="content">
                <ol class="list-dinas">
                    <li>
                        Melaksanakan {{ $assignment->jenis_pengawasan }} pada :
                        @if($assignment->unitDiperiksas && $assignment->unitDiperiksas->isNotEmpty())
                        <table class="unit-list">
                            <tr>
                            @foreach($assignment->unitDiperiksas as $unit)
                                <td> - {{ $unit->nama_unit }}<br><span style="margin-left:14px;font-style:italic;font-size:10pt;color:#444;">{{ $unit->nama_kecamatan }}</span></td>
                                @if($loop->iteration % 2 == 0 && !$loop->last)</tr><tr>@endif
                            @endforeach
                            @if($assignment->unitDiperiksas->count() % 2 != 0)<td></td>@endif
                            </tr>
                        </table>
                        @else
                        <span style="font-style: italic;">-</span>
                        @endif
                    </li>
                    <li>Waktu pelaksanaan selama {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($assignment->tanggal_selesai)) + 1 }} hari kerja, mulai tanggal {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->translatedFormat('d F') }} s.d {{ \Carbon\Carbon::parse($assignment->tanggal_selesai)->translatedFormat('d F Y') }}.</li>
                    <li>Melaporkan Hasil Pelaksanaan Tugas Kepada Pejabat Pemberi Tugas.</li>
                    <li>Perintah ini Agar Dilaksanakan dengan Penuh Tanggung Jawab.</li>
                </ol>
            </div>
        </div>

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
                    INSPEKTUR DAERAH<br>KABUPATEN REMBANG
                </div>
                
                <p class="ttd-nama">IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE</p>
                <p class="ttd-nip">Pembina<br>NIP. 197411281999032003</p>
            </div>
        </div>
        <br>

        <div class="footer-note">
            Dilarang meminta dan atau menerima pemberian dalam bentuk apapun dari siapapun.
        </div>
    </div>

</body>
</html>