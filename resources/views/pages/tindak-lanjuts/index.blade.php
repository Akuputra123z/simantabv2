@extends('layouts.app')

@php
$tlAttachments = $tindakLanjuts->mapWithKeys(fn($tl) => [
    $tl->id => $tl->attachments->map(fn($a) => [
        'id'   => $a->id,
        'name' => $a->file_name,
        'url'  => Storage::url($a->file_path),
        'is_image' => $a->isImage(),
    ]),
]);
@endphp

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('lampiran', {
        open: false,
        attachments: [],
        selectedIndex: 0,

        openLampiran(tlId) {
            this.attachments = window.__tlAttachments[tlId] ?? [];
            this.selectedIndex = 0;
            this.open = true;
        },

        close() {
            this.open = false;
            this.attachments = [];
            this.selectedIndex = 0;
        },

        get selected() {
            return this.attachments[this.selectedIndex] ?? null;
        },

        selectFile(index) {
            this.selectedIndex = index;
        },

        get hasMultiple() {
            return this.attachments.length > 1;
        }
    });
});

window.__tlAttachments = @json($tlAttachments);
</script>
@endpush

@section('content')

    {{-- HEADER --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tindak Lanjut</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola dan pantau progres tindak lanjut hasil pemeriksaan.</p>
        </div>
        <a href="{{ route('tindak-lanjuts.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Tindak Lanjut
        </a>
    </div>

    {{-- STAT CARDS --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-50 dark:bg-green-900/20">
                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Total Lunas</p>
                <p class="mt-0.5 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats->total_lunas ?? 0 }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Sedang Berjalan</p>
                <p class="mt-0.5 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats->total_berjalan ?? 0 }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-yellow-50 dark:bg-yellow-900/20">
                <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Menunggu Verifikasi</p>
                <p class="mt-0.5 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats->total_menunggu ?? 0 }}</p>
            </div>
        </div>

    </div>

    {{-- Filter Bar --}}
    <form method="GET" class="mb-6 flex flex-col gap-3 md:flex-row md:items-center">
        <div class="flex-1 min-w-0">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 dark:text-gray-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari uraian rekomendasi atau nomor LHP..."
                       class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-500">
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
                <select name="status" data-auto-submit class="tom-select-filter">
                    <option value="">Semua Status</option>
                    <option value="lunas"               {{ request('status') == 'lunas'               ? 'selected' : '' }}>Lunas</option>
                    <option value="berjalan"            {{ request('status') == 'berjalan'            ? 'selected' : '' }}>Berjalan</option>
                    <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                </select>
            </div>

            <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
                <select name="status_opd" data-auto-submit class="tom-select-filter">
                    <option value="">Semua OPD</option>
                    <option value="belum_upload" {{ request('status_opd') == 'belum_upload' ? 'selected' : '' }}>Belum Upload</option>
                    <option value="draft"       {{ request('status_opd') == 'draft'       ? 'selected' : '' }}>Draft</option>
                    <option value="dikirim"     {{ request('status_opd') == 'dikirim'     ? 'selected' : '' }}>Terkirim</option>
                </select>
            </div>

            <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
                <select name="kategori" data-auto-submit class="tom-select-filter">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k }}" {{ request('kategori') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors whitespace-nowrap">
                Filter
            </button>

            @if (request()->hasAny(['search','status','status_opd','kategori']))
            <a href="{{ route('tindak-lanjuts.index') }}"
               class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors whitespace-nowrap">
                Reset
            </a>
            @endif
        </div>
    </form>

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/70 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-5 py-3.5 w-[22%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Rekomendasi / LHP</th>
                        <th class="px-5 py-3.5 w-[10%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Unit</th>
                        <th class="px-5 py-3.5 w-[9%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Jenis</th>
                        <th class="px-5 py-3.5 w-[9%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Kategori</th>
                        <th class="px-5 py-3.5 w-[12%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-right">Nilai TL</th>
                        <th class="px-5 py-3.5 w-[10%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-center">Status</th>
                        <th class="px-5 py-3.5 w-[12%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-center">OPD</th>
                        <th class="px-5 py-3.5 w-[16%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tindakLanjuts as $tl)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">

                        {{-- Rekomendasi / LHP --}}
                        <td class="px-5 py-4 max-w-[280px]">
                            <div class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $tl->recommendation->temuan->lhp->nomor_lhp ?? 'N/A' }}
                            </div>
                            <div class="text-[11px] text-gray-400 mt-0.5">
                                {{ $tl->nama_program ?? '-' }}
                            </div>
                            <p class="mt-0.5 text-[11px] text-gray-400 line-clamp-2 leading-snug break-words">
                                {{ $tl->recommendation->uraian_rekom ?? '-' }}
                            </p>
                            @if($tl->irban)
                            <span class="mt-1 inline-block px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/20 text-[9px] font-semibold text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30">
                                Irban: {{ $tl->irban }}
                            </span>
                            @endif
                        </td>

                        {{-- Unit --}}
                        <td class="px-5 py-4">
                            <div class="text-[11px] leading-snug text-gray-600 dark:text-gray-400 break-words max-w-[140px]">
                                {{ $tl->recommendation?->temuan?->lhp?->unitDiperiksa?->label ?? $tl->recommendation?->temuan?->lhp?->unitDiperiksa?->nama_unit ?? '-' }}
                            </div>
                        </td>

                        {{-- Jenis --}}
                        <td class="px-5 py-4">
                            @php
                                $jenisCls = $tl->jenis_penyelesaian === 'cicilan'
                                    ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'
                                    : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300';
                            @endphp
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase {{ $jenisCls }}">
                                {{ ucfirst($tl->jenis_penyelesaian) }}
                            </span>
                        </td>

                        {{-- Kategori --}}
                        <td class="px-5 py-4">
                            @php
                                $kategoriLabel = $tl->kategori ?? '-';
                                $kategoriCls = match($tl->kategori) {
                                    'PKPT'   => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                                    'BPK'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'BPKP'   => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
                                    'ITPROV' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                    'ITDA'   => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                    'LAINNYA' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    default  => 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase {{ $kategoriCls }}">
                                {{ $kategoriLabel }}
                            </span>
                        </td>

                        {{-- Nilai TL --}}
                        <td class="px-5 py-4 text-right">
                            @if($tl->recommendation?->isUang())
                                <span class="text-sm font-mono font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    Rp {{ number_format($tl->nilai_tindak_lanjut, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 uppercase">
                                    {{ $tl->recommendation?->jenis_rekomendasi ?? '-' }}
                                </span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4 text-center">
                            @php
                                $statusCls = match($tl->status_verifikasi) {
                                    'lunas'               => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'berjalan'            => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'menunggu_verifikasi' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    default               => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusCls }}">
                                {{ str_replace('_', ' ', $tl->status_verifikasi) }}
                            </span>
                        </td>

                        {{-- Status OPD --}}
                        <td class="px-5 py-4 text-center">
                            @php
                                $opdCls = match($tl->status_opd) {
                                    'dikirim' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'draft' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    default => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                };
                                $opdLabel = $tl->status_opd === 'dikirim' ? 'Terkirim' : ($tl->status_opd === 'draft' ? 'Draft' : '-');
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $opdCls }}">
                                {{ $opdLabel }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('tindak-lanjuts.show', $tl->id) }}"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors"
                                   title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
                                </a>
                                <button type="button"
                                        x-on:click="$store.lampiran.openLampiran({{ $tl->id }})"
                                        x-show="{{ $tl->attachments->isNotEmpty() ? 'true' : 'false' }}"
                                        class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors"
                                        title="Lampiran">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                </button>
                                <a href="{{ route('tindak-lanjuts.edit', $tl->id) }}"
                                   class="p-1.5 text-gray-400 hover:text-amber-600 transition-colors"
                                   title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 17H9v-2.828l9.414-9.586z" stroke-width="2"/></svg>
                                </a>
                                <form action="{{ route('tindak-lanjuts.destroy', $tl->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus tindak lanjut ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 text-gray-400 hover:text-red-600 transition-colors"
                                            title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Data tidak ditemukan</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Coba ubah filter pencarian</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($tindakLanjuts->hasPages())
        <div class="flex flex-col gap-3 border-t border-gray-100 px-5 py-3.5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
            {{-- Info --}}
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $tindakLanjuts->firstItem() }}</span>
                –
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $tindakLanjuts->lastItem() }}</span>
                dari
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $tindakLanjuts->total() }}</span>
                data
            </p>

            {{-- Page Buttons --}}
            <div class="flex items-center gap-1">

                {{-- Prev --}}
                @if($tindakLanjuts->onFirstPage())
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300 dark:border-gray-700 dark:bg-white/[0.02] cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $tindakLanjuts->previousPageUrl() }}"
                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition-colors dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:text-blue-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif

                {{-- Page Numbers --}}
                @foreach($tindakLanjuts->getUrlRange(
                    max(1, $tindakLanjuts->currentPage() - 2),
                    min($tindakLanjuts->lastPage(), $tindakLanjuts->currentPage() + 2)
                ) as $page => $url)
                    @if($page == $tindakLanjuts->currentPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white shadow-sm">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-xs font-medium text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition-colors dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:text-blue-400">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($tindakLanjuts->hasMorePages())
                    <a href="{{ $tindakLanjuts->nextPageUrl() }}"
                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition-colors dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:text-blue-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300 dark:border-gray-700 dark:bg-white/[0.02] cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif

            </div>
        </div>
        @else
        {{-- Jika hanya 1 halaman, tetap tampilkan info total --}}
        <div class="border-t border-gray-100 px-5 py-3.5 dark:border-gray-800">
            <p class="text-sm text-gray-400 dark:text-gray-500">
                Total <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $tindakLanjuts->total() }}</span> data
            </p>
        </div>
        @endif
    </div>

