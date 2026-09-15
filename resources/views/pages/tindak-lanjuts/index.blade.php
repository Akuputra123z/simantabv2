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

@if(session('error'))
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-xs sm:text-sm font-medium text-red-800 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300 shadow-xs">
        {{ session('error') }}
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
        @php
            $terbayar = (float) ($stats->total_terbayar ?? 0);
            $target   = (float) ($stats->total_nilai_rekom ?? 0);
            $persen   = $stats->persen_setor ?? ($target > 0 ? min(100, round(($terbayar / $target) * 100, 1)) : 0);

            // Format singkatan satuan mata uang yang tepat
            if ($terbayar >= 1000000000) {
                $terbayarFmt = 'Rp' . number_format($terbayar / 1000000000, 2, ',', '.') . ' M';
            } elseif ($terbayar >= 1000000) {
                $terbayarFmt = 'Rp' . number_format($terbayar / 1000000, 1, ',', '.') . ' Jt';
            } else {
                $terbayarFmt = 'Rp' . number_format($terbayar, 0, ',', '.');
            }

            if ($target >= 1000000000) {
                $targetFmt = 'Rp' . number_format($target / 1000000000, 2, ',', '.') . ' M';
            } elseif ($target >= 1000000) {
                $targetFmt = 'Rp' . number_format($target / 1000000, 1, ',', '.') . ' Jt';
            } else {
                $targetFmt = 'Rp' . number_format($target, 0, ',', '.');
            }
        @endphp
        <div class="min-w-0 flex-1" title="Terbayar: Rp {{ number_format($terbayar, 0, ',', '.') }} dari Target: Rp {{ number_format($target, 0, ',', '.') }}">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Realisasi Setoran</p>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $persen >= 100 ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($persen > 0 ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                    {{ $persen }}%
                </span>
            </div>
            <p class="mt-0.5 text-lg font-bold text-gray-900 dark:text-white font-mono">
                {{ $terbayarFmt }}
            </p>
            <p class="text-[11px] text-gray-400 truncate">
                dari {{ $targetFmt }} target
            </p>
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

{{-- FORM BULK DELETE & TABLE DAFTAR LHP --}}
<form id="main-form" action="{{ route('tindak-lanjuts.bulkDelete') }}" method="POST">
    @csrf
    @method('DELETE')

    {{-- BULK ACTION BANNER (KHUSUS SUPER ADMIN & KEPALA INSPEKTORAT) --}}
    @if(auth()->user()?->hasRole(['super_admin', 'kepala_inspektorat']))
        <div id="bulk-action-bar" class="hidden mb-4 p-3.5 px-4 rounded-2xl bg-red-50/90 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 flex flex-wrap items-center justify-between gap-3 shadow-md backdrop-blur-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-600 text-white font-bold text-xs shadow-xs" id="count-selected-badge">
                    0
                </div>
                <div>
                    <p class="text-xs font-bold text-red-900 dark:text-red-200">
                        <span id="count-selected-text">0</span> LHP Terpilih
                    </p>
                    <p class="text-[11px] text-red-700/80 dark:text-red-300/70">
                        Hapus seluruh data tindak lanjut dan file pendukung dari LHP yang dicentang.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="uncheckAll()" class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100/50 dark:border-red-800 dark:bg-gray-800 dark:text-red-300 dark:hover:bg-red-900/30 transition-colors cursor-pointer">
                    Batal Pilihan
                </button>
                <button type="button" id="btn-bulk-delete" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 active:scale-[0.98] transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Hapus Terpilih (<span id="count-selected">0</span>)</span>
                </button>
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto min-w-full">
            <table class="w-full text-left text-xs whitespace-nowrap sm:whitespace-normal">
                <thead class="bg-gray-50/80 text-gray-500 uppercase tracking-wider dark:bg-gray-800/50 dark:text-gray-400 text-[11px]">
                    <tr>
                        @if(auth()->user()?->hasRole(['super_admin', 'kepala_inspektorat']))
                            <th class="px-3.5 py-3.5 w-10 text-center border-r border-gray-200/70 dark:border-gray-800/70">
                                <input type="checkbox" id="check-all" class="h-4 w-4 rounded border-gray-300 text-blue-600 cursor-pointer">
                            </th>
                        @endif
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
                            @if(auth()->user()?->hasRole('super_admin'))
                                <td class="px-3.5 py-3.5 text-center">
                                    <input type="checkbox" name="lhp_ids[]" value="{{ $lhp->id }}" class="check-item h-4 w-4 rounded border-gray-300 text-blue-600 cursor-pointer">
                                </td>
                            @endif

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
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Detail LHP Icon --}}
                                    <a href="{{ route('tindak-lanjuts.lhp', $lhp->id) }}"
                                       class="h-9 w-9 inline-flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white transition-all duration-200 shadow-xs hover:shadow-md hover:scale-105 cursor-pointer"
                                       title="Detail LHP & Rekomendasi">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    {{-- Hapus TL Icon (Khusus Super Admin & Kepala Inspektorat) --}}
                                    @if(auth()->user()?->hasRole(['super_admin', 'kepala_inspektorat']))
                                        <button type="button"
                                                onclick="deleteSingleLhp({{ $lhp->id }}, '{{ addslashes($lhp->nomor_lhp) }}')"
                                                class="h-9 w-9 inline-flex items-center justify-center rounded-xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white transition-all duration-200 shadow-xs hover:shadow-md hover:scale-105 cursor-pointer"
                                                title="Hapus Seluruh Tindak Lanjut LHP Ini">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->hasRole(['super_admin', 'kepala_inspektorat']) ? 10 : 9 }}" class="py-8 text-center text-gray-400 dark:text-gray-500">
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
</form>

