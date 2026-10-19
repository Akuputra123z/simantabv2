@extends('layouts.app')

@section('content')
@php
    $statistik = $lhp->statistik;
    $totalTemuan = $lhp->temuans->count();
    $totalRekomendasi = $lhp->temuans->sum(fn($t) => $t->recommendations->count());
    $totalNilaiTemuan = (float)($statistik?->total_kerugian ?? $lhp->temuans->sum('nilai_temuan'));
    $totalNilaiRekomendasi = (float)$lhp->temuans->sum(fn($t) => $t->recommendations->sum('nilai_rekom'));
    $totalTlSelesai = (float)($statistik?->total_nilai_tl_selesai ?? 0);
    $totalSisaKerugian = (float)($statistik?->total_sisa_kerugian ?? max(0, $totalNilaiTemuan - $totalTlSelesai));

    $rekomSelesai = (int)($statistik?->rekom_selesai ?? 0);
    $rekomProses = (int)($statistik?->rekom_proses ?? 0);
    $rekomBelum = (int)($statistik?->rekom_belum ?? max(0, $totalRekomendasi - $rekomSelesai - $rekomProses));

    $persenSelesai = $totalRekomendasi > 0 
        ? (float)($statistik?->persen_selesai_gabungan ?? round(($rekomSelesai / $totalRekomendasi) * 100, 1))
        : ($lhp->is_nihil ? 100 : 0);

    $assignment = $lhp->auditAssignment;
    $program = $assignment?->auditProgramDetail?->auditProgram;
    $units = $lhp->unitDiperiksas->isNotEmpty() 
        ? $lhp->unitDiperiksas 
        : ($lhp->unitDiperiksa ? collect([$lhp->unitDiperiksa]) : collect());
    $kategori = $program?->kategori ?: 'PKPT';
@endphp

