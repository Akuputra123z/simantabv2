@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 p-4 lg:p-8">

    {{-- Alert Notification --}}
    @if(session('success') || session('error'))
    <div id="global-alert" class="transform transition-all duration-500 ease-in-out rounded-2xl border px-5 py-4 {{ session('success') ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/20 dark:bg-emerald-500/10' : 'border-rose-200 bg-rose-50 dark:border-rose-500/20 dark:bg-rose-500/10' }}">
        <div class="flex items-start gap-3">
            <div class="{{ session('success') ? 'text-emerald-500' : 'text-rose-500' }}">
                @if(session('success'))
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                @else
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                @endif
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium {{ session('success') ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                    {{ session('success') ? 'Berhasil' : 'Perhatian' }}
                </p>
                <p class="mt-1 text-sm {{ session('success') ? 'text-emerald-600 dark:text-emerald-400/90' : 'text-rose-600 dark:text-rose-400/90' }}">
                    {{ session('success') ?? session('error') }}
                </p>
            </div>
            <button onclick="dismissAlert('global-alert')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    @endif

    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        
        {{-- Header Utama --}}
        <div class="flex flex-col gap-5 border-b border-gray-100 p-6 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between bg-gray-50/30 dark:bg-transparent">
            <div class="flex items-center gap-4">
                <a href="{{ route('audit-program.index') }}" class="group flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                    <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-medium tracking-tight text-gray-900 dark:text-white">{{ $auditProgram->nama_program }}</h1>
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs text-blue-700 dark:bg-blue-500/10 dark:text-blue-300 border border-blue-100 dark:border-blue-500/20">
                            TA {{ $auditProgram->tahun }}
                        </span>
                        @php
                            $kat = $auditProgram->kategori;
                            $katBadge = match($kat) {
                                'PKPT' => 'bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-300 dark:border-indigo-500/20',
                                'BPK'  => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20',
                                'BPKP' => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20',
                                'ITPROV' => 'bg-cyan-50 text-cyan-700 border-cyan-100 dark:bg-cyan-500/10 dark:text-cyan-300 dark:border-cyan-500/20',
                                'ITDA'   => 'bg-teal-50 text-teal-700 border-teal-100 dark:bg-teal-500/10 dark:text-teal-300 dark:border-teal-500/20',
                                default  => 'bg-gray-50 text-gray-700 border-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase border {{ $katBadge }}">
                            {{ $kat ?? '-' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pusat Kendali Program Kerja Pengawasan Tahunan.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
                    Status: <span class="uppercase text-blue-600 font-medium">{{ $auditProgram->status_dinamis }}</span>
                </span>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 gap-4 border-b border-gray-100 p-6 dark:border-gray-800 lg:grid-cols-4 bg-gray-50/50 dark:bg-white/[0.01]">
            @php
                $stats = [
                    ['label' => 'Sub-Program', 'value' => $details->total(), 'color' => 'text-gray-900 dark:text-white', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                    ['label' => 'Target ST', 'value' => $auditProgram->target_assignment, 'color' => 'text-blue-600', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Realisasi LHP', 'value' => $auditProgram->sudah_lhp, 'color' => 'text-emerald-600', 'icon' => 'M5 13l4 4L19 7'],
                    ['label' => 'Total Progres', 'value' => ($auditProgram->progress_persen ?? 0) . '%', 'color' => 'text-amber-500', 'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z'],
                ];
            @endphp
            @foreach($stats as $stat)
            <div class="group rounded-2xl bg-white p-5 border border-gray-100 transition-all dark:bg-white/[0.03] dark:border-gray-800">
                <div class="flex justify-between items-start">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-gray-400">{{ $stat['label'] }}</p>
                    <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="{{ $stat['icon'] }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="mt-2">
                    <span class="text-2xl font-light tracking-tight {{ $stat['color'] }}">{{ $stat['value'] }}</span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Sub-Program Header --}}
        <div class="flex flex-col gap-4 p-6 lg:flex-row lg:items-center border-b border-gray-50 dark:border-gray-800/50">
            <div class="flex items-center gap-4">
                <div class="flex flex-col">
                    <h3 class="text-[10px] uppercase tracking-[0.2em] text-gray-400 dark:text-gray-500">
                        Daftar Sub-Program Kerja
                    </h3>
                    <div class="mt-1 h-0.5 w-6 rounded-full bg-blue-500"></div>
                </div>
            </div>
            <div class="lg:ml-auto flex flex-wrap items-center gap-3">
                {{-- Search --}}
                <form method="GET" action="{{ route('audit-program.show', $auditProgram->id) }}" class="relative">
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Cari sub-program..."
                           class="w-48 rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    @if(request('search'))
                        <a href="{{ route('audit-program.show', $auditProgram->id) }}"
                           class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
                {{-- Export --}}
                <div class="flex gap-1">
                    <button type="button" data-export="pdf"
                            class="btn-export inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-xs text-rose-600 transition hover:bg-rose-50 hover:border-rose-200 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-rose-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-7a1 1 0 00-1-1H8a1 1 0 00-1 1v7"/></svg>
                        PDF
                    </button>
                    <button type="button" data-export="excel"
                            class="btn-export inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-xs text-emerald-600 transition hover:bg-emerald-50 hover:border-emerald-200 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-emerald-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </button>
                </div>

                <button onclick="openImportModal()" 
                   class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-600 transition hover:bg-gray-50 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </button>

                <a href="{{ route('audit-program-detail.create', ['audit_program_id' => $auditProgram->id]) }}" 
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm text-white transition hover:bg-blue-700 shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah
                </a>
            </div>
        </div>

        {{-- Bulk Delete Form --}}
        <form id="bulk-delete-form" action="{{ route('audit-program-detail.bulk-delete') }}" method="POST">
            @csrf
        </form>

        {{-- Export Form --}}
        <form id="export-form" method="POST" class="hidden">
            @csrf
        </form>

        {{-- Bulk Action Bar --}}
        <div id="bulk-bar" class="hidden flex items-center justify-between gap-4 px-6 py-3 bg-rose-50 border-b border-rose-200 dark:bg-rose-500/10 dark:border-rose-500/20">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-rose-200 text-rose-700 text-[11px] font-black dark:bg-rose-500/30 dark:text-rose-300" id="selected-count">0</span>
                <span class="text-sm font-medium text-rose-700 dark:text-rose-300">sub-program dipilih</span>
            </div>
            <button type="button" onclick="confirmBulkDelete()"
                    class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition-all shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Terpilih
            </button>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/50 text-[10px] uppercase tracking-[0.15em] text-gray-400 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-4 w-12 text-center">
                            <input type="checkbox" id="select-all" 
                                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-4 w-12 text-center">No</th>
                        <th class="px-4 py-4">Detail Program</th>
                        <th class="px-4 py-4">Objek / Jenis</th>
                        <th class="px-4 py-4 text-right">Anggaran</th>
                        <th class="px-4 py-4">Penugasan</th>
                        <th class="px-4 py-4 text-center">Status</th>
                        <th class="px-4 py-4 text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($details as $index => $detail)
                    @php
                        $totalST = $detail->assignments_count;
                        $selesaiST = $detail->assignments_selesai_count;
                        $progSub = $totalST > 0 ? round(($selesaiST / $totalST) * 100) : 0;
                    @endphp
                    <tr class="group transition-colors hover:bg-gray-50/50 dark:hover:bg-blue-500/5">
                        <td class="px-4 py-5 text-center">
                            <input type="checkbox" name="ids[]" value="{{ $detail->id }}" form="bulk-delete-form"
                                   class="row-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        <td class="px-4 py-5 text-center text-gray-400 font-mono">
                            {{ $details->firstItem() + $index }}
                        </td>
                        <td class="px-4 py-5">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $detail->nama_detail_program }}</span>
                        </td>
                        <td class="px-4 py-5">
                            <div class="flex flex-col gap-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px]">{{ $detail->objek_pengawasan ?? '-' }}</div>
                                <div class="text-[10px] text-blue-500 uppercase font-semibold tracking-wider">{{ $detail->jenis_kegiatan ?? '-' }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-5 text-right whitespace-nowrap font-mono text-gray-900 dark:text-white">
                            Rp {{ number_format($detail->anggaran, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-5 min-w-[140px]">
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center justify-between text-[10px] font-medium uppercase">
                                    <span class="text-gray-400">{{ $totalST }} ST</span>
                                    <span class="text-blue-500">{{ $progSub }}%</span>
                                </div>
                                <div class="h-1 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div class="h-full rounded-full bg-blue-500 transition-all duration-1000" style="width: {{ $progSub }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-5 text-center">
                            <span class="inline-flex rounded-lg px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide 
                                {{ $detail->status == 'aktif' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20' : 'bg-gray-50 text-gray-400 border border-gray-100 dark:bg-gray-800 dark:border-gray-700' }}">
                                {{ $detail->status }}
                            </span>
                        </td>
                        <td class="px-4 py-5">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('audit-program-detail.show', $detail->id) }}" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all dark:hover:bg-emerald-500/10">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('audit-program-detail.edit', $detail->id) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all dark:hover:bg-blue-500/10">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                                <form action="{{ route('audit-program-detail.destroy', $detail->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus sub-program ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all dark:hover:bg-rose-500/10">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center text-gray-400 italic bg-gray-50/20 dark:bg-transparent">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>{{ request('search') ? 'Sub-program tidak ditemukan.' : 'Belum ada sub-program kerja.' }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($details->hasPages())
        <div class="px-6 py-5 border-t border-gray-100 bg-gray-50/30 dark:bg-transparent dark:border-gray-800">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">
                    Showing <span class="text-gray-900 dark:text-white">{{ $details->firstItem() }}</span> to <span class="text-gray-900 dark:text-white">{{ $details->lastItem() }}</span> of {{ $details->total() }}
                </p>

                <nav class="flex items-center gap-1.5">
                    @if ($details->onFirstPage())
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-gray-200 dark:text-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $details->previousPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-white hover:shadow-sm transition-all text-gray-500 dark:hover:bg-gray-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    @foreach ($details->getUrlRange(max(1, $details->currentPage() - 1), min($details->lastPage(), $details->currentPage() + 1)) as $page => $url)
                        @if ($page == $details->currentPage())
                            <span class="h-8 min-w-[32px] flex items-center justify-center rounded-full bg-slate-900 text-white text-[11px] font-black tracking-tighter dark:bg-white dark:text-slate-900 shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="h-8 min-w-[32px] flex items-center justify-center rounded-full hover:bg-white text-gray-400 text-[11px] font-black tracking-tighter transition-all hover:text-slate-900 dark:hover:bg-gray-800">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    @if ($details->hasMorePages())
                        <a href="{{ $details->nextPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-white hover:shadow-sm transition-all text-gray-500 dark:hover:bg-gray-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-gray-200 dark:text-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal Import --}}
<div id="modal-import" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeImportModal()"></div>
        
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-3xl bg-white p-8 text-left shadow-2xl transition-all dark:bg-gray-900 sm:my-8">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white" id="modal-title">Import Sub-Program</h3>
                <button onclick="closeImportModal()" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('audit-program-detail.import') }}" method="POST" enctype="multipart/form-data" class="mt-6">
                @csrf
                <input type="hidden" name="audit_program_id" value="{{ $auditProgram->id }}">
                
                <div class="space-y-5">
                    {{-- Mode Import --}}
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-3 font-black">Mode Import</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-start gap-3 cursor-pointer rounded-xl border-2 p-4 transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 border-gray-200 bg-white hover:border-blue-300 dark:border-gray-700 dark:bg-gray-800 dark:has-[:checked]:bg-blue-500/10">
                                <input type="radio" name="mode" value="add" checked
                                       class="mt-0.5 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Tambah Data</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Menambahkan data baru tanpa menghapus data yang sudah ada.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer rounded-xl border-2 p-4 transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/50 border-gray-200 bg-white hover:border-amber-300 dark:border-gray-700 dark:bg-gray-800 dark:has-[:checked]:bg-amber-500/10">
                                <input type="radio" name="mode" value="replace"
                                       class="mt-0.5 h-4 w-4 border-gray-300 text-amber-600 focus:ring-amber-500">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Ganti Data</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Hapus semua data lama, lalu import data baru dari file.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-8 transition-colors hover:border-blue-400 dark:border-gray-700 dark:bg-gray-800/50">
                        <div id="upload-icon" class="text-blue-500">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <span class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400" id="file-name-label">Klik untuk pilih file Excel</span>
                        <p class="mt-1 text-xs text-gray-400">Format .xlsx atau .xls (Maks. 10MB)</p>
                        <input type="file" name="file" id="file-input" class="hidden" accept=".xlsx, .xls" required onchange="updateFileName(this)">
                    </label>

                   <div class="rounded-xl bg-blue-50/50 p-4 border border-blue-100 dark:bg-blue-500/5 dark:border-blue-500/20">
    <div class="flex gap-3">
        <svg class="h-5 w-5 shrink-0 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="space-y-3">
            <div>
                <p class="text-sm font-bold text-blue-900 dark:text-blue-100">Panduan Kolom Excel</p>
                <p class="text-xs text-blue-700/80 dark:text-blue-300/80 leading-relaxed mt-1">
                    Urutan kolom wajib sesuai: <span class="font-medium text-blue-900 dark:text-blue-200">1. Nama Detail, 2. Jenis, 3. Objek, 4. Ruang Lingkup, 5. Personil, 6. Tujuan, 7. Anggaran, 8. Risiko, 9. Target, 10. Jadwal, 11. Tim, 12. Status.</span>
                </p>
            </div>

            <a href="{{ route('audit-program-detail.download-template') }}" 
                class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                UNDUH TEMPLATE EXCEL
            </a>
        </div>
    </div>
</div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeImportModal()" class="flex-1 rounded-xl border border-gray-200 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">Batal</button>
                    <button type="submit" class="flex-1 rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Alert Handling
    window.dismissAlert = (id) => {
        const alert = document.getElementById(id);
        if (alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }
    };
    
    // Auto hide alert
    if(document.getElementById('global-alert')) {
        setTimeout(() => dismissAlert('global-alert'), 6000);
    }

    // Modal Handling
    const importModal = document.getElementById('modal-import');
    window.openImportModal = () => { 
        importModal.classList.remove('hidden'); 
        document.body.style.overflow = 'hidden'; 
    };
    window.closeImportModal = () => { 
        importModal.classList.add('hidden'); 
        document.body.style.overflow = 'auto'; 
    };

    // File Input Styling
    window.updateFileName = (input) => {
        const label = document.getElementById('file-name-label');
        const iconContainer = document.getElementById('upload-icon');
        
        if (input.files.length > 0) {
            label.textContent = input.files[0].name;
            label.classList.add('text-blue-600', 'font-bold');
            iconContainer.innerHTML = '<svg class="h-10 w-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        } else {
            label.textContent = "Klik untuk pilih file Excel";
            label.classList.remove('text-blue-600', 'font-bold');
        }
    };

    // Keyboard ESC to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !importModal.classList.contains('hidden')) closeImportModal();
    });

    // ─── Bulk Delete ────────────────────────────────────────────────────────
    const selectAll = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkBar = document.getElementById('bulk-bar');
    const selectedCount = document.getElementById('selected-count');

    function updateBulkBar() {
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        if (checked > 0) {
            selectedCount.textContent = checked;
            bulkBar.classList.remove('hidden');
        } else {
            bulkBar.classList.add('hidden');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkBar();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    window.confirmBulkDelete = function() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return;
        if (!confirm('Hapus ' + checked.length + ' sub-program terpilih?')) return;

        const form = document.getElementById('bulk-delete-form');
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });
        form.submit();
    };

    // ─── Export (PDF / Excel) ──────────────────────────────────────────────
    const exportForm = document.getElementById('export-form');
    const searchInput = document.querySelector('input[name="search"]');

    document.querySelectorAll('.btn-export').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.export;
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const search = searchInput ? searchInput.value.trim() : '';

            // Bersihkan form dari input sebelumnya
            exportForm.innerHTML = '';
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            exportForm.appendChild(csrf);

            if (checked.length > 0) {
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    exportForm.appendChild(input);
                });
            } else if (search) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'search';
                input.value = search;
                exportForm.appendChild(input);
            }

            const action = type === 'pdf'
                ? '{{ route("audit-program.export-pdf", $auditProgram->id) }}'
                : '{{ route("audit-program.export-excel", $auditProgram->id) }}';

            exportForm.action = action;
            exportForm.target = type === 'pdf' ? '_blank' : '';
            exportForm.submit();
        });
    });
</script>
@endsection