@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Laporan Kepatuhan OPD</h1>
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                    Kepatuhan 100%
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Penerbitan dan rekapitulasi kesimpulan tindak lanjut temuan & rekomendasi bagi Objek Pemeriksaan yang telah tuntas 100% atau nihil.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('laporan.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Rekap Semua LHP
            </a>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('warning'))
        <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-300">
            <svg class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1 font-medium">{{ session('warning') }}</div>
        </div>
    @endif

    {{-- RINGKASAN METRIK KEPATUHAN --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Objek / LHP --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-800/80">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total LHP Diaudit</span>
                <span class="rounded-lg bg-gray-100 p-2 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($summary['total_lhp']) }}</div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Data hasil pemeriksaan terdaftar</p>
            </div>
        </div>

        {{-- LHP Patuh 100% --}}
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Patuh Tuntas (100%)</span>
                <span class="rounded-lg bg-emerald-100 p-2 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-emerald-700 dark:text-emerald-400">{{ number_format($summary['total_patuh']) }}</div>
                <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">Siap Cetak / Diterbitkan Laporan</p>
            </div>
        </div>

        {{-- LHP Dalam Proses TL --}}
        <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-5 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Dalam Proses TL (&lt;100%)</span>
                <span class="rounded-lg bg-amber-100 p-2 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-amber-700 dark:text-amber-400">{{ number_format($summary['total_proses']) }}</div>
                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Tindak lanjut belum tuntas</p>
            </div>
        </div>

        {{-- Tingkat Kepatuhan --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50/40 p-5 shadow-sm dark:border-blue-900/50 dark:bg-blue-950/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-400">Rasio Kepatuhan</span>
                <span class="rounded-lg bg-blue-100 p-2 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-blue-700 dark:text-blue-400">{{ $summary['rate_patuh'] }}%</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">dari total LHP</span>
                </div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-blue-100 dark:bg-blue-900/50">
                    <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400 transition-all duration-500" style="width: {{ min(100, $summary['rate_patuh']) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-800/80">
        <form method="GET" action="{{ route('laporan.kepatuhan') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center">
            {{-- Input Pencarian --}}
            <div class="flex-1 min-w-0">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari No. LHP, OPD, atau Program Pengawasan..."
                           class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50/50 pl-10 pr-4 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-500 transition-colors">
                </div>
            </div>

            {{-- Select Filters & Action Buttons Group --}}
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                {{-- Dropdown Status Kepatuhan --}}
                <div class="relative flex-1 min-w-[150px] sm:flex-initial sm:w-56">
                    <select name="status" data-no-ts onchange="this.form.submit()"
                            class="h-10 w-full appearance-none cursor-pointer rounded-lg border border-gray-300 bg-gray-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-gray-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 transition-colors">
                        <option value="semua" {{ request('status', 'semua') === 'semua' ? 'selected' : '' }}>Semua Status Kepatuhan</option>
                        <option value="patuh" {{ request('status') === 'patuh' ? 'selected' : '' }}>Patuh 100% (Siap Terbit Laporan)</option>
                        <option value="proses" {{ request('status') === 'proses' ? 'selected' : '' }}>Dalam Proses TL (&lt;100%)</option>
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 dark:text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </div>

                {{-- Dropdown Tahun --}}
                <div class="relative flex-1 min-w-[120px] sm:flex-initial sm:w-36">
                    <select name="tahun" data-no-ts onchange="this.form.submit()"
                            class="h-10 w-full appearance-none cursor-pointer rounded-lg border border-gray-300 bg-gray-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-gray-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 transition-colors">
                        <option value="">Semua Tahun</option>
                        @foreach($tahunList as $thn)
                            <option value="{{ $thn }}" {{ request('tahun') == $thn ? 'selected' : '' }}>Tahun {{ $thn }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 dark:text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </div>

                {{-- Dropdown Baris Per Halaman --}}
                <div class="relative flex-1 min-w-[100px] sm:flex-initial sm:w-28">
                    <select name="per_page" data-no-ts onchange="this.form.submit()"
                            class="h-10 w-full appearance-none cursor-pointer rounded-lg border border-gray-300 bg-gray-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-gray-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 transition-colors">
                        @foreach([10, 15, 20, 25, 50, 100] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 15) == $n ? 'selected' : '' }}>{{ $n }} Baris</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 dark:text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit"
                            class="h-10 flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 active:scale-95 transition-all whitespace-nowrap">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>
                    @if(request()->filled('search') || request()->filled('tahun') || (request()->filled('status') && request('status') !== 'semua') || (request()->filled('per_page') && request('per_page') != 15))
                        <a href="{{ route('laporan.kepatuhan') }}"
                           title="Reset Semua Filter"
                           class="h-10 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors whitespace-nowrap">
                            <svg class="h-3.5 w-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Active Filter Pills & Ringkasan Hasil --}}
        @if(request()->filled('search') || request()->filled('tahun') || (request()->filled('status') && request('status') !== 'semua'))
            <div class="mt-3 flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100 text-xs text-gray-600 dark:border-gray-700/60 dark:text-gray-400">
                <span class="font-medium text-gray-500 dark:text-gray-400">Filter aktif:</span>

                @if(request()->filled('search'))
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/40">
                        Kata Kunci: "{{ request('search') }}"
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-blue-900 dark:hover:text-blue-100 font-bold">&times;</a>
                    </span>
                @endif

                @if(request()->filled('status') && request('status') !== 'semua')
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/40">
                        Status: {{ request('status') === 'patuh' ? 'Patuh 100%' : 'Dalam Proses TL' }}
                        <a href="{{ request()->fullUrlWithQuery(['status' => 'semua']) }}" class="hover:text-blue-900 dark:hover:text-blue-100 font-bold">&times;</a>
                    </span>
                @endif

                @if(request()->filled('tahun'))
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/40">
                        Tahun: {{ request('tahun') }}
                        <a href="{{ request()->fullUrlWithQuery(['tahun' => null]) }}" class="hover:text-blue-900 dark:hover:text-blue-100 font-bold">&times;</a>
                    </span>
                @endif

                <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                    Ditemukan <strong class="font-semibold text-gray-800 dark:text-gray-200">{{ $lhps->total() }}</strong> LHP
                </span>
            </div>
        @endif
    </div>

    {{-- TABEL LAPORAN KEPATUHAN --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300">
                <thead class="border-b border-gray-200 bg-gray-50/80 font-semibold uppercase tracking-wider text-gray-600 dark:border-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
                    <tr>
                        <th scope="col" class="py-3.5 px-4 w-12 text-center">No</th>
                        <th scope="col" class="py-3.5 px-4">Objek Pemeriksaan (OPD) & LHP</th>
                        <th scope="col" class="py-3.5 px-4">Program Pengawasan</th>
                        <th scope="col" class="py-3.5 px-4 text-center">Temuan & Rekomendasi</th>
                        <th scope="col" class="py-3.5 px-4 w-44">Progres Kepatuhan</th>
                        <th scope="col" class="py-3.5 px-4 w-48 text-center">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($lhps as $index => $lhp)
                        @php
                            $isLengkap = $lhp->isKepatuhanLengkap();
                            $progresVal = $lhp->progres_kepatuhan;
                            $stat = $lhp->statistik;
                            $opdName = $lhp->unitDiperiksa?->nama_unit ?? $lhp->nama_unit ?? '-';
                            $programName = $lhp->auditAssignment?->auditProgram?->nama_program ?? '-';
                            $irban = $lhp->auditAssignment?->auditProgramDetail?->tim ?? '-';
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition dark:hover:bg-gray-750 {{ $isLengkap ? 'bg-emerald-50/20 dark:bg-emerald-950/10' : '' }}">
                            <td class="py-4 px-4 text-center font-medium text-gray-500 dark:text-gray-400">
                                {{ $lhps->firstItem() + $index }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-gray-900 dark:text-white text-sm">
                                    {{ $opdName }}
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center font-mono font-medium text-gray-700 dark:text-gray-300">
                                        {{ $lhp->nomor_lhp }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ $lhp->tanggal_lhp ? \Carbon\Carbon::parse($lhp->tanggal_lhp)->format('d M Y') : '-' }}</span>
                                    @if($irban !== '-')
                                        <span class="rounded bg-gray-100 px-1.5 py-0.2 text-[10px] font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $irban }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-medium text-gray-900 dark:text-white line-clamp-2" title="{{ $programName }}">
                                    {{ $programName }}
                                </div>
                                <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    Tahun: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $lhp->auditAssignment?->auditProgram?->tahun ?? '-' }}</span>
                                    @if($lhp->auditAssignment?->auditProgram?->kategori)
                                        • <span class="capitalize">{{ $lhp->auditAssignment->auditProgram->kategori }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($lhp->is_nihil)
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Nihil Temuan
                                    </span>
                                @else
                                    <div class="inline-flex flex-col items-center">
                                        <div class="flex items-center gap-2">
                                            <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300" title="Total Temuan">
                                                {{ $stat?->total_temuan ?? 0 }} Temuan
                                            </span>
                                            <span class="rounded bg-purple-50 px-2 py-0.5 text-xs font-bold text-purple-700 dark:bg-purple-900/30 dark:text-purple-300" title="Total Rekomendasi">
                                                {{ $stat?->total_rekomendasi ?? 0 }} Rekomendasi
                                            </span>
                                        </div>
                                        @if(($stat?->total_kerugian ?? 0) > 0)
                                            <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">
                                                Kerugian: Rp {{ number_format($stat->total_kerugian, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-bold {{ $isLengkap ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                            {{ $isLengkap ? '100% Selesai' : number_format($progresVal, 1) . '%' }}
                                        </span>
                                        @if($isLengkap)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                PATUH
                                            </span>
                                        @else
                                            <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                PROSES
                                            </span>
                                        @endif
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="h-full rounded-full {{ $isLengkap ? 'bg-emerald-500' : 'bg-amber-500' }} transition-all duration-300"
                                             style="width: {{ min(100, max(0, $progresVal)) }}%"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] text-gray-400">
                                        <span>TL: {{ $stat?->rekom_selesai ?? 0 }}/{{ $stat?->total_rekomendasi ?? 0 }}</span>
                                        <span>Sisa: Rp {{ number_format($stat?->total_sisa_kerugian ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($isLengkap)
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Tombol Lihat Laporan Web (Dilindungi Verifikasi Keamanan Sandi) --}}
                                        <button type="button"
                                                onclick="window.requestSecurePdf('kepatuhan_show', '{{ $lhp->uuid }}', 'Laporan Kepatuhan: {{ addslashes($lhp->nomor_lhp) }}')"
                                                title="Lihat Laporan Kepatuhan Resmi (Verifikasi Keamanan Sandi)"
                                                class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Lihat
                                        </button>

                                        {{-- Tombol Unduh PDF (Dilindungi Verifikasi Keamanan Sandi) --}}
                                        <button type="button"
                                                onclick="window.requestSecurePdf('kepatuhan_pdf', '{{ $lhp->uuid }}', 'Laporan Kepatuhan: {{ addslashes($lhp->nomor_lhp) }}')"
                                                title="Unduh Dokumen PDF Resmi (Verifikasi Keamanan Sandi)"
                                                class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 transition cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            PDF
                                        </button>
                                    </div>
                                @else
                                    {{-- Disabled state with security guard feedback --}}
                                    <div class="inline-flex flex-col items-center" title="Laporan kepatuhan terkunci. Hanya dapat diakses & diunduh bila progres tindak lanjut telah 100%.">
                                        <span class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-150 px-2.5 py-1.5 text-[11px] font-medium text-gray-400 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500">
                                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Belum 100%
                                        </span>
                                        <span class="mt-0.5 text-[9px] text-gray-400 dark:text-gray-500">Terkunci</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">Tidak ada data laporan kepatuhan yang sesuai</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter status.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($lhps->hasPages())
            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700 sm:px-6">
                {{ $lhps->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
