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

{{-- Filter --}}
<form method="GET" action="{{ url()->current() }}" class="mb-6 flex flex-col gap-3 md:flex-row md:items-center">
    <div class="flex-1 min-w-0">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 dark:text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nomor LHP..."
                   class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-500">
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
        <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
            <select name="tahun" data-auto-submit>
                <option value="">Semua Tahun</option>
                @foreach(range(date('Y'), date('Y') - 3) as $y)
                    <option value="{{ $y }}" @selected(request('tahun') == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
            <select name="kategori" data-auto-submit>
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $k)
                    <option value="{{ $k }}" @selected(request('kategori') == $k)>{{ $k }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg bg-gray-950 text-sm font-medium text-white hover:bg-gray-850 focus:outline-none focus:ring-2 focus:ring-gray-950/20 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-500/20 transition-colors whitespace-nowrap">
            Filter
        </button>
        @if (request()->hasAny(['search', 'tahun', 'kategori', 'sort', 'direction']))
        <a href="{{ route('lhps.index') }}"
           class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors whitespace-nowrap">
            Reset
        </a>
        @endif
    </div>
</form>

{{-- Table --}}
<form id="main-form" action="{{ route('lhps.bulkDelete') }}" method="POST" onsubmit="return false;">
    @csrf
    @method('DELETE')

    {{-- Bulk Action Banner Bar (User Friendly Top Position) --}}
    <div id="bulk-action-bar" class="hidden mb-4 p-3 px-4 rounded-xl bg-red-50/90 dark:bg-red-950/40 border border-red-200/80 dark:border-red-800/60 flex items-center justify-between shadow-xs transition-all duration-300">
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

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50/80 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-800">
                <tr>
                    <th class="px-3.5 py-3 w-[4%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center border-r border-gray-200/70 dark:border-gray-800/70">
                        <input type="checkbox" id="check-all"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 cursor-pointer">
                    </th>
                    <th class="px-4 py-3 w-[25%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-r border-gray-200/70 dark:border-gray-800/70">
                        <div class="flex items-center justify-between">
                            <a href="{{ $sortUrl('nama_program') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Nama Program & Nomor LHP</span>
                            </a>
                            <a href="{{ $sortUrl('nama_program') }}">
                                {!! $renderSortIcons('nama_program') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 w-[18%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-r border-gray-200/70 dark:border-gray-800/70">
                        <div class="flex items-center justify-between">
                            <a href="{{ $sortUrl('unit_diperiksa') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Unit Diperiksa</span>
                            </a>
                            <a href="{{ $sortUrl('unit_diperiksa') }}">
                                {!! $renderSortIcons('unit_diperiksa') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 w-[13%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-r border-gray-200/70 dark:border-gray-800/70">
                        <div class="flex items-center justify-between">
                            <a href="{{ $sortUrl('tanggal_lhp') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Tanggal LHP</span>
                            </a>
                            <a href="{{ $sortUrl('tanggal_lhp') }}">
                                {!! $renderSortIcons('tanggal_lhp') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 w-[13%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center border-r border-gray-200/70 dark:border-gray-800/70">
                        <div class="flex items-center justify-center">
                            <a href="{{ $sortUrl('progress') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Progress TL</span>
                            </a>
                            <a href="{{ $sortUrl('progress') }}">
                                {!! $renderSortIcons('progress') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 w-[8%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center border-r border-gray-200/70 dark:border-gray-800/70">
                        <div class="flex items-center justify-center">
                            <a href="{{ $sortUrl('kategori') }}" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>Kategori</span>
                            </a>
                            <a href="{{ $sortUrl('kategori') }}">
                                {!! $renderSortIcons('kategori') !!}
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 w-[10%] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Aksi</th>
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
                    $k = $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->kategori;
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

                    {{-- Nama Program & Nomor LHP --}}
                    <td class="px-4 py-4 border-r border-gray-100 dark:border-gray-800/60">
                        <div class="mb-1.5">
                            <span class="font-mono text-xs font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50 px-2 py-0.5 rounded border border-blue-200/80 dark:border-blue-800/40">
                                {{ $lhp->nomor_lhp }}
                            </span>
                        </div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">
                            <a href="{{ route('lhps.show', $lhp->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                {{ $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->nama_program ?? '-' }}
                            </a>
                        </div>
                        @if($lhp->is_nihil)
                        <div class="mt-1.5">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/40">
                                🌿 Bebas Temuan (Nihil)
                            </span>
                        </div>
                        @endif
                        @if($lhp->auditAssignment?->auditProgramDetail?->nama_detail_program)
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            {{ $lhp->auditAssignment->auditProgramDetail->nama_detail_program }}
                        </div>
                        @endif
                    </td>

                    {{-- Unit Diperiksa --}}
                    <td class="px-4 py-4 border-r border-gray-100 dark:border-gray-800/60">
                        <div class="text-xs font-medium text-gray-800 dark:text-gray-300 leading-relaxed">
                            {{ $lhp->unitDiperiksa?->label ?? $lhp->unitDiperiksa?->nama_unit ?? '-' }}
                        </div>
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

                    {{-- Kategori --}}
                    <td class="px-4 py-4 text-center border-r border-gray-100 dark:border-gray-800/60">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $kategoriBadge }}">
                            {{ $k ?? '-' }}
                        </span>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-4 py-4 text-right">
                        <div class="flex justify-end items-center gap-1">
                            <a href="{{ route('lhps.show', $lhp->id) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Lihat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
                            </a>
                            <a href="{{ route('lhps.edit', $lhp->id) }}" class="p-1.5 text-gray-400 hover:text-amber-600 rounded-md hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 17H9v-2.828l9.414-9.586z" stroke-width="2"/></svg>
                            </a>
                            <a href="{{ route('laporan.preview-pdf-per-lhp', $lhp->id) }}" target="_blank"
                               class="p-1.5 text-gray-400 hover:text-green-600 rounded-md hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors" title="Unduh PDF">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-width="2"/></svg>
                            </a>
                            <button type="button" onclick="openDeleteModal('single', '{{ $lhp->id }}')"
                                    class="p-1.5 text-gray-400 hover:text-red-600 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-20 text-center">
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

    @if($lhps->hasPages())
    <div class="flex items-center justify-center border-t border-gray-100 px-5 py-3.5 dark:border-gray-800">
        {{ $lhps->links() }}
    </div>
    @endif
</div>
</form>

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