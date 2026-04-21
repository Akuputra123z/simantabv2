<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring TL - {{ $lhp->nomor_lhp }}</title>
    <style>
        /* Modern Government Report Style - 2026 */
        @page { size: A4; margin: 1.2cm 1.5cm; }
        
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            font-size: 9px; 
            line-height: 1.4; 
            color: #1e293b; 
            margin: 0; 
            padding: 0; 
        }

        /* Kop Surat Modern & Tegas */
        .header { 
            text-align: center; 
            border-bottom: 2.5px solid #0f172a; 
            padding-bottom: 8px; 
            margin-bottom: 18px; 
        }
        .kop-instansi { font-size: 15px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; color: #0f172a; }
        .kop-sub { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #2563eb; margin-top: 2px; }
        .alamat { font-size: 8px; color: #64748b; margin-top: 4px; }

        .title { 
            text-align: center; 
            font-size: 12px; 
            font-weight: 800; 
            margin: 10px 0 15px; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Info Metadata */
        .meta-container { 
            width: 100%; 
            margin-bottom: 15px; 
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 0;
        }
        .meta-container td { padding: 2px 0; }
        .label-meta { color: #64748b; font-weight: 600; width: 14%; text-transform: uppercase; font-size: 8px; }
        .val-meta { font-weight: 700; width: 36%; color: #0f172a; }

        /* Bento Stats - Improved UX */
        .stat-grid { 
            display: table; 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 6px 0; 
            margin-bottom: 20px; 
        }
        .stat-item { 
            display: table-cell; 
            background: #f8fafc; 
            border: 1px solid #e2e8f0; 
            padding: 8px; 
            border-radius: 8px; 
            text-align: left;
            border-left: 3px solid #cbd5e1;
        }
        .stat-item.selesai { border-left-color: #10b981; }
        .stat-item.proses { border-left-color: #f59e0b; }
        .stat-item.belum { border-left-color: #ef4444; }
        
        .st-label { font-size: 7px; text-transform: uppercase; font-weight: 700; color: #64748b; display: block; margin-bottom: 2px; }
        .st-val { font-size: 12px; font-weight: 800; color: #0f172a; }

        /* Main Table - Modern Minimalist */
        .table-main { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-main th { 
            background: #f1f5f9; 
            color: #475569; 
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 8px; 
            padding: 10px 8px; 
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .table-main td { padding: 10px 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        
        /* Stripes for better readability */
        .table-main tbody tr:nth-child(even) { background: #fbfcfd; }

        /* Rekomendasi UX */
        .rekom-wrapper { margin-bottom: 8px; }
        .rekom-tag { 
            font-weight: 700; 
            color: #2563eb; 
            margin-right: 4px;
        }
        .rekom-text { font-weight: 500; color: #1e293b; line-height: 1.5; }
        
        .tl-box { 
            background: #ffffff; 
            border: 1px solid #f1f5f9;
            border-left: 2px solid #e2e8f0; 
            padding: 4px 8px; 
            margin-top: 4px; 
            border-radius: 0 4px 4px 0;
        }
        .tl-row { 
            font-size: 7.5px; 
            color: #64748b; 
            padding: 2px 0; 
            border-bottom: 0.5px solid #f8fafc;
        }
        .tl-row:last-child { border-bottom: none; }
        .tl-amount { color: #0f172a; font-weight: 700; float: right; }

        /* Footer & Totals */
        .tfoot-dark { background: #1e293b !important; color: #ffffff; }
        .tfoot-red { background: #fef2f2 !important; color: #991b1b; }

        .signature-section { margin-top: 35px; width: 100%; }
        .sig-box { float: right; width: 220px; text-align: center; }
        .sig-space { height: 55px; }

        /* Helpers */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .badge-kode { font-size: 7px; color: #94a3b8; border: 1px solid #e2e8f0; padding: 1px 4px; border-radius: 4px; margin-top: 4px; display: inline-block; }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="kop-instansi">Pemerintah Kabupaten Rembang</div>
        <div class="kop-sub">Inspektorat Daerah</div>
        <div class="alamat">Gedung Inspektorat, Jl. Gatot Subroto No. 8, Rembang 59211 | (0295) 691234</div>
    </div>

    <div class="title">Monitoring Tindak Lanjut Hasil Pemeriksaan</div>

    <table class="meta-container">
        <tr>
            <td class="label-meta">No. Dokumen</td>
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
                                            <div class="tl-row" style="color: #e2e8f0;">• Belum ada realisasi</div>
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
            <p>Inspektur Kabupaten Rembang,</p>
            <div class="sig-space"></div>
            <p class="font-bold"><u>( ........................................... )</u></p>
            <p style="margin-top: -10px; color: #64748b;">NIP. .............................................</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>