{{-- Lampiran Modal --}}
<template x-teleport="body">
    <div x-show="$store.lampiran.open"
         class="fixed inset-0 z-[999999] flex items-center justify-center bg-black/50 p-4"
         x-cloak>
        <div class="flex w-full max-w-5xl flex-col rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
             style="height: 90vh;"
             @click.outside="$store.lampiran.close()">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Lampiran</h3>
                <button @click="$store.lampiran.close()"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="$store.lampiran.attachments.length === 0">
                <div class="flex flex-1 items-center justify-center">
                    <p class="text-sm text-gray-400">Tidak ada file.</p>
                </div>
            </template>

            <template x-if="$store.lampiran.attachments.length > 0">
                <div class="flex flex-1 flex-col overflow-hidden">
                    {{-- File tabs --}}
                    <div x-show="$store.lampiran.hasMultiple"
                         class="flex gap-2 overflow-x-auto border-b border-gray-100 px-6 py-3 dark:border-gray-800">
                        <template x-for="(att, idx) in $store.lampiran.attachments" :key="att.id">
                            <button @click="$store.lampiran.selectFile(idx)"
                                    :class="idx === $store.lampiran.selectedIndex
                                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'"
                                    class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors truncate max-w-[200px]">
                                <span x-text="att.name"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Preview area --}}
                    <div class="flex flex-1 items-center justify-center bg-gray-100 dark:bg-gray-800/50">
                        <iframe :src="$store.lampiran.selected?.url"
                                class="h-full w-full"
                                frameborder="0">
                        </iframe>
                    </div>

                    {{-- Footer with link --}}
                    <div class="flex items-center justify-between border-t border-gray-100 px-6 py-3 dark:border-gray-800">
                        <span class="truncate text-sm text-gray-500 dark:text-gray-400" x-text="$store.lampiran.selected?.name"></span>
                        <a :href="$store.lampiran.selected?.url" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Buka di tab baru
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

@endsection