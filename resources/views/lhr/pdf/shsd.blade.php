<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>LHR - {{ $lhr->nomor_lhr ?? 'DRAFT' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 2cm 2cm 2cm 2.5cm;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #000;
        }

        .page-break {
            page-break-after: always;
        }

        /* Kop Surat */
        .kop-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .kop-table td {
            vertical-align: middle;
            text-align: center;
        }
        .kop-instansi {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop-sub {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: 8.5pt;
            margin-top: 4px;
        }

        /* Judul Laporan */
        .judul-container {
            text-align: center;
            margin-bottom: 25px;
        }
        .judul-laporan {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        .nomor-laporan {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 4px;
        }

        /* Sections */
        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 6px;
        }
        .paragraph {
            text-align: justify;
            margin-bottom: 8px;
            text-indent: 1.25cm;
        }
        .paragraph-no-indent {
            text-align: justify;
            margin-bottom: 8px;
        }

        /* Lists */
        ol, ul {
            margin-top: 4px;
            margin-bottom: 10px;
            padding-left: 1.5cm;
        }
        li {
            margin-bottom: 4px;
            text-align: justify;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        /* Tanda Tangan */
        .signature-container {
            margin-top: 30px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border: none;
        }
        .signature-table td {
            border: none;
            vertical-align: top;
        }

        /* QR / Verification Box */
        .qr-box {
            border: 1px dashed #666;
            padding: 8px;
            font-size: 8pt;
            color: #444;
            margin-top: 20px;
            background-color: #fafafa;
        }

        /* Watermark Draft */
        .watermark {
            position: fixed;
            top: 40%;
            left: 15%;
            transform: rotate(-35deg);
            font-size: 70pt;
            color: rgba(220, 38, 38, 0.15);
            font-weight: bold;
            z-index: -1000;
        }
    </style>
</head>
<body>

    @if($isDraft)
        <div class="watermark">DRAFT / PRATINJAU</div>
    @endif

    <!-- KOP SURAT RESMI -->
    <table class="kop-table">
        <tr>
            <td style="width: 15%; text-align: left;">
                {{-- Logo daerah --}}
                <img src="{{ public_path('tailadmin-laravel.png') }}" alt="Logo" style="height: 70px; object-fit: contain;">
            </td>
            <td style="width: 85%;">
                <div class="kop-instansi">Pemerintah Kabupaten Rembang</div>
                <div class="kop-sub">Inspektorat Daerah</div>
                <div class="kop-alamat">Jl. Gatot Subroto No. 8 Rembang, Jawa Tengah 59211<br>Telepon (0295) 691234 | Email: inspektorat@rembangkab.go.id</div>
            </td>
        </tr>
    </table>

    <!-- JUDUL LAPORAN -->
    <div class="judul-container">
        <h1 class="judul-laporan">Laporan Hasil Reviu (LHR)</h1>
        <div style="font-weight: bold; text-transform: uppercase;">Atas {{ $jenis->nama ?? 'Standar Harga Satuan Daerah' }}</div>
        <div>Tahun Anggaran {{ $lhr->tahun_anggaran }}</div>
        <div class="nomor-laporan">Nomor: {{ $lhr->nomor_lhr ?? 'Draft Belum Diterbitkan' }}</div>
    </div>

    <!-- BAGIAN A: PERNYATAAN REVIU -->
    <div class="section-title">A. Pernyataan Reviu</div>
    <div class="paragraph">
        {{ $sections['pernyataan']->isi ?? 'Kami telah mereviu Rancangan Standar Harga Satuan Daerah (SHSD) Tahun Anggaran ' . $lhr->tahun_anggaran . ' sesuai dengan Standar Audit Intern Pemerintah Indonesia (SAIPI).' }}
    </div>

    <!-- BAGIAN B: DASAR REVIU & TIM -->
    <div class="section-title">B. Dasar Reviu</div>
    <div class="paragraph-no-indent">
        Pelaksanaan reviu didasarkan pada ketentuan perundang-undangan sebagai berikut:
    </div>
    <ol>
        @forelse($dasarHukums as $dh)
            <li>{{ $dh->teks }}</li>
        @empty
            <li>Peraturan Menteri Dalam Negeri Nomor 3 Tahun 2026 tentang Pedoman Pengawasan Penyelenggaraan Pemerintahan Daerah.</li>
        @endforelse
    </ol>
    <div class="paragraph-no-indent">
        Susunan Tim Reviu Inspektorat Daerah Kabupaten Rembang berdasarkan Surat Perintah Tugas Nomor {{ $assignment->nomor_surat ?? '-' }} tanggal {{ $assignment->tanggal_mulai ? $assignment->tanggal_mulai->translatedFormat('d F Y') : '-' }} terdiri dari:
    </div>
    <ul>
        <li><strong>Penanggung Jawab:</strong> {{ $team['penandatangan']['nama'] ?? 'Inspektur Kabupaten Rembang' }}</li>
        <li><strong>Pengendali Teknis:</strong> {{ $team['pengendali_teknis']['nama'] ?? '-' }} (NIP. {{ $team['pengendali_teknis']['nip'] ?? '-' }})</li>
        <li><strong>Ketua Tim:</strong> {{ $team['ketua_tim']['nama'] ?? '-' }} (NIP. {{ $team['ketua_tim']['nip'] ?? '-' }})</li>
        @if(!empty($team['anggota']))
            <li><strong>Anggota Tim:</strong>
                @foreach($team['anggota'] as $ang)
                    {{ $ang['nama'] }} ({{ $ang['jabatan'] }}){{ ! $loop->last ? ', ' : '' }}
                @endforeach
            </li>
        @endif
    </ul>

    <!-- BAGIAN C: TUJUAN -->
    <div class="section-title">C. Tujuan dan Sasaran Reviu</div>
    <div class="paragraph">
        {{ $sections['tujuan']->isi ?? 'Tujuan reviu adalah memberikan keyakinan terbatas atas kepatuhan proses penyusunan SHSD terhadap ketentuan perundang-undangan dan kewajaran harga yang ditetapkan.' }}
    </div>

    <!-- BAGIAN D: RUANG LINGKUP -->
    <div class="section-title">D. Ruang Lingkup Reviu</div>
    <div class="paragraph">
        {{ $sections['ruang_lingkup']->isi ?? 'Ruang lingkup reviu mencakup pengujian kesesuaian SOP penyusunan, kepatuhan batas tertinggi Standar Harga Satuan Regional, survei harga pasar, pertimbangan kondisi geografis dan indeks kemahalan daerah, serta implementasi pemetaan pada aplikasi SIPD-RI.' }}
    </div>

    <!-- BAGIAN E: OBJEK REVIU -->
    <div class="section-title">E. Objek Reviu</div>
    <div class="paragraph">
        {{ $sections['objek']->isi ?? 'Objek reviu adalah Dokumen Rancangan Standar Harga Satuan Daerah (SHSD) Kabupaten Rembang Tahun Anggaran ' . $lhr->tahun_anggaran . ' yang disusun oleh ' . ($unit->nama_unit ?? 'BPPKAD') . '.' }}
    </div>

    <!-- BAGIAN F: WAKTU PELAKSANAAN -->
    <div class="section-title">F. Waktu Pelaksanaan</div>
    <div class="paragraph">
        {{ $sections['waktu']->isi ?? 'Reviu dilaksanakan mulai tanggal ' . ($lhr->tanggal_mulai ? $lhr->tanggal_mulai->translatedFormat('d F Y') : '-') . ' sampai dengan ' . ($lhr->tanggal_selesai ? $lhr->tanggal_selesai->translatedFormat('d F Y') : '-') . '.' }}
    </div>

    <div class="page-break"></div>

    <!-- BAGIAN G: SIMPULAN DAN REKOMENDASI -->
    <div class="section-title">G. Simpulan dan Rekomendasi Hasil Reviu</div>
    <div class="paragraph-no-indent">
        Berdasarkan rangkaian pengujian yang telah dilakukan, ringkasan simpulan dan rekomendasi disajikan dalam tabel berikut:
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Uraian Pengujian & Simpulan</th>
                <th style="width: 30%;">Catatan Reviu</th>
                <th style="width: 30%;">Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengujians as $idx => $peng)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $peng->judul }}</strong>
                        <div style="margin-top: 4px; font-size: 8.5pt; color: #333;">
                            <em>Status: </em><strong>{{ $peng->status_simpulan->label() }}</strong>
                        </div>
                        <div style="margin-top: 4px; text-align: justify;">
                            {{ $peng->teks_simpulan ?? '-' }}
                        </div>
                    </td>
                    <td>
                        @forelse($peng->catatans as $cat)
                            <div style="margin-bottom: 6px;">
                                @if($cat->referensi_bagian)
                                    <span style="font-size: 8pt; font-weight: bold; background: #eee; padding: 1px 3px;">{{ $cat->referensi_bagian }}</span>
                                @endif
                                <div>{!! $cat->isi !!}</div>
                            </div>
                        @empty
                            <div style="color: #666; font-style: italic;">Tidak ada catatan.</div>
                        @endforelse
                    </td>
                    <td>
                        @php
                            $rekomList = $peng->catatans->pluck('rekomendasis')->flatten();
                        @endphp
                        @forelse($rekomList as $rIdx => $rekom)
                            <div style="margin-bottom: 6px;">
                                <strong>{{ $rIdx + 1 }}.</strong> {!! $rekom->uraian_rekom !!}
                                @if($rekom->batas_waktu)
                                    <div style="font-size: 8pt; color: #555;">(Batas waktu: {{ $rekom->batas_waktu->format('d-m-Y') }})</div>
                                @endif
                            </div>
                        @empty
                            <div style="color: #666; font-style: italic;">Nihil.</div>
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada pengujian yang terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- BAGIAN H: APRESIASI -->
    <div class="section-title">H. Apresiasi</div>
    <div class="paragraph">
        {{ $sections['apresiasi']->isi ?? 'Kami menyampaikan terima kasih dan apresiasi kepada seluruh jajaran ' . ($unit->nama_unit ?? 'OPD') . ' atas koordinasi dan kerja sama yang baik selama pelaksanaan reviu berlangsung.' }}
    </div>

    <!-- BAGIAN I: PENUTUP -->
    <div class="section-title">I. Penutup</div>
    <div class="paragraph">
        {{ $sections['penutup']->isi ?? 'Demikian Laporan Hasil Reviu ini disampaikan untuk dapat dipergunakan dan ditindaklanjuti sebagaimana mestinya.' }}
    </div>

    <!-- TANDA TANGAN -->
    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <div>Rembang, {{ $lhr->tanggal_lhr ? $lhr->tanggal_lhr->translatedFormat('d F Y') : date('d F Y') }}</div>
                    <div style="font-weight: bold; margin-top: 5px;">Inspektur Daerah Kabupaten Rembang</div>
                    
                    <div style="height: 60px; margin-top: 10px;">
                        @if($lhr->isTerbit())
                            <div style="font-size: 8pt; color: #0284c7; font-weight: bold; border: 1px solid #0284c7; padding: 4px; display: inline-block;">
                                [DITANDATANGANI SECARA ELEKTRONIK]<br>
                                {{ $team['penandatangan']['nama'] ?? 'Inspektur' }}<br>
                                Pada: {{ $lhr->ditandatangani_pada ? $lhr->ditandatangani_pada->format('d/m/Y H:i') : '-' }} WIB
                            </div>
                        @else
                            <div style="color: #999; font-style: italic; padding-top: 20px;">[Belum Ditandatangani]</div>
                        @endif
                    </div>

                    <div style="font-weight: bold; text-decoration: underline; margin-top: 10px;">
                        {{ $team['penandatangan']['nama'] ?? 'Inspektur Kabupaten Rembang' }}
                    </div>
                    <div>NIP. {{ $team['penandatangan']['nip'] ?? '-' }}</div>
                </td>
                <td style="width: 50%; text-align: right; padding-left: 20px;">
                    <div class="qr-box" style="text-align: left;">
                        <div style="font-weight: bold; margin-bottom: 4px;">Verifikasi Keaslian Dokumen:</div>
                        <div>Pindai kode atau kunjungi tautan:</div>
                        <div style="color: #2563eb; word-break: break-all;">{{ $verifyUrl }}</div>
                        @if($lhr->dokumen_final_sha256)
                            <div style="font-size: 7pt; margin-top: 4px;">SHA-256: <code>{{ substr($lhr->dokumen_final_sha256, 0, 24) }}...</code></div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
