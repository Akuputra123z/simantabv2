<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Temuan & Tindak Lanjut Hasil Pemeriksaan</title>
    <style>
        @page { 
            size: A4 landscape; 
            margin: 1.2cm 1cm 1cm 1cm; 
        }
        
        body { 
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; 
            font-size: 8pt; 
            line-height: 1.35; 
            color: #0f172a; 
            margin: 0;
            padding: 0;
        }

        /* --- KOP SURAT LANDSCAPE RESMI --- */
        .kop-container {
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 3px double #000000;
            padding-bottom: 8px;
        }

        .table-kop {
            width: 100%;
            border-collapse: collapse;
            border: none !important;
        }

        .table-kop td {
            border: none !important;
            vertical-align: middle;
            padding: 0;
        }

        .kop-instansi { 
            font-size: 13pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            color: #000000;
            margin: 0;
            line-height: 1.15;
            letter-spacing: 0.5px;
        }

        .kop-sub { 
            font-size: 15pt; 
            font-weight: 900; 
            text-transform: uppercase; 
            color: #000000; 
            margin: 2px 0;
            letter-spacing: 1px;
        }

        .alamat { 
            font-size: 7.5pt; 
            color: #334155;
            font-style: italic;
            line-height: 1.3;
            margin-top: 3px;
        }

        /* --- JUDUL DOKUMEN --- */
        .doc-title-container {
            text-align: center;
            margin: 10px 0 12px 0;
        }

        .title-report {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .meta-periode {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }

        /* --- TABEL DATA UTAMA --- */
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; 
            font-size: 7.5pt;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table tr {
            page-break-inside: avoid;
        }
        
        table.data-table th { 
            background-color: #f1f5f9; 
            color: #0f172a;
            border: 1px solid #334155; 
            padding: 6px 4px; 
            text-align: center; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 7.5pt; 
            vertical-align: middle;
            letter-spacing: 0.2px;
        }

        table.data-table td { 
            border: 1px solid #475569; 
            padding: 5px 4px; 
            vertical-align: top; 
            line-height: 1.3;
        }

        .bg-subtotal { 
            background-color: #f1f5f9 !important; 
            font-weight: bold; 
        }

        .bg-grandtotal {
            background-color: #e2e8f0 !important;
            font-weight: bold;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .nowrap { white-space: nowrap; }

        .col-money {
            font-size: 7pt;
            text-align: right;
            padding: 4px 3px !important;
            word-wrap: break-word;
            line-height: 1.25;
        }

        /* --- FOOTER / TANDA TANGAN --- */
        .signature-section { 
            margin-top: 20px; 
            width: 100%; 
            page-break-inside: avoid;
        }

        .sig-container {
            float: right;
            width: 330px;
            text-align: center;
        }

        .sig-space { 
            height: 60px; 
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 8.5pt;
            color: #000000;
        }

        .sig-nip {
            margin-top: 2px;
            font-size: 8pt;
            color: #0f172a;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <div class="kop-container">
        <table class="table-kop">
            <tr>
                <td style="width: 75px; text-align: left; vertical-align: middle;">
                    @php
                        $logoPath = public_path('images/logo/rembang.png');
                        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
                    @endphp
                    @if($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" style="width: 65px; height: auto; display: block;">
                    @endif
                </td>
                
                <td style="text-align: center; vertical-align: middle; padding-right: 40px;">
                    <div class="kop-instansi">Pemerintah Kabupaten Rembang</div>
                    <div class="kop-sub">Inspektorat Daerah</div>
                    <div class="alamat">
                        Jl. Raya Rembang-Lasem Km 1,1, Tireman Barat, Tireman, Kec. Rembang, Kabupaten Rembang, Jawa Tengah 59219<br>
                        Telepon: (0295) 691320 | Email: inspektorat@rembangkab.go.id | Laman: https://inspektorat.rembangkab.go.id
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="doc-title-container">
        <div class="title-report">Daftar Rekapitulasi Temuan Pemeriksaan & Tindak Lanjut</div>
        <div class="meta-periode">
            Tanggal Cetak: {{ now()->translatedFormat('d F Y') }}
            @if(!empty($filter['tahun'])) | Tahun: {{ $filter['tahun'] }} @endif
            @if(!empty($filter['semester'])) | Semester: {{ $filter['semester'] }} @endif
        </div>
    </div>

    <!-- TABEL DATA UTAMA -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">NO</th>
                <th style="width: 12%;">UNIT KERJA / NO. LHP</th>
                <th style="width: 15%;">TEMUAN (KONDISI)</th>
                <th style="width: 4%;">KODE TMD</th>
                <th style="width: 16%;">REKOMENDASI</th>
                <th style="width: 4%;">KODE REK</th>
                <th style="width: 9.5%;">NILAI TEMUAN (Rp)</th>
                <th style="width: 7.5%;">TGL / KET</th>
                <th style="width: 9%;">NILAI TL (Rp)</th>
                <th style="width: 9%;">SETORAN (Rp)</th>
                <th style="width: 11%;">SISA (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1;
                $grandTotalTemuan = 0;
                $grandTotalTL = 0;
                $grandTotalSetoran = 0;
            @endphp

            @forelse($lhps as $lhp)
                @php 
                    $lhpTotalTemuan = 0;
                    $lhpTotalTL = 0;
                    $lhpTotalSetoran = 0;
                @endphp

                @foreach($lhp->temuans as $indexTemuan => $temuan)
                    @php 
                        $lhpTotalTemuan += (float) $temuan->nilai_temuan; 
                        
                        // Hitung total baris yang dibutuhkan oleh temuan ini
                        $temuanRows = 0;
                        $rekomRowsMap = [];
                        foreach($temuan->recommendations as $rKey => $rObj) {
                            $tlRows = [];
                            foreach($rObj->tindakLanjuts as $tl) {
                                if ($tl->is_cicilan) {
                                    foreach($tl->cicilans as $c) {
                                        $tlRows[] = [
                                            'tanggal' => $c->tanggal_bayar?->format('d/m/Y') ?? '-',
                                            'nilai_tl' => (float) $c->nilai_bayar,
                                            'setoran' => ($c->status === 'diterima') ? (float) $c->nilai_bayar : 0,
                                            'keterangan' => 'Cicilan ke-' . $c->ke,
                                        ];
                                    }
                                } else {
                                    $isLunas = ($tl->status_verifikasi === 'lunas' || $tl->status_verifikasi === 'diterima');
                                    $tlRows[] = [
                                        'tanggal' => $tl->created_at?->format('d/m/Y') ?? '-',
                                        'nilai_tl' => (float) ($rObj->isUang() ? $tl->nilai_tindak_lanjut : 0),
                                        'setoran' => (float) ($isLunas && $rObj->isUang() ? $tl->nilai_tindak_lanjut : 0),
                                        'keterangan' => $tl->catatan_tl ?? ($rObj->isUang() ? 'Setoran Lunas' : 'Administrasi/Fisik'),
                                    ];
                                }
                            }
                            $rekomRowsMap[$rKey] = !empty($tlRows) ? $tlRows : [
                                [
                                    'tanggal' => '-',
                                    'nilai_tl' => 0,
                                    'setoran' => 0,
                                    'keterangan' => 'Belum ditindaklanjuti',
                                ]
                            ];
                            $temuanRows += count($rekomRowsMap[$rKey]);
                        }
                        if ($temuan->recommendations->isEmpty()) {
                            $temuanRows = 1;
                        }
                        $temuanRowStarted = false;
                    @endphp

                    @forelse($temuan->recommendations as $indexRekom => $rekom)
                        @php 
                            $displayRows = $rekomRowsMap[$indexRekom] ?? [];
                            $rekomRowCount = count($displayRows);
                            $totalSetoranRekom = collect($displayRows)->sum('setoran');
                            $totalTlRekom = collect($displayRows)->sum('nilai_tl');
                            $lhpTotalTL += $totalTlRekom;
                            $lhpTotalSetoran += $totalSetoranRekom;
                            $rekomRowStarted = false;
                        @endphp

                        @foreach($displayRows as $indexRow => $row)
                            <tr>
                                {{-- Kolom LHP & Temuan (hanya muncul di baris pertama temuan) --}}
                                @if(!$temuanRowStarted)
                                    @php $temuanRowStarted = true; @endphp
                                    <td rowspan="{{ $temuanRows }}" class="text-center font-bold">{{ $no++ }}</td>
                                    <td rowspan="{{ $temuanRows }}">
                                        <strong>{{ $lhp->unitDiperiksa?->nama_unit ?? 'Unit Tidak Diketahui' }}</strong>
                                        <div style="font-size: 7pt; color: #475569; margin-top: 2px;">{{ $lhp->nomor_lhp }}</div>
                                    </td>
                                    <td rowspan="{{ $temuanRows }}">
                                        {{ $temuan->kondisi }}
                                    </td>
                                    <td rowspan="{{ $temuanRows }}" class="text-center">
                                        {{ $temuan->kodeTemuan?->kode ?? '-' }}
                                    </td>
                                @endif

                                {{-- Kolom Rekomendasi & Nilai Temuan (hanya muncul di baris pertama rekomendasi) --}}
                                @if(!$rekomRowStarted)
                                    @php $rekomRowStarted = true; @endphp
                                    <td rowspan="{{ $rekomRowCount }}">
                                        {!! $rekom->uraian_rekom !!}
                                    </td>
                                    <td rowspan="{{ $rekomRowCount }}" class="text-center">
                                        {{ $rekom->kodeRekomendasi?->kode ?? '-' }}
                                    </td>
                                    <td rowspan="{{ $rekomRowCount }}" class="col-money font-bold">
                                        Rp {{ number_format($temuan->nilai_temuan, 0, ',', '.') }}
                                    </td>
                                @endif

                                {{-- Kolom Rincian Tindak Lanjut --}}
                                <td class="text-center">
                                    {{ $row['tanggal'] }}
                                    @if(!empty($row['keterangan']) && $row['keterangan'] !== 'Belum ditindaklanjuti')
                                        <div style="font-size: 6.5pt; color: #64748b;">{{ $row['keterangan'] }}</div>
                                    @endif
                                </td>
                                <td class="col-money">
                                    {{ $row['nilai_tl'] > 0 ? 'Rp ' . number_format($row['nilai_tl'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="col-money">
                                    {{ $row['setoran'] > 0 ? 'Rp ' . number_format($row['setoran'], 0, ',', '.') : '-' }}
                                </td>
                                
                                {{-- Kolom Sisa Rekomendasi --}}
                                @if($indexRow == 0)
                                    <td rowspan="{{ $rekomRowCount }}" class="col-money font-bold">
                                        Rp {{ number_format(max(0, $temuan->nilai_temuan - $totalSetoranRekom), 0, ',', '.') }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        {{-- Jika Temuan belum memiliki rekomendasi --}}
                        <tr>
                            <td class="text-center font-bold">{{ $no++ }}</td>
                            <td>
                                <strong>{{ $lhp->unitDiperiksa?->nama_unit ?? 'Unit Tidak Diketahui' }}</strong>
                                <div style="font-size: 7pt; color: #475569; margin-top: 2px;">{{ $lhp->nomor_lhp }}</div>
                            </td>
                            <td>{{ $temuan->kondisi }}</td>
                            <td class="text-center">{{ $temuan->kodeTemuan?->kode ?? '-' }}</td>
                            <td colspan="2" class="text-center" style="color: #64748b;">Belum ada rekomendasi</td>
                            <td class="col-money font-bold">Rp {{ number_format($temuan->nilai_temuan, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                            <td class="col-money">-</td>
                            <td class="col-money">-</td>
                            <td class="col-money font-bold">Rp {{ number_format($temuan->nilai_temuan, 0, ',', '.') }}</td>
                        </tr>
                    @endforelse
                @endforeach

                {{-- Subtotal per LHP --}}
                @php
                    $grandTotalTemuan += $lhpTotalTemuan;
                    $grandTotalTL += $lhpTotalTL;
                    $grandTotalSetoran += $lhpTotalSetoran;
                @endphp
                <tr class="bg-subtotal">
                    <td colspan="6" class="text-right font-bold" style="padding-right: 6px;">SUBTOTAL: {{ $lhp->nomor_lhp }}</td>
                    <td class="col-money font-bold">Rp {{ number_format($lhpTotalTemuan, 0, ',', '.') }}</td>
                    <td></td>
                    <td class="col-money font-bold">Rp {{ number_format($lhpTotalTL, 0, ',', '.') }}</td>
                    <td class="col-money font-bold">Rp {{ number_format($lhpTotalSetoran, 0, ',', '.') }}</td>
                    <td class="col-money font-bold">Rp {{ number_format(max(0, $lhpTotalTemuan - $lhpTotalSetoran), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 16px; color: #64748b;">
                        Data rekapitulasi temuan dan tindak lanjut tidak ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if($lhps->isNotEmpty())
            <tfoot>
                <tr class="bg-grandtotal">
                    <td colspan="6" class="text-right font-bold" style="border-top: 2px solid #000; border-bottom: 3px double #000; padding-right: 6px;">
                        TOTAL KESELURUHAN (SEMUA LHP)
                    </td>
                    <td class="col-money font-bold" style="border-top: 2px solid #000; border-bottom: 3px double #000;">
                        Rp {{ number_format($grandTotalTemuan, 0, ',', '.') }}
                    </td>
                    <td style="border-top: 2px solid #000; border-bottom: 3px double #000;"></td>
                    <td class="col-money font-bold" style="border-top: 2px solid #000; border-bottom: 3px double #000;">
                        Rp {{ number_format($grandTotalTL, 0, ',', '.') }}
                    </td>
                    <td class="col-money font-bold" style="border-top: 2px solid #000; border-bottom: 3px double #000;">
                        Rp {{ number_format($grandTotalSetoran, 0, ',', '.') }}
                    </td>
                    <td class="col-money font-bold" style="border-top: 2px solid #000; border-bottom: 3px double #000;">
                        Rp {{ number_format(max(0, $grandTotalTemuan - $grandTotalSetoran), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- TANDA TANGAN / PENGESAHAN -->
    @php
        $inspektur = \App\Models\User::find(2);
    @endphp
    <div class="signature-section">
        <div class="sig-container">
            <p style="margin-bottom: 2px;">Mengetahui / Mengesahkan,</p>
            <p style="margin-top: 0; font-weight: bold; text-transform: uppercase;">Inspektur Daerah Kab. Rembang</p>
            
            <div class="sig-space"></div>
            
            <div class="sig-name">
                {{ $inspektur?->name ?? 'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.' }}
            </div>
            <div class="sig-nip" style="margin-top: 3px;">
                {{ $inspektur?->pangkat_gol ?? 'Pembina Utama Ahli Muda (IV/c)' }}
            </div>
            <div class="sig-nip">
                NIP. {{ $inspektur?->nip ?? '197411281999032003' }}
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>