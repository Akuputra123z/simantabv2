@extends('layouts.app')

@section('content')
<div class="space-y-5 sm:space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        
        <div class="px-5 py-4 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    Kode Rekomendasi
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola daftar kode rekomendasi sistem</p>
            </div>

            <a href="{{ route('kode-rekomendasi.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Data
            </a>
        </div>

        <div class="px-5 pb-4 sm:px-6">
            <form method="GET" class="relative max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari kode atau deskripsi..."
                       class="w-full border border-gray-200 dark:border-gray-800 bg-transparent rounded-lg pl-10 pr-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:text-white">
            </form>
        </div>

        <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto">
                    <table class="w-full min-w-[900px]">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.02]">
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Kode</p>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Numerik</p>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Kategori</p>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Deskripsi</p>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Status</p>
                                </th>
                                <th class="px-5 py-3 text-right">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider pr-4">Aksi</p>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($data as $item)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                <td class="px-5 py-4">
                                    <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                        {{ $item->kode }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400 font-mono">{{ $item->kode_numerik }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $item->kategori }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400 line-clamp-1 max-w-xs" title="{{ $item->deskripsi }}">
                                        {{ $item->deskripsi }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <form action="{{ route('kode-rekomendasi.toggle', $item->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" 
                                            class="inline-block rounded-full px-3 py-1 text-theme-xs font-medium transition-all
                                            {{ $item->is_active 
                                                ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' 
                                                : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400' }}">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('kode-rekomendasi.edit', $item->id) }}"
                                           class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('kode-rekomendasi.show', $item->id) }}" class="text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors" title="Lihat Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        <form action="{{ route('kode-rekomendasi.destroy', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-500 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-200 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400">Data tidak ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
@endsection