@extends('layouts.app')

@section('content')

{{-- Header --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Hasil Reviu (LHR)</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Pengawasan dan reviu dokumen perencanaan & keuangan daerah (Permendagri No. 3 Tahun 2026)
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('lhr.create') }}"
           class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat LHR Baru
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 border border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800 flex items-center gap-2">
        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- Panduan Visual Alur 3 Langkah Mudah --}}
<div x-data="{ showPanduan: false }" class="mb-5 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50/90 to-indigo-50/80 p-4 shadow-sm dark:border-blue-900/60 dark:from-blue-950/40 dark:to-indigo-950/30">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white shadow-sm">💡</span>
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-blue-950 dark:text-blue-200">
                    Panduan Alur Pengoperasian Modul LHR
                </h2>
                <p class="text-xs text-blue-800/80 dark:text-blue-300/80 mt-0.5">
                    Cara cepat memahami alur Create (Buat), Edit (Ubah Informasi), dan Buka/Show (Isi Ruang Kerja)
                </p>
            </div>
        </div>
        <button type="button" @click="showPanduan = !showPanduan"
                class="rounded-lg bg-white/80 px-3 py-1.5 text-xs font-semibold text-blue-700 shadow-sm border border-blue-200 hover:bg-white dark:bg-gray-800 dark:text-blue-300 dark:border-gray-700 transition flex items-center gap-1.5">
            <span x-text="showPanduan ? 'Tutup Panduan' : 'Buka Panduan Alur'"></span>
            <svg class="w-3.5 h-3.5 transition-transform" :class="showPanduan ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
    </div>

    <div x-show="showPanduan" x-collapse class="mt-4 pt-4 border-t border-blue-200/80 dark:border-blue-900/60 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
        <div class="rounded-xl bg-white p-4 border border-blue-100 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-2 font-bold text-blue-700 dark:text-blue-400 mb-1.5">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">1</span>
                <span>Menu CREATE (Buat LHR Baru)</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-[11px]">
                Gunakan tombol <strong>"+ Buat LHR Baru"</strong> di pojok kanan atas untuk menghubungkan <em>Surat Perintah Tugas (SPT)</em>, memilih jenis dokumen (RKPD / KUA-PPAS), OPD, dan pejabat Inspektur penandatangan.
            </p>
        </div>

        <div class="rounded-xl bg-white p-4 border border-blue-100 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-2 font-bold text-blue-700 dark:text-blue-400 mb-1.5">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">2</span>
                <span>Menu SHOW (Ruang Kerja KKP)</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-[11px]">
                Klik tombol biru <strong>"Buka"</strong> pada baris tabel untuk masuk ke Ruang Kerja 3-kolom. Di sinilah auditor mengisi simpulan 14 butir pengujian, mencatat temuan, dan menulis rekomendasi perbaikan.
            </p>
        </div>

        <div class="rounded-xl bg-white p-4 border border-blue-100 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-2 font-bold text-blue-700 dark:text-blue-400 mb-1.5">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">3</span>
                <span>Menu EDIT (Ubah Informasi)</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-[11px]">
                Klik tombol <strong>"Edit"</strong> jika Anda ingin mengoreksi nomor surat LHR, tanggal LHR, tanggal surat permohonan, atau mengganti Inspektur penandatangan tanpa mengubah isi catatan temuan.
            </p>
        </div>
    </div>
</div>

