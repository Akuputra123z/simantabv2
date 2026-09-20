<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kepatuhan OPD - {{ $lhp->nomor_lhp }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 1.2cm 1.5cm 1.2cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.35;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        /* --- KOP SURAT --- */
        .kop-container {
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 2.5px double #000;
            padding-bottom: 6px;
        }

        .table-kop {
            width: 100%;
            border-collapse: collapse;
        }

        .table-kop td {
            vertical-align: middle;
            padding: 0;
        }

        .kop-instansi {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 0;
            line-height: 1.1;
        }

        .kop-sub {
            font-size: 14pt;
            font-weight: 900;
            text-transform: uppercase;
            color: #000;
            margin: 2px 0;
            letter-spacing: 0.5px;
        }

        .alamat {
            font-size: 6.8pt;
            color: #334155;
            font-style: italic;
            line-height: 1.25;
            margin-top: 2px;
        }

        /* --- JUDUL & STATUS --- */
        .doc-title {
            text-align: center;
            margin-bottom: 10px;
        }

        .doc-title h1 {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 2px 0;
            color: #0f172a;
        }

        .doc-title h2 {
            font-size: 8pt;
            font-weight: normal;
            text-transform: uppercase;
            margin: 0 0 5px 0;
            color: #475569;
        }

        .badge-status {
            display: inline-block;
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* --- SECTION STYLES --- */
        .section-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 2px;
            margin-top: 10px;
            margin-bottom: 6px;
            color: #0f172a;
        }

        .table-meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .table-meta td {
            padding: 2px 3px;
            vertical-align: top;
            font-size: 7.5pt;
        }

        .meta-label {
            width: 25%;
            font-weight: bold;
            color: #475569;
        }

        .meta-sep {
            width: 2%;
            text-align: center;
        }

        .meta-val {
            width: 73%;
            color: #0f172a;
        }

        /* --- REKAP STATISTIK BOX --- */
        .table-rekap {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .table-rekap td {
            border: 1px solid #cbd5e1;
            padding: 5px 4px;
            text-align: center;
            background-color: #f8fafc;
        }

        .rekap-num {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            display: block;
        }

        .rekap-label {
            font-size: 6pt;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }

        /* --- TEMUAN CARD TABLE (CLEAN, NO OVERLAPPING ROWSPAN) --- */
        .temuan-card {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .temuan-card-berulang {
            border: 1.5px solid #e11d48 !important;
        }

        .temuan-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 5px 8px;
        }

        .temuan-header-berulang {
            background-color: #ffe4e6 !important;
            border-bottom: 1px solid #fecdd3 !important;
        }

        .box-warning-repeat {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
            border-left: 3px solid #e11d48;
            padding: 5px 7px;
            margin: 5px 8px;
            font-size: 6.8pt;
            line-height: 1.35;
        }

        /* --- SUB-TABLE REKOMENDASI --- */
        .table-sub-rekom {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .table-sub-rekom th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 6px;
            border: 1px solid #334155;
            text-align: left;
        }

        .table-sub-rekom td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 7pt;
            vertical-align: top;
        }

        /* --- STANDARD DATA TABLE --- */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 7pt;
        }

        .table-data th {
            background-color: #0f172a;
            color: #ffffff;
            border: 1px solid #334155;
            padding: 4px 5px;
            text-align: left;
            text-transform: uppercase;
            font-size: 6.5pt;
            font-weight: bold;
        }

        .table-data td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            vertical-align: top;
        }

        .table-data tr {
            page-break-inside: avoid;
        }

        /* --- BADGES --- */
        .badge-code {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 1px 4px;
            border-radius: 2px;
            font-family: monospace;
            font-size: 6.5pt;
            font-weight: bold;
            color: #0f172a;
        }

        .tag-berulang {
            display: inline-block;
            background-color: #be123c;
            color: #ffffff;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .tag-baru {
            display: inline-block;
            background-color: #d1fae5;
            color: #065f46;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* --- KESIMPULAN BOX --- */
        .box-kesimpulan {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            margin-top: 8px;
            margin-bottom: 12px;
            font-size: 7pt;
            line-height: 1.35;
            page-break-inside: avoid;
        }

        /* --- SIGNATURE SECTION --- */
        .table-signature {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            page-break-inside: avoid;
        }

        .table-signature td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 7.5pt;
        }

        .sig-space {
            height: 50px;
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 8pt;
            color: #000;
        }

        .sig-nip {
            font-size: 7pt;
            color: #334155;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <div class="kop-container">
        <table class="table-kop">
            <tr>
                <td style="width: 70px; text-align: left; vertical-align: middle;">
                    @php
                        $logoPath = public_path('images/logo/rembang.png');
                        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
                    @endphp
                    @if($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" style="width: 60px; height: 60px; object-fit: contain;">
                    @endif
                </td>
                <td style="text-align: center;">
                    <div class="kop-instansi">Pemerintah Kabupaten Rembang</div>
                    <div class="kop-sub">Inspektorat Daerah</div>
                    <div class="alamat">
                        Jl. Raya Rembang-Lasem Km 1,1, Tireman Barat, Tireman, Kec. Rembang, Kabupaten Rembang, Jawa Tengah 59219<br>
                        Telepon: (0295) 691320 | Pos-el: inspektorat@rembangkab.go.id | Laman: https://inspektorat.rembangkab.go.id
                    </div>
                </td>
                <td style="width: 60px;"></td>
            </tr>
        </table>
    </div>

    {{-- DOKUMEN TITLE --}}
    <div class="doc-title">
        <h1>Laporan Kesimpulan Kepatuhan Tindak Lanjut</h1>
        <h2>Hasil Pengawasan dan Pemeriksaan Inspektorat Daerah</h2>
        <div class="badge-status">Status Kepatuhan: Tuntas 100% (Memenuhi Kepatuhan Penuh)</div>
    </div>

    {{-- I. IDENTITAS --}}
    <div class="section-title">I. Identitas Objek Pemeriksaan</div>
    <table class="table-meta">
        <tr>
            <td class="meta-label">Objek Pemeriksaan (OPD)</td>
            <td class="meta-sep">:</td>
            <td class="meta-val"><strong>{{ $lhp->unitDiperiksa?->nama_unit ?? $lhp->nama_unit ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Nomor LHP</td>
            <td class="meta-sep">:</td>
            <td class="meta-val"><strong>{{ $lhp->nomor_lhp }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal LHP</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $lhp->tanggal_lhp ? \Carbon\Carbon::parse($lhp->tanggal_lhp)->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Program Pengawasan</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $lhp->auditAssignment?->auditProgram?->nama_program ?? '-' }} (Tahun {{ $lhp->auditAssignment?->auditProgram?->tahun ?? '-' }})</td>
        </tr>
        <tr>
            <td class="meta-label">Tim Pemeriksa / Irban</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $lhp->auditAssignment?->auditProgramDetail?->tim ?? 'Inspektorat Daerah' }}</td>
        </tr>
    </table>

    {{-- II. REKAPITULASI PENYELESAIAN --}}
    @php
        $stat = $lhp->statistik;
        $totalTemuan = $stat?->total_temuan ?? $lhp->temuans->count();
        $totalRekom = $stat?->total_rekomendasi ?? $lhp->temuans->sum(fn($t) => $t->recommendations->count());
        $rekomSelesai = $stat?->rekom_selesai ?? $totalRekom;
        $totalKerugian = (float) ($stat?->total_kerugian ?? $lhp->total_kerugian);
        $totalSetor = (float) ($stat?->total_nilai_tl_selesai ?? 0);
        $sisaKerugian = (float) ($stat?->total_sisa_kerugian ?? 0);
    @endphp
    <div class="section-title">II. Ringkasan Kepatuhan Tindak Lanjut</div>
    <table class="table-rekap">
        <tr>
            <td>
                <span class="rekap-num">{{ $totalTemuan }}</span>
                <span class="rekap-label">Total Temuan</span>
            </td>
            <td>
                <span class="rekap-num">{{ $totalRekom }}</span>
                <span class="rekap-label">Total Rekomendasi</span>
            </td>
            <td style="background-color: #ecfdf5;">
                <span class="rekap-num" style="color: #065f46;">{{ $rekomSelesai }} (100%)</span>
                <span class="rekap-label" style="color: #047857;">Tuntas Diselesaikan</span>
            </td>
            <td>
                <span class="rekap-num" style="font-size: 8.5pt;">Rp {{ number_format($totalKerugian, 0, ',', '.') }}</span>
                <span class="rekap-label">Nilai Temuan</span>
            </td>
            <td style="background-color: #eff6ff;">
                <span class="rekap-num" style="font-size: 8.5pt; color: #1d4ed8;">Rp {{ number_format($totalSetor, 0, ',', '.') }}</span>
                <span class="rekap-label" style="color: #1e40af;">Pengembalian / Setor</span>
            </td>
            <td>
                <span class="rekap-num" style="font-size: 8.5pt; color: #047857;">Rp {{ number_format($sisaKerugian, 0, ',', '.') }}</span>
                <span class="rekap-label">Sisa (Lunas)</span>
            </td>
        </tr>
    </table>

    {{-- III. MATRIKS RINCIAN TEMUAN & REKOMENDASI --}}
    <div class="section-title">III. Matriks Temuan, Rekomendasi & Tindak Lanjut Terverifikasi</div>

    @if($lhp->is_nihil || $lhp->temuans->isEmpty())
        <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; padding: 8px; text-align: center; margin-bottom: 10px; font-size: 7.5pt; color: #065f46;">
            <strong>LHP Ini Bebas Temuan (Nihil Audit Findings).</strong> Seluruh aspek pengelolaan anggaran dinyatakan tertib dan mematuhi peraturan perundang-undangan.
        </div>
    @else
        @php $no = 1; @endphp
        @foreach($lhp->temuans as $temuan)
            @php
                $tEval = $analisisBerulang['temuans_map'][$temuan->id] ?? null;
                $isTemuanUlang = $tEval['is_berulang'] ?? false;
                $nilaiTemuan = ($temuan->nilai_kerugian_negara ?? 0) + ($temuan->nilai_kerugian_daerah ?? 0) + ($temuan->nilai_kerugian_desa ?? 0) + ($temuan->nilai_kerugian_bos_blud ?? 0);
            @endphp
            <table class="temuan-card {{ $isTemuanUlang ? 'temuan-card-berulang' : '' }}">
                {{-- Header Temuan --}}
                <tr class="temuan-header {{ $isTemuanUlang ? 'temuan-header-berulang' : '' }}">
                    <td style="width: 70%; vertical-align: middle;">
                        <strong>TEMUAN {{ $no++ }}: {{ $temuan->judul ?? 'Temuan Pemeriksaan' }}</strong>
                        <span class="badge-code">{{ $temuan->kodeTemuan?->kode ?? '-' }}</span>
                        @if($isTemuanUlang)
                            <span class="tag-berulang">[TEMUAN BERULANG]</span>
                        @else
                            <span class="tag-baru">[KASUS BARU]</span>
                        @endif
                        <div style="font-size: 6.5pt; color: #475569; margin-top: 1px;">
                            {{ $temuan->kodeTemuan?->sub_kelompok ?? $temuan->kodeTemuan?->deskripsi }}
                        </div>
                    </td>
                    <td style="width: 30%; text-align: right; vertical-align: middle;">
                        @if($nilaiTemuan > 0)
                            <span style="font-weight: bold; color: #be123c; font-size: 7.5pt;">
                                Nilai: Rp {{ number_format($nilaiTemuan, 0, ',', '.') }}
                            </span>
                        @else
                            <span style="color: #64748b; font-size: 6.5pt;">Non-Finansial</span>
                        @endif
                    </td>
                </tr>

                {{-- Alert Box jika Berulang --}}
                @if($isTemuanUlang)
                    <tr>
                        <td colspan="2" style="padding: 0;">
                            <div class="box-warning-repeat">
                                <strong style="color: #be123c;">[PERINGATAN PENGAWASAN - TEMUAN BERULANG]:</strong><br>
                                Terdeteksi pengulangan dari LHP No. {{ implode(', ', $tEval['lhp_lampau'] ?? []) }} ({{ implode(', ', $tEval['periode_lampau'] ?? []) }}).<br>
                                <strong>Kesimpulan Evaluasi OPD:</strong> {{ $tEval['kesimpulan_temuan_opd'] ?? '' }}<br>
                                <strong style="color: #9f1239;">Instruksi Khusus Kepala OPD:</strong> {{ $tEval['instruksi_khusus_opd'] ?? '' }}
                            </div>
                        </td>
                    </tr>
                @endif

                {{-- Kondisi Temuan --}}
                @if($temuan->kondisi)
                    <tr>
                        <td colspan="2" style="padding: 4px 8px; font-size: 6.8pt; color: #334155; background-color: #fafafa; border-bottom: 1px solid #e2e8f0;">
                            <strong>Kondisi / Fakta Temuan:</strong> {{ $temuan->kondisi }}
                        </td>
                    </tr>
                @endif

                {{-- Sub-table Rekomendasi & Tindak Lanjut --}}
                <tr>
                    <td colspan="2" style="padding: 4px 6px;">
                        <table class="table-sub-rekom">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Rekomendasi Audit</th>
                                    <th style="width: 38%;">Pelaksanaan Tindak Lanjut & Setoran Kas</th>
                                    <th style="width: 12%; text-align: center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($temuan->recommendations as $rIdx => $rekom)
                                    @php
                                        $tlLatest = $rekom->tindakLanjuts->last();
                                        $setoran = $rekom->tindakLanjuts->flatMap->cicilans->sum('jumlah') ?? 0;
                                        $rEval = $tEval['rekom_map'][$rekom->id] ?? null;
                                        $isRekomUlang = $rEval['is_berulang'] ?? false;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge-code">R{{ $rIdx + 1 }}: {{ $rekom->kodeRekomendasi?->kode ?? '-' }}</span>
                                            @if($isRekomUlang)
                                                <span style="color: #b45309; font-weight: bold; font-size: 5.8pt;">[REKOMENDASI BERULANG]</span>
                                            @endif
                                            <div style="margin-top: 2px; color: #0f172a; line-height: 1.25;">
                                                {{ $rekom->deskripsi ?? $rekom->rekomendasi ?? '-' }}
                                            </div>
                                            @if(($rekom->nilai_rekomendasi ?? 0) > 0)
                                                <div style="font-size: 6pt; font-weight: bold; color: #be123c; margin-top: 2px;">
                                                    Kewajiban Setor: Rp {{ number_format($rekom->nilai_rekomendasi, 0, ',', '.') }}
                                                </div>
                                            @endif
                                            @if($isRekomUlang)
                                                <div style="margin-top: 2px; font-size: 5.8pt; color: #854d0e; background: #fefce8; padding: 2px 4px; border: 0.5px solid #fef08a;">
                                                    <strong>Catatan OPD:</strong> {{ $rEval['kesimpulan_rekom_opd'] }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="color: #0f172a; line-height: 1.25;">
                                                {{ $tlLatest?->uraian_tindak_lanjut ?? $tlLatest?->keterangan ?? 'Telah ditindaklanjuti secara penuh dan disetujui sesuai rekomendasi.' }}
                                            </div>
                                            @if($setoran > 0)
                                                <div style="font-size: 6pt; font-weight: bold; color: #15803d; margin-top: 2px;">
                                                    Setoran Kas Lunas: Rp {{ number_format($setoran, 0, ',', '.') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; background-color: #f0fdf4; color: #047857; font-weight: bold;">
                                            SELESAI<br>(100%)
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        @endforeach
    @endif

    {{-- IV. ANALISIS TEMUAN BERULANG --}}
    <div class="section-title">IV. Analisis Riwayat & Deteksi Temuan Berulang</div>
    @if($analisisBerulang['has_berulang'])
        <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-left: 3px solid #e11d48; padding: 5px 7px; margin-bottom: 6px; font-size: 6.8pt; line-height: 1.35;">
            <strong style="color: #9f1239;">[PERINGATAN PENGAWASAN - TEMUAN BERULANG TERDETEKSI]:</strong><br>
            {{ $analisisBerulang['catatan_ringkas'] }} Meskipun seluruh rekomendasi telah berstatus 100% tuntas, pengulangan temuan sejenis menunjukkan kelemahan manajerial/sistemik yang memerlukan perhatian serius pimpinan OPD.
        </div>

        <table class="table-data" style="margin-bottom: 8px;">
            <thead>
                <tr>
                    <th style="width: 15%;">Kode Temuan</th>
                    <th style="width: 40%;">Klasifikasi Temuan Lampau</th>
                    <th style="width: 15%; text-align: center;">Frekuensi</th>
                    <th style="width: 30%;">Nomor LHP Sebelumnya</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analisisBerulang['daftar_berulang'] as $item)
                    <tr>
                        <td style="font-family: monospace; font-weight: bold; color: #be123c;">{{ $item['kode'] }}</td>
                        <td>
                            <strong>{{ $item['deskripsi_kode'] }}</strong><br>
                            <span style="font-size: 6pt; color: #64748b;">{{ $item['kelompok'] }}</span>
                        </td>
                        <td class="text-center" style="font-weight: bold; color: #be123c;">{{ $item['frekuensi_lampau'] }} kali</td>
                        <td style="font-family: monospace; font-size: 6.5pt;">{{ implode(', ', $item['lhp_lampau']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-left: 3px solid #059669; padding: 5px 7px; margin-bottom: 8px; font-size: 6.8pt; color: #065f46;">
            <strong>Status Non-Pengulangan:</strong> {{ $analisisBerulang['catatan_ringkas'] }}
        </div>
    @endif

    {{-- V. REKOMENDASI MANAJERIAL & AKSI PENCEGAHAN --}}
    <div class="section-title">V. Rekomendasi Manajerial & Rencana Aksi Pencegahan</div>
    <div style="font-size: 6.8pt; margin-bottom: 4px; color: #475569;">
        Guna mencegah terulangnya temuan pada tahun anggaran berikutnya, Objek Pemeriksaan diinstruksikan melaksanakan aksi perbaikan berikut:
    </div>
    <table class="table-data" style="margin-bottom: 8px;">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 35%;">Area Pengendalian / Master Kode</th>
                <th style="width: 60%;">Instruksi Aksi Nyata Pencegahan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($masukanManajerial as $idx => $masukan)
                <tr>
                    <td class="text-center" style="font-weight: bold;">{{ $idx + 1 }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $masukan['kategori'] }}</strong>
                        <div style="font-size: 6.2pt; color: #475569; margin-top: 1px;">{{ $masukan['poin'] }}</div>
                    </td>
                    <td style="line-height: 1.3; color: #1e293b;">
                        {{ $masukan['tindakan'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- VI. PROTOKOL KETENTUAN SANKSI TEMUAN BERULANG --}}
    <div class="section-title">VI. Ketentuan & Protokol Sanksi Jika Terjadi Temuan Berulang</div>
    <div style="font-size: 6.8pt; margin-bottom: 4px; color: #475569;">
        Berdasarkan PP No. 94 Tahun 2021 dan regulasi pengawasan Pemda, pengulangan temuan pada pemeriksaan berkala berikutnya akan dikenakan sanksi berjenjang:
    </div>
    <table class="table-data" style="margin-bottom: 6px;">
        <thead>
            <tr>
                <th style="width: 15%;">Tingkat Sanksi</th>
                <th style="width: 25%;">Kriteria Pengulangan</th>
                <th style="width: 45%;">Tindakan Eskalasi Pengawasan & Sanksi</th>
                <th style="width: 15%;">Dasar Hukum</th>
            </tr>
        </thead>
        <tbody>
            @foreach($eskalasiSanksi as $sanksi)
                <tr>
                    <td>
                        <strong>{{ $sanksi['tingkat'] }}</strong><br>
                        <span style="font-size: 6pt; color: #64748b;">{{ $sanksi['label'] }}</span>
                    </td>
                    <td style="color: #334155;">{{ $sanksi['syarat'] }}</td>
                    <td style="line-height: 1.25;">{{ $sanksi['tindakan'] }}</td>
                    <td style="font-size: 5.8pt; color: #64748b; font-style: italic;">{{ $sanksi['dasar_hukum'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TABEL AMBANG BATAS PENGULANGAN TEMUAN --}}
    <div style="font-size: 6.8pt; font-weight: bold; margin-top: 6px; margin-bottom: 3px; text-transform: uppercase; color: #1e293b;">
        Matriks Kebijakan Sanksi Berdasarkan Jumlah Temuan Berulang:
    </div>
    <table class="table-data" style="margin-bottom: 8px; font-size: 6.5pt;">
        <thead>
            <tr>
                <th style="width: 18%;">Jumlah Temuan Berulang</th>
                <th style="width: 27%;">Tingkat / Kategori Risiko</th>
                <th style="width: 55%;">Konsekuensi Penegakan Sanksi bagi OPD</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold; color: #b45309;">1 – 2 Temuan</td>
                <td>Peringatan Prosedural</td>
                <td>Peringatan pengawasan internal, penyempurnaan SOP verifikasi dokumen, dan peningkatan supervisi atasan langsung.</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #be123c;">3 – 5 Temuan</td>
                <td style="font-weight: bold; color: #be123c;">Kelemahan Sistemik (Gagal SPIP)</td>
                <td><strong>Surat Peringatan Keras Bupati</strong>, pemotongan bobot SAKIP (5–10 poin), <strong>pemotongan TPP 10%–20%</strong> bagi PPK/PPTK/Bendahara 3 bulan, dan status <em>Special Watchlist PKPT</em>.</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #991b1b;">6 – 10 Temuan</td>
                <td style="font-weight: bold; color: #991b1b;">Maladministrasi Kronis (Pembiaran)</td>
                <td><strong>Rekomendasi Pencopotan/Bebas Tugas PPK/PPTK/Bendahara</strong>, evaluasi jabatan Kepala OPD oleh Bupati, <strong>Audit Investigasi Forensik APIP</strong>, pembekuan usulan anggaran tambahan, dan pelimpahan ke <strong>Majelis TP-TGR / APH</strong> jika ada kerugian negara.</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #6b21a8;">&gt; 10 Temuan</td>
                <td style="font-weight: bold; color: #6b21a8;">Darurat Akuntabilitas</td>
                <td>Pembekuan otorisasi belanja mandiri, penempatan Tim Asistensi Melekat APIP secara penuh, dan perombakan total manajemen keuangan OPD.</td>
            </tr>
        </tbody>
    </table>

    {{-- VII. KESIMPULAN & RAPOR EVALUASI KEPATUHAN OPD --}}
    @php
        $kesimpulanOpd = $analisisBerulang['kesimpulan_opd'] ?? [];
        $tipeOpd = $kesimpulanOpd['tipe'] ?? '';
        $isBerulangOpd = in_array($tipeOpd, ['berulang', 'berulang_signifikan', 'berulang_kritis', 'berulang_darurat']);
        $boxBorderColor = match($tipeOpd) {
            'berulang_darurat' => '#6b21a8',
            'berulang_kritis' => '#991b1b',
            'berulang_signifikan' => '#be123c',
            'berulang' => '#d97706',
            default => '#0284c7',
        };
        $boxBgColor = match($tipeOpd) {
            'berulang_darurat' => '#faf5ff',
            'berulang_kritis' => '#fef2f2',
            'berulang_signifikan' => '#fff1f2',
            'berulang' => '#fffbeb',
            default => '#f8fafc',
        };
    @endphp
    <div class="box-kesimpulan" style="border-left: 3.5px solid {{ $boxBorderColor }}; background-color: {{ $boxBgColor }};">
        <strong style="color: {{ $boxBorderColor }}; font-size: 7.5pt;">
            VII. KESIMPULAN & RAPOR EVALUASI PERILAKU KEPATUHAN OPD: [{{ $kesimpulanOpd['status'] ?? 'Kepatuhan 100%' }}]
        </strong><br>
        <div style="margin-top: 4px; line-height: 1.35; font-size: 6.8pt; color: #1e293b;">
            <strong>1. Ringkasan Kepatuhan & Evaluasi Pengulangan Temuan:</strong><br>
            {{ $kesimpulanOpd['ringkasan'] ?? '' }}
        </div>
        <div style="margin-top: 4px; line-height: 1.35; font-size: 6.8pt; color: {{ $isBerulangOpd ? '#991b1b' : '#1e3a8a' }}; background: #ffffff; padding: 4px 6px; border: 0.5px solid #cbd5e1; border-radius: 2px;">
            <strong>2. Catatan Pembinaan & Penegasan Konsekuensi Hukum untuk Kepala OPD:</strong><br>
            {!! nl2br(e($kesimpulanOpd['penegasan'] ?? '')) !!}
        </div>
        <div style="margin-top: 4px; font-size: 6pt; color: #64748b; font-style: italic;">
            Dokumen kesimpulan kepatuhan ini bersifat mengikat dan menjadi dasar pertimbangan evaluasi kinerja serta penjatuhan sanksi jika terbukti melakukan pengulangan pada periode berikutnya.
        </div>
    </div>

    {{-- VIII. TANDA TANGAN --}}
    <table class="table-signature">
        <tr>
            <td>
                Mengetahui / Mengesahkan,<br>
                <strong>Inspektur Daerah Kabupaten Rembang</strong>
                <div class="sig-space"></div>
                <div class="sig-name">IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE</div>
                <div class="sig-nip">Pembina Utama Muda</div>
                <div class="sig-nip">NIP. 19920401202521104733</div>
            </td>
            <td>
                Rembang, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>{{ $lhp->auditAssignment?->auditProgramDetail?->tim ? 'Inspektur Pembantu ' . $lhp->auditAssignment->auditProgramDetail->tim : 'Pengendali Mutu / Irban' }}</strong>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $lhp->auditAssignment?->ketuaTim?->name ?? 'Tim Pemeriksa Inspektorat' }}</div>
                <div class="sig-nip">Ketua Tim Pengawas</div>
                <div class="sig-nip">NIP. {{ $lhp->auditAssignment?->ketuaTim?->nip ?? '..................................' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