{{-- MODAL KONFIRMASI DELETE --}}
@if(auth()->user()?->hasRole(['super_admin', 'kepala_inspektorat']))
    <div id="delete-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-xs">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 mb-4 mx-auto">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-center text-lg font-bold text-gray-900 dark:text-white" id="modal-title">Konfirmasi Hapus Data</h3>
            <p class="mt-2 text-center text-xs sm:text-sm text-gray-500 dark:text-gray-400" id="modal-desc">
                Apakah Anda yakin ingin menghapus data tindak lanjut dari LHP yang dipilih? Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal()"
                        class="w-full sm:w-auto rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Batal
                </button>
                <button type="button" onclick="submitDelete()"
                        class="w-full sm:w-auto rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-red-700 transition-all">
                    Ya, Hapus Sekarang
                </button>
            </div>
        </div>
    </div>

    <script>
        let singleLhpId = null;

        document.addEventListener('DOMContentLoaded', function () {
            const checkAll = document.getElementById('check-all');
            const checkItems = document.querySelectorAll('.check-item');
            const bulkActionBar = document.getElementById('bulk-action-bar');
            const countSelectedBadge = document.getElementById('count-selected-badge');
            const countSelectedText = document.getElementById('count-selected-text');
            const countSelected = document.getElementById('count-selected');
            const btnBulkDelete = document.getElementById('btn-bulk-delete');

            function updateBulkBar() {
                const checked = document.querySelectorAll('.check-item:checked');
                const count = checked.length;

                if (countSelectedBadge) countSelectedBadge.textContent = count;
                if (countSelectedText) countSelectedText.textContent = count;
                if (countSelected) countSelected.textContent = count;

                if (count > 0) {
                    bulkActionBar?.classList.remove('hidden');
                } else {
                    bulkActionBar?.classList.add('hidden');
                }

                if (checkAll) {
                    checkAll.checked = (checkItems.length > 0 && checked.length === checkItems.length);
                }
            }

            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    checkItems.forEach(item => item.checked = this.checked);
                    updateBulkBar();
                });
            }

            checkItems.forEach(item => {
                item.addEventListener('change', updateBulkBar);
            });

            if (btnBulkDelete) {
                btnBulkDelete.addEventListener('click', function () {
                    singleLhpId = null;
                    const checkedCount = document.querySelectorAll('.check-item:checked').length;
                    document.getElementById('modal-title').textContent = 'Hapus Bulk Tindak Lanjut';
                    document.getElementById('modal-desc').textContent = `Apakah Anda yakin ingin menghapus data tindak lanjut dari ${checkedCount} LHP yang dicentang?`;
                    document.getElementById('delete-modal').classList.remove('hidden');
                });
            }
        });

        function uncheckAll() {
            document.querySelectorAll('.check-item').forEach(item => item.checked = false);
            const checkAll = document.getElementById('check-all');
            if (checkAll) checkAll.checked = false;
            document.getElementById('bulk-action-bar')?.classList.add('hidden');
        }

        function deleteSingleLhp(id, lhpNo) {
            singleLhpId = id;
            document.getElementById('modal-title').textContent = 'Hapus Tindak Lanjut LHP';
            document.getElementById('modal-desc').textContent = `Apakah Anda yakin ingin menghapus seluruh data tindak lanjut dari LHP No "${lhpNo}"?`;
            document.getElementById('delete-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('delete-modal').classList.add('hidden');
            singleLhpId = null;
        }

        function submitDelete() {
            const form = document.getElementById('main-form');
            if (singleLhpId) {
                // Clear all checked items and create single hidden input
                uncheckAll();
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'lhp_ids[]';
                input.value = singleLhpId;
                form.appendChild(input);
            }
            form.submit();
        }
    </script>
@endif

@endsection