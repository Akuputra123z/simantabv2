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

{{-- Header --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Daftar Laporan Hasil Pemeriksaan</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Total: {{ $lhps->total() }} LHP ditemukan</p>
    </div>
    <a href="{{ route('lhps.create') }}"
       class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm shadow-blue-500/10 active:scale-[0.98] transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Buat LHP Baru
    </a>
</div>

{{-- Card Container --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
    
    {{-- Top Controls Bar: Show entries, Filter Kategori, Filter Tahun, Search & Reset --}}
    <form method="GET" action="{{ route('lhps.index') }}" id="filterForm" class="mb-4 px-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            
            {{-- Left Controls: Entries Per Page, Category & Year Select --}}
            <div class="flex flex-wrap items-center gap-2.5">
                
                {{-- Entries Per Page --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Show</span>
                    <div class="relative z-20">
                        <select name="per_page" onchange="document.getElementById('filterForm').submit()" data-no-ts
                                class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @foreach([5, 8, 10, 20, 25, 30, 50, 100] as $sz)
                                <option value="{{ $sz }}" @selected(request('per_page', 10) == $sz)>{{ $sz }}</option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                            <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">entries</span>
                </div>

                {{-- Kategori Dropdown --}}
                <div class="relative z-20">
                    <select name="kategori" onchange="document.getElementById('filterForm').submit()" data-no-ts
                            class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                    <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                        <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </div>

                {{-- Tahun Dropdown --}}
                <div class="relative z-20">
                    <select name="tahun" onchange="document.getElementById('filterForm').submit()" data-no-ts
                            class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="" @selected(!request()->filled('tahun'))>Semua Tahun</option>
                        @foreach($tahuns ?? range(date('Y'), date('Y') - 4) as $thn)
                            <option value="{{ $thn }}" @selected(request()->filled('tahun') && request('tahun') == $thn)>{{ $thn }}</option>
                        @endforeach
                    </select>
                    <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                        <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </div>

            </div>

            {{-- Right Controls: Search Box & Reset --}}
            <div class="flex items-center gap-2">
                <div class="relative flex-1 sm:flex-initial">
                    <button type="submit" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""></path>
                        </svg>
                    </button>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nomor LHP, unit, program..."
                           class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full sm:w-[260px] rounded-lg border border-gray-300 bg-transparent py-2 pr-3 pl-9 text-xs text-gray-800 placeholder:text-gray-400 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                </div>

                @if (request()->filled('search') || request()->filled('tahun') || request()->filled('kategori') || request()->filled('sort') || (request()->filled('per_page') && request('per_page') != 10))
                <a href="{{ route('lhps.index') }}"
                   class="shadow-theme-xs inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Bulk Action Banner Bar (User Friendly Top Position) --}}
    <div id="bulk-action-bar" class="hidden mx-4 mb-4 p-3 px-4 rounded-xl bg-red-50/90 dark:bg-red-950/40 border border-red-200/80 dark:border-red-800/60 flex items-center justify-between shadow-xs transition-all duration-300">
        <div class="flex items-center gap-3">
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-600 text-white font-bold text-xs shadow-xs" id="count-selected-badge">
                0
            </div>
            <div>
                <p class="text-xs font-bold text-red-900 dark:text-red-200">
                    <span id="count-selected-text">0</span> data LHP terpilih
                </p>
                <p class="text-[11px] text-red-700/80 dark:text-red-300/70">
                    Hapus semua data LHP yang dicentang secara bersamaan.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="uncheckAll()" class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100/50 dark:border-red-800 dark:bg-gray-800 dark:text-red-300 dark:hover:bg-red-900/30 transition-colors cursor-pointer">
                Batal Pilihan
            </button>
            <button type="button" id="btn-bulk-delete" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 active:scale-[0.98] transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v2m3 3h7M3 7h18"/>
                </svg>
                <span>Hapus Terpilih (<span id="count-selected">0</span>)</span>
            </button>
        </div>
    </div>

    {{-- Table Area --}}
    <form id="main-form" action="{{ route('lhps.bulkDelete') }}" method="POST" onsubmit="return false;">
        @csrf
        @method('DELETE')
        <div class="max-w-full overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-y border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-900/40 text-xs font-semibold text-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-3.5 py-3 w-[4%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center border-r border-gray-200/70 dark:border-gray-800/70">
                        <input type="checkbox" id="check-all"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 cursor-pointer">
                    </th>
                    <th class="px-4 py-3 w-[25%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-r border-gray-200/70 dark:border-gray-800/70">
                        <div class="flex items-center justify-between">
                            <a href="{{ $sortUrl('unit_diperiksa') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Unit Diperiksa, Program & Nomor LHP</span>
                            </a>
                            <a href="{{ $sortUrl('unit_diperiksa') }}">
                                {!! $renderSortIcons('unit_diperiksa') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 w-[10%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center border-r border-gray-200/70 dark:border-gray-800/70">
                        <div class="flex items-center justify-center">
                            <a href="{{ $sortUrl('kategori') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Kategori</span>
                            </a>
                            <a href="{{ $sortUrl('kategori') }}">
                                {!! $renderSortIcons('kategori') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 w-[14%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-r border-gray-200/70 dark:border-gray-800/70">
                        <div class="flex items-center justify-between">
                            <a href="{{ $sortUrl('tanggal_lhp') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Tanggal LHP</span>
                            </a>
                            <a href="{{ $sortUrl('tanggal_lhp') }}">
                                {!! $renderSortIcons('tanggal_lhp') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 w-[10%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center border-r border-gray-200/70 dark:border-gray-800/70">
                        <div class="flex items-center justify-center">
                            <a href="{{ $sortUrl('progress') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Progress TL</span>
                            </a>
                            <a href="{{ $sortUrl('progress') }}">
                                {!! $renderSortIcons('progress') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 w-[12%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                @forelse($lhps as $lhp)
                @php
                    $persen      = (float) ($lhp->statistik?->persen_selesai_gabungan ?? 0);
                    $persenLabel = number_format($persen, 0);
                    $barColor = match(true) {
                        $persen >= 100 => 'bg-green-500',
                        $persen >= 50  => 'bg-amber-400',
                        $persen > 0    => 'bg-blue-500',
                        default        => 'bg-gray-300',
                    };
                    $k = $lhp->kategori;
                    $kategoriBadge = match($k) {
                        'PKPT' => 'bg-indigo-50 text-indigo-600 border border-indigo-200/60 dark:bg-indigo-900/20 dark:text-indigo-400 dark:border-indigo-800/30',
                        'BPK'  => 'bg-amber-50 text-amber-600 border border-amber-200/60 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/30',
                        'BPKP' => 'bg-rose-50 text-rose-600 border border-rose-200/60 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/30',
                        'ITPROV' => 'bg-cyan-50 text-cyan-600 border border-cyan-200/60 dark:bg-cyan-900/20 dark:text-cyan-400 dark:border-cyan-800/30',
                        'ITDA'   => 'bg-teal-50 text-teal-600 border border-teal-200/60 dark:bg-teal-900/20 dark:text-teal-400 dark:border-teal-800/30',
                        default  => 'bg-gray-50 text-gray-500 border border-gray-200/60 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700/50',
                    };
                @endphp
                <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors align-top">
                    {{-- Checkbox --}}
                    <td class="px-3.5 py-4 text-center border-r border-gray-100 dark:border-gray-800/60">
                        <input type="checkbox" name="ids[]" value="{{ $lhp->id }}"
                            class="check-item h-4 w-4 rounded border-gray-300 cursor-pointer mt-0.5">
                    </td>

                    {{-- Unit Diperiksa, Program & Nomor LHP --}}
                    <td class="px-4 py-4 border-r border-gray-100 dark:border-gray-800/60">
                        @php
                            $units = $lhp->unitDiperiksas->isNotEmpty() ? $lhp->unitDiperiksas : ($lhp->unitDiperiksa ? collect([$lhp->unitDiperiksa]) : collect());
                        @endphp
                        {{-- 1. Unit Diperiksa --}}
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $units->first()?->nama_unit ?? ($lhp->unitDiperiksa?->nama_unit ?? '-') }}
                            </span>
                            @if($units->count() > 1)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800" title="{{ $units->pluck('nama_unit')->join(', ') }}">
                                    +{{ $units->count() - 1 }} unit lainnya
                                </span>
                            @endif
                        </div>

                        {{-- 2. Nama Program --}}
                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-300 leading-snug">
                            <a href="{{ route('lhps.show', $lhp) }}" class="font-medium text-gray-800 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                {{ $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->nama_program ?? '-' }}
                            </a>
                            @if($lhp->auditAssignment?->auditProgramDetail?->nama_detail_program)
                                <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ $lhp->auditAssignment->auditProgramDetail->nama_detail_program }}
                                </div>
                            @endif
                        </div>

                        {{-- 3. Nomor LHP & Badges --}}
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <a href="{{ route('lhps.show', $lhp) }}" class="font-mono text-xs font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50 px-2 py-0.5 rounded border border-blue-200/80 dark:border-blue-800/40 hover:bg-blue-100 dark:hover:bg-blue-900/60 transition-colors">
                                {{ $lhp->nomor_lhp }}
                            </a>
                            @if(isset($sharedNomorMap[$lhp->nomor_lhp]))
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/40 px-1.5 py-0.5 rounded border border-purple-200 dark:border-purple-800" title="Nomor LHP ini mencakup {{ $sharedNomorMap[$lhp->nomor_lhp] }} unit kerja terpisah">
                                    Kolektif ({{ $sharedNomorMap[$lhp->nomor_lhp] }} unit)
                                </span>
                            @endif
                            @if($lhp->is_nihil)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/40">
                                    🌿 Bebas Temuan (Nihil)
                                </span>
                            @endif
                            @php
                                $irban = $lhp->irban ?? $lhp->auditAssignment?->auditProgramDetail?->tim;
                            @endphp
                            @if($irban)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 border border-gray-200/80 dark:bg-gray-800/80 dark:text-gray-300 dark:border-gray-700">
                                    <svg class="w-3 h-3 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span>{{ Str::startsWith(strtolower($irban), ['irban', 'tim', 'semua']) ? $irban : 'Irban ' . $irban }}</span>
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Kategori --}}
                    <td class="px-4 py-4 text-center border-r border-gray-100 dark:border-gray-800/60">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $kategoriBadge }}">
                            {{ $k ?: '-' }}
                        </span>
                    </td>

                    {{-- Tanggal LHP --}}
                    <td class="px-4 py-4 whitespace-nowrap border-r border-gray-100 dark:border-gray-800/60">
                        <div class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                            {{ $lhp->tanggal_lhp ? $lhp->tanggal_lhp->translatedFormat('d M Y') : '-' }}
                        </div>
                    </td>

                    {{-- Progress TL --}}
                    <td class="px-4 py-4 border-r border-gray-100 dark:border-gray-800/60">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ min(100, max(0, $persen)) }}%"></div>
                            </div>
                            <span class="text-[11px] font-bold text-gray-600 dark:text-gray-400">{{ $persenLabel }}% Selesai</span>
                        </div>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-4 py-4 text-right whitespace-nowrap">
                        <div class="flex justify-end items-center gap-1.5">
                            <a href="{{ route('lhps.show', $lhp) }}"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-blue-700 dark:hover:bg-blue-950/40 dark:hover:text-blue-300 transition-all shadow-2xs hover:scale-105 active:scale-95"
                               title="Lihat Detail LHP">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
                            </a>
                            <a href="{{ route('lhps.edit', $lhp) }}"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-amber-700 dark:hover:bg-amber-950/40 dark:hover:text-amber-300 transition-all shadow-2xs hover:scale-105 active:scale-95"
                               title="Edit LHP">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 17H9v-2.828l9.414-9.586z" stroke-width="2"/></svg>
                            </a>
                            <button type="button"
                               onclick="window.requestSecurePdf('lhp_preview', '{{ $lhp->id }}', 'LHP: {{ addslashes($lhp->nomor_lhp) }}')"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:border-green-300 hover:bg-green-50 hover:text-green-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-green-700 dark:hover:bg-green-950/40 dark:hover:text-green-300 transition-all shadow-2xs hover:scale-105 active:scale-95 cursor-pointer"
                               title="Unduh / Pratinjau PDF">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-width="2"/></svg>
                            </button>
                            <button type="button" onclick="openDeleteModal('single', '{{ $lhp->uuid }}')"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-red-700 dark:hover:bg-red-950/40 dark:hover:text-red-300 transition-all shadow-2xs hover:scale-105 active:scale-95"
                                    title="Hapus LHP">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-full text-gray-300">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012 2h2a2 2 0 002-2M9 5a2 2 0 012 2h2a2 2 0 012 2" stroke-width="1.5"/></svg>
                            </div>
                            <p class="text-sm text-gray-400 font-medium italic">Tidak ada data LHP.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </form>

    @if($lhps->hasPages())
    <div class="flex items-center justify-center border-t border-gray-200 px-5 py-3.5 dark:border-gray-800">
        {{ $lhps->links() }}
    </div>
    @endif
</div>

{{-- Modal Konfirmasi Hapus --}}
<div id="delete-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 transition-all duration-300 ease-out opacity-0">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
    <div id="modal-content" class="relative w-full max-w-md transform rounded-3xl bg-white p-8 shadow-2xl transition-all duration-300 ease-out scale-95 opacity-0 dark:bg-gray-900 border border-white/10">
        <div class="flex flex-col items-center text-center">
            <div class="relative mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/20">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v2m3 3h7M3 7h18"/>
                </svg>
            </div>
            <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Konfirmasi Hapus</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400" id="modal-message"></p>
            <div class="mt-8 flex w-full gap-3">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 rounded-2xl border border-gray-200 bg-white py-3 text-sm font-semibold text-gray-600 dark:bg-transparent dark:text-gray-400">
                    Batal
                </button>
                <button type="button" id="confirm-delete-btn"
                    class="flex-1 rounded-2xl bg-red-600 py-3 text-sm font-semibold text-white hover:bg-red-700 active:scale-95 transition-all">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Form Hapus Satuan --}}
<form id="delete-single-form" method="POST" class="hidden">@csrf @method('DELETE')</form>

<script>
    let currentDeleteType = '';
    let currentId = null;

    const modal        = document.getElementById('delete-modal');
    const modalContent = document.getElementById('modal-content');
    const btnBulk      = document.getElementById('btn-bulk-delete');
    const checkAll     = document.getElementById('check-all');
    const checkboxes   = document.querySelectorAll('.check-item');
    const countSpan    = document.getElementById('count-selected');

    // Auto-dismiss notifikasi
    function dismissAlert() {
        const alert = document.getElementById('alert-success');
        if (alert) {
            alert.classList.add('opacity-0', 'scale-95');
            setTimeout(() => alert.remove(), 500);
        }
    }
    if (document.getElementById('alert-success')) {
        setTimeout(dismissAlert, 5000);
    }

    // Checkbox bulk
    function toggleBulkUI() {
        const checked   = document.querySelectorAll('.check-item:checked');
        const actionBar = document.getElementById('bulk-action-bar');
        const badgeSpan = document.getElementById('count-selected-badge');
        const textSpan  = document.getElementById('count-selected-text');

        if (actionBar) {
            if (checked.length > 0) {
                actionBar.classList.remove('hidden');
                actionBar.classList.add('flex');
            } else {
                actionBar.classList.add('hidden');
                actionBar.classList.remove('flex');
            }
        }
        if (badgeSpan) badgeSpan.innerText = checked.length;
        if (textSpan)  textSpan.innerText  = checked.length;
        if (countSpan) countSpan.innerText = checked.length;

        if (checkAll && checkboxes.length > 0) {
            checkAll.checked = checked.length === checkboxes.length;
        }
    }

    function uncheckAll() {
        if (checkAll) checkAll.checked = false;
        checkboxes.forEach(cb => cb.checked = false);
        toggleBulkUI();
    }

    if (checkAll) {
        checkAll.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            toggleBulkUI();
        });
    }
    checkboxes.forEach(cb => cb.addEventListener('change', toggleBulkUI));

    // Modal hapus
    function openDeleteModal(type, id = null) {
        currentDeleteType = type;
        currentId = id;
        document.getElementById('modal-message').innerText = type === 'bulk'
            ? `Anda akan menghapus ${document.querySelectorAll('.check-item:checked').length} data LHP.`
            : 'Apakah Anda yakin ingin menghapus data LHP ini secara permanen?';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('opacity-0', 'scale-95');
            modalContent.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function closeDeleteModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    if (btnBulk) {
        btnBulk.addEventListener('click', () => openDeleteModal('bulk'));
    }

    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            this.disabled  = true;
            this.innerText = 'Memproses...';
            if (currentDeleteType === 'bulk') {
                const mainForm = document.getElementById('main-form');
                mainForm.onsubmit = null;
                mainForm.submit();
            } else {
                const form   = document.getElementById('delete-single-form');
                form.action  = `/lhps/${currentId}`;
                form.submit();
            }
        });
    }
</script>
@endsection