<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $assignment->nomor_surat }}</title>
    <style>
        @page { size: A4; margin: 2cm 1.5cm 1.5cm 2cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: 0; color: #000; }
        .page-margin { padding: 0; margin: 0; }
        .container { width: 100%; }
        .list-isi {
    margin: 0;
    padding: 0;
}

.item {
    margin-bottom: 6px;
    text-align: justify;
}

.sub-item {
    margin-left: 20px;
}

.sub-kecamatan {
    margin-left: 35px;
    font-style: italic;
}
        
        .kop-surat { border-bottom: 4px double black; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        .kop-text h1 { font-size: 14pt; margin: 0; text-transform: uppercase; font-weight: bold; }
        .kop-text h2 { font-size: 18pt; margin: 0; text-transform: uppercase; font-weight: bold; line-height: 1.2; }
        .kop-text p { font-size: 10pt; margin: 2px 0 0 0; font-style: normal; }
        .kop-text .sub-p { font-size: 9pt; font-style: italic; }
        
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
        .list-isi { margin: 0; padding: 0; }
        .item { margin-bottom: 4px; }
        .ttd-wrapper { width: 100%; margin-top: 30px; text-align: right; }
        .ttd-container { display: inline-block; text-align: center; width: 400px; }
        .table-ttd-meta { width: 100%; font-size: 11pt; margin-bottom: 10px; text-align: left; }
        .table-ttd-meta td { padding: 1px 0; vertical-align: top; }
        .ttd-jabatan { text-align: center; font-weight: bold; line-height: 1.3; margin-bottom: 10px; text-transform: uppercase; }
        .ttd-nama { text-align: center; font-weight: bold; text-decoration: underline; margin: 0; font-size: 12pt; }
        .ttd-nip { text-align: center; margin: 0; font-size: 11pt; line-height: 1.2; }
        .footer-note { border: 1px dashed black; padding: 8px; text-align: center; font-style: italic; font-size: 9.5pt; margin-top: 40px; font-weight: bold; page-break-inside: avoid; }
        .btn-print {
            display: inline-block; margin-top: 20px;
            background: #2563eb; color: #fff; border: none;
            padding: 10px 30px; font-size: 11pt; font-weight: bold;
            border-radius: 8px; cursor: pointer;
        }
        .btn-print:hover { background: #1d4ed8; }
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
            .page-margin { padding: 0 !important; margin: 0 !important; }
        }
        @media screen {
            .page-margin { padding: 2cm 1.5cm 1.5cm 2cm; }
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

                        @if($assignment->pengendaliTeknis)
                        <tr>
                            <td style="text-align: center;">{{ $assignment->ketuaTim ? 2 : 1 }}.</td>
                            <td>
                                <strong>{{ $assignment->pengendaliTeknis->name }}</strong><br>
                                <span style="font-size: 11pt; color: #333;">NIP. {{ $assignment->pengendaliTeknis->nip ?? '-' }}</span>
                            </td>
                            <td>Pengendali Teknis</td>
                        </tr>
                        @endif

                        @if($assignment->members && $assignment->members->isNotEmpty())
                            @php $nomor = ($assignment->ketuaTim ? 1 : 0) + ($assignment->pengendaliTeknis ? 1 : 0); @endphp
                            @foreach($assignment->members as $index => $member)
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 + $nomor }}.</td>
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

        <div class="section" style="margin-top: 15px; page-break-inside: avoid;">
            <div class="label">UNTUK :</div>
            <div class="content">
                <div class="list-isi">

    <div class="item">
        <span style="display:inline-block; width:18px;">1.</span>
        Melaksanakan {{ $assignment->jenis_pengawasan }} pada :

        @if($assignment->unitDiperiksas && $assignment->unitDiperiksas->isNotEmpty())
            @foreach($assignment->unitDiperiksas as $unit)
                <div style="margin-left:18px;">
                    - {{ $unit->nama_unit }}
                </div>
                <div style="margin-left:35px; font-style:italic;">
                    {{ $unit->nama_kecamatan }}
                </div>
            @endforeach
        @else
            <div style="margin-left:18px;">
                -
            </div>
        @endif
    </div>

    <div class="item">
        <span style="display:inline-block; width:18px;">2.</span>
        Waktu pelaksanaan selama {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($assignment->tanggal_selesai)) + 1 }} hari kerja, mulai tanggal {{ \Carbon\Carbon::parse($assignment->tanggal_mulai)->translatedFormat('d F') }} s.d {{ \Carbon\Carbon::parse($assignment->tanggal_selesai)->translatedFormat('d F Y') }}.
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

                @if($assignment->isSigned() && $assignment->signer)
                    @if($assignment->signer->signature)
                        <div style="margin-bottom:5px;">
                            <img src="{{ Storage::url($assignment->signer->signature) }}" alt="Tanda Tangan" style="height:80px;">
                        </div>
                    @endif
                    <p class="ttd-nama">{{ $assignment->signer->name }}</p>
                    <p class="ttd-nip">
                        {{ $assignment->signer->jabatan ?? '-' }}<br>
                        NIP. {{ $assignment->signer->nip ?? '-' }}
                    </p>
                @else
                    <p class="ttd-nama">IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE</p>
                    <p class="ttd-nip">Pembina<br>NIP. 197411281999032003</p>
                @endif
            </div>
        </div>

        <div class="footer-note">
            Dilarang meminta dan atau menerima pemberian dalam bentuk apapun dari siapapun.
        </div>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        @php $canSign = auth()->check() && (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('kepala_inspektorat')); @endphp

        @if($canSign && !$assignment->isSigned())
        <form action="{{ route('audit-assignment.sign', $assignment->id) }}" method="POST" style="display:inline;">
            @csrf
            <input type="hidden" name="from" value="preview">
            <button style="display:inline-block; margin:0 5px; background:#16a34a; color:#fff; border:none; padding:14px 40px; font-size:13pt; font-weight:bold; border-radius:8px; cursor:pointer; letter-spacing:1px;" onclick="return confirm('Tandatangani Surat Tugas ini?')">
                ✓ TANDA TANGANI
            </button>
        </form>
        @endif

        @if($assignment->isSigned())
        <div style="display:inline-block; margin:0 5px; background:#f0fdf4; border:2px solid #22c55e; border-radius:8px; padding:10px 30px;">
            <strong style="color:#16a34a; font-size:12pt;">✓ SURAT TUGAS TELAH DITANDATANGANI</strong>
            <p style="color:#166534; margin:4px 0 0; font-size:10pt;">
                oleh {{ $assignment->signer->name }}, {{ $assignment->approved_at->translatedFormat('d F Y H:i') }}
            </p>
        </div>
        @endif

        <br><br>
        <a href="{{ route('audit-assignment.print', $assignment->id) }}" target="_blank"
           style="display:inline-block; margin:0 5px; background:#2563eb; color:#fff; border:none; padding:10px 30px; font-size:11pt; font-weight:bold; border-radius:8px; cursor:pointer; text-decoration:none;">
            📄 Unduh PDF
        </a>
        <button class="btn-print" onclick="window.print()" style="margin:0 5px;">🖨 Cetak Browser</button>
    </div>

</body>
</html>