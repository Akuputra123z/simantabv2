<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <style>
        /* Margin diperlebar di atas (1.5cm) agar logo tidak terpotong printer */
        @page { 
            margin: 1.5cm 0.5cm 0.5cm 0.5cm; 
            size: landscape; 
        }
        
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 9px; 
            line-height: 1.4; 
            color: #333; 
            margin: 0;
            padding: 0;
        }
        
        /* --- KOP SURAT LANDSCAPE --- */
        .kop-container {
            width: 100%;
            margin-bottom: 20px;
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
            vertical-align: middle;
            padding: 0;
        }

        .kop-instansi { 
            font-size: 16px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin: 0;
            line-height: 1.1;
        }

        .kop-sub { 
            font-size: 20px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin: 2px 0;
        }

        .alamat { 
            font-size: 9px; 
            font-style: italic;
            line-height: 1.3;
        }

        /* --- STYLES TABEL DATA --- */
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; 
        }
        
        table.data-table th, 
        table.data-table td { 
            border: 1px solid #000; 
            padding: 4px; 
            word-wrap: break-word; 
            vertical-align: top; 
        }
        
        table.data-table th { 
            background-color: #d9d9d9; 
            text-align: center; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 8px; 
        }
        
        .title-report {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            text-decoration: underline;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer-lhp { font-weight: bold; background-color: #eeeeee; }
        .invalid { color: red; font-style: italic; }

        /* --- FOOTER / SIGNATURE STYLE --- */
.signature-section { 
    margin-top: 30px; 
    width: 100%; 
}

.sig-container {
    float: right;
    width: 350px; /* Lebar disesuaikan agar nama panjang tidak terpotong */
    text-align: center;
}

.sig-space { 
    height: 65px; /* Ruang untuk tanda tangan basah */
}

.sig-name {
    font-weight: bold;
    text-decoration: underline;
    font-size: 9.5px;
}

.sig-nip {
    margin-top: 2px;
    font-size: 9px;
}
    </style>
</head>
<body>

    <div class="kop-container">
        <table class="table-kop">
            <tr>
                <td style="width: 80px;">
                    @php
                        $logoPath = public_path('images/logo/rembang.png');
                        $logoBase64 = base64_encode(file_get_contents($logoPath));
                    @endphp
                    <img src="data:image/png;base64,{{ $logoBase64 }}" 
                         style="width: 70px; height: auto; display: block;">
                </td>
                
                <td style="text-align: center; padding-right: 80px;">
                    <div class="kop-instansi">Pemerintah Kabupaten Rembang</div>
                    <div class="kop-sub">Inspektorat Daerah</div>
                    <div class="alamat">
                      Jl. Raya Rembang-Lasem Km 1,1, Tireman Barat, Tireman, Kec. Rembang, Kabupaten Rembang, Jawa Tengah 59219 <br>
                      Telepon: (0295) 691320 | Email: inspektorat@rembangkab.go.id <br>
                      Laman: https://inspektorat.rembangkab.go.id
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="title-report">
        DAFTAR TEMUAN PEMERIKSAAN & PELAKSANAAN TINDAK LANJUT
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="12%">NAMA UNIT / LHP</th>
                <th width="14%">TEMUAN (KONDISI)</th>
                <th width="5%">KODE TMD</th>
                <th width="14%">REKOMENDASI</th>
                <th width="5%">KODE REK</th>
                <th width="9%">NILAI TEMUAN</th>
                <th width="8%">TGL BAYAR / KET</th>
                <th width="10%">NILAI TL (Rp/Barang)</th>
                <th width="10%">SETORAN (VALID)</th>
                <th width="10%">SISA (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($lhps as $lhp)
                @php 
                    $lhpTotalTemuan = 0;
                    $lhpTotalTL = 0;
                    $lhpTotalSetoran = 0;
                @endphp

                @foreach($lhp->temuans as $temuan)
                    @php $lhpTotalTemuan += $temuan->nilai_temuan; @endphp
                    
                    @foreach($temuan->recommendations as $indexRekom => $rekom)
                        @php 
                            $displayRows = [];
                            foreach($rekom->tindakLanjuts as $tl) {
                                if ($tl->is_cicilan) {
                                    foreach($tl->cicilans as $c) {
                                        $displayRows[] = [
                                            'tanggal' => $c->tanggal_bayar?->format('d/m/Y'),
                                            'nilai_tl' => $c->nilai_bayar,
                                            'setoran' => ($c->status === 'diterima') ? $c->nilai_bayar : 0,
                                            'keterangan' => "Cicilan Ke-" . $c->ke,
                                            'is_valid' => ($c->status === 'diterima')
                                        ];
                                    }
                                } else {
                                    $displayRows[] = [
                                        'tanggal' => $tl->created_at->format('d/m/Y'),
                                        'nilai_tl' => ($rekom->isUang()) ? $tl->nilai_tindak_lanjut : 0,
                                        'setoran' => ($tl->status_verifikasi === 'lunas' && $rekom->isUang()) ? $tl->nilai_tindak_lanjut : 0,
                                        'keterangan' => $tl->catatan_tl ?? ($rekom->isUang() ? 'Bayar Tunai' : 'Penyerahan Barang/Admin'),
                                        'is_valid' => ($tl->status_verifikasi === 'lunas')
                                    ];
                                }
                            }
                            
                            $totalSetoranRekom = collect($displayRows)->sum('setoran');
                            $lhpTotalTL += collect($displayRows)->sum('nilai_tl');
                            $lhpTotalSetoran += $totalSetoranRekom;
                        @endphp

                        @forelse($displayRows as $indexRow => $row)
                        <tr>
                            @if($indexRekom == 0 && $indexRow == 0)
                                <td class="text-center">{{ $no++ }}</td>
                                <td>
                                    <strong>{{ $lhp->unitDiperiksa?->nama_unit ?? 'TIDAK ADA UNIT' }}</strong><br>
                                    {{ $lhp->nomor_lhp }}
                                </td>
                                <td>{{ $temuan->kondisi }}</td>
                                <td class="text-center">{{ $temuan->kodeTemuan?->kode }}</td>
                            @else
                                <td colspan="4" style="border-left: none; border-top: none; border-bottom: none;"></td>
                            @endif

                            <td>{{ $indexRow == 0 ? $rekom->uraian_rekom : '' }}</td>
                            <td class="text-center">{{ $indexRow == 0 ? ($rekom->kodeRekomendasi?->kode ?? '-') : '' }}</td>
                            <td class="text-right">{{ $indexRow == 0 ? number_format($temuan->nilai_temuan, 0, ',', '.') : '' }}</td>

                            <td class="text-center">{{ $row['tanggal'] }}</td>
                            <td class="text-right">
                                @if($row['nilai_tl'] > 0)
                                    {{ number_format($row['nilai_tl'], 0, ',', '.') }}
                                @else
                                    <small>{{ \Illuminate\Support\Str::limit($row['keterangan'], 25) }}</small>
                                @endif
                            </td>
                            <td class="text-right {{ !$row['is_valid'] ? 'invalid' : '' }}">
                                @if($row['is_valid'])
                                    {{ $row['setoran'] > 0 ? number_format($row['setoran'], 0, ',', '.') : 'LUNAS' }}
                                @else
                                    0
                                @endif
                            </td>
                            <td class="text-right">
                                @if($loop->last)
                                    <strong>{{ number_format(max(0, $temuan->nilai_temuan - $totalSetoranRekom), 0, ',', '.') }}</strong>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            @if($indexRekom == 0)
                                <td class="text-center">{{ $no++ }}</td>
                                <td>
                                    <strong>{{ $lhp->unitDiperiksa?->nama_unit ?? 'TIDAK ADA UNIT' }}</strong><br>
                                    {{ $lhp->nomor_lhp }}
                                </td>
                                <td>{{ $temuan->kondisi }}</td>
                                <td class="text-center">{{ $temuan->kodeTemuan?->kode }}</td>
                            @else
                                <td colspan="4" style="border-left: none; border-top: none; border-bottom: none;"></td>
                            @endif
                            <td>{{ $rekom->uraian_rekom }}</td>
                            <td class="text-center">{{ $rekom->kodeRekomendasi?->kode ?? '-' }}</td>
                            <td class="text-right">{{ number_format($temuan->nilai_temuan, 0, ',', '.') }}</td>
                            <td colspan="3" class="text-center invalid">Belum ditindaklanjuti</td>
                            <td class="text-right">{{ number_format($temuan->nilai_temuan, 0, ',', '.') }}</td>
                        </tr>
                        @endforelse
                    @endforeach
                @endforeach

                <tr class="footer-lhp">
                    <td colspan="6" class="text-right">JUMLAH PER LHP : {{ $lhp->nomor_lhp }}</td>
                    <td class="text-right">{{ number_format($lhpTotalTemuan, 0, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($lhpTotalTL, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($lhpTotalSetoran, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format(max(0, $lhpTotalTemuan - $lhpTotalSetoran), 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <div class="sig-container">
            <p>Rembang, {{ now()->translatedFormat('d F Y') }}</p>
            <p style="margin-top: -5px;">Inspektur Kabupaten Rembang,</p>
            
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