{{-- Quick Filter Pills Fokus RKPD & KUA --}}
<div class="mb-4 flex flex-wrap items-center gap-2">
    <a href="{{ route('lhr.index') }}"
       class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition
       {{ !request('jenis_kode') && !request('jenis_dokumen_id') ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700' }}">
        Semua Dokumen
    </a>
    <a href="{{ route('lhr.index', ['jenis_kode' => 'RKPD']) }}"
       class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition
       {{ request('jenis_kode') === 'RKPD' ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-300' : 'bg-white text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700' }}">
        <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
        Fokus: Reviu RKPD
    </a>
    <a href="{{ route('lhr.index', ['jenis_kode' => 'KUA_PPAS']) }}"
       class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition
       {{ request('jenis_kode') === 'KUA_PPAS' ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-300' : 'bg-white text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700' }}">
        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
        Fokus: Reviu KUA-PPAS
    </a>
</div>

{{-- Card Container --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
    
    {{-- Top Controls Bar: Show entries, Filter Jenis Dokumen, Filter Status, Filter Tahun, Search & Reset --}}
    <form method="GET" action="{{ route('lhr.index') }}" id="filterForm" class="mb-4 px-4">
        @if(request('jenis_kode'))
            <input type="hidden" name="jenis_kode" value="{{ request('jenis_kode') }}">
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            
            {{-- Left Controls: Show Entries, Jenis Dokumen, Status & Tahun Dropdowns --}}
            <div class="flex flex-wrap items-center gap-2.5">
                
                {{-- Entries Per Page --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Show</span>
                    <div class="relative z-20">
                        <select name="per_page" onchange="document.getElementById('filterForm').submit()" data-no-ts
                                class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @foreach([5, 8, 10, 20, 25, 30, 50, 100] as $sz)
                                <option value="{{ $sz }}" @selected(request('per_page', 10) == $sz)>{{ $sz }}</option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                            <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">entries</span>
                </div>

                {{-- Jenis Dokumen Dropdown --}}
                <div class="relative z-20">
                    <select name="jenis_dokumen_id" onchange="document.getElementById('filterForm').submit()" data-no-ts
                            class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Semua Jenis Dokumen</option>
                        @foreach($jenisDokumens as $jd)
                            <option value="{{ $jd->id }}" @selected(request('jenis_dokumen_id') == $jd->id)>{{ $jd->kode }} - {{ $jd->nama }}</option>
                        @endforeach
                    </select>
                    <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                        <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </div>

                {{-- Status Dropdown --}}
                <div class="relative z-20">
                    <select name="status" onchange="document.getElementById('filterForm').submit()" data-no-ts
                            class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Semua Status</option>
                        @foreach(\App\Enums\LhrStatus::cases() as $st)
                            <option value="{{ $st->value }}" @selected(request('status') == $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </select>
                    <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                        <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </div>

                {{-- Tahun Dropdown --}}
                <div class="relative z-20">
                    <select name="tahun" onchange="document.getElementById('filterForm').submit()" data-no-ts
                            class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="" @selected(!request()->filled('tahun'))>Semua Tahun</option>
                        @foreach($tahunList as $th)
                            <option value="{{ $th }}" @selected(request()->filled('tahun') && request('tahun') == $th)>{{ $th }}</option>
                        @endforeach
                    </select>
                    <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                        <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </div>

            </div>

            {{-- Right Controls: Search Box & Reset --}}
            <div class="flex items-center gap-2">
                <div class="relative flex-1 sm:flex-initial">
                    <button type="submit" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""></path>
                        </svg>
                    </button>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search..."
                           class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full sm:w-[220px] rounded-lg border border-gray-300 bg-transparent py-2 pr-3 pl-9 text-xs text-gray-800 placeholder:text-gray-400 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                </div>

                @if (request()->filled('search') || request()->filled('tahun') || request()->filled('jenis_dokumen_id') || request()->filled('status') || request()->filled('jenis_kode') || (request()->filled('per_page') && request('per_page') != 10))
                <a href="{{ route('lhr.index') }}"
                   class="shadow-theme-xs inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tabel Dokumen LHR --}}
    <div class="max-w-full overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="border-y border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-900/40 text-xs font-semibold text-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-4 w-12 text-center">No</th>
                    <th class="px-5 py-4">Perangkat Daerah, Nomor LHR & SPT</th>
                    <th class="px-5 py-4">Jenis Dokumen</th>
                    <th class="px-5 py-4">Progres Reviu &amp; TL</th>
                    <th class="px-5 py-4 text-center">Status</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($lhrs as $idx => $lhr)
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition">
                        <td class="px-5 py-4 text-center font-medium text-gray-500">
                            {{ $lhrs->firstItem() + $idx }}
                        </td>
                        <td class="px-5 py-4">
                            {{-- 1. Perangkat Daerah --}}
                            <div class="font-bold text-gray-900 dark:text-white text-sm">
                                {{ $lhr->unitDiperiksa->nama_unit ?? '-' }}
                            </div>

                            {{-- 2. Nomor LHR --}}
                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                <a href="{{ route('lhr.show', $lhr->uuid) }}"
                                   class="font-mono text-xs font-semibold px-2 py-0.5 rounded border transition-colors {{ $lhr->nomor_lhr ? 'text-blue-700 bg-blue-50 border-blue-200/80 hover:bg-blue-100 dark:text-blue-400 dark:bg-blue-950/50 dark:border-blue-800/40 dark:hover:bg-blue-900/60' : 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100 dark:text-amber-300 dark:bg-amber-950/40 dark:border-amber-800' }}">
                                    {{ $lhr->nomor_lhr ?? 'DRAFT (Belum Terbit)' }}
                                </a>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                    TA {{ $lhr->tahun_anggaran }} &bull; {{ $lhr->tanggal_lhr ? $lhr->tanggal_lhr->format('d/m/Y') : 'Dalam Proses' }}
                                </span>
                            </div>

                            {{-- 3. Nomor SPT & Irban --}}
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-1.5">
                                <div class="flex items-center gap-1">
                                    <span class="font-medium text-gray-600 dark:text-gray-300">SPT:</span>
                                    <span class="font-mono text-[11px] text-gray-700 dark:text-gray-300">{{ $lhr->auditAssignment->nomor_surat ?? '-' }}</span>
                                    @if($lhr->auditAssignment?->tanggal_surat)
                                        <span class="text-gray-400 dark:text-gray-500 text-[10px]">({{ $lhr->auditAssignment->tanggal_surat->format('d/m/Y') }})</span>
                                    @endif
                                </div>

                                @php
                                    $irbanName = $lhr->irban;
                                @endphp
                                @if($irbanName)
                                    <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-200/80 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700" title="Inspektur Pembantu / Wilayah Tim">
                                        <svg class="w-3 h-3 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span>{{ $irbanName }}</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ $lhr->jenisDokumen->kode ?? '-' }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1 line-clamp-1">
                                {{ $lhr->jenisDokumen->nama ?? '-' }}
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $progres = $lhr->getProgresReviuData();
                            @endphp

                            @if($progres->tahap === 'penyusunan')
                                {{-- TAHAP PENYUSUNAN REVIU (Sebelum Terbit) --}}
                                <div class="space-y-1.5 min-w-[180px]">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-medium text-slate-600 dark:text-slate-400">Penyusunan KKP</span>
                                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $progres->persen }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 dark:bg-slate-700 overflow-hidden">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progres->persen }}%"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                                        <span>Rekomendasi Dirumuskan:</span>
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $progres->total_rekom }} butir</span>
                                    </div>
                                </div>
                            @elseif($lhr->status === \App\Enums\LhrStatus::Terbit)
                                {{-- TAHAP TERBIT RESMI: Reviu KKP Selesai 100% & Terbit Resmi --}}
                                <div class="space-y-1.5 min-w-[210px]">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 dark:text-emerald-400">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            <span>Reviu: Selesai 100%</span>
                                        </span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                            100%
                                        </span>
                                    </div>

                                    {{-- Full Green Progress Bar (Reviu 100% Selesai & Terbit Resmi) --}}
                                    <div class="w-full bg-slate-200 rounded-full h-2 dark:bg-slate-700 overflow-hidden">
                                        <div class="bg-emerald-500 h-2 w-full rounded-full transition-all duration-300" title="Reviu KKP 100% Selesai dan LHR Terbit Resmi"></div>
                                    </div>

                                    {{-- Status Tindak Lanjut Rekomendasi OPD --}}
                                    <div class="pt-0.5 space-y-1">
                                        @if($progres->total_rekom === 0)
                                            <span class="inline-flex items-center rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                ✓ Bebas Catatan Rekomendasi
                                            </span>
                                        @else
                                            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                                <span>TL Rekomendasi &bull; Tuntas: {{ $progres->selesai }}/{{ $progres->total_rekom }}</span>
                                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $progres->persen }}%</span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-1.5 text-[10px]">
                                                @if($progres->dikirim_opd > 0)
                                                    <span class="inline-flex items-center gap-1 rounded bg-blue-100 px-1.5 py-0.5 font-bold text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 ring-1 ring-blue-300 animate-pulse">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                                        {{ $progres->dikirim_opd }} Revisi OPD Masuk
                                                    </span>
                                                @endif

                                                @if($progres->ditolak_opd > 0)
                                                    <span class="inline-flex items-center gap-1 rounded bg-rose-100 px-1.5 py-0.5 font-semibold text-rose-800 dark:bg-rose-900/50 dark:text-rose-300" title="Dikembalikan untuk perbaikan">
                                                        {{ $progres->ditolak_opd }} Perlu Koreksi
                                                    </span>
                                                @endif

                                                @if($progres->draft_opd > 0 && $progres->dikirim_opd === 0)
                                                    <span class="inline-flex items-center rounded bg-amber-50 px-1.5 py-0.5 font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                                        {{ $progres->draft_opd }} Draf OPD
                                                    </span>
                                                @endif

                                                @if($progres->selesai === $progres->total_rekom)
                                                    <span class="inline-flex items-center rounded bg-emerald-100 px-1.5 py-0.5 font-semibold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                                        ✓ Tuntas Diverifikasi
                                                    </span>
                                                @elseif($progres->dikirim_opd === 0 && $progres->draft_opd === 0 && $progres->selesai === 0 && $progres->ditolak_opd === 0)
                                                    <span class="text-slate-400 dark:text-slate-500 italic">
                                                        Menunggu Tindak Lanjut OPD
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                {{-- TAHAP TINDAK LANJUT & SELESAI (Pasca Terbit & Ada Aksi TL) --}}
                                <div class="space-y-1.5 min-w-[210px]">
                                    <div class="flex items-center justify-between text-[11px] pb-1 border-b border-slate-100 dark:border-slate-800">
                                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 dark:text-emerald-400">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            <span>Reviu: Selesai 100%</span>
                                        </span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">
                                            TL OPD
                                        </span>
                                    </div>

                                    @php
                                        $persenDikirim = $progres->total_rekom > 0 ? round(($progres->dikirim_opd / $progres->total_rekom) * 100, 1) : 0;
                                    @endphp

                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-medium text-slate-600 dark:text-slate-400">
                                            @if($progres->total_rekom === 0)
                                                Bebas Catatan
                                            @else
                                                TL Rekomendasi &bull; Tuntas: {{ $progres->selesai }}/{{ $progres->total_rekom }}
                                            @endif
                                        </span>
                                        <div class="flex items-center gap-1.5 font-bold">
                                            <span class="{{ $progres->persen >= 100 ? 'text-emerald-600 dark:text-emerald-400' : ($progres->persen > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400') }}">
                                                {{ $progres->persen }}%
                                            </span>
                                            @if($persenDikirim > 0)
                                                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium" title="Revisi OPD Masuk: {{ $progres->dikirim_opd }} butir ({{ $persenDikirim }}%)">
                                                    (+{{ $persenDikirim }}% revisi)
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Stacked Progress Bar: Hijau (Selesai) + Biru (Revisi Masuk OPD) --}}
                                    <div class="w-full bg-slate-200 rounded-full h-2 dark:bg-slate-700 overflow-hidden flex">
                                        @if($progres->total_rekom === 0)
                                            <div class="bg-emerald-500 h-2 w-full"></div>
                                        @else
                                            <div class="bg-emerald-500 h-2 transition-all duration-300" style="width: {{ $progres->persen }}%" title="Selesai Diverifikasi: {{ $progres->selesai }} butir"></div>
                                            @if($persenDikirim > 0)
                                                <div class="bg-blue-500 h-2 transition-all duration-300" style="width: {{ $persenDikirim }}%" title="Revisi OPD Masuk: {{ $progres->dikirim_opd }} butir"></div>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- Status Butir Tindak Lanjut OPD --}}
                                    <div class="flex flex-wrap items-center gap-1.5 text-[10px] pt-0.5">
                                        @if($progres->total_rekom === 0)
                                            <span class="inline-flex items-center rounded bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                Nihil Catatan Rekomendasi
                                            </span>
                                        @else
                                            @if($progres->dikirim_opd > 0)
                                                <span class="inline-flex items-center gap-1 rounded bg-blue-100 px-1.5 py-0.5 font-bold text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 ring-1 ring-blue-300 dark:ring-blue-800 animate-pulse" title="Revisi OPD telah dikirim & menunggu ditelaah">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                                    {{ $progres->dikirim_opd }} Revisi OPD Masuk
                                                </span>
                                            @endif

                                            @if($progres->ditolak_opd > 0)
                                                <span class="inline-flex items-center gap-1 rounded bg-rose-100 px-1.5 py-0.5 font-semibold text-rose-800 dark:bg-rose-900/50 dark:text-rose-300" title="Dikembalikan untuk perbaikan">
                                                    {{ $progres->ditolak_opd }} Perlu Koreksi
                                                </span>
                                            @endif

                                            @if($progres->draft_opd > 0 && $progres->dikirim_opd === 0)
                                                <span class="inline-flex items-center rounded bg-amber-50 px-1.5 py-0.5 font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                                    {{ $progres->draft_opd }} Draf OPD
                                                </span>
                                            @endif

                                            @if($progres->selesai === $progres->total_rekom)
                                                <span class="inline-flex items-center rounded bg-emerald-100 px-1.5 py-0.5 font-semibold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                                    ✓ Tuntas Diverifikasi
                                                </span>
                                            @elseif($progres->dikirim_opd === 0 && $progres->draft_opd === 0 && $progres->selesai === 0 && $progres->ditolak_opd === 0)
                                                <span class="text-slate-400 dark:text-slate-500 italic">
                                                    Menunggu Tindak Lanjut OPD
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $badgeClass = match($lhr->status) {
                                    \App\Enums\LhrStatus::Draft               => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    \App\Enums\LhrStatus::MenungguTelaah      => 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200',
                                    \App\Enums\LhrStatus::PerluPerbaikan      => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300 border border-yellow-200',
                                    \App\Enums\LhrStatus::MenungguPersetujuan => 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200',
                                    \App\Enums\LhrStatus::Terbit              => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200',
                                    \App\Enums\LhrStatus::TindakLanjut        => 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200',
                                    \App\Enums\LhrStatus::Selesai             => 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300 border border-green-200',
                                    \App\Enums\LhrStatus::Dibatalkan          => 'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                };
                            @endphp
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">
                                    {{ $lhr->status->label() }}
                                </span>

                                {{-- Sub-status sinkronisasi revisi OPD --}}
                                @if($lhr->isTerbit())
                                    @if($progres->dikirim_opd > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border border-blue-300 shadow-2xs">
                                            <span>📩</span> Ada Revisi OPD
                                        </span>
                                    @elseif($progres->ditolak_opd > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300 border border-rose-300 shadow-2xs">
                                            Perlu Koreksi OPD
                                        </span>
                                    @elseif($progres->total_rekom > 0 && $progres->selesai === $progres->total_rekom)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border border-emerald-300 shadow-2xs">
                                            ✓ Tuntas 100%
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                {{-- Buka Ruang Kerja (Show) --}}
                                <a href="{{ route('lhr.show', $lhr->uuid) }}"
                                   title="Buka Ruang Kerja LHR (KKP Pengujian & Simpulan)"
                                   class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-xs hover:bg-blue-700 hover:shadow-md hover:scale-102 active:scale-95 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Buka</span>
                                </a>

                                {{-- Edit Informasi Dokumen --}}
                                @if($lhr->canEdit())
                                    <a href="{{ route('lhr.edit', $lhr->uuid) }}"
                                       title="Edit Informasi Dokumen (Nomor, Tanggal, Penandatangan)"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-amber-700 dark:hover:bg-amber-950/40 dark:hover:text-amber-300 transition-all shadow-2xs hover:scale-105 active:scale-95">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                @endif

                                {{-- Unduh PDF --}}
                                <button type="button"
                                   onclick="window.requestSecurePdf('lhr_pdf', '{{ $lhr->uuid }}', 'LHR: {{ addslashes($lhr->nomor_lhr ?? 'DRAFT') }}')"
                                   title="Unduh PDF Resmi"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-red-700 dark:hover:bg-red-950/40 dark:hover:text-red-300 transition-all shadow-2xs hover:scale-105 active:scale-95 cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </button>

                                {{-- Unduh Word --}}
                                <a href="{{ route('lhr.word', $lhr->uuid) }}"
                                   title="Unduh Dokumen Word (.docx)"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-blue-700 dark:hover:bg-blue-950/40 dark:hover:text-blue-300 transition-all shadow-2xs hover:scale-105 active:scale-95">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 17.5l-1.5-6h1.5l.8 3.8.8-3.8h1.5l.8 3.8.8-3.8H15l-1.5 6h-1.5l-.8-3.6-.8 3.6H8.5z"/></svg>
                                </a>

                                {{-- Hapus Draft --}}
                                @if($lhr->status === \App\Enums\LhrStatus::Draft)
                                    <form method="POST" action="{{ route('lhr.destroy', $lhr->uuid) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draft LHR ini?')" class="inline-flex m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Hapus Draft LHR"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-300 transition-all shadow-2xs hover:scale-105 active:scale-95">
                                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-base font-semibold text-gray-700 dark:text-gray-300">Belum ada dokumen LHR</p>
                                <p class="text-xs text-gray-500 mt-1">Gunakan tombol "Buat LHR Baru" untuk memulai reviu dari Surat Perintah Tugas (SPT).</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($lhrs->hasPages())
        <div class="border-t border-gray-200 px-5 py-3.5 dark:border-gray-800">
            {{ $lhrs->links() }}
        </div>
    @endif
</div>

@endsection