<div class="w-full space-y-6" x-data="{ activeTab: 'all', expandAll: true }">

    {{-- ═════════════════════════════════════════════════════════════ --}}
    {{-- ── 1. PAGE BREADCRUMB & EXECUTIVE HEADER ── --}}
    {{-- ═════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('lhps.index') }}" class="font-medium hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    Daftar LHP
                </a>
                <span>/</span>
                <span class="rounded bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 font-mono text-[11px] text-gray-700 dark:text-gray-300">
                    {{ $kategori }}
                </span>
                <span>/</span>
                <span class="text-gray-900 dark:text-white font-semibold truncate max-w-[200px]">
                    {{ $lhp->nomor_lhp }}
                </span>
            </nav>

            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Laporan Hasil Pemeriksaan
                </h1>

                {{-- Status Document Badge --}}
                @if($lhp->is_nihil)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 shadow-xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Bebas Temuan (Nihil)
                    </span>
                @elseif(($lhp->status ?? 'published') === 'published' || ($lhp->status ?? '') === 'final')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 shadow-xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                        Dokumen Terbit
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300 shadow-xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Draft
                    </span>
                @endif
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">{{ $lhp->nomor_lhp }}</span>
                <span>•</span>
                <span>Terbit: <strong class="text-gray-700 dark:text-gray-300">{{ optional($lhp->tanggal_lhp)->translatedFormat('d F Y') ?? '-' }}</strong></span>
                <span>•</span>
                <span>Kategori: <strong class="text-blue-600 dark:text-blue-400">{{ $kategori }}</strong></span>
            </div>
        </div>

        {{-- Action Buttons Toolbar --}}
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('lhps.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>

            <a href="{{ route('lhps.edit', $lhp) }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50/70 px-4 py-2 text-xs font-bold text-blue-700 shadow-xs hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Dokumen
            </a>

            @if(Route::has('tindak-lanjuts.lhp') && !$lhp->is_nihil && $totalTemuan > 0)
                <a href="{{ route('tindak-lanjuts.lhp', $lhp) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-2 text-xs font-bold text-emerald-700 shadow-xs hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Tindak Lanjut
                </a>
            @endif

            {{-- Dropdown Cetak / Unduh --}}
            <div class="relative" x-data="{ openPrint: false }">
                <button type="button" @click="openPrint = !openPrint" @click.outside="openPrint = false"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition active:scale-95 cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak / Ekspor
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="openPrint" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     class="absolute right-0 z-20 mt-1.5 w-48 rounded-xl border border-gray-100 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900">
                    @if(Route::has('laporan.preview-pdf-per-lhp'))
                        <button type="button"
                           @click="openPrint = false; window.requestSecurePdf('lhp_preview', '{{ $lhp->uuid }}', 'LHP: {{ addslashes($lhp->nomor_lhp) }}')"
                           class="w-full flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition text-left cursor-pointer">
                            <span class="text-rose-500 font-bold text-xs">PDF</span>
                            <span>Pratinjau PDF</span>
                        </button>
                    @endif
                    @if(Route::has('laporan.download-pdf-per-lhp'))
                        <button type="button"
                           @click="openPrint = false; window.requestSecurePdf('lhp_download', '{{ $lhp->uuid }}', 'LHP: {{ addslashes($lhp->nomor_lhp) }}')"
                           class="w-full flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition text-left cursor-pointer">
                            <span class="text-rose-500 font-bold text-xs">⬇</span>
                            <span>Unduh Dokumen PDF</span>
                        </button>
                    @endif
                    @if(Route::has('laporan.download-excel-per-lhp'))
                        <a href="{{ route('laporan.download-excel-per-lhp', $lhp) }}"
                           class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            <span class="text-emerald-600 font-bold text-xs">XLS</span>
                            <span>Unduh Data Excel</span>
                        </a>
                    @endif
                    <button type="button" onclick="window.print()"
                            class="w-full flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition text-left cursor-pointer">
                        <span class="text-blue-500 font-bold text-xs">🖨</span>
                        <span>Cetak Halaman (Print)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════ --}}
    {{-- ── 2. METADATA PENUGASAN & OBJEK AUDIT BANNER ── --}}
    {{-- ═════════════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="grid grid-cols-1 divide-y divide-gray-100 sm:grid-cols-2 lg:grid-cols-4 sm:divide-y-0 sm:divide-x dark:divide-gray-800">
            {{-- Program Kerja --}}
            <div class="p-4 sm:p-5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Program Kerja</span>
                <p class="mt-1 text-xs sm:text-sm font-bold text-gray-900 dark:text-white line-clamp-2">
                    {{ $program?->nama_program ?? '-' }}
                </p>
                <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                    Tahun Anggaran {{ $program?->tahun ?? date('Y') }}
                </p>
            </div>

            {{-- Penugasan / Surat Tugas --}}
            <div class="p-4 sm:p-5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Surat Tugas / Penugasan</span>
                <p class="mt-1 text-xs sm:text-sm font-bold text-gray-900 dark:text-white line-clamp-2 font-mono">
                    {{ $assignment?->nomor_surat ?? '-' }}
                </p>
                <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400 truncate">
                    {{ $assignment?->auditProgramDetail?->nama_detail_program ?? '-' }}
                </p>
            </div>

            {{-- Objek Audit / Unit Kerja --}}
            <div class="p-4 sm:p-5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Unit Kerja / Objek Audit</span>
                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                    @forelse($units as $u)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80 dark:bg-blue-950/40 dark:border-blue-900/60 dark:text-blue-300">
                            {{ $u->nama_unit ?? $u->nama ?? ('Unit #' . $u->id) }}
                        </span>
                    @empty
                        <span class="text-xs text-gray-400 italic">Belum ditentukan</span>
                    @endforelse
                </div>
            </div>

            {{-- Administrasi Penerbitan --}}
            <div class="p-4 sm:p-5 bg-gray-50/60 dark:bg-gray-800/30">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Pencatatan &amp; Pembuat</span>
                <p class="mt-1 text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $lhp->creator?->name ?? 'Administrator' }}
                </p>
                <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                    Input: {{ optional($lhp->created_at)->translatedFormat('d M Y, H:i') }}
                </p>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════ --}}
    {{-- ── 3. STATE BANNER / KPI HIGHLIGHT CARDS ── --}}
    {{-- ═════════════════════════════════════════════════════════════ --}}
    @if($lhp->is_nihil)
        {{-- Banner Elegan Bebas Temuan --}}
        <div class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent p-6 dark:border-emerald-900/50 dark:from-emerald-950/40">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-md shadow-emerald-600/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-emerald-950 dark:text-emerald-100">
                            Pemeriksaan Dinyatakan Bebas Temuan (Nihil)
                        </h2>
                        <p class="mt-1 text-xs text-emerald-800 dark:text-emerald-300/90 leading-relaxed max-w-2xl">
                            Laporan Hasil Pemeriksaan ini selesai tanpa mencatat temuan kerugian, ketidakpatuhan, maupun kelemahan SPI. Seluruh kewajiban audit dinyatakan tuntas dengan progres tindak lanjut 100%.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0 sm:border-l sm:border-emerald-200 sm:pl-6 dark:border-emerald-800/60">
                    <div class="text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Progres TL</span>
                        <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">100%</p>
                        <span class="text-[10px] font-semibold text-emerald-700/80 dark:text-emerald-400/80">Tuntas &amp; Bersih</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- 4-Card Executive KPI Financial & Resolution Strip --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Card 1: Temuan & Rekomendasi --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Temuan &amp; Rekomendasi</span>
                    <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">Hasil Audit</span>
                </div>
                <div class="mt-2.5 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $totalTemuan }}</span>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Temuan</span>
                    <span class="text-gray-300 dark:text-gray-700">•</span>
                    <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $totalRekomendasi }}</span>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Rekom</span>
                </div>
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    Rekomendasi wajib ditindaklanjuti objek audit.
                </p>
            </div>

            {{-- Card 2: Total Nilai Temuan (Plafon Kerugian) --}}
            <div class="rounded-2xl border border-rose-200/80 bg-rose-50/30 p-5 shadow-xs dark:border-rose-900/40 dark:bg-rose-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Nilai Temuan Kerugian</span>
                    <span class="rounded-lg bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-950/80 dark:text-rose-300">Plafon</span>
                </div>
                <p class="mt-2 text-xl sm:text-2xl font-extrabold text-rose-600 dark:text-rose-400 tracking-tight">
                    Rp {{ number_format($totalNilaiTemuan, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-[11px] text-rose-700/80 dark:text-rose-400/80">
                    Nilai rekomendasi: Rp {{ number_format($totalNilaiRekomendasi, 0, ',', '.') }}
                </p>
            </div>

            {{-- Card 3: Realisasi Tindak Lanjut Finansial --}}
            <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/30 p-5 shadow-xs dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Penyelesaian (Setor/TL)</span>
                    <span class="rounded-lg bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">Realisasi</span>
                </div>
                <p class="mt-2 text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight">
                    Rp {{ number_format($totalTlSelesai, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-[11px] text-emerald-800/80 dark:text-emerald-400/80">
                    Sisa kerugian: <strong class="text-rose-600 dark:text-rose-400">Rp {{ number_format($totalSisaKerugian, 0, ',', '.') }}</strong>
                </p>
            </div>

            {{-- Card 4: Progres Penyelesaian TL --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Status Progres TL</span>
                        <span class="text-xs font-extrabold text-blue-600 dark:text-blue-400">{{ $persenSelesai }}%</span>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-full rounded-full transition-all duration-500 {{ $persenSelesai >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}"
                             style="width: {{ min(100, max(0, $persenSelesai)) }}%"></div>
                    </div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 pt-1">
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">✓ {{ $rekomSelesai }} Selesai</span>
                    <span class="text-amber-600 dark:text-amber-400 font-bold">⏳ {{ $rekomProses }} Proses</span>
                    <span class="text-gray-400 font-medium">• {{ $rekomBelum }} Belum</span>
                </div>
            </div>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════ --}}
    {{-- ── 4. TWO-COLUMN MAIN WORKSPACE ── --}}
    {{-- ═════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">

        {{-- LEFT COLUMN: DETAIL TEMUAN & REKOMENDASI (COL 8) --}}
        <div class="space-y-6 lg:col-span-8">

            {{-- Ringkasan Eksekutif / Catatan Umum --}}
            @if($lhp->catatan_umum)
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-3">
                    <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 text-xs font-bold">📝</span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                            Catatan Umum &amp; Ringkasan LHP
                        </h3>
                    </div>
                    <div class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {!! nl2br(e($lhp->catatan_umum)) !!}
                    </div>
                </div>
            @endif

            {{-- Daftar Temuan Section --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-600 text-white text-xs font-bold">
                            {{ $totalTemuan }}
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                Temuan Pemeriksaan &amp; Rekomendasi
                            </h2>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Klik judul temuan untuk membuka atau menutup rinciannya.
                            </p>
                        </div>
                    </div>

                    @if(!$lhp->is_nihil && $totalTemuan > 0)
                        <div class="flex items-center gap-2">
                            <button type="button" @click="expandAll = !expandAll"
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition cursor-pointer">
                                <span x-text="expandAll ? 'Ciutkan Semua' : 'Buka Semua'"></span>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- State Jika Nihil --}}
                @if($lhp->is_nihil)
                    <div class="p-12 text-center space-y-3">
                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Temuan Pemeriksaan</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                            Laporan Hasil Pemeriksaan ini berstatus <strong>Nihil / Bebas Temuan</strong>. Tidak terdapat daftar ketidakpatuhan maupun rekomendasi tindak lanjut.
                        </p>
                    </div>
                @elseif($totalTemuan === 0)
                    <div class="p-12 text-center space-y-2">
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Belum ada temuan yang diinputkan pada LHP ini.</p>
                        <a href="{{ route('lhps.edit', $lhp) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline">
                            + Tambah Temuan di Halaman Edit
                        </a>
                    </div>
                @else
                    {{-- Loop Temuan --}}
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($lhp->temuans as $idx => $t)
                            <div x-data="{ open: true }" x-init="$watch('expandAll', val => open = val)" class="transition-colors">
                                {{-- Temuan Accordion Header --}}
                                <div @click="open = !open"
                                     class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-5 sm:px-6 cursor-pointer select-none hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition">
                                    <div class="flex items-start gap-3.5 min-w-0">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-xs font-extrabold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            #{{ $idx + 1 }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="rounded bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 px-2 py-0.5 font-mono text-[11px] font-bold">
                                                    {{ $t->kodeTemuan?->kode ?? 'TEMUAN' }}
                                                </span>
                                                <span class="rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 px-2 py-0.5 text-[10px] font-semibold">
                                                    {{ $t->recommendations->count() }} Rekomendasi
                                                </span>
                                            </div>
                                            <h3 class="mt-1 text-sm font-bold text-gray-900 dark:text-white leading-snug">
                                                {{ $t->kodeTemuan?->deskripsi ?? $t->kodeTemuan?->nama ?? 'Temuan Hasil Audit' }}
                                            </h3>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 self-end sm:self-center shrink-0">
                                        @if(($t->nilai_temuan ?? 0) > 0)
                                            <div class="text-right">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500">Nilai Temuan</span>
                                                <p class="text-xs sm:text-sm font-bold text-rose-600 dark:text-rose-400">
                                                    Rp {{ number_format($t->nilai_temuan, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        @endif
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 transition-transform duration-200"
                                             :class="open ? 'rotate-180' : ''">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                {{-- Temuan Accordion Body --}}
                                <div x-show="open" x-cloak x-collapse class="border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/30 dark:bg-gray-900/40 p-5 sm:p-6 space-y-5">
                                    {{-- 1. Kondisi --}}
                                    <div class="rounded-xl border border-gray-200/80 bg-white p-4 shadow-2xs dark:border-gray-800 dark:bg-gray-800/50 space-y-1.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Uraian Kondisi Fakta:</span>
                                        <div class="text-xs sm:text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line">
                                            {!! nl2br(e($t->kondisi ?: '-')) !!}
                                        </div>
                                    </div>

                                    {{-- 2. Sebab & Akibat Grid --}}
                                    @if($t->sebab || $t->akibat)
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                            @if($t->sebab)
                                                <div class="rounded-xl border border-amber-200/60 bg-amber-50/40 p-3.5 text-xs dark:border-amber-900/40 dark:bg-amber-950/20 space-y-1">
                                                    <span class="font-bold uppercase text-[10px] tracking-wider text-amber-800 dark:text-amber-300">🔍 Penyebab (Sebab):</span>
                                                    <p class="text-amber-900 dark:text-amber-200 leading-relaxed whitespace-pre-line">
                                                        {!! nl2br(e($t->sebab)) !!}
                                                    </p>
                                                </div>
                                            @endif
                                            @if($t->akibat)
                                                <div class="rounded-xl border border-rose-200/60 bg-rose-50/40 p-3.5 text-xs dark:border-rose-900/40 dark:bg-rose-950/20 space-y-1">
                                                    <span class="font-bold uppercase text-[10px] tracking-wider text-rose-800 dark:text-rose-300">⚠️ Dampak (Akibat):</span>
                                                    <p class="text-rose-900 dark:text-rose-200 leading-relaxed whitespace-pre-line">
                                                        {!! nl2br(e($t->akibat)) !!}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- 3. Daftar Rekomendasi di bawah Temuan --}}
                                    <div class="space-y-3 pt-2">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                                <span>📋 Rekomendasi Tindak Lanjut</span>
                                                <span class="text-gray-400">({{ $t->recommendations->count() }})</span>
                                            </h4>
                                        </div>

                                        <div class="space-y-2.5">
                                            @forelse($t->recommendations as $rIdx => $r)
                                                @php
                                                    $rStatus = $r->status ?? 'belum';
                                                @endphp
                                                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-2xs dark:border-gray-800 dark:bg-gray-800/80 space-y-3">
                                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2.5">
                                                        <div class="flex items-start gap-2.5">
                                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 text-[11px] font-bold">
                                                                {{ $rIdx + 1 }}
                                                            </span>
                                                            <div>
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <span class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400">
                                                                        {{ $r->kodeRekomendasi?->kode ?? 'REKOM' }}
                                                                    </span>
                                                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                                        {{ $r->jenis_rekomendasi ?: 'Administrasi' }}
                                                                    </span>
                                                                </div>
                                                                <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                                    {{ $r->kodeRekomendasi?->deskripsi ?? 'Rekomendasi Pemeriksaan' }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {{-- Badge Status TL Rekomendasi --}}
                                                        <div class="self-end sm:self-start">
                                                            @if($rStatus === 'selesai')
                                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                                    ✓ Selesai TL
                                                                </span>
                                                            @elseif($rStatus === 'proses')
                                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                                    ⏳ Dalam Proses
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-[10px] font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                                    • Belum TL
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Uraian Rekomendasi --}}
                                                    <div class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed pl-8 whitespace-pre-line">
                                                        {!! nl2br(e($r->uraian_rekom ?: '-')) !!}
                                                    </div>

                                                    {{-- Footer Rekomendasi (Nilai & Batas Waktu) --}}
                                                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 dark:border-gray-700/60 pt-2.5 pl-8 text-xs text-gray-500 dark:text-gray-400">
                                                        <div class="flex items-center gap-1.5">
                                                            <span>Batas Waktu:</span>
                                                            <strong class="text-gray-700 dark:text-gray-300">
                                                                {{ optional($r->batas_waktu)->translatedFormat('d F Y') ?? '-' }}
                                                            </strong>
                                                        </div>
                                                        @if(($r->nilai_rekom ?? 0) > 0)
                                                            <div class="flex items-center gap-1.5">
                                                                <span>Nilai Rekomendasi:</span>
                                                                <strong class="text-blue-600 dark:text-blue-400 font-bold">
                                                                    Rp {{ number_format($r->nilai_rekom, 0, ',', '.') }}
                                                                </strong>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-xs text-gray-400 italic">Belum ada rekomendasi pada temuan ini.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: REKAP PROGRESS, LAMPIRAN & METADATA (COL 4) --}}
        <aside class="space-y-6 lg:col-span-4">

            {{-- 1. Card Status Tindak Lanjut Terpadu --}}
            @if(!$lhp->is_nihil)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Progres Tindak Lanjut
                        </h3>
                        <span class="rounded bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 px-2 py-0.5 text-[10px] font-bold">
                            {{ $persenSelesai }}% Tuntas
                        </span>
                    </div>

                    {{-- Multi-color segmented progress bar --}}
                    <div>
                        <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            @php
                                $totalR = max(1, $totalRekomendasi);
                                $wSelesai = round(($rekomSelesai / $totalR) * 100);
                                $wProses = round(($rekomProses / $totalR) * 100);
                                $wBelum = max(0, 100 - $wSelesai - $wProses);
                            @endphp
                            <div style="width: {{ $wSelesai }}%" class="bg-emerald-500 transition-all"></div>
                            <div style="width: {{ $wProses }}%" class="bg-amber-400 transition-all"></div>
                            <div style="width: {{ $wBelum }}%" class="bg-gray-300 dark:bg-gray-700 transition-all"></div>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="rounded-lg bg-emerald-50/80 p-2 dark:bg-emerald-950/30">
                                <span class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400">Selesai</span>
                                <p class="text-sm font-extrabold text-emerald-800 dark:text-emerald-300">{{ $rekomSelesai }}</p>
                            </div>
                            <div class="rounded-lg bg-amber-50/80 p-2 dark:bg-amber-950/30">
                                <span class="text-[10px] font-bold uppercase text-amber-700 dark:text-amber-400">Proses</span>
                                <p class="text-sm font-extrabold text-amber-800 dark:text-amber-300">{{ $rekomProses }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-2 dark:bg-gray-800/50">
                                <span class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Belum</span>
                                <p class="text-sm font-extrabold text-gray-700 dark:text-gray-300">{{ $rekomBelum }}</p>
                            </div>
                        </div>
                    </div>

                    @if(Route::has('tindak-lanjuts.lhp'))
                        <a href="{{ route('tindak-lanjuts.lhp', $lhp) }}"
                           class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-blue-50 py-2.5 text-xs font-bold text-blue-700 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition">
                            Kelola Tindak Lanjut Lengkap →
                        </a>
                    @endif
                </div>
            @endif

            {{-- 2. Card Berkas Lampiran LHP --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                Berkas Lampiran LHP
                            </h3>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500">Dokumen pendukung &amp; bukti fisik</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $lhp->attachments && $lhp->attachments->isNotEmpty() ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                        {{ $lhp->attachments ? $lhp->attachments->count() : 0 }} Berkas
                    </span>
                </div>

                @if($lhp->attachments && $lhp->attachments->isNotEmpty())
                    <div class="space-y-2.5">
                        @foreach($lhp->attachments as $att)
                            @php
                                $ext = strtoupper(pathinfo($att->file_name ?: $att->file_path, PATHINFO_EXTENSION) ?: 'FILE');
                                $isPdf = $att->isPdf();
                                $isImg = $att->isImage();
                            @endphp
                            <div class="group rounded-xl border border-gray-100 bg-gray-50/70 p-3 hover:bg-white hover:border-blue-200 hover:shadow-xs dark:border-gray-800 dark:bg-gray-800/40 dark:hover:bg-gray-800 transition-all space-y-2">
                                <div class="flex items-start justify-between gap-2.5">
                                    <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                        {{-- File Type Icon / Image Thumbnail --}}
                                        @if($isImg && $att->id)
                                            <a href="{{ route('attachments.show', $att->id) }}" target="_blank"
                                               class="relative h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 hover:opacity-90 transition block">
                                                <img src="{{ route('attachments.show', $att->id) }}" alt="{{ $att->file_name }}"
                                                     class="h-full w-full object-cover">
                                            </a>
                                        @else
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-extrabold text-[10px] uppercase shadow-2xs {{ $isPdf ? 'bg-rose-50 text-rose-600 border border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900/40' : 'bg-blue-50 text-blue-600 border border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-900/40' }}">
                                                {{ $isPdf ? 'PDF' : $ext }}
                                            </div>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gray-900 dark:text-white truncate" title="{{ $att->file_name }}">
                                                {{ $att->file_name ?: 'Lampiran LHP' }}
                                            </p>
                                            @if($att->keterangan)
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1 mt-0.5" title="{{ $att->keterangan }}">
                                                    {{ $att->keterangan }}
                                                </p>
                                            @endif
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1 text-[10px] text-gray-400 dark:text-gray-500">
                                                <span class="font-mono">{{ $att->file_size_formatted }}</span>
                                                <span>&bull;</span>
                                                <span>{{ $ext }}</span>
                                                @if($att->created_at)
                                                    <span>&bull;</span>
                                                    <span>{{ $att->created_at->translatedFormat('d M Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center justify-end gap-1.5 pt-1.5 border-t border-gray-100 dark:border-gray-800/80">
                                    @if(Route::has('attachments.show'))
                                        <a href="{{ route('attachments.show', $att->id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-[11px] font-semibold text-gray-700 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/50 shadow-2xs dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-blue-400 transition"
                                           title="Buka Berkas di Tab Baru">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Buka</span>
                                        </a>
                                    @endif
                                    @if(Route::has('attachments.download'))
                                        <a href="{{ route('attachments.download', $att->id) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-[11px] font-semibold text-gray-700 hover:text-emerald-600 hover:border-emerald-200 hover:bg-emerald-50/50 shadow-2xs dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-emerald-400 transition"
                                           title="Unduh Berkas ke Perangkat">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span>Unduh</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-6 text-center dark:border-gray-800 dark:bg-gray-800/30">
                        <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Belum Ada Berkas Lampiran</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Tidak ada berkas pendukung yang disertakan pada laporan ini.</p>
                    </div>
                @endif
            </div>

            {{-- 3. Detail Audit Trail Administrasi --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Informasi Sistem
                </h3>
                <dl class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                        <dt class="text-gray-500">ID LHP</dt>
                        <dd class="font-mono font-bold text-gray-800 dark:text-gray-200">#{{ $lhp->id }}</dd>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                        <dt class="text-gray-500">Dibuat Pada</dt>
                        <dd class="font-semibold text-gray-800 dark:text-gray-200">{{ optional($lhp->created_at)->translatedFormat('d M Y, H:i') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-500">Terakhir Diperbarui</dt>
                        <dd class="font-semibold text-gray-800 dark:text-gray-200">{{ optional($lhp->updated_at)->translatedFormat('d M Y, H:i') }}</dd>
                    </div>
                </dl>
            </div>

        </aside>

    </div>

</div>
@endsection
