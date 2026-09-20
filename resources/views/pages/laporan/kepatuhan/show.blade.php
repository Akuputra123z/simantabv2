@extends('layouts.app')

@push('styles')
<style>
    @media print {
        header, aside, nav, .no-print { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        .print-document { border: none !important; shadow: none !important; padding: 0 !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6 pb-12">

    {{-- TOP ACTION BAR (NO PRINT) --}}
    <div class="no-print flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('laporan.kepatuhan') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    &larr; Laporan Kepatuhan OPD
                </a>
                <span class="text-gray-400">/</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">Detail Kepatuhan</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Laporan Kepatuhan: {{ $lhp->unitDiperiksa?->nama_unit ?? $lhp->nama_unit ?? 'OPD' }}</h1>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Web
            </button>

            <a href="{{ route('laporan.kepatuhan.pdf', $lhp->id) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unduh PDF Resmi
            </a>
        </div>
    </div>

    {{-- OFFICIAL DOCUMENT CONTAINER --}}
    <div class="print-document mx-auto max-w-4xl rounded-2xl border border-gray-200 bg-white p-8 sm:p-12 shadow-md dark:border-gray-800 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

        {{-- KOP SURAT --}}
        <div class="border-b-4 border-double border-gray-900 pb-6 mb-8 text-center dark:border-gray-100">
            <div class="flex items-center justify-center gap-5">
                <img src="{{ asset('images/logo/rembang.png') }}"
                     alt="Logo Rembang"
                     class="h-20 w-20 object-contain"
                     onerror="this.style.display='none'">
                <div class="text-center">
                    <div class="text-lg font-bold tracking-wider uppercase text-gray-900 dark:text-white">PEMERINTAH KABUPATEN REMBANG</div>
                    <div class="text-2xl font-black tracking-wide uppercase text-gray-900 dark:text-white">INSPEKTORAT DAERAH</div>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        Jl. Raya Rembang - Lasem Km 1.1, Tireman Barat, Tireman, Rembang 59219<br>
                        Telepon: (0295) 691320 | Pos-el: inspektorat@rembangkab.go.id | Laman: inspektorat.rembangkab.go.id
                    </p>
                </div>
            </div>
        </div>

        {{-- DOCUMENT TITLE & STATUS --}}
        <div class="text-center mb-8">
            <h2 class="text-xl font-extrabold uppercase tracking-wide text-gray-900 dark:text-white">
                LAPORAN KESIMPULAN KEPATUHAN TINDAK LANJUT
            </h2>
            <p class="text-sm font-semibold uppercase text-gray-600 dark:text-gray-400 mt-0.5">
                HASIL PENGAWASAN DAN PEMERIKSAAN
            </p>
            <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-4 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                STATUS: TUNTAS 100% (MEMENUHI KEPATUHAN)
            </div>
        </div>

        {{-- BAGIAN I: IDENTITAS PEMERIKSAAN --}}
        <div class="mb-8">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 border-b border-gray-200 pb-1.5 mb-3 dark:border-gray-700 dark:text-gray-200">
                I. IDENTITAS HASIL PENGAWASAN
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2.5 gap-x-6 text-xs">
                <div class="flex">
                    <span class="w-44 font-semibold text-gray-600 dark:text-gray-400">Objek Pemeriksaan (OPD)</span>
                    <span class="font-bold text-gray-900 dark:text-white">: {{ $lhp->unitDiperiksa?->nama_unit ?? $lhp->nama_unit ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-44 font-semibold text-gray-600 dark:text-gray-400">Nomor LHP</span>
                    <span class="font-mono font-bold text-gray-900 dark:text-white">: {{ $lhp->nomor_lhp }}</span>
                </div>
                <div class="flex">
                    <span class="w-44 font-semibold text-gray-600 dark:text-gray-400">Program Audit / PKPT</span>
                    <span class="text-gray-900 dark:text-white">: {{ $lhp->auditAssignment?->auditProgram?->nama_program ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-44 font-semibold text-gray-600 dark:text-gray-400">Tanggal LHP</span>
                    <span class="text-gray-900 dark:text-white">: {{ $lhp->tanggal_lhp ? \Carbon\Carbon::parse($lhp->tanggal_lhp)->translatedFormat('d F Y') : '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-44 font-semibold text-gray-600 dark:text-gray-400">Tahun Anggaran / Kategori</span>
                    <span class="text-gray-900 dark:text-white">: {{ $lhp->auditAssignment?->auditProgram?->tahun ?? '-' }} / {{ ucfirst($lhp->auditAssignment?->auditProgram?->kategori ?? '-') }}</span>
                </div>
                <div class="flex">
                    <span class="w-44 font-semibold text-gray-600 dark:text-gray-400">Tim Pengawas (Irban)</span>
                    <span class="text-gray-900 dark:text-white">: {{ $lhp->auditAssignment?->auditProgramDetail?->tim ?? 'Inspektorat Daerah' }}</span>
                </div>
            </div>
        </div>

        {{-- BAGIAN II: REKAPITULASI HASIL & PENYELESAIAN --}}
        @php
            $stat = $lhp->statistik;
            $totalTemuan = $stat?->total_temuan ?? $lhp->temuans->count();
            $totalRekom = $stat?->total_rekomendasi ?? $lhp->temuans->sum(fn($t) => $t->recommendations->count());
            $rekomSelesai = $stat?->rekom_selesai ?? $totalRekom;
            $totalKerugian = (float) ($stat?->total_kerugian ?? $lhp->total_kerugian);
            $totalSetor = (float) ($stat?->total_nilai_tl_selesai ?? 0);
            $sisaKerugian = (float) ($stat?->total_sisa_kerugian ?? 0);
        @endphp
        <div class="mb-8">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 border-b border-gray-200 pb-1.5 mb-3 dark:border-gray-700 dark:text-gray-200">
                II. REKAPITULASI PENYELESAIAN TINDAK LANJUT
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-lg border border-gray-200 bg-gray-50/70 p-3 text-center dark:border-gray-800 dark:bg-gray-800/50">
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 block font-medium">Total Temuan</span>
                    <span class="text-lg font-extrabold text-gray-900 dark:text-white">{{ $totalTemuan }} Temuan</span>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50/70 p-3 text-center dark:border-gray-800 dark:bg-gray-800/50">
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 block font-medium">Total Rekomendasi</span>
                    <span class="text-lg font-extrabold text-gray-900 dark:text-white">{{ $totalRekom }} Rekomendasi</span>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50/50 p-3 text-center dark:border-emerald-900/40 dark:bg-emerald-950/20">
                    <span class="text-[11px] text-emerald-700 dark:text-emerald-400 block font-medium">Rekomendasi Selesai</span>
                    <span class="text-lg font-extrabold text-emerald-700 dark:text-emerald-300">{{ $rekomSelesai }} (100%)</span>
                </div>
                <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-3 text-center dark:border-blue-900/40 dark:bg-blue-950/20">
                    <span class="text-[11px] text-blue-700 dark:text-blue-400 block font-medium">Penyetoran TL / Sisa</span>
                    <span class="text-xs font-bold text-blue-800 dark:text-blue-300 block">Rp {{ number_format($totalSetor, 0, ',', '.') }}</span>
                    <span class="text-[10px] text-gray-500 dark:text-gray-400">Sisa: Rp {{ number_format($sisaKerugian, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- BAGIAN III: MATRIKS KESIMPULAN TEMUAN & REKOMENDASI --}}
        <div class="mb-8">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 border-b border-gray-200 pb-1.5 mb-3 dark:border-gray-700 dark:text-gray-200">
                III. MATRIKS RINCIAN TEMUAN, REKOMENDASI & TINDAK LANJUT
            </h3>

            @if($lhp->is_nihil || $lhp->temuans->isEmpty())
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-center text-xs text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
                    <p class="font-bold">LHP Ini Dinyatakan Bebas / Nihil Temuan (Nihil Audit Findings).</p>
                    <p class="mt-1">Tidak terdapat temuan ketidakpatuhan maupun kelemahan sistem pengendalian intern yang perlu ditindaklanjuti.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($lhp->temuans as $tIndex => $temuan)
                        @php
                            $kodeTemuan = $temuan->kodeTemuan;
                            $nilaiTemuan = ($temuan->nilai_kerugian_negara ?? 0) + ($temuan->nilai_kerugian_daerah ?? 0) + ($temuan->nilai_kerugian_desa ?? 0) + ($temuan->nilai_kerugian_bos_blud ?? 0);
                            $tEval = $analisisBerulang['temuans_map'][$temuan->id] ?? null;
                        @endphp
                        <div class="rounded-xl border {{ ($tEval['is_berulang'] ?? false) ? 'border-rose-300 dark:border-rose-900/70 shadow-sm' : 'border-gray-200 dark:border-gray-700' }} overflow-hidden">
                            {{-- Header Temuan --}}
                            <div class="{{ ($tEval['is_berulang'] ?? false) ? 'bg-rose-50/70 border-rose-200 dark:bg-rose-950/30 dark:border-rose-900/70' : 'bg-gray-50 border-gray-200 dark:bg-gray-800/80 dark:border-gray-700' }} px-4 py-3 border-b flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs text-gray-900 dark:text-white">
                                            Temuan {{ $tIndex + 1 }}: {{ $temuan->judul ?? 'Temuan Pemeriksaan' }}
                                        </span>
                                        @if($tEval['is_berulang'] ?? false)
                                            <span class="inline-flex items-center gap-1 rounded bg-rose-600 px-2 py-0.5 text-[10px] font-bold text-white uppercase">
                                                ⚠️ Berulang
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                Kasus Baru
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-0.5 flex items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                                        <span class="font-mono font-semibold text-blue-600 dark:text-blue-400">
                                            [{{ $kodeTemuan?->kode ?? 'Kode -' }}]
                                        </span>
                                        <span>{{ $kodeTemuan?->sub_kelompok ?? $kodeTemuan?->deskripsi ?? '-' }}</span>
                                    </div>
                                </div>
                                @if($nilaiTemuan > 0)
                                    <span class="rounded bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                                        Nilai: Rp {{ number_format($nilaiTemuan, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Isi Temuan --}}
                            <div class="p-4 space-y-3 text-xs">
                                {{-- Kotak Peringatan & Kesimpulan Khusus untuk OPD per Temuan --}}
                                @if($tEval['is_berulang'] ?? false)
                                    <div class="rounded-lg border border-rose-200 bg-rose-50/80 p-3.5 dark:border-rose-900/60 dark:bg-rose-950/40 space-y-2">
                                        <div class="flex items-center gap-2 text-rose-900 dark:text-rose-300 font-bold text-xs">
                                            <svg class="h-4 w-4 shrink-0 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>TEMUAN INI TERBUKTI BERULANG DARI PEMERIKSAAN SEBELUMNYA</span>
                                        </div>
                                        <div class="text-[11px] text-gray-700 dark:text-gray-300 leading-relaxed pl-6">
                                            {{ $tEval['kesimpulan_temuan_opd'] }}
                                        </div>
                                        <div class="rounded bg-white/80 p-2.5 text-[11px] text-rose-950 dark:bg-gray-900/80 dark:text-rose-300 border border-rose-200 dark:border-rose-900/50">
                                            <strong>Instruksi Khusus untuk Kepala OPD:</strong> {{ $tEval['instruksi_khusus_opd'] }}
                                            <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">
                                                Riwayat Terkait: LHP No. {{ implode(', ', $tEval['lhp_lampau']) }} ({{ implode(', ', $tEval['periode_lampau']) }})
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($temuan->kondisi)
                                    <div>
                                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-0.5">Kondisi / Uraian Temuan:</span>
                                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $temuan->kondisi }}</p>
                                    </div>
                                @endif

                                {{-- Daftar Rekomendasi di bawah Temuan --}}
                                <div class="mt-3">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-2">Rekomendasi & Kesimpulan Tindak Lanjut:</span>
                                    <div class="space-y-3">
                                        @foreach($temuan->recommendations as $rIndex => $rekom)
                                            @php
                                                $kodeRekom = $rekom->kodeRekomendasi;
                                                $latestTl = $rekom->tindakLanjuts->last();
                                                $totalCicil = $rekom->tindakLanjuts->flatMap->cicilans->sum('jumlah') ?? 0;
                                                $rEval = $tEval['rekom_map'][$rekom->id] ?? null;
                                            @endphp
                                            <div class="rounded-lg border {{ ($rEval['is_berulang'] ?? false) ? 'border-amber-300 dark:border-amber-900/60' : 'border-gray-200 dark:border-gray-750' }} bg-white p-3 dark:bg-gray-850">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="flex-1">
                                                        <div class="flex flex-wrap items-center gap-2 text-[11px]">
                                                            <span class="font-bold text-gray-900 dark:text-white">
                                                                Rekomendasi {{ $tIndex + 1 }}.{{ $rIndex + 1 }}
                                                            </span>
                                                            <span class="rounded bg-indigo-50 px-1.5 py-0.2 font-mono text-[10px] font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                                Kode: {{ $kodeRekom?->kode ?? '-' }}
                                                            </span>
                                                            <span class="text-gray-500 dark:text-gray-400">{{ $kodeRekom?->deskripsi ?? '' }}</span>
                                                            @if($rEval['is_berulang'] ?? false)
                                                                <span class="rounded bg-amber-100 px-1.5 py-0.2 text-[9.5px] font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                                    ⚠️ Rekomendasi Berulang
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="mt-1 text-gray-800 dark:text-gray-200 font-medium">
                                                            {{ $rekom->deskripsi ?? $rekom->rekomendasi ?? '-' }}
                                                        </p>
                                                    </div>
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 shrink-0">
                                                        ✓ SELESAI (SESUAI)
                                                    </span>
                                                </div>

                                                {{-- Uraian Tindak Lanjut OPD --}}
                                                <div class="mt-2.5 rounded border border-emerald-100 bg-emerald-50/40 p-2.5 text-[11px] dark:border-emerald-950/60 dark:bg-emerald-950/20">
                                                    <div class="font-semibold text-emerald-900 dark:text-emerald-300">Kesimpulan Pelaksanaan Tindak Lanjut:</div>
                                                    <p class="mt-0.5 text-gray-700 dark:text-gray-300 leading-relaxed">
                                                        {{ $latestTl?->uraian_tindak_lanjut ?? $latestTl?->keterangan ?? 'Telah ditindaklanjuti secara penuh sesuai dengan rekomendasi yang diberikan oleh Tim Pemeriksa Inspektorat Daerah.' }}
                                                    </p>
                                                    @if(($rekom->nilai_rekomendasi ?? 0) > 0 || $totalCicil > 0)
                                                        <div class="mt-1.5 flex items-center gap-4 text-[10px] font-medium text-emerald-800 dark:text-emerald-400">
                                                            <span>Nilai Rekomendasi: Rp {{ number_format($rekom->nilai_rekomendasi ?? 0, 0, ',', '.') }}</span>
                                                            <span>Setoran Selesai: Rp {{ number_format($totalCicil > 0 ? $totalCicil : ($rekom->nilai_rekomendasi ?? 0), 0, ',', '.') }}</span>
                                                            <span class="font-bold text-emerald-700 dark:text-emerald-300">Sisa: Rp 0 (Lunas)</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Catatan Khusus untuk OPD jika Rekomendasi Ini Berulang --}}
                                                @if($rEval['is_berulang'] ?? false)
                                                    <div class="mt-2 rounded border border-amber-200 bg-amber-50/70 p-2 text-[10.5px] text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                                                        <strong class="text-amber-800 dark:text-amber-300">Kesimpulan untuk OPD:</strong> {{ $rEval['kesimpulan_rekom_opd'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- BAGIAN IV: ANALISIS RIWAYAT & PENGAWASAN TEMUAN BERULANG --}}
        <div class="mb-8">
            <div class="flex items-center justify-between border-b border-gray-200 pb-1.5 mb-3 dark:border-gray-700">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                    IV. ANALISIS RIWAYAT & DETEKSI TEMUAN BERULANG (RECURRENT AUDIT FINDINGS)
                </h3>
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $analisisBerulang['has_berulang'] ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                    Risiko Pengulangan: {{ $analisisBerulang['tingkat_risiko'] }}
                </span>
            </div>

            @if($analisisBerulang['has_berulang'])
                <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 dark:border-rose-900/50 dark:bg-rose-950/20 text-xs mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="font-bold text-rose-900 dark:text-rose-300">
                                {{ $analisisBerulang['catatan_ringkas'] }}
                            </p>
                            <p class="mt-1 text-rose-700 dark:text-rose-400">
                                Meskipun seluruh tindak lanjut pada LHP ini telah berstatus 100% tuntas, riwayat temuan sejenis mengindikasikan kelemahan pengendalian yang bersifat berulang (*chronic/systemic issue*). OPD diwajibkan melakukan pembenahan tata kelola operasional dan pengawasan melekat.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-100 font-semibold uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="py-2.5 px-3">Kode Temuan</th>
                                <th class="py-2.5 px-3">Uraian Klasifikasi Temuan</th>
                                <th class="py-2.5 px-3 text-center">Frekuensi Lampau</th>
                                <th class="py-2.5 px-3">Nomor LHP Riwayat Sebelumnya</th>
                                <th class="py-2.5 px-3 text-right">Nilai Kerugian Terkait</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 bg-white dark:bg-gray-850">
                            @foreach($analisisBerulang['daftar_berulang'] as $item)
                                <tr>
                                    <td class="py-2.5 px-3 font-mono font-bold text-rose-600 dark:text-rose-400">
                                        {{ $item['kode'] }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $item['deskripsi_kode'] }}</span>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400">{{ $item['kelompok'] }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="rounded bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                            {{ $item['frekuensi_lampau'] }} kali
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-[11px] text-gray-700 dark:text-gray-300">
                                        {{ implode(', ', $item['lhp_lampau']) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold text-gray-900 dark:text-white">
                                        {{ $item['nilai_terkait'] > 0 ? 'Rp ' . number_format($item['nilai_terkait'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20 text-xs flex items-center gap-3">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-emerald-900 dark:text-emerald-300">{{ $analisisBerulang['catatan_ringkas'] }}</p>
                        <p class="mt-0.5 text-emerald-700 dark:text-emerald-400 text-[11px]">Tidak teridentifikasi adanya pengulangan kode temuan pada riwayat LHP sebelumnya untuk OPD ini.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- BAGIAN V: REKOMENDASI MANAJERIAL & RENCANA AKSI PENCEGAHAN --}}
        <div class="mb-8">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 border-b border-gray-200 pb-1.5 mb-3 dark:border-gray-700 dark:text-gray-200">
                V. REKOMENDASI MANAJERIAL & RENCANA AKSI PENCEGAHAN (ACTIONABLE ADVISORY)
            </h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                Untuk memastikan kepatuhan yang berkesinambungan dan mencegah pengulangan temuan pada periode anggaran berikutnya, OPD diinstruksikan melaksanakan langkah-langkah mitigasi berikut:
            </p>
            <div class="space-y-3 text-xs">
                @foreach($masukanManajerial as $mIdx => $masukan)
                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-700 dark:bg-gray-800/60">
                        <div class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[10px] text-white">
                                {{ $mIdx + 1 }}
                            </span>
                            <span>{{ $masukan['kategori'] }}</span>
                        </div>
                        <div class="mt-2 pl-7 space-y-1">
                            <p class="text-gray-600 dark:text-gray-400"><strong class="text-gray-700 dark:text-gray-300">Fokus Kelemahan:</strong> {{ $masukan['poin'] }}</p>
                            <p class="text-gray-900 dark:text-gray-100 leading-relaxed"><strong class="text-blue-700 dark:text-blue-300">Instruksi Aksi Nyata:</strong> {{ $masukan['tindakan'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- BAGIAN VI: PROTOKOL ESKALASI SANKSI JIKA OPD MELAKUKAN PENGULANGAN TEMUAN --}}
        <div class="mb-8">
            <div class="flex items-center justify-between border-b border-gray-200 pb-1.5 mb-3 dark:border-gray-700">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                    VI. PROTOKOL KETENTUAN & ESKALASI SANKSI JIKA TERJADI TEMUAN BERULANG
                </h3>
                <span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    PP 94/2021 & UU 1/2004
                </span>
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                Apabila pada pemeriksaan berkala periode berikutnya OPD kembali melakukan pelanggaran atau kelemahan sejenis (*repeated findings*), Inspektorat Daerah Kabupaten Rembang memberlakukan tindakan eskalasi pengawasan bertahap sebagai berikut:
            </p>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100 font-semibold uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <th class="py-2.5 px-3 w-28">Tingkat</th>
                            <th class="py-2.5 px-3 w-48">Kriteria Pelanggaran</th>
                            <th class="py-2.5 px-3">Tindakan Konsekuensi / Sanksi Pengawasan</th>
                            <th class="py-2.5 px-3 w-44">Dasar Yuridis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 bg-white dark:bg-gray-850">
                        @foreach($eskalasiSanksi as $sanksi)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                <td class="py-3 px-3">
                                    <span class="font-bold text-gray-900 dark:text-white block">{{ $sanksi['tingkat'] }}</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $sanksi['label'] }}</span>
                                </td>
                                <td class="py-3 px-3 text-gray-700 dark:text-gray-300">
                                    {{ $sanksi['syarat'] }}
                                </td>
                                <td class="py-3 px-3 font-medium text-gray-900 dark:text-gray-100 leading-relaxed">
                                    {{ $sanksi['tindakan'] }}
                                </td>
                                <td class="py-3 px-3 text-[10px] text-gray-500 dark:text-gray-400 italic">
                                    {{ $sanksi['dasar_hukum'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            {{-- TABEL AMBANG BATAS PENGULANGAN TEMUAN --}}
            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                <h4 class="font-bold text-xs uppercase tracking-wider text-gray-900 dark:text-white mb-2">
                    Matriks Ambang Batas & Sanksi Berdasarkan Jumlah Temuan Berulang
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[11px]">
                        <thead class="bg-gray-200/70 font-semibold uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="py-2 px-3">Jumlah Temuan Berulang</th>
                                <th class="py-2 px-3">Klasifikasi Risiko</th>
                                <th class="py-2 px-3">Konsekuensi Penegakan Sanksi bagi OPD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-850">
                            <tr class="{{ ($analisisBerulang['rentang_keparahan'] ?? '') === '1_sampai_2' ? 'bg-amber-50 font-medium dark:bg-amber-950/30' : '' }}">
                                <td class="py-2 px-3 font-bold text-amber-700 dark:text-amber-400">1 – 2 Temuan</td>
                                <td class="py-2 px-3">Pengulangan Ringan (Prosedural)</td>
                                <td class="py-2 px-3">Peringatan pengawasan internal, perbaikan SOP verifikasi dokumen, dan peningkatan supervisi atasan langsung.</td>
                            </tr>
                            <tr class="{{ ($analisisBerulang['rentang_keparahan'] ?? '') === '3_sampai_5' ? 'bg-rose-50 font-medium dark:bg-rose-950/40 ring-1 ring-rose-300' : '' }}">
                                <td class="py-2 px-3 font-bold text-rose-700 dark:text-rose-400">3 – 5 Temuan</td>
                                <td class="py-2 px-3 font-semibold text-rose-800 dark:text-rose-300">Kelemahan Sistemik (Kegagalan SPIP)</td>
                                <td class="py-2 px-3 leading-relaxed">
                                    <strong>Surat Peringatan Keras Bupati</strong>, pemotongan bobot nilai SAKIP OPD (5–10 poin), <strong>pemotongan TPP 10%–20%</strong> bagi pejabat pengelola anggaran terkait selama 3 bulan, serta penetapan status <em>Special Watchlist PKPT</em>.
                                </td>
                            </tr>
                            <tr class="{{ ($analisisBerulang['rentang_keparahan'] ?? '') === '6_sampai_10' ? 'bg-red-50 font-medium dark:bg-red-950/50 ring-1 ring-red-400' : '' }}">
                                <td class="py-2 px-3 font-bold text-red-700 dark:text-red-400">6 – 10 Temuan</td>
                                <td class="py-2 px-3 font-bold text-red-800 dark:text-red-300">Maladministrasi Kronis (Pembiaran Berat)</td>
                                <td class="py-2 px-3 leading-relaxed">
                                    <strong>Rekomendasi Pencopotan/Bebas Tugas PPK/PPTK/Bendahara</strong>, evaluasi kelayakan jabatan Kepala OPD oleh Bupati, <strong>Audit Investigasi Forensik APIP</strong>, penangguhan tambahan pagu APBD, dan pelimpahan ke <strong>Majelis TP-TGR / APH</strong> jika terdapat kerugian negara.
                                </td>
                            </tr>
                            <tr class="{{ ($analisisBerulang['rentang_keparahan'] ?? '') === 'diatas_10' ? 'bg-purple-50 font-medium dark:bg-purple-950/50 ring-1 ring-purple-400' : '' }}">
                                <td class="py-2 px-3 font-bold text-purple-700 dark:text-purple-400">&gt; 10 Temuan</td>
                                <td class="py-2 px-3 font-bold text-purple-800 dark:text-purple-300">Darurat Akuntabilitas (Kolaps Tata Kelola)</td>
                                <td class="py-2 px-3 leading-relaxed">
                                    Pembekuan otorisasi belanja mandiri, penempatan Tim Asistensi Melekat APIP secara penuh, dan perombakan total manajemen keuangan OPD.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- BAGIAN VII: KESIMPULAN & RAPOR EVALUASI PERILAKU KEPATUHAN OPD --}}
        @php
            $kesimpulanOpd = $analisisBerulang['kesimpulan_opd'] ?? [];
            $tipeOpd = $kesimpulanOpd['tipe'] ?? '';
            $isBerulangOpd = in_array($tipeOpd, ['berulang', 'berulang_signifikan', 'berulang_kritis', 'berulang_darurat']);
            $cardBorder = match($tipeOpd) {
                'berulang_darurat' => 'border-purple-600 bg-purple-50/50 dark:border-purple-900 dark:bg-purple-950/30',
                'berulang_kritis' => 'border-red-500 bg-red-50/50 dark:border-red-900/80 dark:bg-red-950/30',
                'berulang_signifikan' => 'border-rose-400 bg-rose-50/50 dark:border-rose-900/70 dark:bg-rose-950/25',
                'berulang' => 'border-amber-300 bg-amber-50/50 dark:border-amber-900/60 dark:bg-amber-950/20',
                default => 'border-blue-200 bg-blue-50/40 dark:border-blue-900/50 dark:bg-blue-950/20',
            };
            $badgeColor = match($tipeOpd) {
                'berulang_darurat' => 'bg-purple-700 text-white',
                'berulang_kritis' => 'bg-red-700 text-white',
                'berulang_signifikan' => 'bg-rose-600 text-white',
                'berulang' => 'bg-amber-200/90 text-amber-900 dark:bg-amber-900/60 dark:text-amber-200',
                default => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            };
        @endphp
        <div class="mb-10 rounded-2xl border {{ $cardBorder }} p-6 text-xs text-gray-800 dark:text-gray-200 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-200/70 dark:border-gray-700 pb-3 mb-4">
                <h4 class="font-extrabold uppercase tracking-wider text-gray-900 dark:text-white text-sm">
                    VII. KESIMPULAN & RAPOR EVALUASI PERILAKU KEPATUHAN OPD
                </h4>
                <span class="inline-flex items-center gap-1.5 rounded-full {{ $badgeColor }} px-3 py-1 text-xs font-bold">
                    {{ $kesimpulanOpd['status'] ?? 'Kepatuhan 100%' }}
                </span>
            </div>

            <div class="space-y-3 leading-relaxed">
                <div>
                    <span class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-[11px] block mb-1">
                        1. Ringkasan Evaluasi Kepatuhan & Tren Pengulangan:
                    </span>
                    <p class="text-gray-700 dark:text-gray-300">
                        {{ $kesimpulanOpd['ringkasan'] ?? 'Tindak lanjut telah diselesaikan 100%.' }}
                    </p>
                </div>

                <div class="rounded-xl bg-white/85 border border-gray-200 dark:bg-gray-900/80 dark:border-gray-700 p-4">
                    <span class="font-bold {{ $isBerulangOpd ? 'text-rose-700 dark:text-rose-400' : 'text-blue-700 dark:text-blue-300' }} uppercase tracking-wider text-[11px] block mb-1">
                        2. Catatan Pembinaan & Penegasan Konsekuensi Hukum untuk Kepala OPD:
                    </span>
                    <div class="text-gray-800 dark:text-gray-200 font-medium leading-relaxed whitespace-pre-line">
                        {{ $kesimpulanOpd['penegasan'] ?? 'Pertahankan kepatuhan dan integritas pengelolaan anggaran.' }}
                    </div>
                </div>

                <div class="text-[11px] text-gray-600 dark:text-gray-400 pt-1">
                    Pernyataan kesimpulan kepatuhan ini diterbitkan secara resmi sebagai bahan evaluasi Akuntabilitas Kinerja Instansi Pemerintah (AKIP) dan pertimbangan kebijakan pengawasan Inspektorat Daerah Kabupaten Rembang.
                </div>
            </div>
        </div>

        {{-- BAGIAN V: PENGESAHAN / TANDA TANGAN --}}
        <div class="mt-10 pt-4 text-xs">
            <div class="grid grid-cols-2 gap-8">
                <div class="text-center">
                    <p class="text-gray-500 dark:text-gray-400">Mengetahui / Mengesahkan,</p>
                    <p class="font-bold text-gray-900 dark:text-white uppercase mt-0.5">Inspektur Daerah Kab. Rembang</p>
                    <div class="h-20"></div>
                    <p class="font-bold uppercase underline text-gray-900 dark:text-white">IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Pembina Utama Muda</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">19920401202521104733</p>
                </div>

                <div class="text-center">
                    <p class="text-gray-500 dark:text-gray-400">Rembang, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="font-bold text-gray-900 dark:text-white uppercase mt-0.5">
                        {{ $lhp->auditAssignment?->auditProgramDetail?->tim ? 'Inspektur Pembantu ' . $lhp->auditAssignment->auditProgramDetail->tim : 'Pengendali Mutu / Irban' }}
                    </p>
                    <div class="h-20"></div>
                    <p class="font-bold uppercase underline text-gray-900 dark:text-white">
                        {{ $lhp->auditAssignment?->ketuaTim?->name ?? 'Tim Pemeriksa Inspektorat' }}
                    </p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Ketua Tim Pengawas</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                        NIP. {{ $lhp->auditAssignment?->ketuaTim?->nip ?? '..................................' }}
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
