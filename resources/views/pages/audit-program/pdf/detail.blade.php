<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <style>
        @page { margin: 1.2cm 1cm 1cm 1cm; size: landscape; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 9px; color: #000; margin: 0; padding: 0; }
        
        .kop-container { width: 100%; margin-bottom: 15px; border-bottom: 3px double #000; padding-bottom: 8px; }
        .table-kop { width: 100%; border-collapse: collapse; border: none !important; margin: 0; padding: 0; }
        .table-kop td { border: none !important; vertical-align: middle; padding: 0; }
        .kop-logo-cell { width: 75px; text-align: center; }
        .kop-spacer-cell { width: 75px; }
        .kop-logo { width: 65px; height: auto; max-height: 85px; }
        .kop-text-cell { text-align: center; }
        .kop-instansi { font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; }
        .kop-sub { font-size: 16pt; font-weight: bold; text-transform: uppercase; margin: 2px 0; line-height: 1.2; letter-spacing: 1px; }
        .kop-alamat { font-size: 9pt; margin: 2px 0 0 0; line-height: 1.3; }
        .kop-sub-alamat { font-size: 8pt; font-style: italic; }

        .title { text-align: center; font-size: 12px; font-weight: bold; margin: 15px 0 4px; text-transform: uppercase; text-decoration: underline; }
        .subtitle { text-align: center; font-size: 9px; margin: 0 0 10px; color: #555; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th { background: #1e293b; color: #fff; padding: 6px 4px; text-align: left; font-size: 7.5px; text-transform: uppercase; }
        table.data td { padding: 5px 4px; border-bottom: 1px solid #ddd; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8fafc; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .signature-section { margin-top: 15px; width: 100%; page-break-inside: avoid !important; }
        .table-ttd { width: 100%; border-collapse: collapse; border: none !important; }
        .table-ttd td { border: none !important; vertical-align: top; padding: 0; }
        .ttd-box { display: inline-block; text-align: center; min-width: 280px; }
        .ttd-lokasi-tgl { font-size: 9pt; margin: 0 0 4px; }
        .ttd-jabatan { font-size: 10pt; font-weight: bold; text-transform: uppercase; line-height: 1.3; margin: 0 0 8px; }
        .ttd-space { height: 75px; }
        .ttd-signature { text-align: center; margin: 4px 0; }
        .ttd-signature-img { height: 85px; max-height: 95px; width: auto; }
        .ttd-nama { font-size: 10pt; font-weight: bold; text-decoration: underline; margin: 0; white-space: nowrap; }
        .ttd-nip { font-size: 9pt; margin: 3px 0 0; line-height: 1.3; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 7px; color: #999; padding: 5px 0; border-top: 1px solid #ddd; }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logo/image.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logo/rembang.png');
        }
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logos/rembang.png');
        }
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
    @endphp

    {{-- Kop Surat --}}
    <div class="kop-container">
        <table class="table-kop">
            <tr>
                <td class="kop-logo-cell">
                    @if(!empty($logoBase64))
                        <img src="data:image/png;base64,{{ $logoBase64 }}" class="kop-logo" alt="Lambang Kabupaten Rembang">
                    @endif
                </td>
                <td class="kop-text-cell">
                    <div class="kop-instansi">Pemerintah Kabupaten Rembang</div>
                    <div class="kop-sub">Inspektorat Daerah</div>
                    <div class="kop-alamat">Jl. Raya Rembang – Lasem Km 1.1 Telp. ( 0295 ) 691320 Fax. (0295) 693525</div>
                    <div class="kop-sub-alamat">Rembang 59219 Email : inspektorat@rembangkab.go.id</div>
                </td>
                <td class="kop-spacer-cell"></td>
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

    @php
        $tanggalTtd = ($auditProgram->approved_at ?? $auditProgram->created_at ?? now())->locale('id')->translatedFormat('d F Y');
        $waktuTtd = ($auditProgram->approved_at ?? now())->locale('id')->translatedFormat('d F Y H:i') . ' WIB';
        
        $sigBase64 = '';
        $qrBase64 = '';
        $isSigned = $auditProgram->isApproved() 
            || !empty($auditProgram->approved_at) 
            || request()->boolean('preview_ttd') 
            || request()->boolean('with_signature');

        if ($isSigned) {
            $sigPath = null;
            // 1. Cek approver signature
            if ($auditProgram->approver && $auditProgram->approver->signature) {
                $sigPath = $auditProgram->approver->signature;
            }
            // 2. Jika belum ada atau approver bukan Imung, cari signature resmi Kepala Inspektorat (Imung Tri Wijayanti)
            if (!$sigPath || !\Illuminate\Support\Facades\Storage::disk('public')->exists($sigPath)) {
                $kepala = \App\Models\User::where(function($q) {
                        $q->where('name', 'like', '%Imung%')
                          ->orWhere('nip', 'like', '%19741128%');
                    })
                    ->whereNotNull('signature')
                    ->first();
                $sigPath = $kepala?->signature;
            }
            // 3. Fallback ke file signature default jika tersedia di storage
            if (!$sigPath || !\Illuminate\Support\Facades\Storage::disk('public')->exists($sigPath)) {
                $sigPath = 'signatures/4OYa9msXrqsot6MkvjKPRfD773BnAVYqaiDiqnUM.png';
            }
            if ($sigPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($sigPath)) {
                $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($sigPath);
                $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'png';
                $sigBase64 = 'data:image/'.$ext.';base64,'.base64_encode(file_get_contents($fullPath));
            }

            // QR Code Verifikasi Tanda Tangan Elektronik (URL Resmi untuk deteksi cepat kamera smartphone)
            try {
                $qrPayload = route('audit-program.preview', $auditProgram->id);
                $qrBase64 = \App\Helpers\QrCodeHelper::generateDataUrl($qrPayload, 260, 2);
            } catch (\Throwable $e) {
                // Fallback safe
            }
        }
    @endphp

    {{-- Signature & QR Code --}}
    <div class="signature-section">
        <table class="table-ttd">
            <tr>
                {{-- QR Code di sebelah kiri bawah tabel --}}
                <td style="width: 55%; vertical-align: top; padding-top: 2px; text-align: left;">
                    @if($isSigned && !empty($qrBase64))
                        <div style="display: inline-block; border: 1px solid #d1d5db; background: #fafafa; padding: 6px 12px; border-radius: 4px;">
                            <table style="border-collapse: collapse; border: none; margin: 0; padding: 0;">
                                <tr>
                                    <td style="width: 74px; vertical-align: middle; padding: 0 10px 0 0; border: none !important;">
                                        <img src="{{ $qrBase64 }}" width="70" height="70" style="width: 70px; height: 70px; display: block;" alt="QR Code Verifikasi">
                                    </td>
                                    <td style="vertical-align: middle; border: none !important; font-size: 7.5pt; line-height: 1.4; color: #333;">
                                        <strong style="color: #000; font-size: 8pt;">DITANDATANGANI SECARA ELEKTRONIK</strong><br>
                                        Inspektorat Daerah Kabupaten Rembang<br>
                                        <span style="color: #666; font-size: 7pt;">Pindai QR untuk verifikasi keaslian dokumen</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endif
                </td>

                {{-- Tanda Tangan di sebelah kanan --}}
                <td style="width: 45%; text-align: center; vertical-align: top;">
                    <div class="ttd-box">
                        <p class="ttd-lokasi-tgl">Rembang, {{ $tanggalTtd }}</p>
                        <p class="ttd-jabatan">
                            INSPEKTUR DAERAH<br>
                            KABUPATEN REMBANG
                        </p>

                        @if(!empty($sigBase64))
                            <div class="ttd-signature">
                                <img src="{{ $sigBase64 }}" class="ttd-signature-img" alt="Tanda Tangan">
                            </div>
                        @else
                            <div class="ttd-space"></div>
                        @endif

                        @if($auditProgram->approver)
                            <p class="ttd-nama">{{ $auditProgram->approver->name }}</p>
                            <p class="ttd-nip">
                                {{ $auditProgram->approver->jabatan ?? 'Pembina' }}<br>
                                NIP. {{ $auditProgram->approver->nip ?? '-' }}
                            </p>
                        @else
                            <p class="ttd-nama">IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE</p>
                            <p class="ttd-nip">
                                Pembina<br>
                                NIP. 197411281999032003
                            </p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} — SIMANTAP
    </div>
</body>
</html>
