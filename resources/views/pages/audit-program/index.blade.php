@extends('layouts.app')

@section('content')

@if(session('success'))
    <div class="mb-6 rounded-xl border border-success-500 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/15">
        <div class="flex items-start gap-3">
            <div class="-mt-0.5 text-success-500">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.70186 12.0001C3.70186 7.41711 7.41711 3.70186 12.0001 3.70186C16.5831 3.70186 20.2984 7.41711 20.2984 12.0001C20.2984 16.5831 16.5831 20.2984 12.0001 20.2984C7.41711 20.2984 3.70186 16.5831 3.70186 12.0001ZM12.0001 1.90186C6.423 1.90186 1.90186 6.423 1.90186 12.0001C1.90186 17.5772 6.423 22.0984 12.0001 22.0984C17.5772 22.0984 22.1494 17.5772 22.0984 12.0001C22.0984 6.423 17.5772 1.90186 12.0001 1.90186ZM15.6197 10.7395C15.9712 10.388 15.9712 9.81819 15.6197 9.46672C15.2683 9.11525 14.6984 9.11525 14.347 9.46672L11.1894 12.6243L9.6533 11.0883C9.30183 10.7368 8.73198 10.7368 8.38051 11.0883C8.02904 11.4397 8.02904 12.0096 8.38051 12.3611L10.553 14.5335C10.7217 14.7023 10.9507 14.7971 11.1894 14.7971C11.428 14.7971 11.657 14.7023 11.8257 14.5335L15.6197 10.7395Z" fill=""/></svg>
            </div>
            <div>
                <h4 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">Berhasil!</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Header PKPT --}}
        <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-4 sm:px-6 sm:py-5">
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Program Kerja Pengawasan Tahunan (PKPT)</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Monitoring realisasi dan progress LHP per program</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('audit-program.index') }}" method="GET" class="hidden sm:flex gap-2">
                    <select name="tahun" onchange="this.form.submit()" 
                        class="h-10 rounded-lg border border-gray-200 bg-transparent px-3 text-sm text-gray-500 outline-none focus:border-blue-300 dark:border-gray-700 dark:bg-gray-900">
                        <option value="">Semua Tahun</option>
                        @foreach(range(date('Y'), date('Y')-3) as $y)
                            <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari program..." 
                        class="h-10 rounded-lg border border-gray-200 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white">
                </form>
                
                <a href="{{ route('audit-program.create') }}" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-all shadow-sm shadow-blue-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Program Baru
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.02]">
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Nama Program & Tahun</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider text-center">Target / Realisasi</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Progress LHP</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                        @forelse($data as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                            <td class="px-5 py-4">
                                <span class="block font-semibold text-gray-800 dark:text-white/90">{{ $item->nama_program }}</span>
                                <span class="text-xs text-gray-500">Tahun {{ $item->tahun }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="text-sm font-bold text-gray-800 dark:text-white">
                                    {{ $item->realisasi_assignment }} <span class="text-gray-400 font-normal">/ {{ $item->target_assignment }}</span>
                                </div>
                                <span class="text-[10px] text-gray-400 uppercase">Assignment</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ $item->progress }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $item->progress }}%</span>
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 block">{{ $item->sudah_lhp }} LHP Selesai</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $statusColor = $item->status == 'berjalan' 
                                        ? 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400' 
                                        : 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $statusColor }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-3 text-gray-400">
                                    <a href="{{ route('audit-program.show', $item->id) }}" class="hover:text-gray-800 dark:hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('audit-program.edit', $item->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('audit-program.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus program ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <p>Belum ada program kerja pengawasan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="mt-5 flex items-center justify-between border-t border-gray-50 pt-5 dark:border-gray-800">
                <p class="text-[11px] font-bold text-gray-400 uppercase">
                    Halaman {{ $data->currentPage() }} dari {{ $data->lastPage() }}
                </p>
                <div>{{ $data->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection