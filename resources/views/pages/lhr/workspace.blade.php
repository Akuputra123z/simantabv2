@extends('layouts.app')

@section('content')

{{-- Header Ruang Kerja --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-4 dark:border-gray-800">
    <div>
        <div class="flex items-center gap-2">
            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                {{ $lhr->jenisDokumen->kode }}
            </span>
            <span class="text-xs text-gray-500">TA {{ $lhr->tahun_anggaran }}</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $lhr->status->label() }}
            </span>
        </div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-1">
            {{ $lhr->nomor_lhr ?? 'DRAFT LHR' }} &bull; {{ $lhr->unitDiperiksa->nama_unit }}
        </h1>
        <p class="text-xs text-gray-500 mt-0.5">
            SPT: {{ $lhr->auditAssignment->nomor_surat ?? '-' }} &bull; Ketua: {{ $lhr->auditAssignment->ketuaTim->name ?? '-' }} &bull; Pengendali Teknis: {{ $lhr->auditAssignment->pengendaliTeknis->name ?? '-' }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('lhr.preview', $lhr->uuid) }}" target="_blank"
           class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Pratinjau HTML
        </a>
        <a href="{{ route('lhr.pdf', $lhr->uuid) }}" target="_blank"
           class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-gray-800 px-3 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 shadow-sm">
            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Unduh PDF
        </a>
        <a href="{{ route('lhr.word', $lhr->uuid) }}"
           class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 shadow-sm transition">
            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 17.5l-1.5-6h1.5l.8 3.8.8-3.8h1.5l.8 3.8.8-3.8H15l-1.5 6h-1.5l-.8-3.6-.8 3.6H8.5z"/></svg>
            Unduh Word (.docx)
        </a>
        <a href="{{ route('lhr.index') }}"
           class="inline-flex h-9 items-center rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Kembali
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

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- NOTIFIKASI VALIDASI FORM SUBMISSION (JIKA ADA ERROR VALIDASI)              --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
        <div class="font-bold flex items-center gap-1.5 mb-1.5 text-sm">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Terdapat {{ $errors->count() }} Isian Form yang Belum Sesuai:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 pl-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    // Petakan error dan warning linter ke langkah terkait
    $step1Errors = collect($lintResult->errors)->filter(fn($e) => in_array($e['code'], ['L-05', 'L-06']) || str_starts_with($e['target'] ?? '', 'tanggal_') || str_starts_with($e['target'] ?? '', 'permohonan_') || str_starts_with($e['target'] ?? '', 'nomor_'))->count();
    $step1Warnings = collect($lintResult->warnings)->filter(fn($w) => str_starts_with($w['target'] ?? '', 'tanggal_') || str_starts_with($w['target'] ?? '', 'permohonan_'))->count();

    $step4Errors = collect($lintResult->errors)->filter(fn($e) => in_array($e['code'], ['L-02', 'L-07']) || str_starts_with($e['target'] ?? '', 'pengujian_'))->count();
    $step4Warnings = collect($lintResult->warnings)->filter(fn($w) => in_array($w['code'], ['L-07']) || str_starts_with($w['target'] ?? '', 'pengujian_'))->count();

    $step5Errors = collect($lintResult->errors)->filter(fn($e) => in_array($e['code'], ['L-03', 'L-04', 'L-08']) || str_starts_with($e['target'] ?? '', 'catatan_'))->count();
    $step5Warnings = collect($lintResult->warnings)->filter(fn($w) => in_array($w['code'], ['L-08']) || str_starts_with($w['target'] ?? '', 'catatan_'))->count();
@endphp

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- BANNER QUALITY GATE GLOBAL (SELALU MUNCUL DI SEMUA STEP JIKA ADA MASALAH)  --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($lintResult->hasErrors() || $lintResult->hasWarnings())
    <div class="mb-5 rounded-2xl border {{ $lintResult->hasErrors() ? 'border-red-200 bg-red-50/70 dark:border-red-900/50 dark:bg-red-950/30' : 'border-amber-200 bg-amber-50/70 dark:border-amber-900/50 dark:bg-amber-950/30' }} p-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-2.5">
                <span class="text-xl mt-0.5">{{ $lintResult->hasErrors() ? '🚨' : '⚠️' }}</span>
                <div>
                    <div class="font-bold text-xs uppercase tracking-wide {{ $lintResult->hasErrors() ? 'text-red-900 dark:text-red-200' : 'text-amber-900 dark:text-amber-200' }}">
                        Quality Gate &bull; Ditemukan {{ count($lintResult->errors) }} Galat & {{ count($lintResult->warnings) }} Catatan Telaah Mutu
                    </div>
                    <p class="text-[11px] {{ $lintResult->hasErrors() ? 'text-red-700/80 dark:text-red-300/80' : 'text-amber-700/80 dark:text-amber-300/80' }} mt-0.5">
                        Dokumen perlu ditindaklanjuti pada butir-butir di bawah sebelum diajukan ke telaah Dalnis atau persetujuan Inspektur.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="setStep(6)"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold {{ $lintResult->hasErrors() ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-amber-600 text-white hover:bg-amber-700' }} shadow-sm transition">
                    Lihat Langkah 6 (Final) &rarr;
                </button>
            </div>
        </div>

        {{-- Rincian Galat & Tombol Langsung ke Langkah Terkait --}}
        <div class="mt-3 pt-3 border-t {{ $lintResult->hasErrors() ? 'border-red-200 dark:border-red-900/40' : 'border-amber-200 dark:border-amber-900/40' }} grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
            @foreach($lintResult->errors as $err)
                <div class="flex items-center justify-between p-2 rounded-lg bg-white/80 dark:bg-gray-900/50 border border-red-200/80">
                    <span class="text-red-800 dark:text-red-300 font-medium text-[11px] flex items-center gap-1.5">
                        <span class="font-mono font-bold text-[10px] bg-red-100 text-red-800 px-1 py-0.2 rounded">{{ $err['code'] }}</span>
                        {{ $err['message'] }}
                    </span>
                    @if(in_array($err['code'], ['L-05', 'L-06']))
                        <button type="button" @click="setStep(1)" class="text-[10px] font-bold text-blue-600 hover:underline shrink-0 ml-2 whitespace-nowrap">Perbaiki Langkah 1 &rarr;</button>
                    @elseif($err['code'] === 'L-02')
                        <button type="button" @click="setStep(4)" class="text-[10px] font-bold text-blue-600 hover:underline shrink-0 ml-2 whitespace-nowrap">Perbaiki Langkah 4 &rarr;</button>
                    @else
                        <button type="button" @click="setStep(5)" class="text-[10px] font-bold text-blue-600 hover:underline shrink-0 ml-2 whitespace-nowrap">Perbaiki Langkah 5 &rarr;</button>
                    @endif
                </div>
            @endforeach
            @foreach($lintResult->warnings as $warn)
                <div class="flex items-center justify-between p-2 rounded-lg bg-white/80 dark:bg-gray-900/50 border border-amber-200/80">
                    <span class="text-amber-800 dark:text-amber-300 font-medium text-[11px] flex items-center gap-1.5">
                        <span class="font-mono font-bold text-[10px] bg-amber-100 text-amber-800 px-1 py-0.2 rounded">{{ $warn['code'] }}</span>
                        {{ $warn['message'] }}
                    </span>
                    @if(in_array($warn['code'], ['L-08']))
                        <button type="button" @click="setStep(5)" class="text-[10px] font-bold text-indigo-600 hover:underline shrink-0 ml-2 whitespace-nowrap">Lengkapi Langkah 5 &rarr;</button>
                    @else
                        <button type="button" @click="setStep(4)" class="text-[10px] font-bold text-indigo-600 hover:underline shrink-0 ml-2 whitespace-nowrap">Cek Langkah 4 &rarr;</button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- ROOT CONTAINER DENGAN ALPINE.JS WIZARD 6 LANGKAH --}}
