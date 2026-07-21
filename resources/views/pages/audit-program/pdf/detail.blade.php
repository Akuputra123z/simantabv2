<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <style>
        @page { margin: 1.5cm 0.5cm 0.5cm 0.5cm; size: landscape; }
        body { font-family: 'Helvetica', sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        
        .kop-container { width: 100%; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .table-kop { width: 100%; border-collapse: collapse; border: none !important; }
        .table-kop td { border: none !important; vertical-align: middle; padding: 0; }
        .kop-instansi { font-size: 14px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .kop-sub { font-size: 11px; font-weight: bold; margin: 2px 0; }
        .kop-alamat { font-size: 7px; color: #555; margin: 1px 0; }
        .kop-logo { width: 55px; height: 55px; }

        .title { text-align: center; font-size: 12px; font-weight: bold; margin: 20px 0 4px; text-transform: uppercase; text-decoration: underline; }
        .subtitle { text-align: center; font-size: 9px; margin: 0 0 10px; color: #555; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th { background: #1e293b; color: #fff; padding: 6px 4px; text-align: left; font-size: 7.5px; text-transform: uppercase; }
        table.data td { padding: 5px 4px; border-bottom: 1px solid #ddd; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8fafc; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 7px; color: #999; padding: 5px 0; border-top: 1px solid #ddd; }
    </style>
</head>
<body>

    {{-- Kop --}}
    <div class="kop-container">
        <table class="table-kop">
            <tr>
                <td style="width:60px;">
                    @php
                        $logoPath = public_path('storage/logo-kota.png');
                        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
                    @endphp
                    @if($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" class="kop-logo">
                    @endif
                </td>
                <td>
                    <p class="kop-instansi">PEMERINTAH KOTA ...</p>
                    <p class="kop-sub">INSPEKTORAT DAERAH</p>
                    <p class="kop-alamat">Jl. ... Telp. ... Email: ...</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- Title --}}
    <p class="title">SUB-PROGRAM KERJA PENGAWASAN TAHUNAN</p>
    <p class="subtitle">{{ $auditProgram->nama_program }} — TA {{ $auditProgram->tahun }}</p>

    {{-- Table --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width:28px;">No</th>
                <th style="width:130px;">Nama Sub-Program</th>
                <th style="width:40px;">Jenis</th>
                <th style="width:70px;">Objek</th>
                <th style="width:40px;">Personil</th>
                <th style="width:65px;" class="text-center">Anggaran</th>
                <th style="width:40px;">Risiko</th>
                <th style="width:60px;">Jadwal</th>
                <th style="width:50px;">Tim</th>
                <th style="width:35px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $i => $d)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $d->nama_detail_program }}</td>
                <td>{{ $d->jenis_kegiatan ?? '-' }}</td>
                <td>{{ $d->objek_pengawasan ?? '-' }}</td>
                <td>{{ $d->personil ?? '-' }}</td>
                <td class="text-center">Rp {{ number_format($d->anggaran, 0, ',', '.') }}</td>
                <td>{{ $d->tingkat_resiko ?? '-' }}</td>
                <td>{{ App\Helpers\DateHelper::formatJadwal($d->jadwal) }}</td>
                <td>{{ $d->tim ?? '-' }}</td>
                <td class="text-center">{{ strtoupper($d->status) }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center" style="padding:20px; color:#999;">Belum ada sub-program.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Signature --}}
    <div style="margin-top: 30px; width: 100%;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <p style="font-size: 9px; margin: 0 0 5px;">Kota ..., {{ now()->format('d F Y') }}</p>
                    <p style="font-size: 9px; margin: 0 0 5px; font-weight: bold;">KEPALA INSPEKTORAT DAERAH</p>
                    @if($auditProgram->isApproved())
                        @php
                            $signaturePath = $auditProgram->approver?->signature;
                            $sigBase64 = '';
                            if ($signaturePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($signaturePath)) {
                                $sigBase64 = base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($signaturePath));
                            }
                        @endphp
                        @if($sigBase64)
                            <img src="data:image/png;base64,{{ $sigBase64 }}"
                                 style="height: 50px; margin: 8px 0 4px;">
                        @else
                            <br><br><br>
                        @endif
                        <p style="font-size: 11px; font-weight: bold; margin: 0;">{{ $auditProgram->approver?->name ?? '(nama)' }}</p>
                        <p style="font-size: 8px; margin: 2px 0; color: #888;">Disetujui: {{ $auditProgram->approved_at?->format('d/m/Y H:i') }}</p>
                    @else
                        <br><br><br><br>
                        <p style="font-size: 11px; font-weight: bold; margin: 0;">(........................................)</p>
                        <p style="font-size: 8px; margin: 2px 0; color: #888;">&nbsp;</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} — SIMANTAP
    </div>
</body>
</html>
