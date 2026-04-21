@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    
    {{-- Header & Action --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tighter text-gray-900 dark:text-white uppercase">Daftar Temuan</h1>
            <p class="text-sm text-gray-500">Manajemen seluruh temuan audit dari berbagai LHP.</p>
        </div>
        <div class="flex gap-3">
            <button class="flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                FILTER
            </button>
            {{-- Tombol tambah biasanya diarahkan dari detail LHP, tapi ini cadangan --}}
            <a href="{{ route('temuan.create') }}" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all">
                + TAMBAH TEMUAN
            </a>
        </div>
    </div>

    {{-- Statistik Bento Grid (Mini) --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Temuan</span>
            <div class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ number_format($temuans->total()) }}</div>
        </div>
        <div class="rounded-2xl border border-red-100 bg-red-50/50 p-5 dark:border-red-900/20 dark:bg-red-900/10">
            <span class="text-[10px] font-black uppercase tracking-widest text-red-500">Belum Selesai</span>
            <div class="mt-1 text-2xl font-black text-red-600">
                {{ number_format($temuans->where('status_tl', 'belum_ditindaklanjuti')->count()) }}
            </div>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-5 dark:border-blue-900/20 dark:bg-blue-900/10">
            <span class="text-[10px] font-black uppercase tracking-widest text-blue-500">Dalam Proses</span>
            <div class="mt-1 text-2xl font-black text-blue-600">
                {{ number_format($temuans->where('status_tl', 'dalam_proses')->count()) }}
            </div>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5 dark:border-emerald-900/20 dark:bg-emerald-900/10">
            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Selesai (TL)</span>
            <div class="mt-1 text-2xl font-black text-emerald-600">
                {{ number_format($temuans->where('status_tl', 'selesai')->count()) }}
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 dark:bg-gray-900/50 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-4 font-black text-[10px] uppercase tracking-wider text-gray-500">Info LHP & Kode</th>
                        <th class="px-6 py-4 font-black text-[10px] uppercase tracking-wider text-gray-500">Kondisi / Uraian</th>
                        <th class="px-6 py-4 font-black text-[10px] uppercase tracking-wider text-gray-500 text-right">Nilai Temuan</th>
                        <th class="px-6 py-4 font-black text-[10px] uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-4 font-black text-[10px] uppercase tracking-wider text-gray-500 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($temuans as $temuan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-blue-600">{{ $temuan->lhp->nomor_lhp ?? 'N/A' }}</div>
                            <div class="text-[11px] font-medium text-gray-500 mt-1 uppercase">
                                Kode: {{ $temuan->kodeTemuan->kode ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="max-w-xs truncate font-medium text-gray-700 dark:text-gray-300" title="{{ $temuan->kondisi }}">
                                {{ Str::limit($temuan->kondisi, 60) }}
                            </div>
                            <div class="flex gap-2 mt-1">
                                <span class="text-[10px] text-gray-400 italic">Rekomendasi: {{ $temuan->recommendations->count() }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($temuan->total_nilai_temuan, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClasses = [
                                    'belum_ditindaklanjuti' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'dalam_proses'          => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'selesai'               => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                ];
                                $statusLabel = [
                                    'belum_ditindaklanjuti' => 'Belum TL',
                                    'dalam_proses'          => 'Proses',
                                    'selesai'               => 'Selesai',
                                ];
                                $currentStatus = $temuan->status_tl ?? 'belum_ditindaklanjuti';
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase {{ $statusClasses[$currentStatus] ?? $statusClasses['belum_ditindaklanjuti'] }}">
                                {{ $statusLabel[$currentStatus] ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('temuans.edit', $temuan->id) }}" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('temuans.destroy', $temuan->id) }}" method="POST" onsubmit="return confirm('Hapus temuan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                            Belum ada data temuan yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($temuans->hasPages())
        <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
            {{ $temuans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection