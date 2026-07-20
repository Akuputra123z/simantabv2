@extends('layouts.app')

@section('content')

@if(session('success'))
<div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-500/30 dark:bg-green-500/15">
    <div class="flex items-start gap-3">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Berhasil!</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ session('success') }}</p>
        </div>
    </div>
</div>
@endif

<div class="space-y-6">
    <div class="relative overflow-visible rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Header --}}
        <div class="border-b border-gray-100 px-5 py-5 dark:border-gray-800">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Unit</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Kelola informasi BUMD, Sekolah, dan Desa</p>
                </div>
                <div>
                    <a href="{{ route('unit-diperiksa.create') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-500/20 transition-all hover:bg-blue-700 sm:w-auto">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Unit
                    </a>
                </div>
            </div>

            {{-- Filter --}}
            <form id="filterForm" action="{{ route('unit-diperiksa.index') }}" method="GET" class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-800/60">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">

                    <div class="relative sm:col-span-6 lg:col-span-5">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari unit..."
                               class="h-10 w-full rounded-lg border border-gray-200 bg-transparent pl-9 pr-4 text-sm text-gray-800 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white">
                    </div>

                    <div class="sm:col-span-3 lg:col-span-3">
                        <select id="kategori" name="kategori" data-no-ts>
                            <option value="">Semua Kategori</option>
                            @foreach(['BUMD', 'Sekolah', 'OPD', 'Desa', 'BLUD'] as $cat)
                            <option value="{{ $cat }}" {{ request('kategori') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-3 lg:col-span-4">
                        <select id="kecamatan" name="kecamatan" data-no-ts>
                            <option value="">Semua Kecamatan</option>
                            @foreach($kecamatanList as $kec)
                            <option value="{{ $kec }}" {{ request('kecamatan') == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Cari
                    </button>
                    @if(request()->anyFilled(['search', 'kategori', 'kecamatan']))
                    <a href="{{ route('unit-diperiksa.index') }}"
                       class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-500 transition-all hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="p-5 sm:p-6">
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="min-w-[600px] w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Nama Unit</th>
                            <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Kategori</th>
                            <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Kecamatan</th>
                            <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                        @forelse($data as $item)
                        <tr class="transition-colors hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                            <td class="px-5 py-4 font-semibold text-gray-800 dark:text-white/90">{{ $item->nama_unit }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-indigo-600 ring-1 ring-inset ring-indigo-600/10 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    {{ $item->kategori }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $item->nama_kecamatan ?? '-' }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-3 text-gray-400">
                                    <a href="{{ route('unit-diperiksa.show', $item->id) }}" class="transition-colors hover:text-gray-800 dark:hover:text-white" title="Detail">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('unit-diperiksa.edit', $item->id) }}" class="transition-colors hover:text-blue-600 dark:hover:text-blue-400" title="Edit">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('unit-diperiksa.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="transition-colors hover:text-red-600 dark:hover:text-red-400" title="Hapus">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-gray-400">Tidak ada data ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($data->hasPages())
            <div class="mt-5 flex flex-col gap-4 border-t border-gray-100 pt-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-center text-sm text-gray-600 dark:text-gray-400 sm:text-left">
                    Menampilkan <span class="font-medium">{{ $data->firstItem() ?? 0 }}</span> hingga <span class="font-medium">{{ $data->lastItem() ?? 0 }}</span> dari <span class="font-medium">{{ $data->total() }}</span>
                </p>
                <div class="flex justify-center sm:justify-end">
                    {{ $data->onEachSide(1)->links('pagination::tailwind') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filterForm');

    new TomSelect('#kategori', {
        create: false,
        controlInput: null,
        onChange: () => form.submit()
    });

    new TomSelect('#kecamatan', {
        create: false,
        onChange: () => form.submit()
    });
});
</script>
@endpush
@endsection
