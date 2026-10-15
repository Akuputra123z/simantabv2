@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER DASHBOARD OPD --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    Dashboard Tindak Lanjut OPD
                </h1>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Realtime Live Sync
                </span>
            </div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">
                Pemantauan status pengunggahan, verifikasi, dan penyelesaian rekomendasi unit Anda secara otomatis & tersinkronisasi
            </p>
        </div>
        <div class="flex items-center gap-2.5 text-xs font-semibold shrink-0">
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-blue-50 px-3 py-1.5 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 font-bold border border-blue-100 dark:border-blue-800/40">
                <span>👋</span>
                <span class="max-w-[140px] truncate sm:max-w-none">{{ auth()->user()->name }}</span>
            </span>
            <button onclick="window.location.reload()" title="Segarkan Data Dashboard" class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all cursor-pointer shadow-xs">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- 1. GLOBAL DATE FILTER --}}
    <x-dashboard.date-filter
        :preset="$preset"
        :tahun="$tahun ?? date('Y')"
        :listTahun="$listTahun ?? []"
        :kategoriProgram="$kategoriProgram"
        :listKategori="$listKategori"
        :startDate="$startDate"
        :endDate="$endDate"
        :startDateFormatted="$startDateFormatted"
        :endDateFormatted="$endDateFormatted"
        :actionUrl="route('opd.dashboard')"
    />

    {{-- RINGKASAN KPI UTAMA OPD (LHP & LHR) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Card 1: LHP --}}
        <div class="relative overflow-hidden rounded-2xl border border-blue-200/80 bg-gradient-to-br from-blue-50/70 via-white to-white p-5 shadow-xs dark:border-blue-900/50 dark:from-blue-950/30 dark:via-gray-900 dark:to-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">Pemeriksaan (LHP)</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-xs">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white font-mono">{{ $totalLhp }}</span>
                <span class="text-xs font-semibold text-gray-500">Laporan LHP</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-blue-100/80 dark:border-blue-900/30">
                <span>{{ $totalPatuhKepatuhan }} Tuntas &bull; {{ $totalProsesKepatuhan }} Proses</span>
                <a href="{{ route('opd.tindak-lanjut.index') }}" class="font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400">Lihat LHP &rarr;</a>
            </div>
        </div>

        {{-- Card 2: LHR (Reviu Dokumen) --}}
        <div class="relative overflow-hidden rounded-2xl border border-violet-200/80 bg-gradient-to-br from-violet-50/70 via-white to-white p-5 shadow-xs dark:border-violet-900/50 dark:from-violet-950/30 dark:via-gray-900 dark:to-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-violet-700 dark:text-violet-400">Reviu Dokumen (LHR)</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-600 text-white shadow-xs">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white font-mono">{{ $totalLhr }}</span>
                <span class="text-xs font-semibold text-gray-500">LHR Resmi Terbit</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-violet-100/80 dark:border-violet-900/30">
                <span>{{ $totalCatatanLhr }} Catatan Reviu</span>
                <a href="{{ route('opd.lhr.index') }}" class="font-bold text-violet-600 hover:text-violet-700 dark:text-violet-400">Lihat LHR &rarr;</a>
            </div>
        </div>

        {{-- Card 3: Temuan & Rekomendasi LHP --}}
        <div class="relative overflow-hidden rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/70 via-white to-white p-5 shadow-xs dark:border-amber-900/50 dark:from-amber-950/30 dark:via-gray-900 dark:to-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Temuan & Rekomendasi</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white font-mono">{{ $totalTemuan }}</span>
                <span class="text-xs font-semibold text-gray-500">Temuan ({{ $totalRekom }} Rekom)</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-amber-100/80 dark:border-amber-900/30">
                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $rekomSelesai }} Selesai ({{ $rekomPct }}%)</span>
                <span>{{ $rekomBelum }} Belum</span>
            </div>
        </div>

        {{-- Card 4: Penyelamatan Aset --}}
        <div class="relative overflow-hidden rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-emerald-50/70 via-white to-white p-5 shadow-xs dark:border-emerald-900/50 dark:from-emerald-950/30 dark:via-gray-900 dark:to-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Penyelamatan Keuangan</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-xs">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-xl font-extrabold tracking-tight text-emerald-700 dark:text-emerald-400 font-mono truncate" title="Rp {{ number_format($totalPenyelamatan, 0, ',', '.') }}">
                    Rp {{ number_format($totalPenyelamatan / 1000000, 1, ',', '.') }} Jt
                </span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-emerald-100/80 dark:border-emerald-900/30">
                <span class="font-semibold text-emerald-700 dark:text-emerald-400">Recovery: {{ $recoveryRate }}%</span>
                <span class="truncate">Sisa: Rp {{ number_format($sisaKerugian / 1000000, 1, ',', '.') }} Jt</span>
            </div>
        </div>
    </div>

    {{-- 2. INFORMASI KATEGORI PROGRAM AUDIT --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/80">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Informasi Kategori Program Audit</h3>
                <p class="text-xs text-gray-500">Ringkasan LHP, rekomendasi, dan realisasi per kategori audit unit Anda</p>
            </div>
            <a href="{{ route('opd.tindak-lanjut.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 hover:text-blue-800 dark:text-blue-400">
                <span>Lihat Tindak Lanjut OPD</span>
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($kategoriBreakdown as $item)
                @php
                    $isDefault = $item->kategori === 'PKPT';
                @endphp
                <a href="{{ route('opd.tindak-lanjut.index', ['kategori' => $item->kategori]) }}"
                   class="group relative rounded-xl border border-gray-200 bg-slate-50/60 p-4 hover:bg-white hover:border-blue-600 hover:shadow-md transition-all dark:border-gray-800 dark:bg-gray-800/40 dark:hover:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-block rounded-md bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                {{ $item->kategori }}
                            </span>
                            @if($isDefault)
                                <span class="rounded bg-blue-700 px-1.5 py-0.5 text-[10px] font-bold text-white uppercase">
                                    Default Utama
                                </span>
                            @endif
                        </div>
                        <span class="text-xs font-mono font-bold text-gray-500 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                            {{ $item->progres }}% Selesai
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Jumlah LHP</span>
                            <p class="font-mono font-bold text-gray-900 dark:text-white text-sm">{{ $item->total_lhp }} LHP</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Rekomendasi</span>
                            <p class="font-mono font-bold text-gray-900 dark:text-white text-sm">{{ $item->total_rekom }} Items</p>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 border-t border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between text-[11px]">
                        <span class="text-gray-500">Nilai Kerugian:</span>
                        <span class="font-mono font-bold text-gray-900 dark:text-white">Rp {{ number_format($item->total_kerugian, 0, ',', '.') }}</span>
                    </div>

                    <div class="mt-2 h-1.5 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                        <div class="h-full rounded-full {{ $item->progres >= 100 ? 'bg-emerald-500' : ($item->progres > 0 ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600') }}" style="width: {{ min(100, max(0, $item->progres)) }}%"></div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- 3. STATUS LAPORAN KEPATUHAN OPD (TUNTAS 100% / NIHIL) --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/80">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Laporan Kepatuhan OPD</h3>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                            {{ $totalPatuhKepatuhan }} LHP Siap Terbit
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Penerbitan dokumen kesimpulan & surat keterangan tindak lanjut bagi LHP yang tuntas 100% atau nihil
                    </p>
                </div>
            </div>
            <a href="{{ route('laporan.kepatuhan') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 shrink-0">
                <span>Lihat Semua Laporan Kepatuhan</span>
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        {{-- Ringkasan Metrik Kepatuhan --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 mb-4">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 dark:bg-gray-800/40 dark:border-gray-700/60">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total LHP Diaudit</span>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $totalLhpKepatuhan }}</span>
                    <span class="text-xs text-gray-500">LHP Terdaftar</span>
                </div>
            </div>
            <div class="p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-200/80 dark:bg-emerald-950/30 dark:border-emerald-900/50">
                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Patuh Tuntas (100%)</span>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400">{{ $totalPatuhKepatuhan }}</span>
                    <span class="text-xs text-emerald-600 dark:text-emerald-300">Siap Cetak PDF</span>
                </div>
            </div>
            <div class="p-3.5 rounded-xl bg-amber-50/60 border border-amber-200/80 dark:bg-amber-950/30 dark:border-amber-900/50">
                <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Dalam Proses Tindak Lanjut</span>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-amber-700 dark:text-amber-400">{{ $totalProsesKepatuhan }}</span>
                    <span class="text-xs text-amber-600 dark:text-amber-300">Belum 100%</span>
                </div>
            </div>
        </div>

        {{-- Tabel Daftar LHP & Aksi Dokumen Kepatuhan --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
            <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300">
                <thead class="border-b border-gray-200 bg-gray-50/80 font-semibold uppercase tracking-wider text-gray-600 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-300">
                    <tr>
                        <th scope="col" class="py-3 px-4 w-10 text-center">No</th>
                        <th scope="col" class="py-3 px-4">Nomor LHP & Program</th>
                        <th scope="col" class="py-3 px-4 text-center">Rekomendasi</th>
                        <th scope="col" class="py-3 px-4 w-48">Progres Kepatuhan</th>
                        <th scope="col" class="py-3 px-4 w-52 text-center">Dokumen Kepatuhan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($kepatuhanLhps as $index => $lhp)
                        @php
                            $isLengkap = $lhp->isKepatuhanLengkap();
                            $progresVal = $lhp->progres_kepatuhan;
                            $stat = $lhp->statistik;
                            $programName = $lhp->auditAssignment?->auditProgram?->nama_program ?? '-';
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition dark:hover:bg-gray-800/40 {{ $isLengkap ? 'bg-emerald-50/15 dark:bg-emerald-950/10' : '' }}">
                            <td class="py-3.5 px-4 text-center font-medium text-gray-500 dark:text-gray-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900 dark:text-white font-mono">
                                    {{ $lhp->nomor_lhp }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate max-w-sm" title="{{ $programName }}">
                                    {{ $programName }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $stat?->rekom_selesai ?? 0 }} / {{ $stat?->total_rekom ?? 0 }}
                                </span>
                                <span class="text-[10px] text-gray-400 block">Selesai</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                                    <span class="{{ $isLengkap ? 'text-emerald-700 dark:text-emerald-400' : 'text-blue-700 dark:text-blue-400' }}">
                                        {{ $lhp->is_nihil ? 'Nihil Temuan' : round($progresVal, 1) . '%' }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ $isLengkap ? 'Tuntas' : 'Proses' }}
                                    </span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                    <div class="h-full rounded-full {{ $isLengkap ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ min(100, max(0, $progresVal)) }}%"></div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($isLengkap)
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                onclick="window.requestSecurePdf('kepatuhan_show', '{{ $lhp->uuid }}', 'Laporan Kepatuhan: {{ addslashes($lhp->nomor_lhp) }}')"
                                                title="Lihat Laporan Kepatuhan Resmi (Verifikasi Keamanan Sandi)"
                                                class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 shadow-xs dark:border-emerald-800 dark:bg-gray-800 dark:text-emerald-300 dark:hover:bg-gray-700 transition cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Lihat
                                        </button>
                                        <button type="button"
                                                onclick="window.requestSecurePdf('kepatuhan_pdf', '{{ $lhp->uuid }}', 'Laporan Kepatuhan: {{ addslashes($lhp->nomor_lhp) }}')"
                                                title="Unduh Dokumen PDF Resmi (Verifikasi Keamanan Sandi)"
                                                class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            PDF
                                        </button>
                                    </div>
                                @else
                                    <a href="{{ route('opd.tindak-lanjut.lhp', $lhp->id) }}"
                                       title="Progres belum 100%. Lanjutkan unggah tindak lanjut untuk membuka laporan."
                                       class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                                        <span>Tindak Lanjuti</span>
                                        <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400">
                                Belum ada data LHP terdaftar untuk unit Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 4. DOKUMEN HASIL REVIU (LHR) TERBIT OPD --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/80">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/60 dark:text-violet-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Laporan Hasil Reviu (LHR) Unit Anda</h3>
                        <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-0.5 text-[11px] font-bold text-violet-700 dark:bg-violet-950/60 dark:text-violet-300 border border-violet-200/80 dark:border-violet-800/60">
                            {{ $totalLhr }} LHR Resmi Terbit
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Dokumen hasil pengawasan reviu yang telah diterbitkan oleh Inspektorat Daerah untuk ditindaklanjuti unit Anda
                    </p>
                </div>
            </div>
            <a href="{{ route('opd.lhr.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-violet-600 hover:text-violet-700 dark:text-violet-400 shrink-0">
                <span>Lihat Semua LHR OPD</span>
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        {{-- Tabel Daftar LHR --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
            <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300">
                <thead class="border-b border-gray-200 bg-gray-50/80 font-semibold uppercase tracking-wider text-gray-600 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-300">
                    <tr>
                        <th scope="col" class="py-3 px-4 w-10 text-center">No</th>
                        <th scope="col" class="py-3 px-4">Nomor LHR & Jenis Dokumen</th>
                        <th scope="col" class="py-3 px-4">Tanggal Terbit</th>
                        <th scope="col" class="py-3 px-4 text-center">Catatan Reviu</th>
                        <th scope="col" class="py-3 px-4 text-center">Status</th>
                        <th scope="col" class="py-3 px-4 w-44 text-center">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($recentLhrs as $index => $lhr)
                        @php
                            $catatanCount = $lhr->catatans()->count();
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition dark:hover:bg-gray-800/40">
                            <td class="py-3.5 px-4 text-center font-medium text-gray-500 dark:text-gray-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900 dark:text-white font-mono">
                                    {{ $lhr->nomor_lhr ?: 'Nomor Belum Diatur' }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $lhr->jenisDokumen?->nama ?? 'Reviu Dokumen' }} &bull; TA {{ $lhr->tahun_anggaran }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="text-gray-800 dark:text-gray-200 font-medium">
                                    {{ $lhr->tanggal_lhr ? $lhr->tanggal_lhr->format('d M Y') : '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-200 dark:bg-violet-950/50 dark:text-violet-300 dark:border-violet-800/50">
                                    {{ $catatanCount }} Catatan
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/50">
                                    {{ $lhr->status->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('opd.lhr.show', $lhr->uuid) }}"
                                       title="Buka Lembar Reviu LHR"
                                       class="inline-flex items-center gap-1 rounded-lg border border-violet-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-50 shadow-xs dark:border-violet-800 dark:bg-gray-800 dark:text-violet-300 dark:hover:bg-gray-700 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Buka
                                    </a>
                                    <button type="button"
                                       onclick="window.requestSecurePdf('opd_lhr_pdf', '{{ $lhr->uuid }}', 'LHR: {{ addslashes($lhr->nomor_lhr ?? 'DRAFT') }}')"
                                       title="Unduh PDF Resmi"
                                       class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition cursor-pointer">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        PDF
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="font-medium">Belum ada LHR resmi berstatus Terbit untuk unit Anda.</p>
                                    <p class="text-[11px] text-gray-400">Dokumen reviu akan tampil otomatis setelah disahkan dan diterbitkan oleh tim Inspektorat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 5. PERBANDINGAN KERUGIAN & PENYELAMATAN ASET + PANEL PENJELASAN FINANSIAL --}}
    <div class="grid grid-cols-12 gap-6">

        {{-- Left Side: Bar Chart (~65% / 8 cols) --}}
        <div class="col-span-12 lg:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Perbandingan Kerugian & Penyelamatan Aset Unit</h3>
                    <p class="text-xs text-gray-500">
                        @if($isMultiYear ?? false)
                            Grafik real-time perbandingan total kerugian finansial vs realisasi setoran penyelamatan aset per tahun
                        @else
                            Grafik real-time perbandingan total kerugian finansial vs realisasi setoran penyelamatan aset per bulan ({{ $startDateCarbon->year ?? date('Y') }})
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold shrink-0">
                    <span class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Total Kerugian</span>
                    <span class="flex items-center gap-1.5 text-teal-600 dark:text-teal-400"><span class="h-2.5 w-2.5 rounded-full bg-teal-500"></span> Penyelamatan Aset</span>
                </div>
            </div>
            <div id="chartKerugianPenyelamatanBar" class="min-h-[320px] w-full"></div>
        </div>

        {{-- Right Side: Panel Penjelasan & Analisis Finansial (~35% / 4 cols) --}}
        <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Analisis Penyelamatan Aset</h3>
                <p class="text-xs text-gray-500 mb-4">Ringkasan status pengembalian & pemulihan keuangan unit Anda (Realtime Database Sync)</p>

                {{-- Metrics Breakdown Cards --}}
                <div class="space-y-3">
                    {{-- Total Kerugian --}}
                    <div class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-100 dark:bg-rose-900/20 dark:border-rose-800/40">
                        <div class="flex items-center justify-between text-xs font-semibold text-rose-700 dark:text-rose-300 mb-1">
                            <span>Total Kerugian (Nominal)</span>
                            <span>🔴</span>
                        </div>
                        <h5 class="text-lg font-bold text-rose-700 dark:text-rose-400 truncate" title="Rp {{ number_format($totalKerugian ?? 0, 0, ',', '.') }}">
                            Rp {{ number_format($totalKerugian ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>

                    {{-- Penyelamatan Aset --}}
                    <div class="p-3.5 rounded-xl bg-teal-50/70 border border-teal-100 dark:bg-teal-900/20 dark:border-teal-800/40">
                        <div class="flex items-center justify-between text-xs font-semibold text-teal-700 dark:text-teal-300 mb-1">
                            <span>Penyelamatan Aset (Setoran)</span>
                            <span>🟢</span>
                        </div>
                        <h5 class="text-lg font-bold text-teal-700 dark:text-teal-400 truncate" title="Rp {{ number_format($totalPenyelamatan ?? 0, 0, ',', '.') }}">
                            Rp {{ number_format($totalPenyelamatan ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>

                    {{-- Sisa Kerugian --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 dark:bg-gray-800/60 dark:border-gray-700">
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                            <span>Sisa Kerugian Belum Disetor</span>
                            <span>⏳</span>
                        </div>
                        <h5 class="text-lg font-bold text-gray-800 dark:text-gray-200 truncate" title="Rp {{ number_format($sisaKerugian ?? 0, 0, ',', '.') }}">
                            Rp {{ number_format($sisaKerugian ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>

            {{-- Recovery Rate Progress Bar & Penjelasan --}}
            <div class="pt-4 border-t border-slate-100 dark:border-gray-800 mt-4">
                <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                    <span class="text-gray-700 dark:text-gray-300">Tingkat Pemulihan (Recovery Rate)</span>
                    <span class="text-teal-600 dark:text-teal-400">{{ $recoveryRate ?? 0 }}%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden mb-3">
                    <div class="h-full rounded-full bg-teal-500 transition-all duration-500" style="width: {{ min(100, $recoveryRate ?? 0) }}%"></div>
                </div>
                <p class="text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                    💡 <strong class="text-gray-700 dark:text-gray-300">Catatan:</strong> Penyelamatan aset dihitung secara real-time dari akumulasi setoran lunas & cicilan yang telah diverifikasi oleh tim inspektorat.
                </p>
            </div>
        </div>

    </div>

    {{-- 4. REKAPITULASI + VERIFIKASI PROGRESS --}}
    <div class="grid grid-cols-12 gap-6">

        <div class="col-span-12 lg:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
            <div class="mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Rekapitulasi Rekomendasi Unit</h3>
                <p class="text-xs text-gray-500">Progres fisik dan finansial penyelesaian rekomendasi</p>
            </div>

            @php $rekomPctCalc = ($rekapitulasi && $rekapitulasi->total_rekom > 0) ? min(100, round(($rekapitulasi->rekom_selesai / $rekapitulasi->total_rekom) * 100)) : 0; @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-4 p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                <div>
                    <span class="text-xs font-semibold text-gray-400">Total Rekomendasi</span>
                    <h4 class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $rekapitulasi->total_rekom ?? 0 }}</h4>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-400">Total Kerugian / Nilai</span>
                    <h4 class="text-xl font-extrabold text-gray-900 dark:text-white">Rp {{ number_format($rekapitulasi->total_kerugian ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-gray-600 dark:text-gray-300">Rekomendasi Selesai</span>
                        <span class="text-gray-900 dark:text-white font-bold">{{ $rekapitulasi->rekom_selesai ?? 0 }} / {{ $rekapitulasi->total_rekom ?? 0 }} ({{ $rekomPctCalc }}%)</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $rekomPctCalc }}%"></div>
                    </div>
                </div>
                <div>
                    @php $tlPctCalc = ($rekapitulasi && $rekapitulasi->total_kerugian > 0) ? min(100, round(($rekapitulasi->total_tl_selesai / $rekapitulasi->total_kerugian) * 100)) : 0; @endphp
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-gray-600 dark:text-gray-300">Nilai TL Selesai Disetor</span>
                        <span class="text-gray-900 dark:text-white font-bold">Rp {{ number_format($rekapitulasi->total_tl_selesai ?? 0, 0, ',', '.') }} ({{ $tlPctCalc }}%)</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-600 transition-all duration-500" style="width: {{ $tlPctCalc }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Status Pengunggahan OPD</h3>
            <p class="text-xs text-gray-500 mb-4">Distribusi status pengunggahan berkas tindak lanjut</p>

            <div id="chartTlBar" class="min-h-[220px] w-full"></div>
        </div>

    </div>

    {{-- 5. JATUH TEMPO & KEGIATAN TERBARU --}}
    <div class="grid grid-cols-12 gap-6">

        {{-- Jatuh Tempo --}}
        <div class="col-span-12 lg:col-span-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Mendekati Jatuh Tempo (7 Hari)</h3>
                <span class="text-xs text-gray-400">{{ $overdue->count() }} data</span>
            </div>

            <div class="space-y-2">
                @forelse($overdue as $tl)
                <a href="{{ route('opd.tindak-lanjut.show', $tl) }}" class="flex items-center justify-between p-3 rounded-xl border border-amber-100 bg-amber-50/50 hover:bg-amber-100/70 transition-all dark:border-amber-900/30 dark:bg-amber-900/10 min-h-[44px]">
                    <div class="min-w-0 pr-3">
                        <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">
                            {{ $tl->recommendation?->kodeRekomendasi?->kode ?? '-' }} &middot; {{ Str::limit(strip_tags($tl->recommendation?->uraian_rekom ?? '-'), 45) }}
                        </h4>
                        <p class="text-[11px] text-gray-500">Jatuh Tempo: {{ $tl->tanggal_jatuh_tempo?->format('d M Y') }}</p>
                    </div>
                    <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">
                        {{ $tl->tanggal_jatuh_tempo?->isPast() ? 'Terlambat' : $tl->tanggal_jatuh_tempo?->diffForHumans() }}
                    </span>
                </a>
                @empty
                <p class="text-xs text-gray-400 italic py-4 text-center">Tidak ada rekomendasi mendekati jatuh tempo.</p>
                @endforelse
            </div>
        </div>

        {{-- Kegiatan Terbaru OPD --}}
        <div class="col-span-12 lg:col-span-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Kegiatan Terbaru Unit</h3>
                <span class="text-xs text-gray-400">{{ $recent->count() }} data</span>
            </div>

            <div class="space-y-2">
                @forelse($recent as $tl)
                <a href="{{ route('opd.tindak-lanjut.show', $tl) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-colors min-h-[44px]">
                    <div class="min-w-0 pr-3">
                        <h4 class="text-xs font-bold text-blue-600 dark:text-blue-400 truncate">
                            {{ $tl->recommendation?->kodeRekomendasi?->kode ?? '-' }}
                        </h4>
                        <p class="text-[11px] text-gray-500 truncate">
                            {{ Str::limit(strip_tags($tl->recommendation?->uraian_rekom ?? '-'), 50) }}
                        </p>
                    </div>
                    <span class="text-[10px] font-semibold text-gray-400 whitespace-nowrap">
                        {{ $tl->updated_at?->diffForHumans() }}
                    </span>
                </a>
                @empty
                <p class="text-xs text-gray-400 italic py-4 text-center">Belum ada aktivitas terbaru.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');

    // 1. Bar Chart Perbandingan Kerugian & Penyelamatan Aset Unit (Adaptive RWD)
    if (document.querySelector("#chartKerugianPenyelamatanBar") && typeof ApexCharts !== 'undefined') {
        new ApexCharts(document.querySelector("#chartKerugianPenyelamatanBar"), {
            series: [
                { name: "Total Kerugian", data: @json($chartKerugian ?? []) },
                { name: "Penyelamatan Aset", data: @json($chartPenyelamatan ?? []) }
            ],
            chart: {
                type: "bar",
                height: 320,
                toolbar: { show: false },
                fontFamily: "Outfit, sans-serif"
            },
            responsive: [
                {
                    breakpoint: 640,
                    options: {
                        chart: { height: 260 },
                        plotOptions: { bar: { columnWidth: "65%" } },
                        xaxis: { labels: { style: { fontSize: "10px" } } }
                    }
                }
            ],
            colors: ["#f43f5e", "#0d9488"],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "45%",
                    borderRadius: 5,
                    dataLabels: { position: "top" }
                }
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 3, colors: ["transparent"] },
            xaxis: {
                categories: @json($chartMonths ?? []),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: isDark ? "#94a3b8" : "#64748b" } }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        if (val >= 1000000000) return (val / 1000000000).toFixed(1) + " M";
                        if (val >= 1000000) return (val / 1000000).toFixed(0) + " Jt";
                        return val;
                    },
                    style: { colors: isDark ? "#94a3b8" : "#64748b" }
                }
            },
            grid: {
                borderColor: isDark ? "#1e293b" : "#f1f5f9",
                strokeDashArray: 4
            },
            legend: { show: false },
            tooltip: {
                theme: isDark ? "dark" : "light",
                y: {
                    formatter: function (val) {
                        return "Rp " + new Intl.NumberFormat("id-ID").format(val);
                    }
                }
            }
        }).render();
    }

    // 2. Bar Chart Status Pengunggahan OPD
    const belum = {{ $opdStats->belum_upload ?? 0 }};
    const draft = {{ $opdStats->draft ?? 0 }};
    const dikirim = {{ $opdStats->dikirim ?? 0 }};
    const ditolak = {{ $opdStats->ditolak ?? 0 }};

    if (document.getElementById('chartTlBar') && typeof ApexCharts !== 'undefined') {
        new ApexCharts(document.getElementById('chartTlBar'), {
            series: [{ name: 'Jumlah', data: [belum, draft, dikirim, ditolak] }],
            chart: {
                type: 'bar',
                height: 220,
                fontFamily: 'Outfit, sans-serif',
                toolbar: { show: false }
            },
            colors: ['#94a3b8', '#f59e0b', '#2563eb', '#f43f5e'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '50%',
                    distributed: true
                }
            },
            xaxis: {
                categories: ['Belum Upload', 'Draft', 'Terkirim', 'Ditolak'],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: isDark ? "#94a3b8" : "#64748b" } }
            },
            yaxis: {
                labels: { style: { colors: isDark ? "#94a3b8" : "#64748b" } }
            },
            dataLabels: { enabled: false },
            grid: { borderColor: isDark ? "#1e293b" : "#f1f5f9", strokeDashArray: 4 },
            legend: { show: false }
        }).render();
    }
});
</script>
@endpush
