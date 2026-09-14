@extends('layouts.app')

@section('content')

@php
    $sort = request('sort');
    $direction = request('direction', 'desc');

    $sortUrl = fn(string $col) => request()->fullUrlWithQuery([
        'sort'      => $col,
        'direction' => ($sort === $col && $direction === 'desc') ? 'asc' : 'desc',
        'page'      => 1,
    ]);

    $renderSortIcons = function(string $col) use ($sort, $direction): string {
        $activeAsc = ($sort === $col && $direction === 'asc') ? 'fill-blue-600 dark:fill-blue-400' : 'fill-gray-300 dark:fill-gray-700';
        $activeDesc = ($sort === $col && $direction === 'desc') ? 'fill-blue-600 dark:fill-blue-400' : 'fill-gray-300 dark:fill-gray-700';
        return '<span class="flex flex-col gap-0.5 ml-1.5 shrink-0">
            <svg class="'.$activeAsc.'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill=""></path></svg>
            <svg class="'.$activeDesc.'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill=""></path></svg>
        </span>';
    };
@endphp

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Monitoring Tindak Lanjut LHP</h1>
        <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            Daftar LHP dengan komulatif data temuan, rekomendasi, dan status verifikasi tindak lanjut.
        </p>
    </div>
    <div class="flex items-center gap-2 w-full sm:w-auto">
        <a href="{{ route('tindak-lanjuts.create') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 min-h-[44px] text-xs sm:text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Tindak Lanjut</span>
        </a>
    </div>
</div>

{{-- FLASH NOTIFICATIONS --}}
@if(session('success'))
    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-xs sm:text-sm font-medium text-green-800 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300 shadow-xs">
        {{ session('success') }}
    </div>
@endif

{{-- STAT CARDS (KOMULATIF SEMUA DATA) --}}
<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

    <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Total LHP TL</p>
            <p class="mt-0.5 text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ $stats->total_lhp ?? 0 }}</p>
            <p class="text-[11px] text-gray-400">{{ $stats->total_rekomendasi ?? 0 }} Rekomendasi</p>
        </div>
    </div>

    <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">TL Selesai (Lunas)</p>
            <p class="mt-0.5 text-2xl font-bold text-green-600 dark:text-green-400 font-mono">{{ $stats->total_lunas ?? 0 }}</p>
            <p class="text-[11px] text-gray-400">Telah terverifikasi</p>
        </div>
    </div>

    <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Proses / Berjalan</p>
            <p class="mt-0.5 text-2xl font-bold text-amber-600 dark:text-amber-400 font-mono">{{ $stats->total_berjalan ?? 0 }}</p>
            <p class="text-[11px] text-gray-400">{{ $stats->total_menunggu ?? 0 }} Menunggu Verif</p>
        </div>
    </div>

    <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Realisasi Setoran</p>
            <p class="mt-0.5 text-lg font-bold text-gray-900 dark:text-white font-mono">
                Rp{{ number_format(($stats->total_terbayar ?? 0) / 1000000, 1) }}M
            </p>
            <p class="text-[11px] text-gray-400">dari Rp{{ number_format(($stats->total_nilai_rekom ?? 0) / 1000000, 1) }}M target</p>
        </div>
    </div>

</div>

{{-- FILTER TOOLBAR --}}
<form method="GET" action="{{ route('tindak-lanjuts.index') }}" class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 items-end">
        
        {{-- Search --}}
        <div class="lg:col-span-4">
            <label for="search" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                Cari Nomor LHP / Topik
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" id="search" value="{{ $search }}"
                       placeholder="Nomor LHP atau nama program..."
                       class="h-10 w-full rounded-xl border border-gray-300 bg-white pl-9 pr-3 text-xs text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
        </div>

        {{-- Tahun --}}
        <div class="lg:col-span-2">
            <label for="tahun" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                Tahun
            </label>
            <select name="tahun" id="tahun" data-no-ts
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-xs text-gray-900 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                <option value="">Semua Tahun</option>
                @foreach(range(date('Y'), date('Y') - 4) as $y)
                    <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        {{-- Kategori --}}
        <div class="lg:col-span-2">
            <label for="kategori" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                Kategori Program
            </label>
            <select name="kategori" id="kategori" data-no-ts
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-xs text-gray-900 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $k)
                    <option value="{{ $k }}" @selected($kategori == $k)>{{ $k }}</option>
                @endforeach
            </select>
        </div>

        {{-- Status TL --}}
        <div class="lg:col-span-2">
            <label for="status" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                Status Verifikasi
            </label>
            <select name="status" id="status" data-no-ts
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-xs text-gray-900 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                <option value="">Semua Status</option>
                <option value="lunas" @selected($status == 'lunas')>Lunas</option>
                <option value="berjalan" @selected($status == 'berjalan')>Sedang Berjalan</option>
                <option value="menunggu_verifikasi" @selected($status == 'menunggu_verifikasi')>Menunggu Verifikasi</option>
            </select>
        </div>

        {{-- Status OPD --}}
        <div class="lg:col-span-2">
            <label for="status_opd" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                Status Dokumen OPD
            </label>
            <select name="status_opd" id="status_opd" data-no-ts
                    class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-xs text-gray-900 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                <option value="">Semua Dokumen</option>
                <option value="dikirim" @selected($statusOpd == 'dikirim')>Terkirim (Ada Upload)</option>
                <option value="draft" @selected($statusOpd == 'draft')>Draft</option>
                <option value="ditolak" @selected($statusOpd == 'ditolak')>Ditolak (Perlu Revisi)</option>
                <option value="belum_upload" @selected($statusOpd == 'belum_upload')>Belum Upload</option>
            </select>
        </div>

    </div>

    <div class="mt-4 flex items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
        <button type="submit"
                class="h-9 px-5 inline-flex items-center gap-1.5 rounded-xl bg-blue-600 text-xs font-bold text-white hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Terapkan Filter
        </button>
        @if(request()->hasAny(['search', 'tahun', 'kategori', 'status', 'status_opd', 'unit_id']))
            <a href="{{ route('tindak-lanjuts.index') }}"
               class="h-9 px-4 inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                Reset
            </a>
        @endif
    </div>
</form>

