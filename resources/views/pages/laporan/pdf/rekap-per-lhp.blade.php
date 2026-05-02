<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring TL - {{ $lhp->nomor_lhp }}</title>
   <style>
        /* Modern Government Report Style - 2026 */
        @page { 
            size: A4; 
            /* Perhatikan margin atas dinaikkan signifikan ke 2.5cm agar logo tidak terpotong printer */
            margin: 2.5cm 1.5cm 1.5cm 1.5cm; 
        }
        
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            font-size: 9px; 
            line-height: 1.4; 
            color: #1e293b; 
            margin: 0; 
            padding: 0; 
        }

        /* --- KOP SURAT SECTION (OPTIMIZED & CLEAN) --- */
        .kop-container {
            width: 100%;
            margin-bottom: 20px; /* Tambah jarak ke judul */
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }

        .table-kop {
            width: 100%;
            border-collapse: collapse;
            border: none !important;
        }

        .table-kop td {
            border: none !important;
            vertical-align: middle; /* Logo dan teks sejajar tengah */
            padding: 0;
        }

        .kop-instansi { 
            font-size: 16px; 
            font-weight: 800; 
            text-transform: uppercase; 
            color: #000;
            margin: 0;
            line-height: 1.1;
        }

        .kop-sub { 
            font-size: 20px; 
            font-weight: 900; 
            text-transform: uppercase; 
            color: #000; 
            margin: 2px 0;
        }

        .alamat { 
            font-size: 8.5px; 
            color: #334155; 
            font-style: italic;
            line-height: 1.3;
            margin-top: 5px;
        }

        /* --- TABLE & CONTENT STYLE --- */
        .title { 
            text-align: center; 
            font-size: 12px; 
            font-weight: 800; 
            margin: 15px 0; 
            text-transform: uppercase;
            text-decoration: underline;
        }

        .meta-container { 
            width: 100%; 
            margin-bottom: 15px; 
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 0;
            border-collapse: collapse;
        }
        .meta-container td { padding: 2px 0; border: none; }
        .label-meta { color: #64748b; font-weight: 600; width: 15%; text-transform: uppercase; font-size: 8px; }
        .val-meta { font-weight: 700; width: 35%; color: #0f172a; }

        .stat-grid { display: table; width: 100%; border-spacing: 6px 0; margin-bottom: 20px; }
        .stat-item { 
            display: table-cell; 
            background: #f8fafc; 
            border: 1px solid #e2e8f0; 
            padding: 8px; 
            border-radius: 8px; 
            border-left: 3px solid #cbd5e1;
        }
        .stat-item.selesai { border-left-color: #10b981; }
        .stat-item.proses { border-left-color: #f59e0b; }
        .stat-item.belum { border-left-color: #ef4444; }
        .st-label { font-size: 7px; text-transform: uppercase; font-weight: 700; color: #64748b; display: block; }
        .st-val { font-size: 12px; font-weight: 800; color: #0f172a; }

        .table-main { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-main th { 
            background: #f1f5f9; 
            padding: 10px 8px; 
            border: 1px solid #e2e8f0;
            font-size: 8px;
            text-transform: uppercase;
        }
        .table-main td { padding: 10px 8px; border: 1px solid #e2e8f0; vertical-align: top; }

        .tl-box { 
            background: #ffffff; 
            border: 1px solid #f1f5f9;
            border-left: 2px solid #e2e8f0; 
            padding: 4px 8px; 
            margin-top: 4px; 
        }
        .tl-row { font-size: 7.5px; color: #64748b; border-bottom: 0.5px solid #f8fafc; }
        .tl-amount { color: #0f172a; font-weight: 700; float: right; }

        .tfoot-dark { background: #1e293b !important; color: #ffffff; }
        .tfoot-red { background: #fef2f2 !important; color: #991b1b; }

        /* --- SIGNATURE STYLE UPDATED --- */
.signature-section { 
    margin-top: 30px; 
    width: 100%; 
}

.sig-box { 
    float: right; 
    width: 320px; /* Diperlebar agar gelar panjang muat satu baris */
    text-align: center; 
}

.sig-space { 
    height: 70px; /* Ruang tanda tangan basah */
}

.sig-name {
    font-weight: 800;
    text-decoration: underline;
    text-transform: uppercase;
    font-size: 9.5px;
    color: #000;
}

.sig-nip {
    color: #1e293b;
    font-weight: 600;
    margin-top: 2px;
}
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .badge-kode { font-size: 7px; color: #94a3b8; border: 1px solid #e2e8f0; padding: 1px 4px; border-radius: 4px; display: inline-block; }

        
    </style>
</head>
<body onload="window.print()">

   <!-- KOP SURAT SECTION (FIX RAPI) -->
<div class="kop-container">
    <table class="table-kop">
        <tr>
       <td style="width: 100px; text-align: left; vertical-align: middle; padding: 10px;">
    @php
        $logoPath = public_path('images/logo/rembang.png');
        $logoBase64 = base64_encode(file_get_contents($logoPath));
    @endphp
    <img src="data:image/png;base64,{{ $logoBase64 }}" 
         style="width: 80px; height: 80px; object-fit: contain; display: block;">
</td>

            <td style="text-align: center;">
                <div class="kop-instansi">Pemerintah Kabupaten Rembang</div>
                <div class="kop-sub">Inspektorat Daerah</div>
                <div class="alamat">

                   
                    Jl. Raya Rembang-Lasem Km 1,1, Tireman Barat, Tireman, Kec. Rembang, Kabupaten Rembang, Jawa Tengah 59219 <br>
                    Telepon: (0295) 691320 | Email: inspektorat@rembangkab.go.id <br>
                    Laman: https://inspektorat.rembangkab.go.id
                </div>
            </td>
            
            <td style="width: 80px;"></td>
        </tr>
    </table>
</div>

    <div class="title">Laporan Monitoring Tindak Lanjut Hasil Pemeriksaan</div>

    <table class="meta-container">
        <tr>
            <td class="label-meta">No. LHP</td>
            <td class="val-meta">: {{ $lhp->nomor_lhp }}</td>
            <td class="label-meta">Tanggal Cetak</td>
            <td class="val-meta">: {{ now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    @php
        $stat = $lhp->statistik;
        $persen = $stat->persen_selesai_gabungan ?? 0;
    @endphp

    <div class="stat-grid">
        <div class="stat-item"><span class="st-label">Total Temuan</span><span class="st-val">{{ $stat->total_temuan ?? 0 }}</span></div>
        <div class="stat-item"><span class="st-label">Rekomendasi</span><span class="st-val">{{ $stat->total_rekomendasi ?? 0 }}</span></div>
        <div class="stat-item selesai"><span class="st-label">Selesai</span><span class="st-val" style="color: #059669;">{{ $stat->rekom_selesai ?? 0 }}</span></div>
        <div class="stat-item proses"><span class="st-label">Proses</span><span class="st-val" style="color: #d97706;">{{ $stat->rekom_proses ?? 0 }}</span></div>
        <div class="stat-item belum"><span class="st-label">Belum TL</span><span class="st-val" style="color: #dc2626;">{{ $stat->rekom_belum ?? 0 }}</span></div>
        <div class="stat-item"><span class="st-label">% Progres</span><span class="st-val">{{ number_format($persen, 1, ',', '.') }}%</span></div>
    </div>

    <table class="table-main">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="28%">Temuan & Kondisi</th>
                <th width="38%">Rekomendasi & Realisasi</th>
                <th width="15%">Nilai Temuan</th>
                <th width="15%">Realisasi</th>
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
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $temuan->kondisi }}</div>
                        <div class="badge-kode">KODE: {{ $temuan->kodeTemuan?->kode ?? '-' }}</div>
                    </td>
                    <td>
                        @foreach($temuan->recommendations as $rekom)
                            <div class="rekom-wrapper">
                                <span class="rekom-tag">R{{ $loop->iteration }}.</span>
                                <span class="rekom-text">{{ $rekom->uraian_rekom }}</span>
                                
                                <div class="tl-box">
                                    @php 
                                        $cicilan = $rekom->tindakLanjuts->flatMap->cicilans
                                            ->where('status', 'diterima')->sortBy('tanggal_bayar');
                                    @endphp

                                    @forelse($cicilan as $c)
                                        <div class="tl-row">
                                            <span>• Cicilan {{ $c->ke }} ({{ $c->tanggal_bayar?->format('d/m/y') }})</span>
                                            <span class="tl-amount">Rp{{ number_format($c->nilai_bayar, 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        @php $lunas = $rekom->tindakLanjuts->where('is_cicilan', false)->where('status_verifikasi', '!=', 'ditolak'); @endphp
                                        @foreach($lunas as $lns)
                                            <div class="tl-row">
                                                <span>• Setoran Lunas</span>
                                                <span class="tl-amount">Rp{{ number_format($lns->total_terbayar, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                        @if($lunas->isEmpty())
                                            <div class="tl-row" style="color: #cbd5e1;">• Belum ada realisasi</div>
                                        @endif
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </td>
                    <td class="text-right font-bold">Rp {{ number_format($currentNilai, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #059669;">Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Data tindak lanjut tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot-dark">
                <td colspan="3" class="text-right font-bold">TOTAL KESELURUHAN</td>
                <td class="text-right font-bold">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</td>
            </tr>
            <tr class="tfoot-red">
                <td colspan="3" class="text-right font-bold">SISA KERUGIAN NEGARA / DAERAH</td>
                <td colspan="2" class="text-right font-bold" style="font-size: 11px;">
                    Rp {{ number_format(max(0, $totalNilai - $totalRealisasi), 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

   <div class="signature-section">
        <div class="sig-box">
            <p>Rembang, {{ now()->translatedFormat('d F Y') }}</p>
            <p style="margin-top: -8px; font-weight: 700;">Inspektur Kabupaten Rembang,</p>
            
            <div class="sig-space"></div>
            
            <div class="sig-name">
                IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.
            </div>
            <div class="sig-nip">
                NIP. 197411281999032003
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>