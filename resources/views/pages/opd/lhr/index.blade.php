@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10 space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 dark:border-slate-800 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800">
                    SIAPTUNTAS OPD
                </span>
                <span class="text-xs text-slate-400 font-medium">&bull;</span>
                <span class="text-xs text-slate-500 font-semibold dark:text-slate-400">
                    {{ $userUnitName }}
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Laporan Hasil Reviu (LHR)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Daftar dokumen perencanaan & keuangan perangkat daerah Anda yang telah direviu dan disahkan resmi oleh Inspektorat Daerah.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('opd.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition-all shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard OPD
            </a>
        </div>
    </div>

    {{-- TAB NAVIGATOR: PEMERIKSAAN (LHP) VS REVIU (LHR) --}}
    <div class="flex border-b border-slate-200 dark:border-slate-800">
        <a href="{{ route('opd.tindak-lanjut.index') }}"
           class="inline-flex items-center gap-2 px-5 py-3 border-b-2 font-medium text-xs transition-colors border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Pemeriksaan (LHP)
        </a>
        <a href="{{ route('opd.lhr.index') }}"
           class="inline-flex items-center gap-2 px-5 py-3 border-b-2 font-bold text-xs transition-colors border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400">
            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Reviu Dokumen (LHR)
            <span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                {{ $stats->total_lhr }}
            </span>
        </a>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Dokumen LHR --}}
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900/80">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Naskah LHR</p>
                <p class="mt-0.5 text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats->total_lhr }}</p>
            </div>
        </div>

        {{-- Total Butir Rekomendasi --}}
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900/80">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Rekomendasi</p>
                <p class="mt-0.5 text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats->total_rekomendasi }} Butir</p>
            </div>
        </div>

        {{-- Dalam Proses TL --}}
        <div class="flex items-center gap-4 rounded-xl border border-amber-200/80 bg-white p-4 shadow-xs dark:border-amber-900/40 dark:bg-slate-900/80">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Perlu Tindak Lanjut</p>
                <p class="mt-0.5 text-xl font-bold font-mono text-amber-700 dark:text-amber-400">{{ $stats->total_rekomendasi - $stats->rekomendasi_selesai }} Butir</p>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="flex items-center gap-4 rounded-xl border border-emerald-200/80 bg-white p-4 shadow-xs dark:border-emerald-900/40 dark:bg-slate-900/80">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Tuntas Selesai</p>
                <p class="mt-0.5 text-xl font-bold font-mono text-emerald-700 dark:text-emerald-400">{{ $stats->rekomendasi_selesai }} Butir</p>
            </div>
        </div>
    </div>

    {{-- FILTER TOOLBAR --}}
    <form method="GET" action="{{ route('opd.lhr.index') }}" class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4">
            {{-- Cari Dokumen --}}
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor LHR, Jenis Dokumen..."
                       class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>

            {{-- Tahun Anggaran --}}
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Tahun Anggaran</label>
                <select name="tahun" data-no-ts onchange="this.form.submit()"
                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white cursor-pointer">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $th)
                        <option value="{{ $th }}" @selected(request('tahun') == $th)>{{ $th }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Jenis Dokumen --}}
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Jenis Dokumen Reviu</label>
                <select name="jenis_dokumen_id" data-no-ts onchange="this.form.submit()"
                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white cursor-pointer">
                    <option value="">Semua Dokumen</option>
                    @foreach($jenisDokumens as $jd)
                        <option value="{{ $jd->id }}" @selected(request('jenis_dokumen_id') == $jd->id)>{{ $jd->kode }} - {{ $jd->nama }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="h-9 w-full rounded-lg bg-blue-600 px-4 text-xs font-semibold text-white hover:bg-blue-700 transition">
                    Filter
                </button>
                <a href="{{ route('opd.lhr.index') }}" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300" title="Reset Filter">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </div>
    </form>

    {{-- TABEL DOKUMEN LHR OPD --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-4 w-12 text-center">No</th>
                        <th class="px-5 py-4">Nomor & Tanggal LHR</th>
                        <th class="px-5 py-4">Jenis Dokumen Reviu</th>
                        <th class="px-5 py-4">Tahun Anggaran</th>
                        <th class="px-5 py-4 text-center">Rekomendasi</th>
                        <th class="px-5 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($lhrs as $idx => $lhr)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-4 text-center font-medium text-slate-400">
                                {{ $lhrs->firstItem() + $idx }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900 dark:text-white text-sm font-mono">
                                    {{ $lhr->nomor_lhr ?? 'Belum Dinomori' }}
                                </div>
                                <div class="mt-1 flex items-center gap-2 text-[11px] text-slate-500">
                                    <span>Tanggal Terbit: {{ $lhr->tanggal_lhr ? $lhr->tanggal_lhr->translatedFormat('d F Y') : '-' }}</span>
                                    @if($lhr->auditAssignment?->nomor_surat)
                                        <span>&bull;</span>
                                        <span>SPT: {{ $lhr->auditAssignment->nomor_surat }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    {{ $lhr->jenisDokumen->kode ?? '-' }}
                                </span>
                                <div class="mt-1 text-slate-500 text-[11px]">
                                    {{ $lhr->jenisDokumen->nama ?? '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                TA {{ $lhr->tahun_anggaran }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="font-bold font-mono text-slate-900 dark:text-white">
                                    {{ $lhr->statistik->total_rekomendasi ?? 0 }} Butir
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Selesai: {{ $lhr->statistik->rekomendasi_selesai ?? 0 }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $badgeClass = match($lhr->status) {
                                        \App\Enums\LhrStatus::Terbit       => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200',
                                        \App\Enums\LhrStatus::TindakLanjut => 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200',
                                        \App\Enums\LhrStatus::Selesai      => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200',
                                        default                            => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $badgeClass }}">
                                    {{ $lhr->status->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    {{-- Lihat Detail & Rekomendasi LHR --}}
                                    <a href="{{ route('opd.lhr.show', $lhr->uuid) }}"
                                       class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-xs hover:bg-blue-700 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Lihat Rekomendasi</span>
                                    </a>

                                    {{-- Unduh PDF Resmi --}}
                                    <button type="button"
                                       onclick="window.requestSecurePdf('opd_lhr_pdf', '{{ $lhr->uuid }}', 'LHR: {{ addslashes($lhr->nomor_lhr ?? 'DRAFT') }}')"
                                       title="Unduh Berkas PDF Resmi LHR"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </button>

                                    {{-- Unduh Word --}}
                                    <a href="{{ route('opd.lhr.word', $lhr->uuid) }}"
                                       title="Unduh Naskah Word (.docx)"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 17.5l-1.5-6h1.5l.8 3.8.8-3.8h1.5l.8 3.8.8-3.8H15l-1.5 6h-1.5l-.8-3.6-.8 3.6H8.5z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Belum Ada Dokumen LHR Terbit untuk Unit Anda</p>
                                    <p class="text-xs text-slate-400 mt-1">Dokumen reviu akan tampil otomatis di sini setelah diterbitkan secara resmi oleh Inspektorat Daerah.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($lhrs->hasPages())
            <div class="border-t border-slate-200 p-4 dark:border-slate-800">
                {{ $lhrs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
