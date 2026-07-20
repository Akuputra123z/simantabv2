@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 py-6">
 
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Rekomendasi</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Daftar seluruh rekomendasi dari hasil temuan audit</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('recommendations.create') }}" 
               class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm shadow-blue-500/10 active:scale-[0.98] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Rekomendasi Baru
            </a>
        </div>
    </div>
 
    {{-- Flash Message --}}
    @if (session('success'))
    <div class="mb-5 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-800/60 dark:bg-green-900/20">
        <svg class="h-5 w-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
    </div>
    @endif
 
  {{-- Filter Bar --}}
    <form method="GET" class="mb-5 flex flex-col gap-3 md:flex-row md:items-center">
        <!-- Input Search -->
        <div class="flex-1 min-w-0 md:max-w-md">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 dark:text-gray-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nomor LHP atau uraian..."
                       class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-500">
            </div>
        </div>
        
        <!-- Select Filters & Buttons Group -->
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            
            <!-- Wrapper Dropdown Status (Aman untuk Tom Select) -->
            <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
                <select name="status" data-auto-submit class="tom-select-filter">
                    <option value="">Semua Status</option>
                    <option value="belum_ditindaklanjuti" {{ request('status') === 'belum_ditindaklanjuti' ? 'selected' : '' }}>Belum TL</option>
                    <option value="proses"                {{ request('status') === 'proses' ? 'selected' : '' }}>Dalam Proses</option>
                    <option value="selesai"               {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <!-- Wrapper Dropdown Jenis (Aman untuk Tom Select) -->
            <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
                <select name="jenis" data-auto-submit class="tom-select-filter">
                    <option value="">Semua Jenis</option>
                    <option value="uang"         {{ request('jenis') === 'uang' ? 'selected' : '' }}>Uang</option>
                    <option value="barang"       {{ request('jenis') === 'barang' ? 'selected' : '' }}>Barang</option>
                    <option value="administrasi" {{ request('jenis') === 'administrasi' ? 'selected' : '' }}>Administrasi</option>
                </select>
            </div>

            <!-- Wrapper Dropdown Kategori (Aman untuk Tom Select) -->
            <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
                <select name="kategori" data-auto-submit class="tom-select-filter">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k }}" {{ request('kategori') === $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Submit -->
            <button type="submit"
                    class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg bg-gray-950 text-sm font-medium text-white hover:bg-gray-850 focus:outline-none focus:ring-2 focus:ring-gray-950/20 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-500/20 transition-colors whitespace-nowrap">
                Filter
            </button>

            <!-- Tombol Reset -->
            @if (request()->hasAny(['search','status','jenis','kategori']))
            <a href="{{ route('recommendations.index') }}"
               class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors whitespace-nowrap">
                Reset
            </a>
            @endif
        </div>
    </form>
 
    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">No. LHP / Temuan</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">Uraian Rekomendasi</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">Jenis</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">Kategori</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">Nilai Rekom</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-center whitespace-nowrap">Progress</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">Status</th>
                        <th class="px-5 py-3.5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($recommendations as $r)
                    @php
                        $statusCfg = match($r->status) {
                            'selesai'               => ['label' => 'Selesai',      'class' => 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-300'],
                            'proses'                => ['label' => 'Dalam Proses', 'class' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-900/30 dark:text-yellow-300'],
                            default                 => ['label' => 'Belum TL',     'class' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-300'],
                        };
                        $jenisCfg = match($r->jenis_rekomendasi) {
                            'uang'         => ['label' => 'Uang',    'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'],
                            'barang'       => ['label' => 'Barang',  'class' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'],
                            'administrasi' => ['label' => 'Admin',   'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                            default        => ['label' => ucfirst($r->jenis_rekomendasi ?? 'N/A'), 'class' => 'bg-gray-100 text-gray-600'],
                        };
                        $progress = method_exists($r, 'progress') ? $r->progress() : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <p class="font-medium text-gray-900 dark:text-white text-sm">
                                {{ $r->temuan?->lhp?->nomor_lhp ?? '-' }}
                            </p>
                            <p class="mt-0.5 text-[10px] uppercase font-bold text-gray-400">
                                {{ $r->temuan?->kodeTemuan?->kode ?? 'KODE TIDAK ADA' }}
                            </p>
                        </td>
                        <td class="max-w-xs px-5 py-4">
                            <p class="line-clamp-2 text-sm text-gray-700 dark:text-gray-300" title="{{ $r->uraian_rekom }}">{{ $r->uraian_rekom }}</p>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $jenisCfg['class'] }}">
                                {{ $jenisCfg['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @php
                                $kategoriLabel = $r->kategori ?? '-';
                                $kategoriCls = match($r->kategori) {
                                    'PKPT'   => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                                    'BPK'    => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'BPKP'   => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
                                    'ITPROV' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                    'ITDA'   => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                    'LAINNYA' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    default  => 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $kategoriCls }}">
                                {{ $kategoriLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if ($r->jenis_rekomendasi === 'uang' && $r->nilai_rekom > 0)
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($r->nilai_rekom, 0, ',', '.') }}</p>
                                <p class="text-[10px] text-gray-400">Sisa: Rp {{ number_format($r->nilai_sisa ?? 0, 0, ',', '.') }}</p>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex flex-col items-center gap-1">
                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div class="h-full rounded-full transition-all duration-300 {{ $progress >= 100 ? 'bg-green-500' : ($progress > 0 ? 'bg-yellow-500' : 'bg-gray-300') }}"
                                         style="width: {{ min(100,$progress) }}%"></div>
                                </div>
                                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">{{ number_format($progress,0) }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold uppercase tracking-tight ring-1 ring-inset {{ $statusCfg['class'] }}">
                                {{ $statusCfg['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('recommendations.show', $r) }}"
                                   class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white transition-colors" title="Detail">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('recommendations.edit', $r) }}"
                                   class="rounded-md p-1.5 text-gray-400 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20 dark:hover:text-blue-400 transition-colors" title="Edit">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                               <form action="{{ route('recommendations.destroy', $r) }}" method="POST" class="inline" 
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus rekomendasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400 transition-colors" 
                                        title="Hapus">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-12 w-12 text-gray-200 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 font-medium">Data rekomendasi tidak ditemukan</p>
                                <a href="{{ route('recommendations.create') }}" class="mt-2 text-xs text-blue-600 dark:text-blue-400 hover:underline">Buat rekomendasi pertama?</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($recommendations->hasPages())
        <div class="border-t border-gray-200 px-5 py-3.5 dark:border-gray-700 bg-gray-50/30">
            {{ $recommendations->links() }}
        </div>
        @endif
    </div>
 
</div>
@endsection