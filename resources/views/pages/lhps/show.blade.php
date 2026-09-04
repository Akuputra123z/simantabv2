@extends('layouts.app')

@section('content')
@php
$totalTemuan = $lhp->temuans->count();

$totalRekomendasi = $lhp->temuans->sum(function ($temuan) {
    return $temuan->recommendations->count();
});

$totalNilaiTemuan = $lhp->temuans->sum('nilai_temuan');

$totalNilaiRekomendasi = $lhp->temuans->sum(function ($temuan) {
    return $temuan->recommendations->sum('nilai_rekom');
});
@endphp

<div class="w-full space-y-6">

    {{-- ============================================================
        PAGE HEADER
    ============================================================ --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('lhps.index') }}"
                   class="transition hover:text-blue-600">
                    LHP
                </a>

                <span>/</span>

                <span class="text-gray-700 dark:text-gray-300">
                    Detail LHP
                </span>
            </nav>

            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Detail Laporan Hasil Pemeriksaan
                </h1>

                @if(($lhp->status ?? null) === 'published' || ($lhp->status ?? null) === 'final')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-900/20 dark:text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Diterbitkan
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Draft
                    </span>
                @endif
            </div>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $lhp->nomor_lhp }}
            </p>
        </div>


        {{-- ACTION --}}
        <div class="flex flex-wrap items-center gap-2">

            <a href="{{ route('lhps.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">

                <svg class="h-4 w-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 19l-7-7 7-7" />
                </svg>

                Kembali
            </a>

            <a href="{{ route('lhps.edit', $lhp->id) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50 dark:border-blue-900 dark:bg-gray-800 dark:text-blue-400">

                <svg class="h-4 w-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 20h9" />

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4z" />
                </svg>

                Edit LHP
            </a>

            @if(Route::has('lhps.print'))
                <a href="{{ route('lhps.print', $lhp->id) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">

                    <svg class="h-4 w-4"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0v4h12v-4" />
                    </svg>

                    Cetak LHP
                </a>
            @else
                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">

                    <svg class="h-4 w-4"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0v4h12v-4" />
                    </svg>

                    Cetak LHP
                </button>
            @endif

        </div>
    </div>


    {{-- ============================================================
        HERO DOCUMENT HEADER
    ============================================================ --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

        <div class="border-b border-gray-100 px-6 py-6 dark:border-gray-800">

            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                <div class="flex items-start gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30">

                        <svg class="h-6 w-6"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />

                        </svg>

                    </div>


                    <div>

                        <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">
                            Laporan Hasil Pemeriksaan
                        </p>

                        <h2 class="mt-1 text-xl font-bold text-gray-900 dark:text-white">
                            {{ $lhp->nomor_lhp }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $lhp->unitDiperiksa?->nama_unit ?? '-' }}
                        </p>

                    </div>

                </div>


                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-right dark:border-gray-700 dark:bg-gray-800">

                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                        Tanggal LHP
                    </p>

                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                        {{ optional($lhp->tanggal_lhp)->translatedFormat('d F Y') ?? (is_string($lhp->tanggal_lhp) ? \Carbon\Carbon::parse($lhp->tanggal_lhp)->translatedFormat('d F Y') : '-') }}
                    </p>

                </div>

            </div>

        </div>


        {{-- QUICK INFO --}}
        <div class="grid grid-cols-1 divide-y divide-gray-100 dark:divide-gray-800 md:grid-cols-3 md:divide-x md:divide-y-0">

            <div class="px-6 py-4">

                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                    Program Kerja
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">
                    {{ $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->nama_program ?? '-' }}
                </p>

            </div>


            <div class="px-6 py-4">

                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                    Penugasan Audit
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">
                    {{ $lhp->auditAssignment?->auditProgramDetail?->nama_detail_program ?? '-' }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    {{ $lhp->auditAssignment?->nomor_surat ?? '-' }}
                </p>

            </div>


            <div class="px-6 py-4">

                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                    Objek Pemeriksaan
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">
                    {{ $lhp->unitDiperiksa?->nama_unit ?? '-' }}
                </p>

            </div>

        </div>

    </div>


    {{-- ============================================================
        SUMMARY
    ============================================================ --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        {{-- TEMUAN --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-start justify-between">

                <div>

                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        Total Temuan
                    </p>

                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $totalTemuan }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Temuan pemeriksaan
                    </p>

                </div>

                <div class="rounded-xl bg-blue-50 p-2.5 text-blue-600 dark:bg-blue-900/30">

                    <svg class="h-5 w-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />

                    </svg>

                </div>

            </div>

        </div>


        {{-- REKOMENDASI --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-start justify-between">

                <div>

                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        Rekomendasi
                    </p>

                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $totalRekomendasi }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Tindak lanjut
                    </p>

                </div>

                <div class="rounded-xl bg-indigo-50 p-2.5 text-indigo-600 dark:bg-indigo-900/30">

                    <svg class="h-5 w-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m5-3a9 9 0 11-6-6" />

                    </svg>

                </div>

            </div>

        </div>


        {{-- NILAI TEMUAN --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                Nilai Temuan
            </p>

            <p class="mt-2 text-xl font-bold tracking-tight text-rose-600">
                Rp {{ number_format($totalNilaiTemuan, 0, ',', '.') }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Akumulasi nilai temuan
            </p>

        </div>


        {{-- NILAI REKOMENDASI --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                Nilai Rekomendasi
            </p>

            <p class="mt-2 text-xl font-bold tracking-tight text-blue-600">
                Rp {{ number_format($totalNilaiRekomendasi, 0, ',', '.') }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Nilai yang direkomendasikan
            </p>

        </div>

    </div>


    {{-- ============================================================
        MAIN CONTENT
    ============================================================ --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">

        {{-- LEFT CONTENT --}}
        <div class="space-y-6 xl:col-span-8">


            {{-- CATATAN --}}
            @if($lhp->catatan_umum)
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">

                    <div class="mb-4 flex items-center gap-2">

                        <div class="h-2 w-2 rounded-full bg-blue-500"></div>

                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                            Ringkasan Pemeriksaan
                        </h3>

                    </div>

                    <p class="text-sm leading-7 text-gray-600 dark:text-gray-300">
                        {{ $lhp->catatan_umum }}
                    </p>

                </div>
            @endif


            {{-- TEMUAN --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">

                    <div>

                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                            Temuan Pemeriksaan
                        </h3>

                        <p class="mt-1 text-xs text-gray-500">
                            Rincian hasil pemeriksaan beserta rekomendasi tindak lanjut.
                        </p>

                    </div>

                    <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {{ $totalTemuan }} Temuan
                    </span>

                </div>


                <div class="divide-y divide-gray-100 dark:divide-gray-800">

                    @foreach($lhp->temuans as $index => $temuan)

                        <div class="p-6 lg:p-7">

                            {{-- TEMUAN HEADER --}}
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                <div class="flex gap-4">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow-sm">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>

                                    <div>

                                        <div class="flex flex-wrap items-center gap-2">

                                            <span class="rounded-md bg-blue-50 px-2 py-1 font-mono text-[10px] font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                                {{ $temuan->kodeTemuan?->kode ?? 'TEMUAN' }}
                                            </span>

                                            <span class="text-xs text-gray-400">
                                                Temuan Pemeriksaan
                                            </span>

                                        </div>

                                        <h4 class="mt-2 text-base font-bold leading-6 text-gray-900 dark:text-white">
                                            {{ $temuan->kodeTemuan?->deskripsi ?? $temuan->kodeTemuan?->nama ?? 'Temuan Pemeriksaan' }}
                                        </h4>

                                    </div>

                                </div>


                                @if($temuan->nilai_temuan > 0)

                                    <div class="shrink-0 rounded-xl border border-rose-100 bg-rose-50 px-4 py-2 text-right dark:border-rose-900/40 dark:bg-rose-900/20">

                                        <p class="text-[9px] font-bold uppercase tracking-wider text-rose-400">
                                            Nilai Temuan
                                        </p>

                                        <p class="mt-1 text-sm font-bold text-rose-600">
                                            Rp {{ number_format($temuan->nilai_temuan, 0, ',', '.') }}
                                        </p>

                                    </div>

                                @endif

                            </div>


                            {{-- KONDISI --}}
                            <div class="mt-6 border-l-2 border-gray-200 pl-5 dark:border-gray-700">

                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    Kondisi
                                </p>

                                <p class="mt-2 text-sm leading-7 text-gray-700 dark:text-gray-300">
                                    {{ $temuan->kondisi ?: '-' }}
                                </p>

                            </div>


                            {{-- SEBAB DAN AKIBAT --}}
                            @if($temuan->sebab || $temuan->akibat)

                                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">

                                    @if($temuan->sebab)
                                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/60">

                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                                Sebab
                                            </p>

                                            <p class="mt-2 text-xs leading-6 text-gray-600 dark:text-gray-300">
                                                {{ $temuan->sebab }}
                                            </p>

                                        </div>
                                    @endif


                                    @if($temuan->akibat)
                                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/60">

                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                                Akibat
                                            </p>

                                            <p class="mt-2 text-xs leading-6 text-gray-600 dark:text-gray-300">
                                                {{ $temuan->akibat }}
                                            </p>

                                        </div>
                                    @endif

                                </div>

                            @endif


                            {{-- REKOMENDASI --}}
                            <div class="mt-7 border-t border-gray-100 pt-6 dark:border-gray-800">

                                <div class="mb-4 flex items-center gap-2">

                                    <div class="h-2 w-2 rounded-full bg-blue-500"></div>

                                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                        Rekomendasi Tindak Lanjut
                                    </h5>

                                </div>


                                <div class="space-y-3">

                                    @foreach($temuan->recommendations as $rIndex => $rekom)

                                        <div class="relative rounded-xl border border-gray-200 p-5 transition hover:border-blue-200 hover:shadow-sm dark:border-gray-700 dark:hover:border-blue-800">

                                            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                                                <div class="flex items-center gap-3">

                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 text-[11px] font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                        {{ $rIndex + 1 }}
                                                    </span>

                                                    <div>

                                                        <span class="font-mono text-[10px] font-bold text-blue-600 dark:text-blue-400">
                                                            {{ $rekom->kodeRekomendasi?->kode ?? 'REKOM' }}
                                                        </span>

                                                        <p class="mt-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                            {{ $rekom->kodeRekomendasi?->deskripsi ?? 'Rekomendasi' }}
                                                        </p>

                                                    </div>

                                                </div>


                                                <span class="w-fit rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase text-gray-500 dark:bg-gray-800">
                                                    {{ $rekom->jenis_rekomendasi }}
                                                </span>

                                            </div>


                                            <p class="text-sm leading-7 text-gray-700 dark:text-gray-300">
                                                {{ $rekom->uraian_rekom }}
                                            </p>


                                            <div class="mt-5 grid grid-cols-1 gap-3 border-t border-gray-100 pt-4 sm:grid-cols-2 dark:border-gray-800">

                                                <div>

                                                    <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400">
                                                        Batas Waktu
                                                    </p>

                                                    <p class="mt-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                        {{ optional($rekom->batas_waktu)->translatedFormat('d F Y') ?? (is_string($rekom->batas_waktu) ? \Carbon\Carbon::parse($rekom->batas_waktu)->translatedFormat('d F Y') : '-') }}
                                                    </p>

                                                </div>


                                                <div class="sm:text-right">

                                                    <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400">
                                                        Nilai Rekomendasi
                                                    </p>

                                                    <p class="mt-1 text-xs font-bold text-blue-600">
                                                        Rp {{ number_format($rekom->nilai_rekom ?? 0, 0, ',', '.') }}
                                                    </p>

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        </div>


        {{-- ========================================================
            RIGHT SIDEBAR
        ======================================================== --}}
        <aside class="space-y-5 xl:col-span-4">


            {{-- INFORMASI DOKUMEN --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">
                    Informasi Dokumen
                </h3>


                <dl class="mt-5 space-y-4 text-sm">

                    <div class="border-b border-gray-100 pb-4 dark:border-gray-800">

                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Nomor LHP
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-800 dark:text-gray-200">
                            {{ $lhp->nomor_lhp }}
                        </dd>

                    </div>


                    <div class="border-b border-gray-100 pb-4 dark:border-gray-800">

                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Tanggal
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-800 dark:text-gray-200">
                            {{ optional($lhp->tanggal_lhp)->translatedFormat('d F Y') ?? (is_string($lhp->tanggal_lhp) ? \Carbon\Carbon::parse($lhp->tanggal_lhp)->translatedFormat('d F Y') : '-') }}
                        </dd>

                    </div>


                    <div>

                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Dibuat
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-800 dark:text-gray-200">
                            {{ optional($lhp->created_at)->translatedFormat('d F Y, H:i') }}
                        </dd>

                    </div>

                </dl>

            </div>


            {{-- PROGRESS --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

                <div class="flex items-center justify-between">

                    <div>

                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">
                            Ringkasan Data
                        </h3>

                        <p class="mt-1 text-xs text-gray-500">
                            Komposisi LHP
                        </p>

                    </div>

                </div>


                <div class="mt-5 space-y-5">

                    <div>

                        <div class="mb-2 flex items-center justify-between text-xs">

                            <span class="text-gray-500">
                                Temuan
                            </span>

                            <strong class="text-gray-800 dark:text-gray-200">
                                {{ $totalTemuan }}
                            </strong>

                        </div>

                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">

                            <div class="h-full w-full rounded-full bg-blue-500"></div>

                        </div>

                    </div>


                    <div>

                        <div class="mb-2 flex items-center justify-between text-xs">

                            <span class="text-gray-500">
                                Rekomendasi
                            </span>

                            <strong class="text-gray-800 dark:text-gray-200">
                                {{ $totalRekomendasi }}
                            </strong>

                        </div>

                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">

                            <div class="h-full w-full rounded-full bg-indigo-500"></div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- LAMPIRAN --}}
            @if($lhp->attachments && $lhp->attachments->count())

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

                    <div class="flex items-center justify-between">

                        <div>

                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">
                                Lampiran
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $lhp->attachments->count() }} berkas
                            </p>

                        </div>

                    </div>


                    <div class="mt-4 space-y-2">

                        @foreach($lhp->attachments as $attachment)

                            <a href="{{ Storage::url($attachment->file_path) }}"
                               target="_blank"
                               class="group flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:border-blue-200 hover:bg-blue-50/30 dark:border-gray-800 dark:hover:border-blue-900 dark:hover:bg-blue-900/10">

                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-600 dark:bg-gray-800">

                                    <svg class="h-4 w-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 00-5.656-5.656L5.05 11.464a6 6 0 108.486 8.486L19.5 14" />

                                    </svg>

                                </div>


                                <div class="min-w-0 flex-1">

                                    <p class="truncate text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $attachment->file_name ?: 'Lampiran LHP' }}
                                    </p>

                                    <p class="mt-0.5 text-[10px] text-gray-400">
                                        Klik untuk membuka
                                    </p>

                                </div>

                            </a>

                        @endforeach

                    </div>

                </div>

            @endif

        </aside>

    </div>

</div>
@endsection