<div x-data="lhrWorkspace({{ $currentStep ?? 1 }})" class="space-y-6">

    {{-- HEADER KONTROL MODE & STEPPER BAR --}}
    <div class="space-y-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
            <div class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2">
                <span>Alur Kerja Reviu LHR</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 font-normal">
                    Mode Aktif: <strong x-text="viewMode === 'all' ? 'Tampilkan Semua Langkah' : 'Wizard Langkah ' + step"></strong>
                </span>
            </div>
            
            {{-- TOGGLE MODE TAMPILAN: WIZARD VS ALL STEPS --}}
            <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl dark:bg-gray-800 shrink-0">
                <button type="button" @click="setViewMode('wizard')"
                        :class="viewMode === 'wizard' ? 'bg-white text-blue-700 shadow-sm font-bold dark:bg-gray-700 dark:text-blue-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-medium'"
                        class="px-2.5 py-1 text-xs rounded-lg transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span>Mode Wizard (Per Langkah)</span>
                </button>
                <button type="button" @click="setViewMode('all')"
                        :class="viewMode === 'all' ? 'bg-white text-blue-700 shadow-sm font-bold dark:bg-gray-700 dark:text-blue-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-medium'"
                        class="px-2.5 py-1 text-xs rounded-lg transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span>Tampilkan Semua Langkah</span>
                </button>
            </div>
        </div>

        {{-- STEPPER BAR (WIZARD 6 LANGKAH SESUAI PRD DENGAN BADGE ERROR/WARNING) --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
                
                {{-- Step 1 --}}
                <button type="button" @click="setStep(1)"
                        :class="step === 1 ? 'border-blue-600 bg-blue-50/80 text-blue-700 dark:bg-blue-950/50 dark:border-blue-500 dark:text-blue-300 shadow-sm ring-2 ring-blue-500/20' : 'border-gray-200 hover:border-gray-300 text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                        class="flex flex-col text-left rounded-xl border p-3 transition duration-150 relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold"
                              :class="step === 1 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                            1
                        </span>
                        @if($step1Errors > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                {{ $step1Errors }} Galat
                            </span>
                        @else
                            <span class="text-[10px] uppercase font-bold text-gray-400">Identitas</span>
                        @endif
                    </div>
                    <div class="font-bold text-xs truncate">Surat & Tim</div>
                    <div class="text-[10px] text-gray-500 truncate mt-0.5">Penugasan & Data Surat</div>
                </button>

                {{-- Step 2 --}}
                <button type="button" @click="setStep(2)"
                        :class="step === 2 ? 'border-blue-600 bg-blue-50/80 text-blue-700 dark:bg-blue-950/50 dark:border-blue-500 dark:text-blue-300 shadow-sm ring-2 ring-blue-500/20' : 'border-gray-200 hover:border-gray-300 text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                        class="flex flex-col text-left rounded-xl border p-3 transition duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold"
                              :class="step === 2 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                            2
                        </span>
                        <span class="text-[10px] uppercase font-bold text-gray-400">Acuan</span>
                    </div>
                    <div class="font-bold text-xs truncate">Dasar & Dokumen</div>
                    <div class="text-[10px] text-gray-500 truncate mt-0.5">Regulasi & Checklist Dokumen</div>
                </button>

                {{-- Step 3 --}}
                <button type="button" @click="setStep(3)"
                        :class="step === 3 ? 'border-blue-600 bg-blue-50/80 text-blue-700 dark:bg-blue-950/50 dark:border-blue-500 dark:text-blue-300 shadow-sm ring-2 ring-blue-500/20' : 'border-gray-200 hover:border-gray-300 text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                        class="flex flex-col text-left rounded-xl border p-3 transition duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold"
                              :class="step === 3 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                            3
                        </span>
                        <span class="text-[10px] uppercase font-bold text-gray-400">Data</span>
                    </div>
                    <div class="font-bold text-xs truncate">Data Pembanding</div>
                    <div class="text-[10px] text-gray-500 truncate mt-0.5">Diff Fiskal & Indikator</div>
                </button>

                {{-- Step 4 --}}
                <button type="button" @click="setStep(4)"
                        :class="step === 4 ? 'border-blue-600 bg-blue-50/80 text-blue-700 dark:bg-blue-950/50 dark:border-blue-500 dark:text-blue-300 shadow-sm ring-2 ring-blue-500/20' : 'border-gray-200 hover:border-gray-300 text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                        class="flex flex-col text-left rounded-xl border p-3 transition duration-150 relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold"
                              :class="step === 4 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                            4
                        </span>
                        @if($step4Errors > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                {{ $step4Errors }} Galat
                            </span>
                        @elseif($step4Warnings > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                                {{ $step4Warnings }} Telaah
                            </span>
                        @else
                            <span class="text-[10px] uppercase font-bold text-gray-400">Uji Butir</span>
                        @endif
                    </div>
                    <div class="font-bold text-xs truncate">Simpulan Hasil</div>
                    <div class="text-[10px] text-gray-500 truncate mt-0.5">14 Butir Uji & Simpulan</div>
                </button>

                {{-- Step 5 --}}
                <button type="button" @click="setStep(5)"
                        :class="step === 5 ? 'border-blue-600 bg-blue-50/80 text-blue-700 dark:bg-blue-950/50 dark:border-blue-500 dark:text-blue-300 shadow-sm ring-2 ring-blue-500/20' : 'border-gray-200 hover:border-gray-300 text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                        class="flex flex-col text-left rounded-xl border p-3 transition duration-150 relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold"
                              :class="step === 5 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                            5
                        </span>
                        @if($step5Errors > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                {{ $step5Errors }} Galat
                            </span>
                        @elseif($step5Warnings > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                                {{ $step5Warnings }} Telaah
                            </span>
                        @else
                            <span class="text-[10px] uppercase font-bold text-gray-400">Temuan</span>
                        @endif
                    </div>
                    <div class="font-bold text-xs truncate">Catatan & Rekom</div>
                    <div class="text-[10px] text-gray-500 truncate mt-0.5">Input Temuan & Arahan</div>
                </button>

                {{-- Step 6 --}}
                <button type="button" @click="setStep(6)"
                        :class="step === 6 ? 'border-purple-600 bg-purple-50/80 text-purple-700 dark:bg-purple-950/50 dark:border-purple-500 dark:text-purple-300 shadow-sm ring-2 ring-purple-500/20' : 'border-gray-200 hover:border-gray-300 text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                        class="flex flex-col text-left rounded-xl border p-3 transition duration-150 relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold"
                              :class="step === 6 ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                            6
                        </span>
                        @if($lintResult->hasErrors())
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                {{ count($lintResult->errors) }} Galat
                            </span>
                        @elseif($lintResult->hasWarnings())
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                                {{ count($lintResult->warnings) }} Catatan
                            </span>
                        @else
                            <span class="text-[10px] uppercase font-bold text-green-600 dark:text-green-400">Lolos</span>
                        @endif
                    </div>
                    <div class="font-bold text-xs truncate">Pratinjau & Sah</div>
                    <div class="text-[10px] text-gray-500 truncate mt-0.5">Linter & Alur Persetujuan</div>
                </button>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- LANGKAH 1: IDENTITAS SURAT & SUSUNAN TIM REVIU                        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="step-section-1" x-show="viewMode === 'all' || step === 1" x-cloak class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5 dark:border-gray-800">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-bold">1</span>
                        Langkah 1: Identitas Surat & Susunan Tim Reviu
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Informasi nomor surat, tanggal penerbitan, satuan kerja Bappeda, dan penetapan tim penugasan reviu.
                    </p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                    FR-05 &bull; FR-06 &bull; FR-07
                </span>
            </div>

            <form method="POST" action="{{ route('lhr.update', $lhr->uuid) }}" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="next_step" value="2">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Satuan Kerja --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Satuan Kerja / Unit Diperiksa (Objek Reviu) <span class="text-red-500">*</span>
                        </label>
                        <select name="unit_diperiksa_id" required data-no-ts {{ !$lhr->canEdit() ? 'disabled' : '' }}
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $lhr->unit_diperiksa_id == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tahun Anggaran --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Tahun Anggaran RKPD <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="tahun_anggaran" value="{{ old('tahun_anggaran', $lhr->tahun_anggaran) }}" required {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    {{-- Nomor LHR --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Nomor Surat LHR
                        </label>
                        <input type="text" name="nomor_lhr" value="{{ old('nomor_lhr', $lhr->nomor_lhr) }}" placeholder="700/026/REVIU/INSP/2026 (kosongkan bila dialokasikan saat terbit)" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <span class="text-[10px] text-gray-500 mt-1 block">Pola nomor baku: 700/{urut}/REVIU/INSP/{tahun}</span>
                    </div>

                    {{-- Tanggal LHR --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Surat LHR
                        </label>
                        <input type="date" name="tanggal_lhr" id="inputTanggalLhr" value="{{ old('tanggal_lhr', $lhr->tanggal_lhr?->format('Y-m-d')) }}" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <span class="text-[10px] text-gray-500 mt-1 block">
                            Tanggal pengesahan naskah. Sesuai standar audit (L-05), tanggal LHR harus <strong>sama dengan atau setelah tanggal selesai reviu</strong> (minimal: <strong>{{ $lhr->tanggal_selesai?->translatedFormat('d F Y') ?? '-' }}</strong>).
                        </span>
                    </div>

                    {{-- Permohonan Bappeda --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Nomor Surat Permohonan Bappeda
                        </label>
                        <input type="text" name="permohonan_nomor" value="{{ old('permohonan_nomor', $lhr->permohonan_nomor) }}" placeholder="000.7/250/2026" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Surat Permohonan Bappeda
                        </label>
                        <input type="date" name="permohonan_tanggal" value="{{ old('permohonan_tanggal', $lhr->permohonan_tanggal?->format('Y-m-d')) }}" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    {{-- Pelaksanaan Reviu --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Tanggal Mulai Pelaksanaan
                            </label>
                            @if($lhr->auditAssignment?->tanggal_mulai)
                                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium">
                                    SPT: {{ $lhr->auditAssignment->tanggal_mulai->format('d-m-Y') }}
                                </span>
                            @endif
                        </div>
                        <input type="date" name="tanggal_mulai" id="inputTanggalMulai" value="{{ old('tanggal_mulai', $lhr->tanggal_mulai?->format('Y-m-d')) }}" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <span class="text-[10px] text-gray-500 mt-1 block">
                            Minimal sama dengan tanggal mulai SPT (<strong>{{ $lhr->auditAssignment?->tanggal_mulai?->format('d-m-Y') ?? '-' }}</strong>).
                        </span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Tanggal Selesai Pelaksanaan
                            </label>
                            @if($lhr->auditAssignment?->tanggal_selesai)
                                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium">
                                    SPT: {{ $lhr->auditAssignment->tanggal_selesai->format('d-m-Y') }}
                                </span>
                            @endif
                        </div>
                        <input type="date" name="tanggal_selesai" id="inputTanggalSelesai" value="{{ old('tanggal_selesai', $lhr->tanggal_selesai?->format('Y-m-d')) }}" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <span class="text-[10px] text-gray-500 mt-1 block">
                            Batas akhir pelaksanaan. Harus &ge; tanggal mulai.
                        </span>
                    </div>

                    @if($lhr->auditAssignment && $lhr->canEdit())
                    <div class="md:col-span-2 -mt-1">
                        <button type="button" 
                                onclick="
                                    const sptMulai = '{{ $lhr->auditAssignment->tanggal_mulai?->format('Y-m-d') }}';
                                    const sptSelesai = '{{ $lhr->auditAssignment->tanggal_selesai?->format('Y-m-d') }}';
                                    if (sptMulai) document.getElementById('inputTanggalMulai').value = sptMulai;
                                    if (sptSelesai) {
                                        document.getElementById('inputTanggalSelesai').value = sptSelesai;
                                        const tglLhr = document.getElementById('inputTanggalLhr');
                                        if (tglLhr && (!tglLhr.value || tglLhr.value < sptSelesai)) {
                                            tglLhr.value = sptSelesai;
                                        }
                                    }
                                "
                                class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 hover:underline">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Sinkronkan Tanggal Otomatis Sesuai SPT ({{ $lhr->auditAssignment->tanggal_mulai?->format('d-m-Y') }} s/d {{ $lhr->auditAssignment->tanggal_selesai?->format('d-m-Y') }})</span>
                        </button>
                    </div>
                    @endif

                    {{-- Penandatangan --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Pejabat Penandatangan LHR (Inspektur Daerah)
                        </label>
                        <select name="ditandatangani_oleh" data-no-ts {{ !$lhr->canEdit() ? 'disabled' : '' }}
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            <option value="">-- Pilih Inspektur Daerah --</option>
                            @foreach($signers as $s)
                                <option value="{{ $s->id }}" {{ $lhr->ditandatangani_oleh == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} (NIP. {{ $s->nip ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tabel Susunan Tim Penugasan (Snapshot) --}}
                <div class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3 flex items-center justify-between">
                        <span>Susunan Tim Reviu Berdasarkan SPT: {{ $lhr->auditAssignment->nomor_surat ?? '-' }}</span>
                        <span class="text-[11px] text-gray-400 font-normal">Sinkron Otomatis dari Penugasan</span>
                    </h3>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2.5 font-bold">Peran dalam Tim</th>
                                    <th class="px-4 py-2.5 font-bold">Nama Lengkap & Gelar</th>
                                    <th class="px-4 py-2.5 font-bold">NIP</th>
                                    <th class="px-4 py-2.5 font-bold">Jabatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold text-purple-700 dark:text-purple-400">Penanggung Jawab</td>
                                    <td class="px-4 py-2.5 font-bold">{{ $lhr->signer?->name ?? 'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.' }}</td>
                                    <td class="px-4 py-2.5 font-mono">{{ $lhr->signer?->nip ?? '19741128 199903 2 003' }}</td>
                                    <td class="px-4 py-2.5">Inspektur Daerah</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold text-blue-700 dark:text-blue-400">Pengendali Teknis</td>
                                    <td class="px-4 py-2.5 font-bold">{{ $lhr->auditAssignment->pengendaliTeknis->name ?? 'SRI SURYANDARI, S.Sos.' }}</td>
                                    <td class="px-4 py-2.5 font-mono">{{ $lhr->auditAssignment->pengendaliTeknis->nip ?? '19690827 199603 2 005' }}</td>
                                    <td class="px-4 py-2.5">{{ $lhr->auditAssignment->pengendaliTeknis->jabatan ?? 'Inspektur Pembantu I' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold text-indigo-700 dark:text-indigo-400">Ketua Tim</td>
                                    <td class="px-4 py-2.5 font-bold">{{ $lhr->auditAssignment->ketuaTim->name ?? 'SITI AMINAH, S.E.' }}</td>
                                    <td class="px-4 py-2.5 font-mono">{{ $lhr->auditAssignment->ketuaTim->nip ?? '19920714 201902 2 002' }}</td>
                                    <td class="px-4 py-2.5">{{ $lhr->auditAssignment->ketuaTim->jabatan ?? 'Auditor Ahli Pertama' }}</td>
                                </tr>
                                @if(!empty($lhr->auditAssignment->members) && $lhr->auditAssignment->members->isNotEmpty())
                                    @foreach($lhr->auditAssignment->members as $agt)
                                        <tr>
                                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Anggota Tim</td>
                                            <td class="px-4 py-2.5">{{ $agt->name }}</td>
                                            <td class="px-4 py-2.5 font-mono">{{ $agt->nip ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $agt->pivot->jabatan_tim ?? $agt->jabatan ?? 'Anggota Tim' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Anggota Tim (7 Orang)</td>
                                        <td class="px-4 py-2.5" colspan="3">Parijan, Agung Tri Prastiyo, Ika Pertiwi, Taufiqur Rachman, Sihono, Wahyu Anjar, Dewi Zahro Wahyuningtyas (Default Template Resmi Rembang)</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Action Bar Step 1 --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    @if($lhr->canEdit())
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                            <span>Simpan Identitas & Lanjut ke Langkah 2: Acuan & Dokumen</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    @else
                        <button type="button" @click="setStep(2)"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                            <span>Lanjut ke Langkah 2: Acuan & Dokumen</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- LANGKAH 2: ACUAN REGULASI & CHECKLIST DOKUMEN PENDUKUNG               --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="step-section-2" x-show="viewMode === 'all' || step === 2" x-cloak class="space-y-6">

        {{-- 1. Dasar Hukum & Regulasi Reviu (Full Width) --}}
        <div class="w-full rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-5 dark:border-gray-800 gap-3">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                            📜
                        </span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                            Dasar Hukum & Regulasi Reviu
                        </h3>
                        <span class="text-[10px] font-semibold bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full dark:bg-blue-900/40 dark:text-blue-300">FR-09</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 pl-10.5">
                        Peraturan perundang-undangan dan pedoman standar pengawasan yang menjadi acuan formal pelaksanaan reviu dokumen perencanaan daerah
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-gray-100 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        7 Regulasi Acuan Resmi
                    </span>
                </div>
            </div>

            @php
                $dasarHukums = [
                    [
                        'urutan' => 1,
                        'kategori' => 'Permendagri',
                        'nomor' => 'Permendagri No. 86 Tahun 2017',
                        'sub' => 'Tata Cara Perencanaan, Pengendalian & Evaluasi',
                        'deskripsi' => 'Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang RPJPD dan RPJMD, Serta Tata Cara Perubahan RPJPD, RPJMD, dan RKPD.',
                    ],
                    [
                        'urutan' => 2,
                        'kategori' => 'Permendagri',
                        'nomor' => 'Permendagri No. 10 Tahun 2018',
                        'sub' => 'Pedoman Reviu Perencanaan & Anggaran Tahunan',
                        'deskripsi' => 'Reviu atas Dokumen Perencanaan Pembangunan dan Anggaran Daerah Tahunan.',
                    ],
                    [
                        'urutan' => 3,
                        'kategori' => 'Permendagri',
                        'nomor' => 'Permendagri No. 3 Tahun 2026',
                        'sub' => 'Pedoman Reviu Perencanaan & Keuangan Daerah',
                        'deskripsi' => 'Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah (Pasal 2, 3, dan 8).',
                    ],
                    [
                        'urutan' => 4,
                        'kategori' => 'Perda Kab. Rembang',
                        'nomor' => 'Perda Kab. Rembang No. 6 Tahun 2021',
                        'sub' => 'Perangkat Daerah Kabupaten Rembang',
                        'deskripsi' => 'Perubahan atas Peraturan Daerah Kabupaten Rembang Nomor 5 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Kabupaten Rembang.',
                    ],
                    [
                        'urutan' => 5,
                        'kategori' => 'Perbup Rembang',
                        'nomor' => 'Perbup Rembang No. 4 Tahun 2026',
                        'sub' => 'Penjabaran APBD Kabupaten Rembang',
                        'deskripsi' => 'Penjabaran Anggaran Pendapatan dan Belanja Daerah Kabupaten Rembang Tahun Anggaran 2026.',
                    ],
                    [
                        'urutan' => 6,
                        'kategori' => 'Surat Edaran Kemendagri',
                        'nomor' => 'Surat Itjen Kemendagri No. 700.1.1.1/1642/IJ',
                        'sub' => 'Surat Arahan Teknis Reviu RKPD',
                        'deskripsi' => 'Perihal Pelaksanaan Reviu Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Tahun Anggaran ' . ($lhr->tahun_anggaran ?? '2027') . '.',
                    ],
                    [
                        'urutan' => 7,
                        'kategori' => 'Standar Audit',
                        'nomor' => 'Standar Audit AAIPI (SAIPI)',
                        'sub' => 'Standar Profesional Auditor Intern Pemerintah',
                        'deskripsi' => 'Standar Audit Intern Pemerintah Indonesia yang diterbitkan oleh Asosiasi Auditor Intern Pemerintah Indonesia (AAIPI).',
                    ],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                @foreach($dasarHukums as $dh)
                    <div class="flex items-start gap-3.5 p-4 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-blue-50/20 hover:border-blue-200 transition dark:border-gray-800 dark:bg-gray-800/40 dark:hover:bg-blue-950/20">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-100 font-bold text-blue-700 text-xs shadow-xs dark:bg-blue-900/60 dark:text-blue-300">
                            {{ $dh['urutan'] }}
                        </div>
                        <div class="space-y-1.5 min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-gray-900 text-xs dark:text-white">{{ $dh['nomor'] }}</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-900/50">
                                    {{ $dh['kategori'] }}
                                </span>
                            </div>
                            <div class="text-[11px] font-medium text-gray-700 dark:text-gray-300">{{ $dh['sub'] }}</div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">{{ $dh['deskripsi'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 2. Checklist Dokumen Pendukung RKPD (RKPD.S1) (Full Width) --}}
        @php
            $dokumenPendukungs = [
                ['kategori' => 'Acuan Nasional', 'nama' => 'RPJMN Tahun 2025-2029', 'status' => 'ada', 'catatan' => 'Tersedia lengkap sebagai acuan prioritas nasional'],
                ['kategori' => 'Acuan Provinsi', 'nama' => 'RPJMD Provinsi Jawa Tengah Tahun 2025-2029', 'status' => 'ada', 'catatan' => 'Tersedia sebagai acuan prioritas provinsi Jawa Tengah'],
                ['kategori' => 'Acuan Provinsi', 'nama' => 'Rancangan RKPD Provinsi Jawa Tengah Tahun ' . $lhr->tahun_anggaran, 'status' => 'ada', 'catatan' => 'Tersedia sebagai acuan penyelarasan target tahunan provinsi'],
                ['kategori' => 'Acuan Daerah', 'nama' => 'RPJMD Kabupaten Rembang Tahun 2025-2029', 'status' => 'ada', 'catatan' => 'Perda No. 7 Tahun 2025 sebagai acuan arah kebijakan daerah'],
                ['kategori' => 'Perangkat Daerah', 'nama' => 'Renstra Perangkat Daerah Tahun 2025-2029', 'status' => 'ada', 'catatan' => 'Telah diunggah dan dihimpun dari seluruh OPD pengampu'],
                ['kategori' => 'Evaluasi Kinerja', 'nama' => 'Hasil Evaluasi Kinerja RKPD Tahun 2025', 'status' => 'ada', 'catatan' => 'Tersedia untuk evaluasi capaian realisasi tahun sebelumnya'],
                ['kategori' => 'Evaluasi Kinerja', 'nama' => 'Hasil Evaluasi RKPD Tahun 2026 (Triwulan 1)', 'status' => 'belum', 'catatan' => 'Belum dilengkapi BAPPEDA saat pelaksanaan reviu'],
                ['kategori' => 'Instrumen Pengendalian', 'nama' => 'Laporan Hasil Pengendalian Kebijakan Formulir E.35 (27 Komponen)', 'status' => 'belum', 'catatan' => 'Belum tersedia lembar kendali mutu kebijakan perencanaan'],
                ['kategori' => 'Manajemen Kerja', 'nama' => 'Jadwal / Agenda Kerja Tim Penyusunan RKPD ' . $lhr->tahun_anggaran, 'status' => 'belum', 'catatan' => 'Belum dilampirkan dalam berkas penyusunan rancangan akhir'],
                ['kategori' => 'Aspirasi Publik / Dewan', 'nama' => 'Rekapitulasi Pokok-Pokok Pikiran (Pokir) DPRD ' . $lhr->tahun_anggaran, 'status' => 'belum', 'catatan' => 'Belum dilengkapi rincian subkegiatan dan volume indikatif'],
            ];
            $countAda = count(array_filter($dokumenPendukungs, fn($d) => $d['status'] === 'ada'));
            $countTotal = count($dokumenPendukungs);
            $percentage = round(($countAda / $countTotal) * 100);
        @endphp

        <div class="w-full rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
             x-data="{ filterStatus: 'all' }">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-100 pb-4 mb-5 dark:border-gray-800 gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                            📋
                        </span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                            Checklist Dokumen Pendukung (RKPD.S1)
                        </h3>
                        <span class="text-[10px] font-semibold bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full dark:bg-emerald-900/40 dark:text-emerald-300">FR-10 Terverifikasi</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 pl-10.5">
                        Verifikasi kelengkapan dokumen acuan nasional, provinsi, daerah, dan instrumen pendukung penyusunan rancangan akhir RKPD
                    </p>
                </div>

                {{-- Filter Buttons --}}
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="filterStatus = 'all'"
                            :class="filterStatus === 'all' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer">
                        Semua ({{ $countTotal }})
                    </button>
                    <button type="button" @click="filterStatus = 'ada'"
                            :class="filterStatus === 'ada' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:hover:bg-emerald-900/40'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Tersedia ({{ $countAda }})
                    </button>
                    <button type="button" @click="filterStatus = 'belum'"
                            :class="filterStatus === 'belum' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-300 dark:hover:bg-amber-900/40'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Belum Lengkap ({{ $countTotal - $countAda }})
                    </button>
                </div>
            </div>

            {{-- Progress Status Card --}}
            <div class="mb-5 rounded-xl bg-gray-50/80 p-4 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-800">
                <div class="flex items-center justify-between text-xs font-semibold mb-2 text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                        <span>Tingkat Kelengkapan Dokumen Rujukan</span>
                        <span class="text-gray-400 font-normal">&bull; {{ $countAda }} dari {{ $countTotal }} dokumen telah diverifikasi</span>
                    </span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold text-sm">{{ $percentage }}% Terpenuhi</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-gray-200 overflow-hidden dark:bg-gray-700">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500" style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            {{-- Full-Width Table Layout --}}
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/90 text-gray-600 font-semibold border-b border-gray-200 dark:bg-gray-800/80 dark:text-gray-300 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3.5 w-12 text-center">No</th>
                            <th class="px-4 py-3.5 min-w-[260px]">Nama Dokumen Pendukung</th>
                            <th class="px-4 py-3.5 w-44">Kategori Acuan</th>
                            <th class="px-4 py-3.5 w-40 text-center">Status Verifikasi</th>
                            <th class="px-4 py-3.5 min-w-[280px]">Catatan / Keterangan Auditor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($dokumenPendukungs as $idx => $dp)
                            <tr x-show="filterStatus === 'all' || filterStatus === '{{ $dp['status'] }}'"
                                class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                <td class="px-4 py-3.5 text-center font-bold text-gray-500 dark:text-gray-400">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 shrink-0 {{ $dp['status'] === 'ada' ? 'text-emerald-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $dp['nama'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $dp['kategori'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($dp['status'] === 'ada')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Tersedia
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Belum Ada
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400">
                                    {{ $dp['catatan'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. Struktur Evaluasi Bab Sesuai Template Resmi (FR-11) (Full Width) --}}
        <div class="w-full rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                        📑
                    </span>
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                            Struktur Evaluasi Bab Sesuai Template Resmi
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Sistematika pengujian naskah dokumen rancangan akhir RKPD sesuai tata urut Bab I s/d Bab VI</p>
                    </div>
                </div>
                <span class="text-[10px] font-semibold bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-full dark:bg-indigo-900/40 dark:text-indigo-300">FR-11 Sistematika</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5 text-xs">
                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/70 hover:border-indigo-300 hover:bg-indigo-50/20 transition dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">Bab I</span>
                        <span class="text-[10px] bg-gray-200/70 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">Hal. 1-15</span>
                    </div>
                    <div class="font-bold text-gray-900 dark:text-white mb-1.5">Pendahuluan</div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Latar Belakang, Dasar Hukum, Hubungan Antar Dokumen, Maksud & Tujuan Reviu.</p>
                </div>

                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/70 hover:border-indigo-300 hover:bg-indigo-50/20 transition dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">Bab II</span>
                        <span class="text-[10px] bg-gray-200/70 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">Hal. 16-45</span>
                    </div>
                    <div class="font-bold text-gray-900 dark:text-white mb-1.5">Evaluasi Kinerja</div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Evaluasi Kinerja RKPD Tahun Sebelumnya & Gambaran Umum Daerah (Indikator Makro).</p>
                </div>

                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/70 hover:border-indigo-300 hover:bg-indigo-50/20 transition dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">Bab III</span>
                        <span class="text-[10px] bg-gray-200/70 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">Hal. 46-70</span>
                    </div>
                    <div class="font-bold text-gray-900 dark:text-white mb-1.5">Kerangka Ekonomi</div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Kerangka Ekonomi Daerah & Kebijakan Keuangan Daerah (Target PAD, Transfer & Belanja).</p>
                </div>

                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/70 hover:border-indigo-300 hover:bg-indigo-50/20 transition dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">Bab IV</span>
                        <span class="text-[10px] bg-gray-200/70 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">Hal. 71-95</span>
                    </div>
                    <div class="font-bold text-gray-900 dark:text-white mb-1.5">Sasaran & Prioritas</div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Sasaran & Prioritas Pembangunan Daerah (Penyelarasan Target Prioritas Nasional & Provinsi).</p>
                </div>

                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/70 hover:border-indigo-300 hover:bg-indigo-50/20 transition dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">Bab V</span>
                        <span class="text-[10px] bg-gray-200/70 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">Hal. 96-180</span>
                    </div>
                    <div class="font-bold text-gray-900 dark:text-white mb-1.5">Rencana Kerja (Pagu)</div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Rencana Kerja & Pendanaan Daerah (Pagu Indikatif Program/Kegiatan Perangkat Daerah).</p>
                </div>

                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/70 hover:border-indigo-300 hover:bg-indigo-50/20 transition dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">Bab VI</span>
                        <span class="text-[10px] bg-gray-200/70 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">Hal. 181-190</span>
                    </div>
                    <div class="font-bold text-gray-900 dark:text-white mb-1.5">Penutup</div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Penutup & Kaidah Pelaksanaan Rancangan Akhir RKPD Kabupaten Rembang.</p>
                </div>
            </div>
        </div>

        {{-- 4. Berkas Lampiran Dokumen Pendukung Terunggah --}}
        <div class="w-full rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-5 dark:border-gray-800 gap-2">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                        📎
                    </span>
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                            Berkas Lampiran Dokumen Pendukung ({{ $lhr->attachments->count() }})
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Berkas dokumen pendukung yang diunggah saat pembuatan LHR dan otomatis tersinkronkan ke Unduh PDF, Unduh Word, serta Pratinjau.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($lhr->canEdit())
                        <a href="{{ route('lhr.edit', $lhr->uuid) }}#seksi-lampiran"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Kelola / Unggah Lampiran</span>
                        </a>
                    @endif
                    <a href="{{ route('lhr.preview', $lhr->uuid) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <svg class="h-3.5 w-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Pratinjau di Dokumen
                    </a>
                </div>
            </div>

            @if($lhr->attachments->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 p-8 text-center dark:border-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Belum ada berkas lampiran yang diunggah untuk LHR ini.
                        @if($lhr->canEdit())
                            <a href="{{ route('lhr.edit', $lhr->uuid) }}#seksi-lampiran" class="text-blue-600 hover:underline font-semibold ml-1 inline-flex items-center gap-1">
                                <span>+ Kelola & Unggah Lampiran Sekarang</span>
                            </a>
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Nama Dokumen</th>
                                <th class="py-3 px-4">Keterangan</th>
                                <th class="py-3 px-4 w-28 text-center">Format</th>
                                <th class="py-3 px-4 w-28 text-right">Ukuran</th>
                                <th class="py-3 px-4 w-32 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($lhr->attachments as $idx => $att)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                    <td class="py-3 px-4 text-center font-medium text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $att->file_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 dark:text-gray-300">
                                        {{ $att->keterangan ?: '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 uppercase dark:bg-gray-800 dark:text-gray-300">
                                            {{ pathinfo($att->file_name, PATHINFO_EXTENSION) ?: 'FILE' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-600 dark:text-gray-400 font-mono">
                                        {{ $att->file_size_formatted }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('attachments.show', $att) }}" target="_blank"
                                               class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-blue-600 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                               title="Buka / Lihat Berkas">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('attachments.download', $att) }}"
                                               class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                               title="Unduh Berkas">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Navigasi Step 2 --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="button" @click="setStep(1)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                &larr; Kembali ke Langkah 1: Identitas
            </button>
            <button type="button" @click="setStep(3)"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                <span>Lanjut ke Langkah 3: Data Pembanding</span>
                &rarr;
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- LANGKAH 3: DATA PEMBANDING (DIFF FISKAL & INDIKATOR MAKRO)            --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="step-section-3" x-show="viewMode === 'all' || step === 3" x-cloak class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5 dark:border-gray-800">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-bold">3</span>
                        Langkah 3: Data Pembanding & Deteksi Selisih Otomatis (Diff)
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Penyandingan angka proyeksi APBD serta konsistensi target indikator sasaran antara dokumen RPJMD dengan Rancangan Akhir RKPD.
                    </p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                    FR-15 &bull; FR-16 (Diff Engine)
                </span>
            </div>

            {{-- 1. Matriks Konsistensi Indikator RPJMD vs RKPD (Tabel Misi 1 - 5) --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        1. Konsistensi Target Indikator Sasaran Pembangunan Daerah (RKPD.S8)
                    </h3>
                    <span class="text-[11px] text-red-600 font-semibold flex items-center gap-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                        Baris merah menunjukkan selisih target yang wajib dikoreksi
                    </span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-3 py-2.5 font-bold" style="width: 25%;">Misi / Tujuan / Sasaran</th>
                                <th class="px-3 py-2.5 font-bold" style="width: 25%;">Indikator & Satuan</th>
                                <th class="px-3 py-2.5 font-bold text-center" style="width: 20%;">Target RPJMD 2025-2029</th>
                                <th class="px-3 py-2.5 font-bold text-center" style="width: 20%;">Target Rankir RKPD {{ $lhr->tahun_anggaran }}</th>
                                <th class="px-3 py-2.5 font-bold text-center" style="width: 10%;">Status Diff</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            {{-- Misi 1 --}}
                            <tr class="bg-blue-50/50 dark:bg-blue-950/20 font-bold">
                                <td colspan="5" class="px-3 py-2 text-blue-900 dark:text-blue-300">Misi 1: Meningkatkan Kualitas Sumber Daya Manusia yang Unggul, Sehat dan Berdaya Saing</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Terwujudnya SDM produktif</td>
                                <td class="px-3 py-2">Indeks Daya Saing Daerah (Angka)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">3,78</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">3,78</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>
                            <tr class="bg-red-50/60 dark:bg-red-950/30">
                                <td class="px-3 py-2 font-medium text-red-900 dark:text-red-300">Akses pendidikan & kesehatan</td>
                                <td class="px-3 py-2 font-medium text-red-900 dark:text-red-300">Indeks Pembangunan Manusia (IPM)</td>
                                <td class="px-3 py-2 text-center font-mono font-bold text-red-700">74,38</td>
                                <td class="px-3 py-2 text-center font-mono font-bold text-red-700">73,70</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Selisih (-0.68)</span></td>
                            </tr>
                            <tr class="bg-red-50/60 dark:bg-red-950/30">
                                <td class="px-3 py-2 font-medium text-red-900 dark:text-red-300">Kesetaraan gender daerah</td>
                                <td class="px-3 py-2 font-medium text-red-900 dark:text-red-300">Indeks Ketimpangan Gender (IUP 21)</td>
                                <td class="px-3 py-2 text-center font-mono font-bold text-red-700">0,246 / 0,22</td>
                                <td class="px-3 py-2 text-center font-mono font-bold text-red-700">0,246 / 2,22</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Selisih Ketik</span></td>
                            </tr>

                            {{-- Misi 2 --}}
                            <tr class="bg-blue-50/50 dark:bg-blue-950/20 font-bold">
                                <td colspan="5" class="px-3 py-2 text-blue-900 dark:text-blue-300">Misi 2: Mewujudkan Pertumbuhan Ekonomi Daerah yang Berkelanjutan, Inklusif & Ketahanan Pangan</td>
                            </tr>
                            <tr class="bg-red-50/60 dark:bg-red-950/30">
                                <td class="px-3 py-2 font-medium text-red-900 dark:text-red-300">Pertumbuhan ekonomi inklusif</td>
                                <td class="px-3 py-2 font-medium text-red-900 dark:text-red-300">Pertumbuhan Ekonomi (%)</td>
                                <td class="px-3 py-2 text-center font-mono font-bold text-red-700">5,90 - 7,75</td>
                                <td class="px-3 py-2 text-center font-mono font-bold text-red-700">5,40 - 6,20</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Selisih Target</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Investasi daerah</td>
                                <td class="px-3 py-2">PMTB dalam PDRB (IUP 32) (%)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">24,02</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">24,02</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Pendapatan per kapita</td>
                                <td class="px-3 py-2">PDRB Per Kapita (Juta Rp) (IUP 33)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">57,81</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">57,81</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Ketenagakerjaan</td>
                                <td class="px-3 py-2">Tingkat Pengangguran Terbuka (%) (IUP 29)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">2,45 / 2,15</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">2,45 / 2,15</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>
                            <tr class="bg-red-50/60 dark:bg-red-950/30">
                                <td class="px-3 py-2 font-medium text-red-900 dark:text-red-300">Penurunan kemiskinan</td>
                                <td class="px-3 py-2 font-medium text-red-900 dark:text-red-300">Tingkat Kemiskinan (%) (IUP 12)</td>
                                <td class="px-3 py-2 text-center font-mono font-bold text-red-700">12,01 / 11,70</td>
                                <td class="px-3 py-2 text-center font-mono font-bold text-red-700">12,73 / 12,43</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Selisih Target</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Ketahanan pangan daerah</td>
                                <td class="px-3 py-2">Indeks Ketahanan Pangan (IKP) (IUP 53)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">85,34</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">85,34</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>

                            {{-- Misi 3 --}}
                            <tr class="bg-blue-50/50 dark:bg-blue-950/20 font-bold">
                                <td colspan="5" class="px-3 py-2 text-blue-900 dark:text-blue-300">Misi 3: Mewujudkan Tata Kelola Pemerintahan dan Pelayanan Publik yang Profesional</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Tata kelola pemerintahan</td>
                                <td class="px-3 py-2">Indeks Reformasi Birokrasi (IRB) (IUP 39)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">89,66</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">89,66</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Pelayanan publik digital</td>
                                <td class="px-3 py-2">Indeks Sistem Pemerintahan Berbasis Elektronik (SPBE)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">3,85</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">3,85</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>

                            {{-- Misi 4 & 5 --}}
                            <tr class="bg-blue-50/50 dark:bg-blue-950/20 font-bold">
                                <td colspan="5" class="px-3 py-2 text-blue-900 dark:text-blue-300">Misi 4 & 5: Infrastruktur Mantap, Lingkungan Hidup, dan Ketahanan Bencana</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Infrastruktur mantap</td>
                                <td class="px-3 py-2">Indeks Infrastruktur (IUP 34)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">70,31</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">70,31</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Kualitas jalan daerah</td>
                                <td class="px-3 py-2">Kemantapan Jalan Kabupaten (%)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">82,50</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">82,50</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Kualitas lingkungan</td>
                                <td class="px-3 py-2">Indeks Kualitas Lingkungan Hidup (IKLH)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">67,80</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">67,80</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-medium">Ketahanan bencana daerah</td>
                                <td class="px-3 py-2">Indeks Risiko Bencana (IRB) (IUP 37)</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">107,82 - 106,8</td>
                                <td class="px-3 py-2 text-center font-mono font-semibold">107,82 - 106,8</td>
                                <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Konsisten</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. Ringkasan Pengujian Arah Fiskal APBD 2027 --}}
            <div class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">
                    2. Catatan Kewajaran Proyeksi Fiskal APBD (RKPD.S5 & Bab III)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div class="p-3.5 rounded-xl border border-red-200 bg-red-50/50 dark:border-red-900/40 dark:bg-red-950/20">
                        <div class="font-bold text-red-800 dark:text-red-300 mb-1">Ketidaksesuaian PAD & Belanja</div>
                        <p class="text-gray-600 dark:text-gray-300 text-[11px] leading-relaxed">
                            Angka proyeksi PAD dan Belanja pada Tabel 3.5 dokumen Rankir RKPD berbeda dengan data proyeksi di SIPD-RI.
                        </p>
                    </div>
                    <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/50 dark:border-amber-900/40 dark:bg-amber-950/20">
                        <div class="font-bold text-amber-800 dark:text-amber-300 mb-1">Belanja Pelayanan Dasar & BTT</div>
                        <p class="text-gray-600 dark:text-gray-300 text-[11px] leading-relaxed">
                            Kebijakan belanja belum memprioritaskan SPM serta belum memproyeksikan Belanja Tidak Terduga (BTT) dan transfer.
                        </p>
                    </div>
                    <div class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/50 dark:border-blue-900/40 dark:bg-blue-950/20">
                        <div class="font-bold text-blue-800 dark:text-blue-300 mb-1">Pembiayaan Utang Rp 4,5 M</div>
                        <p class="text-gray-600 dark:text-gray-300 text-[11px] leading-relaxed">
                            Terdapat pembayaran Pembiayaan Utang Daerah sebesar Rp 4.500.000.000,- yang belum dijelaskan peruntukannya.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Navigasi Step 3 --}}
            <div class="flex items-center justify-between pt-5 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="setStep(2)"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                    &larr; Kembali ke Langkah 2: Acuan & Dokumen
                </button>
                <button type="button" @click="setStep(4)"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                    <span>Lanjut ke Langkah 4: Uji Butir & Simpulan</span>
                    &rarr;
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- LANGKAH 4: PENILAIAN 14 BUTIR PENGUJIAN & SIMPULAN HASIL               --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="step-section-4" x-show="viewMode === 'all' || step === 4" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Panel Kiri: Daftar 14 Butir Hierarkis (4 cols) --}}
            <div class="lg:col-span-5 space-y-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 mb-3 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Pilih Butir Pengujian ({{ $lhr->pengujians->count() }})
                        </h3>
                        <span class="text-[10px] text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded">FR-12</span>
                    </div>

                    <div class="space-y-2 max-h-[650px] overflow-y-auto pr-1">
                        @php
                            $formatPengujians = $lhr->pengujians->filter(fn($p) => $p->grup === 'Format' || str_starts_with($p->kode, 'RKPD.F'));
                            $substansiPengujians = $lhr->pengujians->filter(fn($p) => !$formatPengujians->contains('id', $p->id));
                        @endphp

                        @if($formatPengujians->isNotEmpty())
                            <div class="pt-1 pb-1">
                                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    a. Format Penyusunan Dokumen
                                </span>
                            </div>
                            @foreach($formatPengujians as $p)
                                <button type="button"
                                        @click="selectPengujian({{ $p->id }}, '{{ $p->kode }}', '{{ addslashes($p->judul) }}', '{{ $p->status_simpulan->value }}', '{{ addslashes($p->teks_simpulan ?? '') }}')"
                                        class="w-full text-left rounded-xl p-3 border transition flex items-start gap-2.5"
                                        :class="selectedPengujian.id === {{ $p->id }} ? 'border-blue-500 bg-blue-50/70 text-blue-900 dark:bg-blue-950/40 dark:text-blue-200 ring-2 ring-blue-500/20 shadow-sm' : 'border-gray-100 hover:border-gray-200 bg-white dark:bg-gray-800/60 dark:border-gray-800 text-gray-700 dark:text-gray-300'">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold mt-0.5 shrink-0"
                                          :class="{{ $p->parent_id ? 'true' : 'false' }} ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60' : 'bg-gray-200 text-gray-700 dark:bg-gray-700'">
                                        {{ $p->kode }}
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-xs leading-snug line-clamp-2">{{ $p->judul }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-flex rounded px-1.5 py-0.2 text-[10px] font-bold"
                                                  style="background-color: {{ $p->status_simpulan->value === 'sesuai' ? '#dcfce7; color: #15803d;' : ($p->status_simpulan->value === 'belum_dinilai' ? '#f3f4f6; color: #6b7280;' : '#fee2e2; color: #b91c1c;') }}">
                                                {{ $p->status_simpulan->label() }}
                                            </span>
                                            @if($p->catatans->isNotEmpty())
                                                <span class="text-[10px] text-blue-600 font-bold">
                                                    {{ $p->catatans->count() }} Temuan
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        @endif

                        @if($substansiPengujians->isNotEmpty())
                            <div class="pt-3 pb-1 border-t border-gray-100 dark:border-gray-800 mt-2">
                                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    b. Substansi Rancangan Akhir (1 s/d 8)
                                </span>
                            </div>
                            @foreach($substansiPengujians as $p)
                                <button type="button"
                                        @click="selectPengujian({{ $p->id }}, '{{ $p->kode }}', '{{ addslashes($p->judul) }}', '{{ $p->status_simpulan->value }}', '{{ addslashes($p->teks_simpulan ?? '') }}')"
                                        class="w-full text-left rounded-xl p-3 border transition flex items-start gap-2.5"
                                        :class="selectedPengujian.id === {{ $p->id }} ? 'border-blue-500 bg-blue-50/70 text-blue-900 dark:bg-blue-950/40 dark:text-blue-200 ring-2 ring-blue-500/20 shadow-sm' : 'border-gray-100 hover:border-gray-200 bg-white dark:bg-gray-800/60 dark:border-gray-800 text-gray-700 dark:text-gray-300'">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold mt-0.5 shrink-0"
                                          :class="{{ $p->parent_id ? 'true' : 'false' }} ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60' : 'bg-gray-200 text-gray-700 dark:bg-gray-700'">
                                        {{ $p->kode }}
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-xs leading-snug line-clamp-2">{{ $p->judul }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-flex rounded px-1.5 py-0.2 text-[10px] font-bold"
                                                  style="background-color: {{ $p->status_simpulan->value === 'sesuai' ? '#dcfce7; color: #15803d;' : ($p->status_simpulan->value === 'belum_dinilai' ? '#f3f4f6; color: #6b7280;' : '#fee2e2; color: #b91c1c;') }}">
                                                {{ $p->status_simpulan->label() }}
                                            </span>
                                            @if($p->catatans->isNotEmpty())
                                                <span class="text-[10px] text-blue-600 font-bold">
                                                    {{ $p->catatans->count() }} Temuan
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            {{-- Panel Kanan: Form Penilaian & Simpulan (7 cols) --}}
            <div class="lg:col-span-7 space-y-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 pb-3 mb-4 dark:border-gray-800">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs font-mono font-bold" x-text="selectedPengujian.kode"></span>
                            <span class="text-xs text-gray-400 font-medium">Penilaian Butir & Simpulan Resmi</span>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white" x-text="selectedPengujian.judul"></h3>
                    </div>

                    @if($lhr->canEdit())
                        <form method="POST" :action="'{{ url('/lhr/' . $lhr->uuid . '/pengujian') }}/' + selectedPengujian.id" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Status Simpulan Pengujian <span class="text-red-500">*</span>
                                </label>
                                <select name="status_simpulan" x-model="selectedPengujian.status" required data-no-ts
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                    @foreach(\App\Enums\SimpulanStatus::cases() as $st)
                                        <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                                    <span>Teks Narasi Simpulan LHR (Ditampilkan pada Bab I.1 Simpulan)</span>
                                    <span class="text-[10px] text-gray-400 font-normal">Dapat diubah manual</span>
                                </label>
                                <textarea name="teks_simpulan" rows="5" x-model="selectedPengujian.teksSimpulan"
                                          placeholder="Uraikan simpulan pengujian untuk butir ini..."
                                          class="w-full rounded-lg border border-gray-300 p-3 text-xs text-gray-800 leading-relaxed focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <button type="button" @click="setStep(5)"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    <span>Kelola Catatan Temuan untuk butir ini &rarr;</span>
                                </button>
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                                    Simpan Simpulan Butir
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="space-y-3 text-xs">
                            <div>
                                <span class="text-gray-500 block mb-0.5 font-medium">Status Simpulan:</span>
                                <span class="font-bold text-blue-700 text-sm" x-text="selectedPengujian.status"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-0.5 font-medium">Teks Narasi Simpulan:</span>
                                <p class="text-gray-800 dark:text-gray-200 leading-relaxed bg-gray-50 p-3 rounded-lg dark:bg-gray-800" x-text="selectedPengujian.teksSimpulan || '-'"></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Navigasi Step 4 --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="button" @click="setStep(3)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                &larr; Kembali ke Langkah 3: Data Pembanding
            </button>
            <button type="button" @click="setStep(5)"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                <span>Lanjut ke Langkah 5: Catatan & Rekomendasi</span>
                &rarr;
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- LANGKAH 5: CATATAN TEMUAN & REKOMENDASI PERBAIKAN                      --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="step-section-5" x-show="viewMode === 'all' || step === 5" x-cloak class="space-y-6">
        
        {{-- Banner Bantuan Standar Rembang --}}
        <div class="rounded-2xl border border-indigo-200 bg-indigo-50/60 p-4 dark:border-indigo-900/50 dark:bg-indigo-950/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="font-bold text-xs text-indigo-950 dark:text-indigo-200 uppercase tracking-wide flex items-center gap-1.5">
                    <span>💡 Sinkronisasi Template Rembang (FR-13 &bull; FR-14)</span>
                </div>
                <p class="text-xs text-indigo-800/80 dark:text-indigo-300/80 mt-0.5">
                    Setiap temuan yang diinput otomatis tersinkronisasi ke <strong>Simpulan</strong>, <strong>Catatan Temuan</strong>, dan <strong>Rekomendasi</strong> laporan.
                </p>
            </div>
            @if($lhr->canEdit())
                <form method="POST" action="{{ route('lhr.apply-standard-template', $lhr->uuid) }}" onsubmit="return confirm('Terapkan 14 catatan temuan & rekomendasi resmi template Rembang ke dokumen ini?')">
                    @csrf
                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-indigo-700 shadow-sm transition flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Sinkronisasi Template Rembang
                    </button>
                </form>
            @endif
        </div>

        {{-- Daftar Catatan Temuan Per Butir --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5 dark:border-gray-800">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-bold">5</span>
                        Langkah 5: Catatan Temuan & Rekomendasi Perbaikan
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Pilih butir rujukan di bawah untuk menambahkan atau memperbarui catatan temuan beserta rekomendasi korektif untuk OPD.
                    </p>
                </div>
            </div>

            <div class="space-y-6">
                @foreach($lhr->pengujians as $p)
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/30">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ $p->kode }}
                                </span>
                                <h4 class="font-bold text-xs text-gray-800 dark:text-gray-200">{{ $p->judul }}</h4>
                            </div>
                            @if($lhr->canEdit())
                                <button type="button"
                                        @click="openTambahCatatanModal({{ $p->id }}, '{{ $p->kode }}', $el.dataset.judul)"
                                        data-judul="{{ $p->judul }}"
                                        class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-2.5 py-1 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition cursor-pointer">
                                    + Tambah Temuan
                                </button>
                            @endif
                        </div>

                        @if($p->catatans->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($p->catatans as $cat)
                                    <div class="rounded-lg border border-gray-200 bg-white p-3.5 dark:border-gray-700 dark:bg-gray-800 shadow-xs">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                @if($cat->referensi_bagian)
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                        📍 {{ $cat->referensi_bagian }}
                                                    </span>
                                                @endif
                                                @if($cat->kategori)
                                                    <span class="text-[11px] text-gray-500 italic">
                                                        ({{ $cat->kategori->nama }})
                                                    </span>
                                                @endif
                                            </div>
                                            @if($lhr->canEdit())
                                                <div class="flex items-center gap-2">
                                                    <script type="application/json" id="catatan-data-{{ $cat->id }}">
                                                    {!! json_encode([
                                                        'id' => $cat->id,
                                                        'pengujian_id' => $p->id,
                                                        'pengujian_kode' => $p->kode,
                                                        'pengujian_judul' => $p->judul,
                                                        'kategori_id' => (string) $cat->kategori_id,
                                                        'referensi_bagian' => $cat->referensi_bagian ?? '',
                                                        'isi' => $cat->isi ?? '',
                                                        'uraian_rekom' => $cat->rekomendasis->first()?->uraian_rekom ?? '',
                                                        'batas_waktu' => $cat->rekomendasis->first()?->batas_waktu?->format('Y-m-d') ?? '',
                                                        'updateUrl' => route('lhr.update-catatan', [$lhr->uuid, $cat->id, 'step' => 5]),
                                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
                                                    </script>
                                                    <button type="button"
                                                            @click="openEditCatatanModalById({{ $cat->id }})"
                                                            class="text-xs font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 cursor-pointer">
                                                        Ubah
                                                    </button>
                                                    <form method="POST" action="{{ route('lhr.destroy-catatan', [$lhr->uuid, $cat->id, 'step' => 5]) }}" onsubmit="return confirm('Hapus temuan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 dark:text-red-400 cursor-pointer">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs pt-1 border-t border-gray-100 dark:border-gray-700">
                                            <div>
                                                <div class="font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Temuan:</div>
                                                <div class="text-gray-600 dark:text-gray-300 leading-relaxed prose-sm max-w-none">
                                                    {!! $cat->isi !!}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-indigo-700 dark:text-indigo-400 mb-1">Rekomendasi Perbaikan:</div>
                                                <div class="text-gray-600 dark:text-gray-300 leading-relaxed prose-sm max-w-none">
                                                    {!! $cat->rekomendasis->first()?->uraian_rekom ?? '-' !!}
                                                </div>
                                                @if($cat->rekomendasis->first()?->batas_waktu)
                                                    <div class="text-[10px] text-gray-400 mt-1 italic">
                                                        Batas Tindak Lanjut: {{ $cat->rekomendasis->first()->batas_waktu->translatedFormat('d F Y') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Belum ada catatan temuan spesifik untuk butir ini.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Navigasi Step 5 --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="button" @click="setStep(4)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                &larr; Kembali ke Langkah 4: Uji Butir
            </button>
            <button type="button" @click="setStep(6)"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                <span>Lanjut ke Langkah 6: Pratinjau & Persetujuan</span>
                &rarr;
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- LANGKAH 6: PRATINJAU DOKUMEN & ALUR PERSETUJUAN BERJENJANG             --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="step-section-6" x-show="viewMode === 'all' || step === 6" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Panel Kiri: Quality Gate & Unduh (7 cols) --}}
            <div class="lg:col-span-7 space-y-5">
                
                {{-- LHR Linter Mutu --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Quality Gate & Validasi Kelengkapan (FR-20)
                        </h3>
                        <span class="inline-flex rounded px-2 py-0.5 text-xs font-bold {{ $lintResult->passes() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $lintResult->passes() ? '✓ Siap Diterbitkan' : count($lintResult->errors) . ' Syarat Belum Lengkap' }}
                        </span>
                    </div>

                    @if($lintResult->hasErrors())
                        <div class="space-y-2 mb-3">
                            @foreach($lintResult->errors as $err)
                                <div class="rounded-lg bg-red-50 p-2.5 text-xs text-red-700 border-l-4 border-red-500 dark:bg-red-900/20 dark:text-red-300">
                                    <strong>{{ $err['code'] }}:</strong> {{ $err['message'] }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg bg-green-50 p-3 text-xs text-green-800 border border-green-200 dark:bg-green-900/20 dark:text-green-300 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Seluruh butir pengujian telah dinilai dan syarat pengesahan dokumen terpenuhi lengkap.</span>
                        </div>
                    @endif

                    @if($lintResult->hasWarnings())
                        <div class="space-y-2 mt-3">
                            @foreach($lintResult->warnings as $warn)
                                <div class="rounded-lg bg-yellow-50 p-2.5 text-xs text-yellow-800 border-l-4 border-yellow-500 dark:bg-yellow-900/20 dark:text-yellow-300">
                                    <strong>Peringatan:</strong> {{ $warn['message'] }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Akses Output Berkas Resmi Rembang --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">
                        Output Dokumen LHR Resmi
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <a href="{{ route('lhr.preview', $lhr->uuid) }}" target="_blank"
                           class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 hover:border-blue-500 hover:bg-blue-50/50 dark:border-gray-700 dark:hover:bg-gray-800 transition text-center">
                            <span class="text-2xl mb-1">🖥️</span>
                            <span class="font-bold text-xs text-gray-900 dark:text-white">Pratinjau HTML</span>
                            <span class="text-[10px] text-gray-500 mt-0.5">Format 26 Halaman</span>
                        </a>
                        <a href="{{ route('lhr.pdf', $lhr->uuid) }}" target="_blank"
                           class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 hover:border-red-500 hover:bg-red-50/50 dark:border-gray-700 dark:hover:bg-gray-800 transition text-center">
                            <span class="text-2xl mb-1">📕</span>
                            <span class="font-bold text-xs text-gray-900 dark:text-white">Unduh PDF Asli</span>
                            <span class="text-[10px] text-gray-500 mt-0.5">Dokumen Siap Cetak</span>
                        </a>
                        <a href="{{ route('lhr.word', $lhr->uuid) }}"
                           class="flex flex-col items-center justify-center p-4 rounded-xl border border-blue-200 hover:border-blue-600 bg-blue-50/40 hover:bg-blue-50 dark:border-blue-900 dark:bg-blue-950/20 transition text-center">
                            <span class="text-2xl mb-1">📄</span>
                            <span class="font-bold text-xs text-blue-700 dark:text-blue-300">Unduh Word (.docx)</span>
                            <span class="text-[10px] text-blue-600/80 dark:text-blue-400 mt-0.5">Baku & Tervalidasi</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Panel Kanan: Alur Persetujuan Berjenjang (5 cols) --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">
                        Alur Persetujuan Berjenjang (FR-19)
                    </h3>

                    {{-- Stepper Status Flow --}}
                    <div class="space-y-3 mb-5">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $lhr->status !== \App\Enums\LhrStatus::Draft ? 'bg-green-600 text-white' : 'bg-blue-600 text-white' }}">1</span>
                            <span class="font-semibold {{ $lhr->status === \App\Enums\LhrStatus::Draft ? 'text-blue-600' : 'text-gray-700 dark:text-gray-300' }}">Penyusunan oleh Ketua Tim</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ in_array($lhr->status, [\App\Enums\LhrStatus::MenungguPersetujuan, \App\Enums\LhrStatus::Terbit]) ? 'bg-green-600 text-white' : ($lhr->status === \App\Enums\LhrStatus::MenungguTelaah ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600') }}">2</span>
                            <span class="font-semibold {{ $lhr->status === \App\Enums\LhrStatus::MenungguTelaah ? 'text-blue-600' : 'text-gray-700 dark:text-gray-300' }}">Penelaahan Pengendali Teknis (Irban)</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $lhr->isTerbit() ? 'bg-green-600 text-white' : ($lhr->status === \App\Enums\LhrStatus::MenungguPersetujuan ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600') }}">3</span>
                            <span class="font-semibold {{ $lhr->status === \App\Enums\LhrStatus::MenungguPersetujuan ? 'text-purple-600' : 'text-gray-700 dark:text-gray-300' }}">Persetujuan & TTD Inspektur</span>
                        </div>
                    </div>

                    {{-- Form Aksi Berdasarkan Status --}}
                    @if($lhr->status === \App\Enums\LhrStatus::Draft || $lhr->status === \App\Enums\LhrStatus::PerluPerbaikan)
                        <form method="POST" action="{{ route('lhr.submit', $lhr->uuid) }}" onsubmit="return confirm('Kirim draf LHR ini ke Pengendali Teknis untuk penelaahan?')">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-xl bg-blue-600 px-4 py-3 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                                📤 Kirim ke Pengendali Teknis &rarr;
                            </button>
                        </form>
                    @elseif($lhr->status === \App\Enums\LhrStatus::MenungguTelaah)
                        <div class="space-y-3">
                            <form method="POST" action="{{ route('lhr.review', $lhr->uuid) }}">
                                @csrf
                                <input type="hidden" name="action" value="setujui">
                                <button type="submit" onclick="return confirm('Setujui dan teruskan ke Kepala Inspektorat?')"
                                        class="w-full rounded-xl bg-green-600 px-4 py-3 text-xs font-bold text-white hover:bg-green-700 shadow-sm transition">
                                    ✓ Setujui & Teruskan ke Inspektur
                                </button>
                            </form>
                            <form method="POST" action="{{ route('lhr.review', $lhr->uuid) }}" class="pt-2 border-t border-gray-100 dark:border-gray-800">
                                @csrf
                                <input type="hidden" name="action" value="kembalikan">
                                <textarea name="catatan" rows="3" required placeholder="Catatan perbaikan jika dikembalikan ke Ketua Tim..."
                                          class="w-full rounded-lg border border-gray-300 p-2 text-xs text-gray-800 mb-2 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                                <button type="submit" onclick="return confirm('Kembalikan LHR ke Ketua Tim untuk perbaikan?')"
                                        class="w-full rounded-xl bg-amber-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-amber-700 transition">
                                    ↩ Kembalikan untuk Perbaikan
                                </button>
                            </form>
                        </div>
                    @elseif($lhr->status === \App\Enums\LhrStatus::MenungguPersetujuan)
                        <form method="POST" action="{{ route('lhr.approve', $lhr->uuid) }}" onsubmit="return confirm('Tandatangani dan terbitkan nomor resmi LHR sekarang?')">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-xl bg-purple-600 px-4 py-3.5 text-xs font-bold text-white hover:bg-purple-700 shadow-sm transition">
                                🔏 Tandatangani & Terbitkan LHR Resmi
                            </button>
                        </form>
                    @elseif($lhr->isTerbit())
                        <div class="rounded-xl bg-green-50 p-4 border border-green-200 text-green-800 dark:bg-green-900/30 dark:text-green-300 text-center">
                            <div class="text-2xl mb-1">🎉</div>
                            <div class="font-bold text-sm">Dokumen LHR Telah Terbit Resmi</div>
                            <div class="text-xs mt-1 font-mono font-bold">{{ $lhr->nomor_lhr }}</div>
                            <div class="text-[11px] text-gray-500 mt-1">Status terkunci dan ber-hash integritas</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Navigasi Step 6 --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="button" @click="setStep(5)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                &larr; Kembali ke Langkah 5: Catatan & Rekomendasi
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL TAMBAH & EDIT CATATAN TEMUAN (RICH TEXT & BAKU)                  --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="catatanModal.open" x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto bg-gray-900/70 p-4 backdrop-blur-xs">
        <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 max-h-[92vh] flex flex-col"
             @click.away="closeCatatanModal()">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white" x-text="catatanModal.isEdit ? 'Ubah Catatan Temuan & Rekomendasi' : 'Tambah Catatan Temuan & Rekomendasi'"></h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Butir: <span class="font-bold text-blue-600" x-text="catatanModal.pengujianKode"></span> &bull; <span x-text="catatanModal.pengujianJudul"></span>
                    </p>
                </div>
                <button type="button" @click="closeCatatanModal()"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    &times;
                </button>
            </div>

            <form :action="catatanModal.actionUrl" method="POST" class="space-y-4 pt-4 overflow-y-auto flex-1 pr-1" @submit="submitForm($event)">
                @csrf
                <input type="hidden" name="_method" value="PUT" :disabled="!catatanModal.isEdit">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Kategori Catatan --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Kategori Butir Temuan <span class="text-red-500">*</span>
                        </label>
                        <select name="kategori_id" x-model="catatanModal.kategoriId" required data-no-ts
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Referensi Bagian Dokumen --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Rujukan Bagian Dokumen
                        </label>
                        <input type="text" name="referensi_bagian" x-model="catatanModal.referensiBagian" placeholder="Bab III / Tabel 3.5 hal. III-17"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                {{-- Input Catatan Temuan --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">
                            Uraian Catatan Temuan Reviu <span class="text-red-500">*</span>
                        </label>
                        <button type="button" @click="insertCatatanFormat()"
                                class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-300 cursor-pointer">
                            📋 Sisipkan Format Baku (4 Elemen)
                        </button>
                    </div>
                    <textarea id="modal_rich_editor_isi" name="isi" rows="4"
                              class="w-full rounded-lg border border-gray-300 p-2.5 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>

                {{-- Input Rekomendasi --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">
                            Rekomendasi Perbaikan Dokumen <span class="text-red-500">*</span>
                        </label>
                        <button type="button" @click="insertRekomFormat()"
                                class="inline-flex items-center gap-1 rounded bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 cursor-pointer">
                            🎯 Sisipkan Format Rekomendasi SMART
                        </button>
                    </div>
                    <textarea id="modal_rich_editor_rekom" name="uraian_rekom" rows="4"
                              class="w-full rounded-lg border border-gray-300 p-2.5 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>

                {{-- Batas Waktu Tindak Lanjut --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Batas Waktu Tindak Lanjut
                    </label>
                    <input type="date" name="batas_waktu" x-model="catatanModal.batasWaktu"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="closeCatatanModal()"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition cursor-pointer">
                        Simpan Temuan & Rekomendasi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection

@push('scripts')
{{-- TinyMCE Local Self-Hosted Assets --}}
<script src="{{ asset('tinymce/tinymce.min.js') }}"></script>

<script>
function lhrWorkspace(initialStep = 1) {
    const urlParams = new URLSearchParams(window.location.search);
    const initialMode = urlParams.get('mode') === 'all' ? 'all' : 'wizard';

    return {
        step: initialStep,
        viewMode: initialMode,

        selectedPengujian: {
            id: {{ $lhr->pengujians->first()?->id ?? 'null' }},
            kode: '{{ $lhr->pengujians->first()?->kode ?? '' }}',
            judul: {!! json_encode($lhr->pengujians->first()?->judul ?? '') !!},
            status: '{{ $lhr->pengujians->first()?->status_simpulan->value ?? 'belum_dinilai' }}',
            teksSimpulan: {!! json_encode($lhr->pengujians->first()?->teks_simpulan ?? '') !!},
        },

        catatanModal: {
            open: false,
            isEdit: false,
            pengujianId: null,
            pengujianKode: '',
            pengujianJudul: '',
            actionUrl: '',
            kategoriId: '{{ $kategoris->first()?->id ?? 1 }}',
            referensiBagian: '',
            batasWaktu: '',
        },

        setViewMode(mode) {
            this.viewMode = mode;
            const url = new URL(window.location);
            if (mode === 'all') {
                url.searchParams.set('mode', 'all');
            } else {
                url.searchParams.delete('mode');
            }
            window.history.replaceState({}, '', url);
        },

        setStep(num) {
            this.step = num;
            const url = new URL(window.location);
            url.searchParams.set('step', num);
            window.history.replaceState({}, '', url);

            if (this.viewMode === 'all') {
                const target = document.getElementById('step-section-' + num);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }
            }
            window.scrollTo({ top: 120, behavior: 'smooth' });
        },

        selectPengujian(id, kode, judul, status, teksSimpulan) {
            this.selectedPengujian = { id, kode, judul, status, teksSimpulan };
        },

        openTambahCatatanModal(pengujianId, kode, judul) {
            this.catatanModal.open = true;
            this.catatanModal.isEdit = false;
            this.catatanModal.pengujianId = pengujianId;
            this.catatanModal.pengujianKode = kode;
            this.catatanModal.pengujianJudul = judul || '';
            this.catatanModal.actionUrl = '{{ url('/lhr/' . $lhr->uuid . '/pengujian') }}/' + pengujianId + '/catatan?step=5';
            this.catatanModal.kategoriId = '{{ $kategoris->first()?->id ?? 1 }}';
            this.catatanModal.referensiBagian = '';
            this.catatanModal.batasWaktu = '';
            
            this.$nextTick(() => {
                this.setupTinyMce('', '');
            });
        },

        openEditCatatanModalById(catId) {
            const el = document.getElementById('catatan-data-' + catId);
            if (!el) return;
            try {
                const data = JSON.parse(el.textContent);
                this.openEditCatatanModal(data.pengujian_id, data.pengujian_kode, data.pengujian_judul, data);
            } catch (e) {
                console.error('Error loading catatan data', e);
            }
        },

        openEditCatatanModal(pengujianId, kode, judul, data) {
            this.catatanModal.open = true;
            this.catatanModal.isEdit = true;
            this.catatanModal.pengujianId = pengujianId;
            this.catatanModal.pengujianKode = kode;
            this.catatanModal.pengujianJudul = judul || '';
            this.catatanModal.actionUrl = data.updateUrl || data.update_url;
            this.catatanModal.kategoriId = String(data.kategori_id);
            this.catatanModal.referensiBagian = data.referensi_bagian || '';
            this.catatanModal.batasWaktu = data.batas_waktu || '';

            this.$nextTick(() => {
                this.setupTinyMce(data.isi || '', data.uraian_rekom || '');
            });
        },

        closeCatatanModal() {
            this.catatanModal.open = false;
            if (typeof tinymce !== 'undefined') {
                tinymce.get('modal_rich_editor_isi')?.remove();
                tinymce.get('modal_rich_editor_rekom')?.remove();
            }
        },

        setupTinyMce(isiContent, rekomContent) {
            const elIsi = document.getElementById('modal_rich_editor_isi');
            const elRekom = document.getElementById('modal_rich_editor_rekom');
            if (elIsi) elIsi.value = isiContent || '';
            if (elRekom) elRekom.value = rekomContent || '';

            if (typeof tinymce === 'undefined') return;

            const isDark = document.documentElement.classList.contains('dark');
            const initEditor = (selector, initialContent) => {
                const editorId = selector.replace('#', '');
                const existing = tinymce.get(editorId);
                if (existing) {
                    existing.setContent(initialContent || '');
                    return;
                }

                tinymce.init({
                    selector: selector,
                    height: 200,
                    plugins: 'advlist autolink lists link table code help wordcount',
                    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | table | removeformat',
                    menubar: false,
                    statusbar: false,
                    branding: false,
                    elementpath: false,
                    promotion: false,
                    skin: isDark ? 'oxide-dark' : 'oxide',
                    content_css: isDark ? 'dark' : 'default',
                    content_style: 'body { font-family: ui-sans-serif, system-ui, sans-serif; font-size: 13px; line-height: 1.6; }',
                    setup(editor) {
                        editor.on('init', () => { editor.setContent(initialContent || ''); });
                        editor.on('change input undo redo keyup SetContent', () => { editor.save(); });
                    }
                });
            };

            initEditor('#modal_rich_editor_isi', isiContent);
            initEditor('#modal_rich_editor_rekom', rekomContent);
        },

        insertCatatanFormat() {
            const formatHtml = `<p><strong>1. Kondisi (Fakta):</strong></p>
<p>Berdasarkan hasil pengujian pada dokumen [sebutkan Bab / Tabel / Lampiran], ditemukan bahwa [uraikan fakta atau ketidaksesuaian yang ditemukan].</p>
<p><strong>2. Kriteria (Dasar Hukum / Standar):</strong></p>
<p>Ketentuan [Peraturan Menteri Dalam Negeri Nomor 86 Tahun 2017 Lampiran hal. 384 / PP No. 2 Tahun 2018] menyatakan bahwa [uraikan ketentuan standar baku].</p>
<p><strong>3. Sebab (Akar Masalah):</strong></p>
<p>Hal tersebut terjadi disebabkan oleh [uraikan faktor penyebab].</p>
<p><strong>4. Akibat (Dampak / Risiko):</strong></p>
<p>Mengakibatkan [uraikan potensi ketidaksinkronan target kinerja dan pagu indikatif].</p>`;

            const editor = typeof tinymce !== 'undefined' ? tinymce.get('modal_rich_editor_isi') : null;
            if (editor) {
                editor.insertContent(formatHtml);
            } else {
                const el = document.getElementById('modal_rich_editor_isi');
                if (el) el.value += (el.value ? '\n\n' : '') + formatHtml;
            }
        },

        insertRekomFormat() {
            const formatHtml = `<p>Direkomendasikan kepada <strong>Kepala Bappeda / Tim Anggaran Pemerintah Daerah (TAPD)</strong> agar:</p>
<ol>
    <li>Menyelaraskan dan menyesuaikan [uraikan tindakan korektif konkret 1];</li>
    <li>Melengkapi dokumen/data dukung berupa [uraikan perbaikan konkret 2];</li>
    <li>Memastikan konsistensi indikator dan target program sesuai dokumen perencanaan jangka menengah.</li>
</ol>`;

            const editor = typeof tinymce !== 'undefined' ? tinymce.get('modal_rich_editor_rekom') : null;
            if (editor) {
                editor.insertContent(formatHtml);
            } else {
                const el = document.getElementById('modal_rich_editor_rekom');
                if (el) el.value += (el.value ? '\n\n' : '') + formatHtml;
            }
        },

        submitForm(e) {
            if (typeof tinymce !== 'undefined') {
                const edIsi = tinymce.get('modal_rich_editor_isi');
                if (edIsi) edIsi.save();
                const edRekom = tinymce.get('modal_rich_editor_rekom');
                if (edRekom) edRekom.save();
                tinymce.triggerSave();
            }

            const isiVal = document.getElementById('modal_rich_editor_isi')?.value || '';
            const rekomVal = document.getElementById('modal_rich_editor_rekom')?.value || '';

            if (!isiVal.trim()) {
                alert('Uraian Catatan Temuan wajib diisi!');
                e.preventDefault();
                return false;
            }
            if (!rekomVal.trim()) {
                alert('Rekomendasi Perbaikan Dokumen wajib diisi!');
                e.preventDefault();
                return false;
            }
            return true;
        }
    };
}
</script>
@endpush
