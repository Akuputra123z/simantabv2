@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10 space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 dark:border-slate-800 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800">
                    SIMANTAB OPD
                </span>
                <span class="text-xs text-slate-400 font-medium">&bull;</span>
                <span class="text-xs text-slate-500 font-semibold dark:text-slate-400">
                    {{ auth()->user()->opdUnits()->first()?->nama_unit ?? auth()->user()->name }}
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Tindak Lanjut LHP OPD
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Daftar Laporan Hasil Pemeriksaan (LHP) unit kerja Anda yang memerlukan upload bukti tindak lanjut.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('opd.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition-all shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard OPD
            </a>
        </div>
    </div>

    {{-- FLASH ALERTS --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50/90 p-4 text-xs font-semibold text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/50 dark:text-emerald-300 shadow-xs">
            <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 rounded-lg border border-rose-200 bg-rose-50/90 p-4 text-xs font-semibold text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/50 dark:text-rose-300 shadow-xs">
            <svg class="h-4 w-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        
        {{-- Belum Upload --}}
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900/80">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Belum Upload</p>
                <p class="mt-0.5 text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $opdStats->total_belum_upload ?? 0 }}</p>
            </div>
        </div>

        {{-- Draft --}}
        <div class="flex items-center gap-4 rounded-xl border border-amber-200/80 bg-white p-4 shadow-xs dark:border-amber-900/40 dark:bg-slate-900/80">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 17H9v-2.828l9.414-9.586z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Draft</p>
                <p class="mt-0.5 text-xl font-bold font-mono text-amber-700 dark:text-amber-400">{{ $opdStats->total_draft ?? 0 }}</p>
            </div>
        </div>

        {{-- Terkirim --}}
        <div class="flex items-center gap-4 rounded-xl border border-blue-200/80 bg-white p-4 shadow-xs dark:border-blue-900/40 dark:bg-slate-900/80">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Terkirim</p>
                <p class="mt-0.5 text-xl font-bold font-mono text-blue-700 dark:text-blue-400">{{ $opdStats->total_dikirim ?? 0 }}</p>
            </div>
        </div>

        {{-- Ditolak --}}
        <div class="flex items-center gap-4 rounded-xl border border-rose-200/80 bg-white p-4 shadow-xs dark:border-rose-900/40 dark:bg-slate-900/80">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Ditolak (Perbaikan)</p>
                <p class="mt-0.5 text-xl font-bold font-mono text-rose-700 dark:text-rose-400">{{ $opdStats->total_ditolak ?? 0 }}</p>
            </div>
        </div>

    </div>

    {{-- FILTER TOOLBAR --}}
    <form method="GET" action="{{ route('opd.tindak-lanjut.index') }}" class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4">
            
            {{-- Search --}}
            <div>
                <label for="search" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                    Cari Nomor LHP / Topik Audit
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ $search }}"
                           placeholder="Ketik no. LHP atau kata kunci..."
                           class="h-9 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-900 placeholder-slate-400 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
            </div>

            {{-- Kategori Audit (Default: PKPT) --}}
            <div>
                <label for="kategori" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                    Kategori Audit
                </label>
                <select name="kategori" id="kategori" data-no-ts
                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-800 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="semua" {{ $kategori === 'semua' ? 'selected' : '' }}>Semua Kategori Audit</option>
                    @foreach($listKategori as $kat)
                        <option value="{{ $kat }}" {{ $kategori === $kat ? 'selected' : '' }}>
                            {{ $kat }} {{ $kat === 'PKPT' ? '(Default Utama)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status OPD --}}
            <div>
                <label for="status_opd" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                    Status Upload OPD
                </label>
                <select name="status_opd" id="status_opd" data-no-ts
                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-800 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">Semua Status Upload</option>
                    <option value="belum_upload" {{ $statusOpd === 'belum_upload' ? 'selected' : '' }}>Belum Upload</option>
                    <option value="draft" {{ $statusOpd === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="dikirim" {{ $statusOpd === 'dikirim' ? 'selected' : '' }}>Terkirim ke Inspektorat</option>
                    <option value="ditolak" {{ $statusOpd === 'ditolak' ? 'selected' : '' }}>Ditolak (Perlu Perbaikan)</option>
                </select>
            </div>

            {{-- Status Verifikasi --}}
            <div>
                <label for="status" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                    Status Verifikasi Inspektorat
                </label>
                <select name="status" id="status" data-no-ts
                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-800 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">Semua Status Verifikasi</option>
                    <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>Lunas / Selesai</option>
                    <option value="berjalan" {{ $status === 'berjalan' ? 'selected' : '' }}>Dalam Proses / Berjalan</option>
                    <option value="menunggu_verifikasi" {{ $status === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                </select>
            </div>

        </div>

        <div class="mt-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-t border-slate-200/80 pt-3 dark:border-slate-800">
            <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                <span>Filter Aktif:</span>
                <span class="rounded bg-slate-200/80 px-2 py-0.5 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    Kategori: {{ $kategori === 'semua' ? 'Semua' : $kategori }}
                </span>
                @if($statusOpd)
                    <span class="rounded bg-amber-100 px-2 py-0.5 font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                        OPD: {{ str_replace('_', ' ', $statusOpd) }}
                    </span>
                @endif
                @if($status)
                    <span class="rounded bg-emerald-100 px-2 py-0.5 font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                        Verif: {{ str_replace('_', ' ', $status) }}
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                @if($kategori !== 'PKPT' || $statusOpd || $status || $search)
                    <a href="{{ route('opd.tindak-lanjut.index') }}"
                       class="h-10 sm:h-8 px-3 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition-colors w-full sm:w-auto">
                        Reset Filter
                    </a>
                @endif
                <button type="submit"
                        class="h-10 sm:h-8 px-4 inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-700 text-xs font-semibold text-white hover:bg-blue-800 transition-colors shadow-xs w-full sm:w-auto">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Terapkan Filter
                </button>
            </div>
        </div>
    </form>

    {{-- DATA TABLE --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-100/80 text-slate-600 dark:bg-slate-800/80 dark:text-slate-300 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3.5 w-[24%]">Dokumen LHP</th>
                        <th class="px-5 py-3.5 w-[16%]">Program Audit</th>
                        <th class="px-5 py-3.5 w-[22%] text-center">Temuan & Rekomendasi</th>
                        <th class="px-5 py-3.5 w-[14%]">Nilai & Realisasi</th>
                        <th class="px-5 py-3.5 w-[12%] text-center">Status</th>
                        <th class="px-5 py-3.5 w-[10%] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($lhps as $lhp)
                        @php
                            $program = $lhp->auditAssignment?->auditProgramDetail?->auditProgram;
                            $katProg = $program?->kategori ?? 'PKPT';
                            $allTls = $lhp->temuans->flatMap->recommendations->flatMap->tindakLanjuts;

                            $totalTemuan = $lhp->temuans->count();
                            $totalRekom = $lhp->temuans->flatMap->recommendations->count();
                            $totalKerugian = (float) ($lhp->total_kerugian ?? 0);
                            $totalSetor = (float) ($allTls->sum('total_terbayar') ?? 0);
                            $progres = round($lhp->persen_selesai ?? 0);

                            // OPD Status Aggregate
                            $isDitolak = $allTls->contains(fn($t) => $t->status_opd === 'draft' && !empty($t->alasan_tolak_opd));
                            $isDikirim = $allTls->isNotEmpty() && $allTls->every(fn($t) => $t->status_opd === 'dikirim');
                            $isDraft   = $allTls->contains(fn($t) => $t->status_opd === 'draft' && empty($t->alasan_tolak_opd));

                            $opdLabel = match(true) {
                                $isDitolak => 'Ditolak',
                                $isDikirim => 'Terkirim',
                                $isDraft   => 'Draft',
                                default    => 'Belum Upload'
                            };

                            $opdCls = match(true) {
                                $isDitolak => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
                                $isDikirim => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                $isDraft   => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
                                default    => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700',
                            };

                            // Verifikasi Status Aggregate
                            $isLunas = $allTls->isNotEmpty() && $allTls->every(fn($t) => $t->status_verifikasi === 'lunas');
                            $isBerjalan = $allTls->contains(fn($t) => $t->status_verifikasi === 'berjalan');

                            $verifLabel = match(true) {
                                $isLunas => 'Lunas',
                                $isBerjalan => 'Berjalan',
                                default => 'Menunggu'
                            };

                            $verifCls = match(true) {
                                $isLunas => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                $isBerjalan => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            
                            {{-- Dokumen LHP --}}
                            <td class="px-5 py-4 align-top space-y-1">
                                <div class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-mono font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                    {{ $lhp->nomor_lhp }}
                                </div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 pt-0.5">
                                    Tgl LHP: {{ $lhp->tanggal_lhp ? $lhp->tanggal_lhp->format('d M Y') : '-' }}
                                </div>
                                <div class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $lhp->unitDiperiksa?->nama_unit ?? '-' }}
                                </div>
                            </td>

                            {{-- Program Audit --}}
                            <td class="px-5 py-4 align-top space-y-1">
                                
                                <p class="font-semibold text-slate-800 dark:text-slate-200 leading-snug line-clamp-2">
                                    {{ $program?->nama_program ?? 'Program Audit Internal' }}
                                </p>
                                <div>
                                    <span class="inline-block rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 mb-1">
                                        {{ $katProg }}
                                    </span>
                                </div>
                            </td>

                            {{-- Temuan & Rekomendasi --}}
                            <td class="px-5 py-4 align-top text-center space-y-1">
                                <div class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    <span>{{ $totalTemuan }} Temuan</span>
                                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                    <span>{{ $totalRekom }} Rekomendasi</span>
                                </div>
                            </td>

                            {{-- Nilai & Realisasi --}}
                            <td class="px-5 py-4 align-top space-y-1">
                                <div class="font-mono font-bold text-slate-900 dark:text-white text-xs">
                                    Rp {{ number_format($totalKerugian, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] font-mono text-emerald-600 font-semibold dark:text-emerald-400">
                                    Setor: Rp {{ number_format($totalSetor, 0, ',', '.') }}
                                </div>

                                <div class="pt-1">
                                    <div class="flex justify-between text-[10px] text-slate-500 mb-0.5">
                                        <span>Progres Penyelesaian</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-300 font-mono">{{ $progres }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full {{ $progres >= 100 ? 'bg-emerald-500' : ($progres > 0 ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-700') }}" style="width: {{ min(100, max(0, $progres)) }}%"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Status OPD & Verifikasi --}}
                            <td class="px-5 py-4 align-top text-center space-y-1.5">
                                <div>
                                    <span class="inline-block rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $opdCls }}">
                                        OPD: {{ $opdLabel }}
                                    </span>
                                </div>
                                <div>
                                    <span class="inline-block rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $verifCls }}">
                                        Verif: {{ $verifLabel }}
                                    </span>
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 align-top text-right">
                                <a href="{{ route('opd.tindak-lanjut.lhp', $lhp) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800 transition-colors shadow-xs">
                                    <span>Detail LHP</span>
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-8 w-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Tidak ada LHP yang sesuai dengan kriteria filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lhps->hasPages())
            <div class="border-t border-slate-200 px-5 py-3.5 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{ $lhps->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
