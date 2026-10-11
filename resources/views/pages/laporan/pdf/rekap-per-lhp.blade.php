<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring TL - {{ $lhp->nomor_lhp }}</title>
    <style>
        @page { 
            size: A4 portrait; 
            margin: 1.5cm 1.5cm 1.5cm 1.5cm; 
        }
        
        body { 
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; 
            font-size: 8.5pt; 
            line-height: 1.4; 
            color: #0f172a; 
            margin: 0; 
            padding: 0; 
        }

        /* --- KOP SURAT RESMI PEMKAB REMBANG --- */
        .kop-container {
            width: 100%;
            margin-bottom: 15px;
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
            margin: 12px 0 14px 0;
        }

        .doc-title { 
            font-size: 11pt; 
            font-weight: bold; 
            text-transform: uppercase;
            color: #000000;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .doc-subtitle {
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
        }

        /* --- TABEL INFORMASI & RINGKASAN LHP --- */
        .table-info-ringkasan {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 8pt;
        }

        .table-info-ringkasan td {
            border: 1px solid #475569;
            padding: 5px 8px;
            vertical-align: middle;
        }

        .info-label {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #1e293b;
            width: 16%;
        }

        .info-val {
            color: #0f172a;
            width: 34%;
        }

        /* --- TABEL UTAMA PEMERIKSAAN --- */
        .table-data { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 18px; 
            font-size: 8pt;
        }

        .table-data thead {
            display: table-header-group;
        }

        .table-data tr {
            page-break-inside: avoid;
        }

        .table-data th { 
            background-color: #f1f5f9; 
            color: #0f172a;
            padding: 7px 6px; 
            border: 1px solid #334155;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            vertical-align: middle;
            letter-spacing: 0.3px;
        }

        .table-data td { 
            padding: 6px 7px; 
            border: 1px solid #475569; 
            vertical-align: top; 
            line-height: 1.35;
        }

        .table-data tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .kode-badge {
            display: block;
            margin-top: 4px;
            font-size: 7pt;
            font-weight: 600;
            color: #475569;
        }

        .rekom-item {
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #cbd5e1;
        }

        .rekom-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .rekom-title {
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 3px;
        }

        /* Sub-tabel realisasi yang bersih & presisi */
        .table-sub-tl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 7.5pt;
        }

        .table-sub-tl td {
            border: none !important;
            padding: 2px 0;
            background: transparent !important;
        }

        .sub-tl-desc {
            color: #475569;
            width: 65%;
        }

        .sub-tl-amount {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
            width: 35%;
            white-space: nowrap;
        }

        .sub-tl-empty {
            color: #94a3b8;
            font-style: italic;
            font-size: 7.5pt;
            margin-top: 2px;
        }

        /* --- TFOOT ACCOUNTING STANDARD --- */
        .table-data tfoot td {
            padding: 6px 8px;
            font-size: 8pt;
        }

        .tfoot-total {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 2px solid #000000 !important;
            border-bottom: 1px solid #000000 !important;
        }

        .tfoot-sisa {
            background-color: #ffffff;
            font-weight: bold;
            border-bottom: 3px double #000000 !important;
        }

        /* --- TANDA TANGAN / PENGESAHAN --- */
        .signature-section { 
            margin-top: 25px; 
            width: 100%; 
            page-break-inside: avoid;
        }

        .sig-box { 
            float: right; 
            width: 320px; 
            text-align: center; 
        }

        .sig-space { 
            height: 65px; 
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 9pt;
            color: #000000;
        }

        .sig-nip {
            color: #0f172a;
            font-size: 8.5pt;
            margin-top: 2px;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <div class="kop-container">
        <table class="table-kop">
            <tr>
                <td style="width: 85px; text-align: left; vertical-align: middle;">
                    @php
                        $logoPath = public_path('images/logo/rembang.png');
                        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
                    @endphp
                    @if($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" style="width: 72px; height: auto; display: block;">
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
        <h1 class="doc-title">Laporan Monitoring Tindak Lanjut Hasil Pemeriksaan</h1>
        <div class="doc-subtitle">Nomor LHP: {{ $lhp->nomor_lhp }}</div>
    </div>

    @php
        $stat = $lhp->statistik;
        $persen = $stat->persen_selesai_gabungan ?? 0;
    @endphp

    <!-- TABEL INFORMASI & RINGKASAN PEMERIKSAAN -->
    <table class="table-info-ringkasan">
        <tr>
            <td class="info-label">Unit Diperiksa</td>
            <td class="info-val font-bold">{{ $lhp->unitDiperiksa?->nama_unit ?? '-' }}</td>
            <td class="info-label">Tanggal LHP</td>
            <td class="info-val">{{ $lhp->tanggal_lhp?->translatedFormat('d F Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Jenis Pengawasan</td>
            <td class="info-val">{{ $lhp->jenis_pemeriksaan ?? '-' }} (Semester {{ $lhp->semester }})</td>
            <td class="info-label">Tanggal Cetak</td>
            <td class="info-val">{{ now()->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Total Temuan</td>
            <td class="info-val font-bold">{{ $stat->total_temuan ?? 0 }} Temuan ({{ $stat->total_rekomendasi ?? 0 }} Rekomendasi)</td>
            <td class="info-label">Progres TL</td>
            <td class="info-val font-bold">
                {{ number_format($persen, 1, ',', '.') }}% 
                <span style="font-weight: normal; color: #475569; font-size: 7.5pt;">(Selesai: {{ $stat->rekom_selesai ?? 0 }}, Proses: {{ $stat->rekom_proses ?? 0 }}, Belum: {{ $stat->rekom_belum ?? 0 }})</span>
            </td>
        </tr>
    </table>

    <!-- TABEL UTAMA TEMUAN & REKOMENDASI -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 28%; text-align: left;">Kondisi / Temuan</th>
                <th style="width: 38%; text-align: left;">Rekomendasi & Realisasi Tindak Lanjut</th>
                <th style="width: 15%; text-align: right;">Nilai Temuan (Rp)</th>
                <th style="width: 15%; text-align: right;">Realisasi Setoran (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalNilai = 0; 
                $totalRealisasi = 0; 
            @endphp

            @forelse($lhp->temuans as $index => $temuan)
                @php
                    $currentNilai = (float) $temuan->nilai_temuan;
                    $totalNilai += $currentNilai;
                    
                    $subTotal = $temuan->recommendations->flatMap->tindakLanjuts
                        ->flatMap->cicilans->where('status', 'diterima')->sum('nilai_bayar');
                    
                    $subTotal += $temuan->recommendations->flatMap->tindakLanjuts
                        ->where('is_cicilan', false)->where('status_verifikasi', '!=', 'ditolak')->sum('total_terbayar');
                    
                    $totalRealisasi += (float) $subTotal;
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $temuan->kondisi }}</div>
                        <span class="kode-badge">Kode Temuan: {{ $temuan->kodeTemuan?->kode ?? '-' }}</span>
                    </td>
                    <td>
                        @foreach($temuan->recommendations as $rekom)
                            <div class="rekom-item">
                                <div class="rekom-title">
                                    {{ $loop->iteration }}. {!! $rekom->uraian_rekom !!}
                                    @if($rekom->kodeRekomendasi?->kode)
                                        <span style="font-size: 7pt; font-weight: normal; color: #64748b;">(Kode: {{ $rekom->kodeRekomendasi->kode }})</span>
                                    @endif
                                </div>
                                
                                @php 
                                    $cicilan = $rekom->tindakLanjuts->flatMap->cicilans
                                        ->where('status', 'diterima')->sortBy('tanggal_bayar');
                                    $lunas = $rekom->tindakLanjuts->where('is_cicilan', false)->where('status_verifikasi', '!=', 'ditolak');
                                @endphp

                                @if($cicilan->isNotEmpty() || $lunas->isNotEmpty())
                                    <table class="table-sub-tl">
                                        @foreach($cicilan as $c)
                                            <tr>
                                                <td class="sub-tl-desc">• Cicilan ke-{{ $c->ke }} ({{ $c->tanggal_bayar?->format('d/m/Y') }})</td>
                                                <td class="sub-tl-amount">Rp {{ number_format($c->nilai_bayar, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        @foreach($lunas as $lns)
                                            <tr>
                                                <td class="sub-tl-desc">• Setoran Lunas</td>
                                                <td class="sub-tl-amount">Rp {{ number_format($lns->total_terbayar, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    <div class="sub-tl-empty">• Belum ada realisasi tindak lanjut</div>
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td class="text-right font-bold nowrap">
                        Rp {{ number_format($currentNilai, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-bold nowrap">
                        Rp {{ number_format($subTotal, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 18px; color: #64748b;">
                        Data temuan dan tindak lanjut tidak ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot-total">
                <td colspan="3" class="text-right font-bold">TOTAL KESELURUHAN</td>
                <td class="text-right font-bold nowrap">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                <td class="text-right font-bold nowrap">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</td>
            </tr>
            <tr class="tfoot-sisa">
                <td colspan="3" class="text-right font-bold">SISA KERUGIAN NEGARA / DAERAH</td>
                <td colspan="2" class="text-right font-bold nowrap" style="font-size: 9pt;">
                    Rp {{ number_format(max(0, $totalNilai - $totalRealisasi), 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN / PENGESAHAN -->
    @php
        $inspektur = \App\Models\User::find(2);
    @endphp
    <div class="signature-section">
        <div class="sig-box">
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