@extends('layouts.app')

@section('content')

<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-4 sm:px-6 sm:py-5">
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Kode Temuan</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Referensi PermenPAN 42/2011 Lampiran 2.1</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('kode-temuan.index') }}" method="GET" class="hidden sm:block">
                    <input type="text" name="search" placeholder="Cari kode/kelompok..." 
                        class="h-10 rounded-lg border border-gray-200 bg-transparent px-4 py-2 text-sm text-gray-800 outline-none focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white">
                </form>
                
                <a href="{{ route('kode-temuan.create') }}" class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kode
                </a>
            </div>
        </div>

        <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
            <div class="max-w-full overflow-x-auto custom-scrollbar border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full min-w-[1000px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.02]">
                            <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">KODE & KELOMPOK</th>
                            <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">SUB KELOMPOK</th>
                            <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">KLASIFIKASI</th>
                            <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">DESKRIPSI</th>
                            <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                            <td class="px-5 py-4">
                                <span class="block font-bold text-gray-800 text-sm dark:text-white/90">{{ $item->kode }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $item->kelompok }}</span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ $item->sub_kelompok }}
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $labels = [
                                        1 => ['bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400', 'Ketidakpatuhan'],
                                        2 => ['bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400', 'SPI'],
                                        3 => ['bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400', '3E']
                                    ];
                                    $label = $labels[$item->kel] ?? ['bg-gray-50 text-gray-700', 'N/A'];
                                @endphp
                                <span class="{{ $label[0] }} inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                    {{ $label[1] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 max-w-xs leading-relaxed" title="{{ strip_tags($item->deskripsi) }}">
                                    {{ strip_tags($item->deskripsi) }}
                                </p>
                            </td>
                            <td class="px-5 py-4 text-right text-sm">
                                <div class="flex justify-end items-center gap-3">
                                    <a href="{{ route('kode-temuan.show', $item->id) }}" class="text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors" title="Lihat Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('kode-temuan.edit', $item->id) }}" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Edit Data">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    
                                    <form action="{{ route('kode-temuan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Hapus Data">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p>Tidak ada data ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-5 flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} entries
                </p>
                <div class="flex-1 flex justify-end">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection