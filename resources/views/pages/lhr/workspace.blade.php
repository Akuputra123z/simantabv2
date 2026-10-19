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
        <button type="button"
           onclick="window.requestSecurePdf('lhr_pdf', '{{ $lhr->uuid }}', 'LHR: {{ addslashes($lhr->nomor_lhr ?? 'DRAFT') }}')"
           class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-gray-800 px-3 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 shadow-sm cursor-pointer">
            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Unduh PDF
        </button>
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

{{-- Notifikasi Error Validasi Form --}}
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
    $step1Errors = collect($lintResult->errors)->filter(fn($e) => in_array($e['code'], ['L-05', 'L-06']) || str_starts_with($e['target'] ?? '', 'tanggal_') || str_starts_with($e['target'] ?? '', 'permohonan_') || str_starts_with($e['target'] ?? '', 'nomor_'))->count();
    $step1Warnings = collect($lintResult->warnings)->filter(fn($w) => str_starts_with($w['target'] ?? '', 'tanggal_') || str_starts_with($w['target'] ?? '', 'permohonan_'))->count();

    $step3Errors = collect($lintResult->errors)->filter(fn($e) => in_array($e['code'], ['L-02', 'L-03', 'L-04', 'L-07', 'L-08']) || str_starts_with($e['target'] ?? '', 'pengujian_') || str_starts_with($e['target'] ?? '', 'catatan_'))->count();
    $step3Warnings = collect($lintResult->warnings)->filter(fn($w) => in_array($w['code'], ['L-07', 'L-08']) || str_starts_with($w['target'] ?? '', 'pengujian_') || str_starts_with($w['target'] ?? '', 'catatan_'))->count();
@endphp

{{-- Banner Quality Gate Global --}}
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
                        Dokumen perlu ditindaklanjuti sebelum diajukan ke telaah Dalnis atau persetujuan Inspektur.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="setStep(3)"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold {{ $lintResult->hasErrors() ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-amber-600 text-white hover:bg-amber-700' }} shadow-sm transition">
                    Lihat Langkah 3: Temuan & Rekomendasi &rarr;
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
                    @else
                        <button type="button" @click="setStep(3)" class="text-[10px] font-bold text-blue-600 hover:underline shrink-0 ml-2 whitespace-nowrap">Perbaiki Langkah 3 &rarr;</button>
                    @endif
                </div>
            @endforeach
            @foreach($lintResult->warnings as $warn)
                <div class="flex items-center justify-between p-2 rounded-lg bg-white/80 dark:bg-gray-900/50 border border-amber-200/80">
                    <span class="text-amber-800 dark:text-amber-300 font-medium text-[11px] flex items-center gap-1.5">
                        <span class="font-mono font-bold text-[10px] bg-amber-100 text-amber-800 px-1 py-0.2 rounded">{{ $warn['code'] }}</span>
                        {{ $warn['message'] }}
                    </span>
                    @if(in_array($warn['code'], ['L-05', 'L-06']))
                        <button type="button" @click="setStep(1)" class="text-[10px] font-bold text-indigo-600 hover:underline shrink-0 ml-2 whitespace-nowrap">Cek Langkah 1 &rarr;</button>
                    @else
                        <button type="button" @click="setStep(3)" class="text-[10px] font-bold text-indigo-600 hover:underline shrink-0 ml-2 whitespace-nowrap">Cek Langkah 3 &rarr;</button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- ROOT CONTAINER DENGAN ALPINE.JS WIZARD 3 LANGKAH SELARAS                   --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div x-data="lhrWorkspace({{ $currentStep ?? 1 }})" class="space-y-6">

    {{-- PANEL PERSETUJUAN BERJENJANG & OUTPUT DOKUMEN (PERMANEN DI HEADER ATAS) --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            {{-- Bagian Alur Persetujuan --}}
            <div class="space-y-3 flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full {{ $lhr->isTerbit() ? 'bg-green-500' : 'bg-blue-500 animate-pulse' }}"></span>
                        Alur Persetujuan Dokumen LHR (FR-19)
                    </h3>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $lhr->isTerbit() ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">
                        {{ $lhr->status->label() }}
                    </span>
                </div>

                {{-- Indikator 3 Tahapan --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                    <div class="flex items-center gap-2 p-2 rounded-lg border {{ $lhr->status === \App\Enums\LhrStatus::Draft || $lhr->status === \App\Enums\LhrStatus::PerluPerbaikan ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/30' : ($lhr->status !== \App\Enums\LhrStatus::Draft ? 'border-green-300 bg-green-50/60 dark:bg-green-950/30 text-green-800' : 'border-gray-200 bg-gray-50/50') }}">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $lhr->status !== \App\Enums\LhrStatus::Draft ? 'bg-green-600 text-white' : 'bg-blue-600 text-white' }}">1</span>
                        <div class="truncate">
                            <div class="font-bold truncate">Penyusunan Tim</div>
                            <div class="text-[10px] text-gray-500">Ketua Tim Reviu</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-lg border {{ $lhr->status === \App\Enums\LhrStatus::MenungguTelaah ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/30' : (in_array($lhr->status, [\App\Enums\LhrStatus::MenungguPersetujuan, \App\Enums\LhrStatus::Terbit]) ? 'border-green-300 bg-green-50/60 dark:bg-green-950/30 text-green-800' : 'border-gray-200 bg-gray-50/50 text-gray-500') }}">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ in_array($lhr->status, [\App\Enums\LhrStatus::MenungguPersetujuan, \App\Enums\LhrStatus::Terbit]) ? 'bg-green-600 text-white' : ($lhr->status === \App\Enums\LhrStatus::MenungguTelaah ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600') }}">2</span>
                        <div class="truncate">
                            <div class="font-bold truncate">Telaah Pengendali</div>
                            <div class="text-[10px] text-gray-500">Dalnis / Irban</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-lg border {{ $lhr->status === \App\Enums\LhrStatus::MenungguPersetujuan ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/30' : ($lhr->isTerbit() ? 'border-green-300 bg-green-50/60 dark:bg-green-950/30 text-green-800' : 'border-gray-200 bg-gray-50/50 text-gray-500') }}">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $lhr->isTerbit() ? 'bg-green-600 text-white' : ($lhr->status === \App\Enums\LhrStatus::MenungguPersetujuan ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600') }}">3</span>
                        <div class="truncate">
                            <div class="font-bold truncate">Persetujuan & TTD</div>
                            <div class="text-[10px] text-gray-500">Kepala Inspektorat</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi Persetujuan Berdasarkan Status --}}
            <div class="shrink-0 flex items-center gap-2">
                @if($lhr->status === \App\Enums\LhrStatus::Draft || $lhr->status === \App\Enums\LhrStatus::PerluPerbaikan)
                    <form method="POST" action="{{ route('lhr.submit', $lhr->uuid) }}" onsubmit="return confirm('Kirim draf LHR ini ke Pengendali Teknis untuk penelaahan?')">
                        @csrf
                        <button type="submit"
                                class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition flex items-center gap-2 cursor-pointer">
                            <span>📤 Kirim ke Pengendali Teknis</span>
                        </button>
                    </form>
                @elseif($lhr->status === \App\Enums\LhrStatus::MenungguTelaah)
                    <form method="POST" action="{{ route('lhr.review', $lhr->uuid) }}">
                        @csrf
                        <input type="hidden" name="action" value="setujui">
                        <button type="submit" onclick="return confirm('Setujui dan teruskan ke Kepala Inspektorat?')"
                                class="rounded-xl bg-green-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-green-700 shadow-sm transition cursor-pointer">
                            ✓ Setujui & Teruskan ke Inspektur
                        </button>
                    </form>
                    <form method="POST" action="{{ route('lhr.review', $lhr->uuid) }}">
                        @csrf
                        <input type="hidden" name="action" value="kembalikan">
                        <button type="submit" onclick="return confirm('Kembalikan LHR ke Ketua Tim untuk perbaikan?')"
                                class="rounded-xl bg-amber-600 px-3 py-2.5 text-xs font-bold text-white hover:bg-amber-700 transition cursor-pointer">
                            ↩ Kembalikan
                        </button>
                    </form>
                @elseif($lhr->status === \App\Enums\LhrStatus::MenungguPersetujuan)
                    <form method="POST" action="{{ route('lhr.approve', $lhr->uuid) }}" onsubmit="return confirm('Tandatangani dan terbitkan nomor resmi LHR sekarang?')">
                        @csrf
                        <button type="submit"
                                class="rounded-xl bg-purple-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-purple-700 shadow-sm transition flex items-center gap-2 cursor-pointer">
                            <span>🔏 Tandatangani & Terbitkan LHR Resmi</span>
                        </button>
                    </form>
                @elseif($lhr->isTerbit())
                    <div class="px-4 py-2 rounded-xl bg-green-50 border border-green-200 text-green-800 text-xs font-bold flex items-center gap-2 dark:bg-green-950/40 dark:border-green-800 dark:text-green-300">
                        <span>✓ Terbit Resmi</span>
                        <span class="font-mono text-gray-600 dark:text-gray-400">({{ $lhr->nomor_lhr }})</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- STEPPER BAR (3 LANGKAH: 1. SURAT & TIM, 2. ACUAN & DOKUMEN, 3. TEMUAN & REKOMENDASI) --}}
    <div class="space-y-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
            <div class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2">
                <span>Alur Kerja Reviu LHR</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 font-normal">
                    Mode: <strong x-text="viewMode === 'all' ? 'Tampilkan Semua Langkah' : 'Langkah ' + step"></strong>
                </span>
            </div>
            
            <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl dark:bg-gray-800 shrink-0">
                <button type="button" @click="setViewMode('wizard')"
                        :class="viewMode === 'wizard' ? 'bg-white text-blue-700 shadow-sm font-bold dark:bg-gray-700 dark:text-blue-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-medium'"
                        class="px-2.5 py-1 text-xs rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span>Mode Wizard (Per Langkah)</span>
                </button>
                <button type="button" @click="setViewMode('all')"
                        :class="viewMode === 'all' ? 'bg-white text-blue-700 shadow-sm font-bold dark:bg-gray-700 dark:text-blue-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-medium'"
                        class="px-2.5 py-1 text-xs rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span>Tampilkan Semua Langkah</span>
                </button>
            </div>
        </div>

        {{-- STEPPER BAR (3 TAB SEIMBANG) --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                
                {{-- Step 1 --}}
                <button type="button" @click="setStep(1)"
                        :class="step === 1 ? 'border-blue-600 bg-blue-50/80 text-blue-700 dark:bg-blue-950/50 dark:border-blue-500 dark:text-blue-300 shadow-sm ring-2 ring-blue-500/20' : 'border-gray-200 hover:border-gray-300 text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                        class="flex flex-col text-left rounded-xl border p-3.5 transition duration-150 cursor-pointer">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold"
                              :class="step === 1 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                            1
                        </span>
                        @if($step1Errors > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                {{ $step1Errors }} Galat
                            </span>
                        @else
                            <span class="text-[10px] uppercase font-bold text-gray-400">Identitas</span>
                        @endif
                    </div>
                    <div class="font-bold text-sm truncate">1. Surat & Tim Reviu</div>
                    <div class="text-[11px] text-gray-500 truncate mt-0.5">Penugasan SPT, Nomor LHR & Tim</div>
                </button>

                {{-- Step 2 --}}
                <button type="button" @click="setStep(2)"
                        :class="step === 2 ? 'border-blue-600 bg-blue-50/80 text-blue-700 dark:bg-blue-950/50 dark:border-blue-500 dark:text-blue-300 shadow-sm ring-2 ring-blue-500/20' : 'border-gray-200 hover:border-gray-300 text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                        class="flex flex-col text-left rounded-xl border p-3.5 transition duration-150 cursor-pointer">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold"
                              :class="step === 2 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                            2
                        </span>
                        <span class="text-[10px] uppercase font-bold text-gray-400">Acuan & Dokumen</span>
                    </div>
                    <div class="font-bold text-sm truncate">2. Dasar & Dokumen Pendukung</div>
                    <div class="text-[11px] text-gray-500 truncate mt-0.5">7 Regulasi & Lampiran Berkas Gambar</div>
                </button>

                {{-- Step 3 --}}
                <button type="button" @click="setStep(3)"
                        :class="step === 3 ? 'border-blue-600 bg-blue-50/80 text-blue-700 dark:bg-blue-950/50 dark:border-blue-500 dark:text-blue-300 shadow-sm ring-2 ring-blue-500/20' : 'border-gray-200 hover:border-gray-300 text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                        class="flex flex-col text-left rounded-xl border p-3.5 transition duration-150 cursor-pointer">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold"
                              :class="step === 3 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                            3
                        </span>
                        @if($step3Errors > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                {{ $step3Errors }} Galat
                            </span>
                        @else
                            <span class="text-[10px] uppercase font-bold text-green-600 dark:text-green-400">Lolos</span>
                        @endif
                    </div>
                    <div class="font-bold text-sm truncate">3. Catatan Temuan & Rekomendasi</div>
                    <div class="text-[11px] text-gray-500 truncate mt-0.5">14 Catatan 4-Elemen & Rekomendasi SMART</div>
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
                            Nomor LHR Resmi (Standar Format Rembang)
                        </label>
                        <input type="text" name="nomor_lhr" value="{{ old('nomor_lhr', $lhr->nomor_lhr) }}" placeholder="700.1.2.2/ 011 /2026" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 font-mono focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Kosongkan jika ingin digenerate otomatis saat penetapan naskah final.</p>
                    </div>

                    {{-- Tanggal LHR --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Pengesahan Naskah LHR
                        </label>
                        <input type="date" name="tanggal_lhr" value="{{ old('tanggal_lhr', $lhr->tanggal_lhr?->format('Y-m-d')) }}" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">
                            Tanggal pengesahan naskah. Sesuai standar audit (L-05), tanggal LHR harus <strong>sama dengan atau setelah tanggal selesai reviu</strong> (minimal: <strong>{{ $lhr->tanggal_selesai?->translatedFormat('d F Y') ?? '-' }}</strong>).
                        </p>
                    </div>

                    {{-- Pejabat Penandatangan --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Pejabat Penandatangan (Inspektur)
                        </label>
                        <select name="ditandatangani_oleh" data-no-ts {{ !$lhr->canEdit() ? 'disabled' : '' }}
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            <option value="">-- Pilih Pejabat Penandatangan --</option>
                            @foreach($signers as $s)
                                <option value="{{ $s->id }}" {{ (old('ditandatangani_oleh', $lhr->ditandatangani_oleh) == $s->id) ? 'selected' : '' }}>
                                    {{ $s->name }} ({{ $s->jabatan ?? 'Inspektur' }}) - NIP. {{ $s->nip ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nomor & Tanggal Permohonan OPD --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                No. Permohonan Bappeda
                            </label>
                            <input type="text" name="permohonan_nomor" value="{{ old('permohonan_nomor', $lhr->permohonan_nomor) }}" placeholder="050/094/2026" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Tgl. Permohonan Bappeda
                            </label>
                            <input type="date" name="permohonan_tanggal" value="{{ old('permohonan_tanggal', $lhr->permohonan_tanggal?->format('Y-m-d')) }}" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    {{-- Periode Pelaksanaan Reviu --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Mulai Reviu
                        </label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $lhr->tanggal_mulai?->format('Y-m-d')) }}" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Selesai Reviu
                        </label>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $lhr->tanggal_selesai?->format('Y-m-d')) }}" {{ !$lhr->canEdit() ? 'disabled' : '' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                {{-- Tabel Susunan Tim Reviu --}}
                <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3 flex items-center justify-between">
                        <span>Susunan Tim Reviu Berdasarkan SPT ({{ $lhr->auditAssignment->nomor_surat ?? '-' }})</span>
                        <span class="text-[10px] text-gray-400 font-normal">Tersinkronisasi otomatis dari modul SPT</span>
                    </h3>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                <tr>
                                    <th class="py-2.5 px-4">Peran Tim</th>
                                    <th class="py-2.5 px-4">Nama Lengkap</th>
                                    <th class="py-2.5 px-4">NIP</th>
                                    <th class="py-2.5 px-4">Jabatan Kedinasan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr>
                                    <td class="px-4 py-2.5 font-bold text-blue-700 dark:text-blue-400">Penanggung Jawab</td>
                                    <td class="px-4 py-2.5 font-semibold">{{ $signer->name ?? ($signers->first()?->name ?? 'Imron Achmad, SH, MH') }}</td>
                                    <td class="px-4 py-2.5 font-mono">{{ $signer->nip ?? ($signers->first()?->nip ?? '19700305 199603 1 004') }}</td>
                                    <td class="px-4 py-2.5">Inspektur Daerah Kab. Rembang</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2.5 font-bold text-indigo-700 dark:text-indigo-400">Pengendali Teknis (Dalnis)</td>
                                    <td class="px-4 py-2.5 font-semibold">{{ $lhr->auditAssignment->pengendaliTeknis->name ?? 'Dwi Susilowati, S.Kom., M.Si.' }}</td>
                                    <td class="px-4 py-2.5 font-mono">{{ $lhr->auditAssignment->pengendaliTeknis->nip ?? '19790403 200604 2 018' }}</td>
                                    <td class="px-4 py-2.5">{{ $lhr->auditAssignment->pengendaliTeknis->jabatan ?? 'Inspektur Pembantu Wilayah II' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2.5 font-bold text-emerald-700 dark:text-emerald-400">Ketua Tim Reviu</td>
                                    <td class="px-4 py-2.5 font-semibold">{{ $lhr->auditAssignment->ketuaTim->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 font-mono">{{ $lhr->auditAssignment->ketuaTim->nip ?? '-' }}</td>
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
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100 dark:border-gray-800">
                    @if($lhr->canEdit())
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition cursor-pointer">
                            <span>Simpan & Lanjut ke Langkah 2: Acuan & Dokumen Pendukung</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    @else
                        <button type="button" @click="setStep(2)"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition cursor-pointer">
                            <span>Lanjut ke Langkah 2: Acuan & Dokumen Pendukung</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- LANGKAH 2: DASAR HUKUM & BERKAS DOKUMEN PENDUKUNG (LAMPIRAN/IMAGES)     --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="step-section-2" x-show="viewMode === 'all' || step === 2" x-cloak class="space-y-6">
        
        {{-- Dasar Hukum & Regulasi Acuan Standar --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4 dark:border-gray-800">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <span>📜 Dasar Hukum & Regulasi Acuan Reviu</span>
                        <span class="text-[10px] font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full dark:bg-blue-900/40 dark:text-blue-300">FR-09</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pedoman standar pengawasan yang menjadi acuan formal pelaksanaan reviu.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg text-gray-600 dark:text-gray-400">7 Regulasi Resmi</span>
            </div>
            @php
                $dasarHukums = [
                    ['urutan' => 1, 'nomor' => 'Permendagri No. 86 Tahun 2017', 'sub' => 'Tata Cara Perencanaan, Pengendalian & Evaluasi', 'deskripsi' => 'Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang RPJPD dan RPJMD, Serta Tata Cara Perubahan RPJPD, RPJMD, dan RKPD.'],
                    ['urutan' => 2, 'nomor' => 'Permendagri No. 10 Tahun 2018', 'sub' => 'Pedoman Reviu Perencanaan & Anggaran Tahunan', 'deskripsi' => 'Reviu atas Dokumen Perencanaan Pembangunan dan Anggaran Daerah Tahunan.'],
                    ['urutan' => 3, 'nomor' => 'Permendagri No. 18 Tahun 2020', 'sub' => 'Peraturan Pelaksanaan PP No. 13 Tahun 2019', 'deskripsi' => 'Peraturan Pelaksanaan Peraturan Pemerintah Nomor 13 Tahun 2019 tentang Laporan dan Evaluasi Penyelenggaraan Pemerintahan Daerah.'],
                    ['urutan' => 4, 'nomor' => 'Perda Kab. Rembang No. 6 Tahun 2021', 'sub' => 'RPJMD Kabupaten Rembang Tahun 2021-2026', 'deskripsi' => 'Rencana Pembangunan Jangka Menengah Daerah (RPJMD) Kabupaten Rembang Tahun 2021-2026.'],
                    ['urutan' => 5, 'nomor' => 'Perbup Rembang No. 43 Tahun 2021', 'sub' => 'Kedudukan, Susunan Organisasi & Uraian Tugas', 'deskripsi' => 'Kedudukan, Susunan Organisasi, Tugas dan Fungsi Serta Tata Kerja Inspektorat Daerah Kabupaten Rembang.'],
                    ['urutan' => 6, 'nomor' => 'PKPT Inspektorat Rembang Tahun 2026', 'sub' => 'Program Kerja Pengawasan Tahunan', 'deskripsi' => 'Keputusan Inspektur Daerah Kabupaten Rembang tentang Penetapan PKPT Tahun Berkenaan.'],
                    ['urutan' => 7, 'nomor' => 'Standar Audit Intern Pemerintah Indonesia', 'sub' => 'Kode Etik & Standar Mutu APIP', 'deskripsi' => 'Standar Audit Intern Pemerintah Indonesia (SAIPI) Asosiasi Auditor Intern Pemerintah Indonesia (AAIPI).'],
                ];
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                @foreach($dasarHukums as $dh)
                    <div class="p-2.5 rounded-lg border border-gray-100 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                        <div class="font-bold text-gray-800 dark:text-gray-200">{{ $dh['urutan'] }}. {{ $dh['nomor'] }}</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $dh['sub'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Berkas Lampiran & Galeri Foto / Gambar Pendukung --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                        📎
                    </span>
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                            Berkas Lampiran Dokumen Pendukung ({{ $lhr->attachments->count() }})
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Berkas dokumen dan gambar yang otomatis masuk ke laporan LHR (PDF, Word & Pratinjau).</p>
                    </div>
                </div>
                @if($lhr->canEdit())
                    <a href="{{ route('lhr.edit', $lhr->uuid) }}#seksi-lampiran"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300">
                        <span>+ Kelola / Unggah Lampiran</span>
                    </a>
                @endif
            </div>

            @if($lhr->attachments->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center dark:border-gray-800 text-xs text-gray-500">
                    Belum ada berkas lampiran yang diunggah untuk dokumen LHR ini.
                </div>
            @else
                {{-- Tabel Daftar Berkas --}}
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 mb-6">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                            <tr>
                                <th class="py-2.5 px-4 w-12 text-center">No</th>
                                <th class="py-2.5 px-4">Nama Berkas</th>
                                <th class="py-2.5 px-4">Keterangan</th>
                                <th class="py-2.5 px-4 w-24 text-right">Ukuran</th>
                                <th class="py-2.5 px-4 w-28 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($lhr->attachments as $idx => $att)
                                <tr>
                                    <td class="py-2 px-4 text-center text-gray-400">{{ $idx + 1 }}</td>
                                    <td class="py-2 px-4 font-semibold text-gray-900 dark:text-white">{{ $att->file_name }}</td>
                                    <td class="py-2 px-4 text-gray-500">{{ $att->keterangan ?: '-' }}</td>
                                    <td class="py-2 px-4 text-right font-mono text-gray-500">{{ $att->formatted_size }}</td>
                                    <td class="py-2 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('attachments.show', $att) }}" target="_blank"
                                               class="p-1 rounded text-blue-600 hover:bg-blue-50" title="Buka Berkas">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ route('attachments.download', $att) }}"
                                               class="p-1 rounded text-emerald-600 hover:bg-emerald-50" title="Unduh Berkas">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Galeri Foto / Gambar Lampiran --}}
                @php
                    $imageAttachments = $lhr->attachments->filter(fn($a) => $a->isImage());
                @endphp
                @if($imageAttachments->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-1.5">
                            <span>🖼️ Pratinjau Bukti Gambar / Foto Lampiran ({{ $imageAttachments->count() }})</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($imageAttachments as $i => $imgAtt)
                                <div class="rounded-xl border border-gray-200 overflow-hidden bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40">
                                    <div class="h-40 overflow-hidden bg-gray-100 dark:bg-gray-900 flex items-center justify-center p-2">
                                        <img src="{{ route('attachments.show', $imgAtt) }}" alt="{{ $imgAtt->file_name }}"
                                             class="max-h-full max-w-full object-contain rounded">
                                    </div>
                                    <div class="p-3 border-t border-gray-100 dark:border-gray-800 text-xs">
                                        <div class="font-bold text-gray-900 dark:text-white truncate" title="{{ $imgAtt->file_name }}">
                                            Gambar {{ $i + 1 }}: {{ $imgAtt->file_name }}
                                        </div>
                                        <p class="text-[11px] text-gray-500 mt-0.5 truncate">{{ $imgAtt->keterangan ?: 'Tanpa keterangan' }}</p>
                                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-100 dark:border-gray-800/80 text-[10px] text-gray-400">
                                            <span>{{ $imgAtt->formatted_size }}</span>
                                            <a href="{{ route('attachments.show', $imgAtt) }}" target="_blank" class="text-blue-600 hover:underline font-semibold">Buka Penuh &rarr;</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Navigasi Step 2 --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="button" @click="setStep(1)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 cursor-pointer">
                &larr; Kembali ke Langkah 1: Surat & Tim
            </button>
            <button type="button" @click="setStep(3)"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition cursor-pointer">
                <span>Lanjut ke Langkah 3: Catatan Temuan & Rekomendasi</span>
                &rarr;
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- LANGKAH 3: CATATAN TEMUAN & REKOMENDASI PERBAIKAN                      --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="step-section-3" x-show="viewMode === 'all' || step === 3" x-cloak class="space-y-6">
        
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
                            class="rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-indigo-700 shadow-sm transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
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
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-bold">3</span>
                        Langkah 3: Catatan Temuan & Rekomendasi Perbaikan
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
                                                        'updateUrl' => route('lhr.update-catatan', [$lhr->uuid, $cat->id, 'step' => 3]),
                                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
                                                    </script>
                                                    <button type="button"
                                                            @click="openEditCatatanModalById({{ $cat->id }})"
                                                            class="text-xs font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 cursor-pointer">
                                                        Ubah
                                                    </button>
                                                    <form method="POST" action="{{ route('lhr.destroy-catatan', [$lhr->uuid, $cat->id, 'step' => 3]) }}" onsubmit="return confirm('Hapus temuan ini?')">
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

                                        {{-- Panel Revisi & Tindak Lanjut OPD (Aktif saat LHR Resmi Terbit / Tindak Lanjut) --}}
                                        @php
                                            $rekom = $cat->rekomendasis->first();
                                            $tl = $rekom?->tindakLanjuts?->first();
                                            $isPublishedStage = in_array($lhr->status, [\App\Enums\LhrStatus::Terbit, \App\Enums\LhrStatus::TindakLanjut, \App\Enums\LhrStatus::Selesai], true);
                                        @endphp

                                        @if($isPublishedStage && $rekom)
                                            @php
                                                $uraianOpd = $tl?->keterangan_pendukung_opd ?: $tl?->uraian_tindak_lanjut;
                                                $isDikirim = $tl && $tl->status_opd === 'dikirim' && empty($tl->alasan_tolak_opd);
                                                $isDitolak = $tl && !empty($tl->alasan_tolak_opd);
                                                $isSelesai = $tl && $tl->status_verifikasi === 'lunas';
                                                $isDraft = $tl && $tl->status_opd === 'draft' && empty($tl->alasan_tolak_opd);
                                                $isBelum = !$tl || (empty($tl->status_opd) && empty($uraianOpd) && $tl->attachments->isEmpty());

                                                $canVerify = auth()->user()->hasAnyRole([
                                                    \App\Models\User::ROLE_SUPER_ADMIN,
                                                    \App\Models\User::ROLE_KEPALA_INSPEKTORAT,
                                                    \App\Models\User::ROLE_IRBAN,
                                                    \App\Models\User::ROLE_PENGENDALI_TEKNIS,
                                                    'admin',
                                                    'admin_inspektorat',
                                                    \App\Models\User::ROLE_KETUA_TIM,
                                                ]);
                                            @endphp

                                            <div class="mt-3.5 pt-3 border-t border-dashed border-gray-200 dark:border-gray-700">
                                                <div class="rounded-xl border {{ $isSelesai ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-900/40 dark:bg-emerald-950/20' : ($isDikirim ? 'border-blue-300 bg-blue-50/60 dark:border-blue-900/50 dark:bg-blue-950/30' : ($isDitolak ? 'border-rose-200 bg-rose-50/50 dark:border-rose-900/40 dark:bg-rose-950/20' : 'border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40')) }} p-3.5 transition">
                                                    
                                                    {{-- Header Tindak Lanjut --}}
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                                Revisi & Tindak Lanjut OPD:
                                                            </span>
                                                        </div>

                                                        {{-- Badge Status Verifikasi --}}
                                                        <div>
                                                            @if($isSelesai)
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-300">
                                                                    ✓ Disetujui / Selesai
                                                                </span>
                                                            @elseif($isDikirim)
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-300 animate-pulse">
                                                                    🕒 Revisi Masuk - Menunggu Telaah Irban
                                                                </span>
                                                            @elseif($isDitolak)
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300 border border-rose-300">
                                                                    ⚠️ Dikembalikan ke OPD (Perlu Perbaikan)
                                                                </span>
                                                            @elseif($isDraft)
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 border border-amber-300">
                                                                    ✏️ Draf Disimpan OPD (Belum Dikirim)
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                                                    Belum Ada Pengiriman OPD
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Isi Uraian & Berkas Lampiran dari OPD --}}
                                                    @if($uraianOpd || ($tl && $tl->attachments->isNotEmpty()))
                                                        <div class="space-y-2 mb-3">
                                                            @if($uraianOpd)
                                                                <div class="text-xs text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-900/60 p-2.5 rounded-lg border border-gray-200/70 dark:border-gray-700/70 leading-relaxed">
                                                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Uraian Perbaikan OPD:</div>
                                                                    {!! nl2br(e($uraianOpd)) !!}
                                                                </div>
                                                            @endif

                                                            @if($tl && $tl->attachments->isNotEmpty())
                                                                <div>
                                                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">Berkas Naskah Perbaikan ({{ $tl->attachments->count() }} file):</div>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        @foreach($tl->attachments as $att)
                                                                            <a href="{{ route('attachments.download', $att->id) }}"
                                                                               target="_blank"
                                                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white text-blue-700 border border-blue-200 hover:bg-blue-50 dark:bg-gray-900 dark:text-blue-300 dark:border-blue-800 shadow-xs transition">
                                                                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                                                <span class="truncate max-w-[200px]" title="{{ $att->file_name }}">{{ $att->file_name }}</span>
                                                                                <span class="text-[9px] text-gray-400">({{ number_format($att->file_size / 1024, 0) }} KB)</span>
                                                                            </a>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <div class="flex items-center gap-3 text-[10px] text-gray-500 dark:text-gray-400 pt-1">
                                                                @if($tl->uploadOpdOleh)
                                                                    <span>Pengunggah: <strong>{{ $tl->uploadOpdOleh->name }}</strong></span>
                                                                @endif
                                                                @if($tl->dikirim_pada)
                                                                    <span>Dikirim: <strong>{{ $tl->dikirim_pada->translatedFormat('d F Y H:i') }}</strong></span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="text-[11px] text-gray-500 italic mb-2">
                                                            OPD belum mengunggah naskah perbaikan atau uraian tindak lanjut untuk butir rekomendasi ini.
                                                        </div>
                                                    @endif

                                                    {{-- Catatan Penolakan Sebelumnya --}}
                                                    @if($isDitolak && !empty($tl->alasan_tolak_opd))
                                                        <div class="mb-3 p-2.5 rounded-lg bg-rose-100/60 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-xs text-rose-800 dark:text-rose-300">
                                                            <div class="font-bold text-[10px] uppercase tracking-wider mb-0.5">Catatan Koreksi Irban:</div>
                                                            {{ $tl->alasan_tolak_opd }}
                                                        </div>
                                                    @endif

                                                    {{-- Info Verifikasi Selesai --}}
                                                    @if($isSelesai)
                                                        <div class="text-[11px] text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
                                                            <span>Diverifikasi oleh: <strong>{{ $tl->verifikator->name ?? 'Pengendali Teknis / Irban' }}</strong> pada {{ $tl->diverifikasi_pada ? $tl->diverifikasi_pada->translatedFormat('d F Y H:i') : '-' }}</span>
                                                            @if($canVerify)
                                                                <form method="POST" action="{{ route('lhr.tindak-lanjut.verifikasi', [$lhr->uuid, $tl]) }}" onsubmit="return confirm('Kembalikan status verifikasi menjadi Dalam Proses?')">
                                                                    @csrf
                                                                    <input type="hidden" name="status_verifikasi" value="berjalan">
                                                                    <button type="submit" class="text-[10px] text-gray-500 hover:text-gray-800 underline cursor-pointer">Ubah ke Berjalan</button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    {{-- Tombol Aksi Verifikasi / Tolak bagi Irban & Dalnis --}}
                                                    @if($canVerify && $tl && !$isSelesai)
                                                        <div x-data="{ openTolak: false }" class="mt-2.5 pt-2.5 border-t border-gray-200/80 dark:border-gray-700/80 flex flex-wrap items-center justify-end gap-2">
                                                            {{-- Tombol Tolak / Minta Perbaikan --}}
                                                            <button type="button"
                                                                    @click="openTolak = !openTolak"
                                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 dark:bg-gray-800 dark:border-rose-900/60 dark:text-rose-300 shadow-xs transition cursor-pointer">
                                                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                Tolak / Minta Revisi Ulang
                                                            </button>

                                                            {{-- Tombol Setujui (Lunas/Selesai) --}}
                                                            <form method="POST" action="{{ route('lhr.tindak-lanjut.verifikasi', [$lhr->uuid, $tl]) }}" onsubmit="return confirm('Apakah Anda yakin menyetujui naskah revisi dari OPD untuk rekomendasi butir ini?')">
                                                                @csrf
                                                                <input type="hidden" name="status_verifikasi" value="lunas">
                                                                <button type="submit"
                                                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs transition cursor-pointer">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                    Setujui Revisi (Selesai)
                                                                </button>
                                                            </form>

                                                            {{-- Dropdown / Form Alasan Tolak --}}
                                                            <div x-show="openTolak" x-cloak class="w-full mt-2 p-3 rounded-xl border border-rose-200 bg-white shadow-sm dark:bg-gray-900 dark:border-rose-900/60">
                                                                <form method="POST" action="{{ route('lhr.tindak-lanjut.tolak', [$lhr->uuid, $tl]) }}">
                                                                    @csrf
                                                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                                                        Catatan Koreksi / Alasan Penolakan untuk OPD:
                                                                    </label>
                                                                    <textarea name="alasan_tolak" rows="2" required placeholder="Tuliskan bagian mana yang belum sesuai dan harus diperbaiki oleh OPD..." class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-700 p-2 dark:bg-gray-800 dark:text-white focus:ring-rose-500 focus:border-rose-500"></textarea>
                                                                    <div class="mt-2 flex items-center justify-end gap-2">
                                                                        <button type="button" @click="openTolak = false" class="px-2.5 py-1 text-xs text-gray-500 hover:text-gray-700 cursor-pointer">Batal</button>
                                                                        <button type="submit" class="px-3 py-1 text-xs font-bold bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition cursor-pointer">Kirim Catatan ke OPD</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @endif

                                                </div>
                                            </div>
                                        @endif
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

        {{-- Struktur Evaluasi Bab Sesuai Template Resmi (FR-11) --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4 dark:border-gray-800">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                    <span>📑 Struktur Evaluasi Bab Sesuai Template Resmi</span>
                </h3>
                <span class="text-[10px] font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded">FR-11 Sistematika Bab I - VI</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="font-bold text-gray-800 dark:text-gray-200 mb-1">Bab I</div>
                    <div class="text-[11px] text-gray-500 font-medium">Pendahuluan (Latar Belakang, Dasar Hukum, Maksud & Tujuan)</div>
                </div>
                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="font-bold text-gray-800 dark:text-gray-200 mb-1">Bab II</div>
                    <div class="text-[11px] text-gray-500 font-medium">Evaluasi Kinerja & Gambaran Umum Daerah (Indikator Makro)</div>
                </div>
                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="font-bold text-gray-800 dark:text-gray-200 mb-1">Bab III</div>
                    <div class="text-[11px] text-gray-500 font-medium">Kerangka Ekonomi & Proyeksi Keuangan Daerah (PAD & Belanja)</div>
                </div>
                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="font-bold text-gray-800 dark:text-gray-200 mb-1">Bab IV</div>
                    <div class="text-[11px] text-gray-500 font-medium">Sasaran & Prioritas Pembangunan Daerah (Penyelarasan Nasional)</div>
                </div>
                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="font-bold text-gray-800 dark:text-gray-200 mb-1">Bab V</div>
                    <div class="text-[11px] text-gray-500 font-medium">Rencana Kerja & Pendanaan Daerah (Pagu Indikatif OPD)</div>
                </div>
                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="font-bold text-gray-800 dark:text-gray-200 mb-1">Bab VI</div>
                    <div class="text-[11px] text-gray-500 font-medium">Penutup & Kaidah Pelaksanaan Rancangan Akhir RKPD</div>
                </div>
            </div>
        </div>

        {{-- Navigasi Step 3 --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="button" @click="setStep(2)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 cursor-pointer">
                &larr; Kembali ke Langkah 2: Acuan & Dokumen Pendukung
            </button>
        </div>
    </div>

    {{-- MODAL TAMBAH / UBAH CATATAN TEMUAN & REKOMENDASI --}}
    <div x-show="catatanModal.open" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 space-y-4 max-h-[90vh] overflow-y-auto"
             @click.away="closeCatatanModal()">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300" x-text="catatanModal.pengujianKode"></span>
                        <span x-text="catatanModal.isEdit ? 'Ubah Catatan Temuan & Rekomendasi' : 'Tambah Catatan Temuan & Rekomendasi'"></span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5 truncate max-w-md" x-text="catatanModal.pengujianJudul"></p>
                </div>
                <button type="button" @click="closeCatatanModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="catatanModal.actionUrl" method="POST" class="space-y-4" @submit="submitForm($event)">
                @csrf
                <template x-if="catatanModal.isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Kategori Catatan --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Kategori Permasalahan <span class="text-red-500">*</span>
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
        step: Math.min(3, Math.max(1, parseInt(initialStep) || 1)),
        viewMode: initialMode,

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
            const validStep = Math.min(3, Math.max(1, parseInt(num) || 1));
            this.step = validStep;
            const url = new URL(window.location);
            url.searchParams.set('step', validStep);
            window.history.replaceState({}, '', url);

            if (this.viewMode === 'all') {
                const target = document.getElementById('step-section-' + validStep);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }
            }
            window.scrollTo({ top: 120, behavior: 'smooth' });
        },

        openTambahCatatanModal(pengujianId, kode, judul) {
            this.catatanModal.open = true;
            this.catatanModal.isEdit = false;
            this.catatanModal.pengujianId = pengujianId;
            this.catatanModal.pengujianKode = kode;
            this.catatanModal.pengujianJudul = judul || '';
            this.catatanModal.actionUrl = '{{ url('/lhr/' . $lhr->uuid . '/pengujian') }}/' + pengujianId + '/catatan?step=3';
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