{{-- TABLE DAFTAR LHP (KOMULATIF TINDAK LANJUT PER LHP) --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="overflow-x-auto min-w-full">
        <table class="w-full text-left text-xs whitespace-nowrap sm:whitespace-normal">
            <thead class="bg-gray-50/80 text-gray-500 uppercase tracking-wider dark:bg-gray-800/50 dark:text-gray-400 text-[11px]">
                <tr>
                    <th class="px-4 py-3.5 font-semibold">
                        <div class="flex items-center justify-between">
                            <a href="{{ $sortUrl('nama_program') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Nomor LHP & Program</span>
                            </a>
                            <a href="{{ $sortUrl('nama_program') }}">
                                {!! $renderSortIcons('nama_program') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3.5 font-semibold">
                        <div class="flex items-center justify-between">
                            <a href="{{ $sortUrl('unit_diperiksa') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Unit OPD</span>
                            </a>
                            <a href="{{ $sortUrl('unit_diperiksa') }}">
                                {!! $renderSortIcons('unit_diperiksa') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3.5 font-semibold">
                        <div class="flex items-center justify-between">
                            <a href="{{ $sortUrl('tanggal_lhp') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Tanggal / Kat.</span>
                            </a>
                            <a href="{{ $sortUrl('tanggal_lhp') }}">
                                {!! $renderSortIcons('tanggal_lhp') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3.5 font-semibold text-center">Temuan & Rekom</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Nilai Rekomendasi</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Realisasi Setor</th>
                    <th class="px-4 py-3.5 font-semibold text-center">Progres TL</th>
                    <th class="px-4 py-3.5 font-semibold text-center">Status OPD</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($lhps as $lhp)
                    @php
                        $temuans = $lhp->temuans;
                        $rekomendasis = $temuans->flatMap->recommendations;
                        $tindakLanjuts = $rekomendasis->flatMap->tindakLanjuts;

                        $totalTemuan = $temuans->count();
                        $totalRekom = $rekomendasis->count();
                        $totalNilaiRekom = (float) $rekomendasis->sum('nilai_rekom');
                        $totalSetor = (float) $tindakLanjuts->sum('total_terbayar');
                        
                        $progres = (float) ($lhp->statistik?->persen_selesai_gabungan ?? 0);
                        if ($progres == 0 && $totalNilaiRekom > 0 && $totalSetor > 0) {
                            $progres = min(100, round(($totalSetor / $totalNilaiRekom) * 100));
                        }

                        // Status OPD kumulatif
                        $hasDikirim = $tindakLanjuts->contains(fn($t) => $t->status_opd === 'dikirim');
                        $hasDraft   = $tindakLanjuts->contains(fn($t) => $t->status_opd === 'draft');
                        $hasDitolak = $tindakLanjuts->contains(fn($t) => !empty($t->alasan_tolak_opd));
                        $isAllLunas = $totalRekom > 0 && $tindakLanjuts->where('status_verifikasi', 'lunas')->count() === $totalRekom;
                    @endphp

                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                        {{-- Nomor LHP & Program --}}
                        <td class="px-4 py-3.5">
                            <a href="{{ route('tindak-lanjuts.lhp', $lhp->id) }}"
                               class="font-bold text-gray-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400 font-mono text-xs sm:text-sm block">
                                {{ $lhp->nomor_lhp }}
                            </a>
                            <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-0.5 line-clamp-1">
                                {{ $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->nama_program ?? '-' }}
                            </p>
                        </td>

                        {{-- Unit OPD --}}
                        <td class="px-4 py-3.5">
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $lhp->unitDiperiksa?->nama_unit ?? '-' }}
                            </span>
                            @if($lhp->auditAssignment?->ketuaTim)
                                <p class="text-[10px] text-gray-400 mt-0.5">Ketua: {{ $lhp->auditAssignment->ketuaTim->name }}</p>
                            @endif
                        </td>

                        {{-- Tanggal / Kategori --}}
                        <td class="px-4 py-3.5">
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ $lhp->tanggal_lhp ? $lhp->tanggal_lhp->format('d/m/Y') : '-' }}
                            </span>
                            <div class="mt-1">
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    {{ $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->kategori ?? 'PKPT' }}
                                </span>
                            </div>
                        </td>

                        {{-- Temuan & Rekomendasi --}}
                        <td class="px-4 py-3.5 text-center">
                            <span class="font-bold text-gray-900 dark:text-white">{{ $totalTemuan }}</span>
                            <span class="text-gray-400">/</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $totalRekom }}</span>
                            <p class="text-[10px] text-gray-400">Temuan / Rekom</p>
                        </td>

                        {{-- Nilai Rekomendasi --}}
                        <td class="px-4 py-3.5 text-right font-medium font-mono text-gray-900 dark:text-white">
                            Rp{{ number_format($totalNilaiRekom, 0, ',', '.') }}
                        </td>

                        {{-- Realisasi Setor --}}
                        <td class="px-4 py-3.5 text-right font-bold font-mono text-green-600 dark:text-green-400">
                            Rp{{ number_format($totalSetor, 0, ',', '.') }}
                        </td>

                        {{-- Progres TL --}}
                        <td class="px-4 py-3.5 text-center">
                            <div class="w-24 mx-auto">
                                <div class="flex justify-between items-center text-[10px] font-semibold text-gray-600 dark:text-gray-300 mb-1">
                                    <span class="font-mono">{{ round($progres) }}%</span>
                                    @if($isAllLunas)
                                        <span class="text-green-600 font-bold">LUNAS</span>
                                    @endif
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                    <div class="h-full rounded-full {{ $progres >= 100 ? 'bg-green-500' : ($progres > 0 ? 'bg-blue-600' : 'bg-gray-300') }}"
                                         style="width: {{ min(100, max(0, $progres)) }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Status OPD --}}
                        <td class="px-4 py-3.5 text-center">
                            @if($hasDitolak)
                                <span class="rounded px-2 py-0.5 text-[10px] font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    Ditolak (Revisi)
                                </span>
                            @elseif($hasDikirim)
                                <span class="rounded px-2 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                    Ada Kiriman
                                </span>
                            @elseif($hasDraft)
                                <span class="rounded px-2 py-0.5 text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    Draft OPD
                                </span>
                            @else
                                <span class="rounded px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                    Belum Upload
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('tindak-lanjuts.lhp', $lhp->id) }}"
                               class="inline-flex items-center justify-center gap-1 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition-colors shadow-xs min-h-[38px]">
                                <span>Detail LHP</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-gray-400 dark:text-gray-500">
                            Tidak ada data LHP tindak lanjut yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($lhps->hasPages())
        <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
            {{ $lhps->links() }}
        </div>
    @endif
</div>

@endsection