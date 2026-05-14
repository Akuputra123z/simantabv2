<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $assignment->nomor_surat }}</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.3; margin: 0; padding: 0; }
        .container { width: 100%; }
        .kop-surat { border-bottom: 3px solid black; padding-bottom: 5px; margin-bottom: 20px; position: relative; text-align: center; }
        .logo { position: absolute; left: 0; top: 0; width: 70px; }
        .kop-text h1 { font-size: 14pt; margin: 0; text-transform: uppercase; }
        .kop-text h2 { font-size: 18pt; margin: 0; text-transform: uppercase; }
        .kop-text p { font-size: 9pt; margin: 0; font-style: italic; }
        
        .judul-surat { text-align: center; margin-bottom: 20px; }
        .judul-surat h3 { text-decoration: underline; text-transform: uppercase; margin: 0; font-size: 13pt; }
        .judul-surat p { margin: 5px 0 0 0; text-transform: uppercase; }

        .section { display: table; width: 100%; margin-bottom: 10px; }
        .label { display: table-cell; width: 80px; font-weight: bold; vertical-align: top; }
        .content { display: table-cell; vertical-align: top; text-align: justify; }
        
        .memerintahkan { text-align: center; font-weight: bold; letter-spacing: 3px; margin: 25px 0; }
        
        table.tim { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.tim th { border-bottom: 1px solid black; text-align: left; padding: 5px; font-size: 10pt; text-transform: uppercase; }
        table.tim td { padding: 8px 5px; vertical-align: top; border-bottom: 1px solid #eee; }

        .ttd-container { margin-top: 40px; float: right; width: 300px; }
        .footer-note { border: 1px solid black; padding: 5px; text-align: center; font-style: italic; font-size: 9pt; margin-top: 50px; clear: both; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container">
        <div class="kop-surat">
            <img src="{{ asset('assets/images/logo-rembang.png') }}" class="logo"> <div class="kop-text">
                <h1>Pemerintah Kabupaten Rembang</h1>
                <h2>Inspektorat Daerah</h2>
                <p>Jl. Raya Rembang – Lasem Km 1.1 Telp. ( 0295 ) 691320 Fax. (0295) 693525</p>
                <p>Rembang 59219 Email : inspektorat@rembangkab.go.id</p>
            </div>
        </div>

        <div class="judul-surat">
            <h3>Surat Perintah Tugas</h3>
            <p>Nomor : {{ $assignment->nomor_surat }}</p>
        </div>

        <div class="section">
            <div class="label">DASAR :</div>
            <div class="content">
                <ol style="margin: 0; padding-left: 20px;">
                    <li>Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021...</li>
                    <li>Program Kerja Pengawasan Tahunan (PKPT) Inspektorat Kabupaten Rembang Tahun {{ date('Y') }}.</li>
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
            <th width="30">No</th>
            <th>Nama / NIP</th>
            <th>Jabatan Dalam Tim</th>
        </tr>
    </thead>
    <tbody>
        {{-- 1. Tampilkan Ketua Tim Terlebih Dahulu --}}
        @if($assignment->ketuaTim)
        <tr>
            <td>1.</td>
            <td>
                <strong>{{ $assignment->ketuaTim->name }}</strong><br>
                <small>NIP. {{ $assignment->ketuaTim->nip ?? '-' }}</small>
            </td>
            <td>Ketua Tim</td>
        </tr>
        @endif

        {{-- 2. Tampilkan Anggota dari Relasi members() --}}
        @if($assignment->members && $assignment->members->isNotEmpty())
            @foreach($assignment->members as $index => $member)
            <tr>
                {{-- Penomoran berlanjut setelah Ketua Tim --}}
                <td>{{ $index + 2 }}.</td>
                <td>
                    <strong>{{ $member->name }}</strong><br>
                    <small>NIP. {{ $member->nip ?? '-' }}</small>
                </td>
                <td>{{ $member->pivot->jabatan_tim ?? 'Anggota Tim' }}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
            </div>
        </div>

        <div class="section" style="margin-top: 20px;">
            <div class="label">UNTUK :</div>
            <div class="content">
                <ol style="margin: 0; padding-left: 20px;">
                    <li>Melaksanakan {{ $assignment->jenis_pengawasan }} pada :
    <div class="mt-1 font-bold italic ml-4">
        @if($assignment->unitDiperiksas && $assignment->unitDiperiksas->isNotEmpty())
            @foreach($assignment->unitDiperiksas as $unit)
                <p>➤ {{ $unit->nama_unit }} ({{ $unit->nama_kecamatan }})</p>
            @endforeach
        @else
            <p>-</p>
        @endif
    </div>
</li>
                    <li>Waktu pelaksanaan selama {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($assignment->tanggal_selesai)) + 1 }} hari kerja, mulai tanggal {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->translatedFormat('d F') }} s.d {{ \Carbon\Carbon::parse($assignment->tanggal_selesai)->translatedFormat('d F Y') }}.</li>
                    <li>Melaporkan Hasil Pelaksanaan Tugas Kepada Pejabat Pemberi Tugas.</li>
                    <li>Perintah ini Agar Dilaksanakan dengan Penuh Tanggung Jawab.</li>
                </ol>
            </div>
        </div>

        <div class="ttd-container">
            <table width="100%">
                <tr><td>Ditetapkan di</td><td>: Rembang</td></tr>
                <tr><td>Pada tanggal</td><td>: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td></tr>
            </table>
            <br>
            <div style="text-align: center; font-weight: bold;">
                <p>INSPEKTUR DAERAH<br>KABUPATEN REMBANG</p>
                <br><br><br>
                <p style="text-decoration: underline;">NAMA INSPEKTUR, Gelar</p>
                <p style="font-weight: normal;">Pangkat / Golongan<br>NIP. 197XXXXXXXXXXXXX</p>
            </div>
        </div>

        <div class="footer-note">
            Dilarang meminta dan atau menerima pemberian dalam bentuk apapun dari siapapun.
        </div>
    </div>

</body>
</html>