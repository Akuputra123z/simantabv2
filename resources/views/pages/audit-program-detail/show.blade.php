@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 p-4 lg:p-8">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-5">
            <a href="{{ route('audit-program.show', $detail->audit_program_id) }}"
               class="flex h-11 w-11 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 text-gray-500 transition-all hover:bg-gray-100 hover:scale-105 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>

            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                        {{ $detail->nama_detail_program }}
                    </h1>
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-600 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20">
                        {{ $detail->jenis_kegiatan ?? 'Kegiatan' }}
                    </span>
                </div>
                <p class="mt-1 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-gray-400">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                    {{ $detail->objek_pengawasan }}
                </p>
                @if($detail->auditProgram)
                <p class="mt-1 flex items-center gap-2 text-[10px] text-gray-400">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                    {{ $detail->auditProgram->nama_program }}
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase
                        {{ match($detail->auditProgram->kategori) {
                            'PKPT' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
                            'BPK'  => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
                            'BPKP' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400',
                            'ITPROV' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
                            'ITDA'   => 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400',
                            default  => 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                        } }}">
                        {{ $detail->auditProgram->kategori }}
                    </span>
                </p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('audit-program-detail.edit', $detail->id) }}"
               class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-xs font-bold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                Edit Detail
            </a>
            <button class="flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-slate-800 shadow-md active:scale-95 dark:bg-blue-600 dark:hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Laporan
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $stats = [
                ['label' => 'Status Program', 'value' => $detail->status == 'aktif' ? 'Aktif' : 'Draft', 'sub' => 'Status saat ini', 'color' => $detail->status == 'aktif' ? 'text-emerald-600' : 'text-amber-600'],
                ['label' => 'Total Anggaran', 'value' => 'Rp ' . number_format($detail->anggaran, 0, ',', '.'), 'sub' => 'Alokasi dana PKPT', 'color' => 'text-blue-600'],
                ['label' => 'Tingkat Risiko', 'value' => $detail->tingkat_resiko ?? 'Rendah', 'sub' => 'Klasifikasi audit', 'color' => 'text-gray-900 dark:text-white'],
                ['label' => 'Total Personil', 'value' => ($detail->personil ?? '0') . ' Orang', 'sub' => 'Kebutuhan tim', 'color' => 'text-gray-900 dark:text-white'],
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:border-blue-300 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-gray-400">{{ $stat['label'] }}</p>
            <p class="mt-2 text-lg font-extrabold {{ $stat['color'] }}">{{ $stat['value'] }}</p>
            <p class="mt-1 text-[10px] text-gray-400 font-medium">{{ $stat['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Content Layout --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        {{-- Main Info --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-50 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-500">Informasi Fundamental</h3>
                </div>
                <div class="p-8 space-y-8">
                    <div>
                        <h4 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-600">
                            <span class="h-1 w-5 rounded-full bg-blue-600"></span>
                            Tujuan Pengawasan
                        </h4>
                        <p class="text-sm leading-relaxed font-medium text-gray-600 dark:text-gray-300">
                            {{ $detail->tujuan }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400">
                                <span class="h-1 w-5 rounded-full bg-gray-300"></span>
                                Ruang Lingkup
                            </h4>
                            <p class="text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                                {{ $detail->ruang_lingkup ?? 'Belum ada deskripsi ruang lingkup.' }}
                            </p>
                        </div>
                        <div>
                            <h4 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400">
                                <span class="h-1 w-5 rounded-full bg-gray-300"></span>
                                Struktur Tim
                            </h4>
                            <p class="text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                                {{ $detail->tim ?? 'Belum ada deskripsi tim.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Assignment --}}
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 p-6 dark:border-gray-800">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Daftar Surat Tugas</h3>
                        <p class="text-[10px] font-medium uppercase tracking-wider text-gray-400">Monitoring Penugasan Lapangan</p>
                    </div>

                    <a href="{{ route('audit-assignment.create', ['program_detail_id' => $detail->id]) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-2 text-xs font-bold text-blue-600 transition hover:bg-blue-600 hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Buat Surat
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-800/30">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">Nomor & Objek</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">Pelaksana</th>
                                <th class="px-6 py-4 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">Progress</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($detail->assignments as $assignment)
                            <tr class="group transition hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                <td class="px-6 py-5">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors">
                                        {{ $assignment->nomor_surat }}
                                    </p>
                                    <div class="mt-1.5 flex items-center gap-1.5 text-[10px] font-bold uppercase text-gray-400">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $assignment->unitDiperiksas->pluck('nama_unit')->join(', ') ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $assignment->nama_tim ?? ($assignment->ketuaTim->name ?? '-') }}
                                    </p>
                                    <p class="mt-1 text-[10px] font-medium text-gray-400">
                                        Mulai: {{ $assignment->tanggal_mulai?->translatedFormat('d M Y') }}
                                    </p>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-tight
                                        {{ $assignment->status == 'selesai'
                                            ? 'bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400'
                                            : 'bg-blue-50 text-blue-600 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400'
                                        }}">
                                        {{ $assignment->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('audit-assignment.show', $assignment->id) }}"
                                       class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition-all hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-700 dark:bg-gray-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="rounded-full bg-gray-50 p-3 dark:bg-gray-800">
                                            <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <p class="mt-2 text-sm font-medium text-gray-400 italic">Belum ada penugasan yang dibuat.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Custom --}}
                @if ($detail->assignments instanceof \Illuminate\Pagination\AbstractPaginator && $detail->assignments->hasPages())
                <div class="px-6 py-5 border-t border-gray-100 bg-gray-50/30 dark:bg-transparent dark:border-gray-800">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">
                            Record {{ $detail->assignments->firstItem() }} - {{ $detail->assignments->lastItem() }} / {{ $detail->assignments->total() }}
                        </p>
                        <nav class="flex items-center gap-1">
                            {{-- Previous --}}
                            <a href="{{ $detail->assignments->onFirstPage() ? '#' : $detail->assignments->previousPageUrl() }}" 
                               class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 transition-all {{ $detail->assignments->onFirstPage() ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white hover:shadow-sm' }} dark:border-gray-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                            </a>

                            {{-- Numbers --}}
                            @foreach ($detail->assignments->renderableItems() as $item)
                                @if (is_array($item))
                                    @foreach ($item as $page => $url)
                                        <a href="{{ $url }}" class="h-8 min-w-[32px] flex items-center justify-center rounded-lg text-[11px] font-black transition-all 
                                            {{ $page == $detail->assignments->currentPage() 
                                                ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-md' 
                                                : 'text-gray-400 hover:text-slate-900 dark:hover:bg-gray-800' }}">
                                            {{ $page }}
                                        </a>
                                    @endforeach
                                @endif
                            @endforeach

                            {{-- Next --}}
                            <a href="{{ $detail->assignments->hasMorePages() ? $detail->assignments->nextPageUrl() : '#' }}" 
                               class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 transition-all {{ !$detail->assignments->hasMorePages() ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white hover:shadow-sm' }} dark:border-gray-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </nav>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Timeline Card --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/20">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Timeline</h3>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">Jadwal Audit</p>
                    </div>
                </div>

                <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
                    <p class="text-lg font-extrabold text-slate-800 dark:text-blue-400">
                        {{ App\Helpers\DateHelper::formatJadwal($detail->jadwal) }}
                    </p>
                    <p class="mt-2 text-[10px] font-medium leading-relaxed text-gray-500 dark:text-gray-400 uppercase tracking-tighter">
                        Estimasi durasi pelaksanaan berdasarkan PKPT tahun berjalan.
                    </p>
                </div>
            </div>

            {{-- Info Alert --}}
            <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-6 text-white shadow-xl dark:bg-blue-700">
                <svg class="absolute -right-4 -top-4 h-24 w-24 opacity-10" fill="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="relative mb-3 text-xs font-bold uppercase tracking-widest text-blue-400 dark:text-blue-200">Catatan Penting</h3>
                <p class="relative text-xs leading-relaxed opacity-90 font-medium">
                    Laporan Hasil Pemeriksaan (LHP) hanya dapat diproses apabila seluruh berkas verifikasi lapangan telah diunggah dan disetujui oleh Supervisor.
                </p>
                <div class="relative mt-5 flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-blue-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-400 animate-ping"></span>
                    System Monitored
                </div>
            </div>
        </div>
    </div>
</div>
@endsection