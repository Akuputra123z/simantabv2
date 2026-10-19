@extends('layouts.app')

@section('content')

{{-- Clean Form Styling for TomSelect Dropdowns --}}
<style>
    /* Reset wrapper to prevent double-border and padding inheritance */
    .ts-wrapper.ts-form-select {
        width: 100% !important;
        border: none !important;
        padding: 0 !important;
        background: transparent !important;
        height: auto !important;
        min-height: 0 !important;
        box-shadow: none !important;
        position: relative !important;
    }
    .ts-wrapper.ts-form-select.dropdown-active {
        z-index: 50 !important;
    }
    .w-full:has(.ts-wrapper.dropdown-active) {
        position: relative !important;
        z-index: 40 !important;
    }

    /* Single input button control */
    .ts-form-select .ts-control {
        min-height: 48px !important;
        border-radius: 0.75rem !important;
        border: 1px solid #d1d5db !important;
        padding: 10px 36px 10px 14px !important;
        font-size: 0.875rem !important;
        background-color: #ffffff !important;
        cursor: pointer !important;
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
    .dark .ts-form-select .ts-control {
        border-color: #374151 !important;
        background-color: #1f2937 !important;
        color: #f3f4f6 !important;
    }
    .ts-form-select.focus .ts-control {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }

    /* Validation Error Styling for TomSelect */
    .has-ts-error .ts-form-select .ts-control {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;
    }
    .dark .has-ts-error .ts-form-select .ts-control {
        border-color: #f87171 !important;
        background-color: rgba(239, 68, 68, 0.15) !important;
    }

    /* Caret Chevron */
    .ts-form-select.single .ts-control::after {
        content: "";
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        pointer-events: none;
        transition: transform 0.2s ease;
    }
    .ts-form-select.single.dropdown-active .ts-control::after {
        transform: translateY(-50%) rotate(180deg);
    }
    .dark .ts-form-select.single .ts-control::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='2'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3e%3c/svg%3e");
    }

    /* Selected Item text */
    .ts-form-select .ts-control .item {
        color: #1f2937 !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .dark .ts-form-select .ts-control .item {
        color: #f3f4f6 !important;
    }
    /* Placeholder item text when empty */
    .ts-form-select .ts-control .item[data-value=""] {
        color: #9ca3af !important;
        font-weight: 400 !important;
    }
    .dark .ts-form-select .ts-control .item[data-value=""] {
        color: #6b7280 !important;
    }

    /* Hide the redundant items-placeholder input completely to prevent any duplicate/overlapped text */
    .ts-form-select .items-placeholder {
        display: none !important;
    }

    /* Dropdown container */
    .ts-form-select .ts-dropdown {
        border-radius: 0.75rem !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        z-index: 50 !important;
        margin-top: 4px !important;
        background-color: #ffffff !important;
        overflow: hidden !important;
    }
    .dark .ts-form-select .ts-dropdown {
        border-color: #374151 !important;
        background-color: #1f2937 !important;
    }

    /* Search box inside dropdown */
    .ts-form-select .dropdown-input-wrap {
        padding: 8px 10px !important;
        background-color: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
    }
    .dark .ts-form-select .dropdown-input-wrap {
        background-color: #111827 !important;
        border-bottom-color: #374151 !important;
    }
    .ts-form-select .dropdown-input {
        width: 100% !important;
        height: 38px !important;
        padding: 6px 12px !important;
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        background-color: #ffffff !important;
        font-size: 0.875rem !important;
        color: #111827 !important;
        outline: none !important;
        box-shadow: none !important;
    }
    .dark .ts-form-select .dropdown-input {
        background-color: #1f2937 !important;
        border-color: #4b5563 !important;
        color: #f9fafb !important;
    }
    .ts-form-select .dropdown-input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }

    /* Options list */
    .ts-form-select .ts-dropdown .ts-dropdown-content {
        max-height: 240px !important;
        overflow-y: auto !important;
        padding: 4px 0 !important;
    }
    /* Hide the empty placeholder option from dropdown list */
    .ts-form-select .ts-dropdown .option[data-value=""] {
        display: none !important;
    }
    .ts-form-select .ts-dropdown .option {
        padding: 10px 14px !important;
        font-size: 0.875rem !important;
        line-height: 1.4 !important;
        color: #374151 !important;
        cursor: pointer !important;
        border-bottom: 1px solid #f3f4f6 !important;
    }
    .dark .ts-form-select .ts-dropdown .option {
        color: #d1d5db !important;
        border-bottom-color: #374151 !important;
    }
    .ts-form-select .ts-dropdown .option:last-child {
        border-bottom: none !important;
    }
    .ts-form-select .ts-dropdown .option.active,
    .ts-form-select .ts-dropdown .option:hover {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
    }
    .dark .ts-form-select .ts-dropdown .option.active,
    .dark .ts-form-select .ts-dropdown .option:hover {
        background-color: rgba(59, 130, 246, 0.15) !important;
        color: #93c5fd !important;
    }
    .ts-form-select .ts-dropdown .option.selected {
        background-color: #f0fdf4 !important;
        color: #166534 !important;
        font-weight: 600 !important;
    }
    .dark .ts-form-select .ts-dropdown .option.selected {
        background-color: rgba(34, 197, 94, 0.15) !important;
        color: #86efac !important;
    }
</style>

{{-- Header Halaman --}}
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Buat Laporan Hasil Reviu (LHR) Baru</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Formulir lengkap tersinkronisasi dengan tata naskah dinas pengawasan dan template 26 halaman resmi Kabupaten Rembang
        </p>
    </div>
    <div>
        <a href="{{ route('lhr.index') }}"
           class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition">
            &larr; Kembali ke Daftar LHR
        </a>
    </div>
</div>

{{-- Container Form Full-Width --}}
<div class="w-full rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <form method="POST" action="{{ route('lhr.store') }}" enctype="multipart/form-data" class="space-y-8" id="formCreateLhr" novalidate>
        @csrf

        {{-- Alert Validasi Server-Side (Muncul jika ada error dari backend) --}}
        @if ($errors->any())
            <div id="serverValidationAlert" class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-800/80 dark:bg-red-950/40 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-900/60 dark:text-red-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-300">
                            Mohon lengkapi atau perbaiki isian formulir (terdapat {{ $errors->count() }} catatan):
                        </h3>
                        <ul class="mt-2 list-inside list-disc space-y-1 text-xs font-semibold text-red-700 dark:text-red-400">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-2.5 text-xs text-red-600 dark:text-red-400">
                            Bagian yang memerlukan perbaikan telah ditandai dengan garis merah di bawah.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Alert Validasi Client-Side (Muncul instan saat tombol ditekan jika ada field belum valid) --}}
        <div id="clientValidationAlert" class="hidden rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-800/80 dark:bg-red-950/40 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-900/60 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-red-800 dark:text-red-300" id="clientValidationTitle">
                        Formulir belum dapat disimpan:
                    </h3>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-xs font-semibold text-red-700 dark:text-red-400" id="clientValidationList">
                    </ul>
                    <p class="mt-2.5 text-xs text-red-600 dark:text-red-400">
                        Silakan periksa dan lengkapi field yang ditandai merah di bawah ini.
                    </p>
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 1: DASAR PENUGASAN & KLASIFIKASI ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-5 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm">1</span>
                    Dasar Penugasan & Klasifikasi Dokumen
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pilih Surat Perintah Tugas (SPT) dan jenis pengawasan perencanaan yang mendasari naskah LHR</p>
            </div>

            <div class="space-y-6">
                {{-- 1. Pilih SPT (Full-Width) --}}
                <div class="w-full @error('audit_assignment_id') has-ts-error @enderror" id="fieldWrapperSpt">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                        <span>Surat Perintah Tugas (SPT) Reviu <span class="text-red-500">*</span></span>
                        <span class="text-xs font-normal text-gray-500">Wajib dipilih</span>
                    </label>
                    <select name="audit_assignment_id" id="selectSpt" data-no-ts
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition cursor-pointer">
                        <option value="">-- Pilih Surat Perintah Tugas (SPT) Pengawasan --</option>
                        @foreach($assignments as $asg)
                            @php
                                $tahunAsg = $asg->auditProgram?->tahun ?? ($asg->auditProgramDetail?->auditProgram?->tahun ?? ($asg->tanggal_mulai ? $asg->tanggal_mulai->format('Y') : date('Y')));
                            @endphp
                            <option value="{{ $asg->id }}" 
                                    data-mulai="{{ $asg->tanggal_mulai ? $asg->tanggal_mulai->format('Y-m-d') : '' }}"
                                    data-selesai="{{ $asg->tanggal_selesai ? $asg->tanggal_selesai->format('Y-m-d') : '' }}"
                                    data-tahun="{{ $tahunAsg }}"
                                    data-mulai-formatted="{{ $asg->tanggal_mulai ? $asg->tanggal_mulai->translatedFormat('d F Y') : '-' }}"
                                    data-selesai-formatted="{{ $asg->tanggal_selesai ? $asg->tanggal_selesai->translatedFormat('d F Y') : '-' }}"
                                    @selected(old('audit_assignment_id') == $asg->id)>
                                Nomor SPT: {{ $asg->nomor_surat ?? 'SPT Tanpa Nomor' }} &bull; Tim: {{ $asg->nama_tim ?? 'Tim Reviu' }} &bull; Ketua: {{ $asg->ketuaTim?->name ?? '-' }} (TA {{ $tahunAsg }})
                            </option>
                        @endforeach
                    </select>

                    @if($assignments->isEmpty())
                        <div class="mt-2.5 rounded-xl border border-amber-200/80 bg-amber-50/80 p-3 text-xs text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-300 flex items-start gap-2">
                            <svg class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="font-semibold">Tidak ada SPT tersedia:</span> Seluruh SPT Reviu telah digunakan untuk dokumen LHR atau belum ada SPT dengan jenis pengawasan reviu.
                            </div>
                        </div>
                    @endif

                    {{-- Preview Sinkronisasi Otomatis dari Penugasan Audit --}}
                    <div id="sptSyncInfoBox" class="hidden mt-3 rounded-xl border border-blue-200/80 bg-blue-50/70 p-3.5 dark:border-blue-900/60 dark:bg-blue-950/30 transition-all">
                        <div class="flex items-start gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-600 text-[10px] font-bold text-white shadow-xs">✓</span>
                            <div class="text-xs">
                                <span class="font-bold text-blue-950 dark:text-blue-200">Tersinkronisasi Otomatis dari Penugasan Audit:</span>
                                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-blue-800 dark:text-blue-300">
                                    <span>Tahun Anggaran Dokumen: <strong id="syncTahunText" class="font-mono font-bold text-blue-950 dark:text-white">-</strong></span>
                                    <span>&bull;</span>
                                    <span>Jadwal Reviu: <strong id="syncJadwalText" class="font-mono font-bold text-blue-950 dark:text-white">-</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden input agar tetap terkirim dan tersinkronisasi otomatis --}}
                    <input type="hidden" name="tahun_anggaran" id="hiddenTahunAnggaran" value="{{ old('tahun_anggaran') }}">
                    <input type="hidden" name="tanggal_mulai" id="hiddenTanggalMulai" value="{{ old('tanggal_mulai') }}">
                    <input type="hidden" name="tanggal_selesai" id="hiddenTanggalSelesai" value="{{ old('tanggal_selesai') }}">

                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Nomor, tanggal SPT, Tahun Anggaran, dan Jadwal Reviu otomatis tersinkronisasi dari data penugasan audit. Hanya menampilkan SPT yang belum memiliki dokumen LHR.</p>
                    @error('audit_assignment_id')
                        <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400" id="errorSptServer">
                            <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                    <p class="hidden mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400" id="errorSptClient">
                        <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span id="errorSptClientText">Surat Perintah Tugas (SPT) Reviu wajib dipilih.</span>
                    </p>
                </div>

                {{-- 2. Jenis Dokumen Reviu (Full-Width dengan Card Selector Luas agar tidak terpotong) --}}
                {{-- 2. Jenis Dokumen Reviu (Dropdown Standar Bersih & Konsisten) --}}
                <div class="w-full @error('jenis_dokumen_reviu_id') has-error @enderror" id="fieldWrapperJenis">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                        <span>Jenis Dokumen Reviu <span class="text-red-500">*</span></span>
                        <span class="text-xs font-normal text-gray-500">Wajib dipilih</span>
                    </label>

                    <select name="jenis_dokumen_reviu_id" id="selectJenisDokumen" data-no-ts
                            class="h-12 w-full rounded-xl border @error('jenis_dokumen_reviu_id') border-red-500 bg-red-50/50 dark:bg-red-950/20 @else border-gray-300 @enderror bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition cursor-pointer">
                        <option value="">-- Pilih Jenis Dokumen Reviu --</option>
                        @foreach($jenisDokumens as $jd)
                            @php
                                $isDefault = old('jenis_dokumen_reviu_id') 
                                    ? (old('jenis_dokumen_reviu_id') == $jd->id) 
                                    : ($jd->kode === 'RKPD');
                            @endphp
                            <option value="{{ $jd->id }}" data-kode="{{ $jd->kode }}" @selected($isDefault)>
                                {{ $jd->kode }} — {{ $jd->nama }}
                            </option>
                        @endforeach
                    </select>

                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                        Pilih jenis naskah perencanaan pengawasan (standar: <strong>RKPD</strong> menggunakan template 26 halaman resmi Kabupaten Rembang).
                    </p>

                    @error('jenis_dokumen_reviu_id')
                        <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400" id="errorJenisServer">
                            <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                    <p class="hidden mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400" id="errorJenisClient">
                        <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span id="errorJenisClientText">Jenis Dokumen Reviu wajib dipilih.</span>
                    </p>
                </div>

                {{-- 3. Perangkat Daerah (OPD) Pengampu (Full-Width Luas) --}}
                <div class="w-full @error('unit_diperiksa_id') has-ts-error @enderror" id="fieldWrapperUnit">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                        <span>Perangkat Daerah Pengampu (Auditee) <span class="text-red-500">*</span></span>
                        <span class="text-xs font-normal text-gray-500">Wajib dipilih</span>
                    </label>
                    <select name="unit_diperiksa_id" id="selectUnit" data-no-ts
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition cursor-pointer">
                        <option value="">-- Pilih Perangkat Daerah Pengampu --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_diperiksa_id', 315) == $u->id)>
                                {{ $u->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                        Untuk dokumen RKPD, pengampu utama perencanaan pembangunan daerah adalah <strong>Badan Perencanaan Pembangunan Daerah (BAPPEDA)</strong>.
                    </p>
                    @error('unit_diperiksa_id')
                        <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400" id="errorUnitServer">
                            <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                    <p class="hidden mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400" id="errorUnitClient">
                        <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span id="errorUnitClientText">Perangkat Daerah Pengampu (Auditee) wajib dipilih.</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 2: IDENTITAS DOKUMEN & LEGALITAS RESMI ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-5 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white shadow-sm">2</span>
                    Identitas Naskah LHR & Pejabat Penandatangan
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Penomoran dokumen formal dan pengesahan laporan hasil reviu sesuai tata naskah dinas Kabupaten Rembang</p>
            </div>

            <div class="space-y-5">
                {{-- Baris 1: Nomor LHR & Tanggal Terbit LHR (2 Kolom Luas) --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    {{-- Nomor LHR --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Nomor Dokumen LHR Resmi
                        </label>
                        <input type="text" name="nomor_lhr" value="{{ old('nomor_lhr', '') }}"
                               placeholder="Contoh: 700/027/REVIU/INSP/2026"
                               class="h-12 w-full rounded-xl border @error('nomor_lhr') border-red-500 @else border-gray-300 @enderror bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Nomor naskah dinas LHR bisa diisi sesuai format penomoran Anda</p>
                        @error('nomor_lhr')
                            <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Terbit LHR --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Tanggal Pengesahan / Terbit LHR
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="tanggal_lhr"
                                   name="tanggal_lhr"
                                   value="{{ old('tanggal_lhr', date('Y-m-d')) }}"
                                   placeholder="Pilih tanggal pengesahan"
                                   class="datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border @error('tanggal_lhr') border-red-500 @else border-gray-300 @enderror bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden rtl:pr-4 rtl:pl-11 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 cursor-pointer transition"
                                   readonly="readonly">
                            <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 rtl:right-auto rtl:left-3 dark:text-gray-400">
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""></path>
                                </svg>
                            </span>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Tanggal pengesahan laporan oleh Inspektur Daerah Kabupaten Rembang.</p>
                        @error('tanggal_lhr')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Baris 2: Pejabat Penandatangan (Full-Width Luas agar Nama & Gelar Lengkap Tidak Terpotong) --}}
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Pejabat Penandatangan LHR (Inspektur Daerah)
                    </label>
                    <select name="ditandatangani_oleh" id="selectSigner" data-no-ts
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition cursor-pointer">
                        @foreach($signers as $sgn)
                            <option value="{{ $sgn->id }}" @selected(old('ditandatangani_oleh', 77) == $sgn->id || str_contains(strtoupper($sgn->name), 'IMUNG'))>
                                {{ $sgn->name }} — {{ $sgn->jabatan ?? 'Inspektur Daerah' }} (NIP. {{ $sgn->nip ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                        Pejabat yang bertindak menandatangani lembar pengesahan Bagian J (Apresiasi) pada Halaman 24 naskah LHR.
                    </p>
                    @error('ditandatangani_oleh')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 3: SURAT PERMOHONAN DARI OPD ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-5 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white shadow-sm">3</span>
                    Surat Permohonan Reviu dari Perangkat Daerah
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Surat resmi permohonan reviu dokumen dari OPD kepada Inspektur Daerah (fleksibel/opsional)</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                {{-- Nomor Permohonan OPD --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Nomor Surat Permohonan OPD
                    </label>
                    <input type="text" name="permohonan_nomor" value="{{ old('permohonan_nomor', '') }}" 
                           placeholder="Contoh: 000.7/250/2026 (fleksibel / opsional)"
                           class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Dikutip pada Bagian B (Dasar Hukum Reviu). Bisa diisi fleksibel sesuai surat dari OPD atau dikosongkan jika tidak ada.</p>
                    @error('permohonan_nomor')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Permohonan OPD --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Tanggal Surat Permohonan OPD
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="permohonan_tanggal"
                               name="permohonan_tanggal"
                               value="{{ old('permohonan_tanggal', '') }}"
                               placeholder="Pilih tanggal permohonan"
                               class="datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border @error('permohonan_tanggal') border-red-500 @else border-gray-300 @enderror bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden rtl:pr-4 rtl:pl-11 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 cursor-pointer transition"
                               readonly="readonly">
                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 rtl:right-auto rtl:left-3 dark:text-gray-400">
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""></path>
                            </svg>
                        </span>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Tanggal surat permohonan yang diajukan oleh OPD (opsional).</p>
                    @error('permohonan_tanggal')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 4: UNGGAH DOKUMEN LAMPIRAN PENDUKUNG ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30"
             x-data="lhrAttachmentManager()">
            <div class="mb-5 border-b border-gray-200/80 pb-3 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm">4</span>
                        <span>Dokumen Lampiran Pendukung Reviu</span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Unggah berkas bukti atau dokumen pendukung reviu (Draft RKPD/KUA-PPAS, BA FKP, SK Tim, Pokir DPRD, dll)
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60 shadow-2xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sinkron ke PDF, Word &amp; Pratinjau
                    </span>
                </div>
            </div>

            {{-- Dropzone Area (Clickable Anywhere) --}}
            <div class="relative rounded-2xl border-2 border-dashed border-gray-300 bg-white p-6 sm:p-8 text-center transition hover:border-blue-500 hover:bg-blue-50/20 dark:border-gray-700 dark:bg-gray-900/60 dark:hover:border-blue-400 dark:hover:bg-blue-950/20 cursor-pointer group"
                 :class="{ 'border-blue-500 bg-blue-50/40 ring-4 ring-blue-500/10': isDragging }"
                 @click="triggerFileInput()"
                 @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="handleDrop($event)">
                
                {{-- Native hidden multi-file input --}}
                <input type="file"
                       id="fileAttachmentsInput"
                       name="attachments[]"
                       multiple
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                       class="sr-only"
                       @click.stop
                       @change="handleFileSelect($event)">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 shadow-sm mb-3.5 group-hover:scale-105 transition">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>

                <div class="space-y-2">
                    <div>
                        <button type="button"
                                @click.stop="triggerFileInput()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-600/20 transition hover:scale-[1.02] active:scale-[0.98]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Pilih Berkas Lampiran</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        atau klik area kotak ini / seret dan lepas berkas ke sini
                    </p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">
                        Mendukung format <strong>PDF, DOCX/DOC, XLSX/XLS, dan JPG/PNG</strong> (Maks. 15 MB per berkas)
                    </p>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-center gap-1.5 pointer-events-none">
                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">PDF</span>
                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">DOCX / DOC</span>
                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">XLSX / XLS</span>
                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">JPG / PNG</span>
                </div>
            </div>

            {{-- Error Message Alert Client-Side --}}
            <div x-show="errorMessage" x-cloak class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-semibold text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="errorMessage"></span>
                </div>
                <button type="button" @click="errorMessage = ''" class="text-red-500 hover:text-red-700 font-bold ml-2">&times;</button>
            </div>

            {{-- Error Message dari Server (Laravel Validation) --}}
            @error('attachments')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
            @error('attachments.*')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror

            {{-- List Berkas Terpilih --}}
            <div x-show="files.length > 0" x-cloak class="mt-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                        <span>Berkas Terlampir</span>
                        <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-bold text-blue-700 dark:bg-blue-900/60 dark:text-blue-300"
                              x-text="files.length + ' berkas (' + totalSizeFormatted() + ')'"></span>
                    </span>
                    <div class="flex items-center gap-3">
                        <button type="button"
                                @click="triggerFileInput()"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400 flex items-center gap-1 cursor-pointer">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Tambah Berkas</span>
                        </button>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <button type="button" @click="removeAllFiles()" class="text-xs font-semibold text-red-600 hover:text-red-700 hover:underline dark:text-red-400 cursor-pointer">
                            Hapus Semua
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <template x-for="(f, index) in files" :key="f.id">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3.5 shadow-xs dark:border-gray-700/80 dark:bg-gray-900">
                            {{-- Info File & Icon --}}
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-xs font-bold shadow-xs"
                                     :class="getFileBadgeClass(f.name)">
                                    <span x-text="getFileExtension(f.name)"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="f.name"></div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400" x-text="formatBytes(f.size)"></div>
                                </div>
                            </div>

                            {{-- Input Keterangan Berkas --}}
                            <div class="w-full sm:w-72 shrink-0">
                                <input type="text"
                                       name="attachment_notes[]"
                                       x-model="f.keterangan"
                                       placeholder="Keterangan berkas (opsional)"
                                       class="h-9 w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 text-xs text-gray-800 placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                            </div>

                            {{-- Tombol Hapus Berkas --}}
                            <div class="shrink-0 flex items-center justify-end">
                                <button type="button"
                                        @click="removeFile(index)"
                                        title="Hapus berkas ini"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:border-red-300 hover:bg-red-50 hover:text-red-600 dark:border-gray-700 dark:hover:border-red-800 dark:hover:bg-red-950/40 dark:hover:text-red-400 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>



        {{-- Action Buttons & Status Footer --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-t border-gray-100 pt-6 dark:border-gray-800">
            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Template Resmi 26 Halaman, Bagian A–J, dan 14 Butir Pengujian Rembang otomatis aktif.</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('lhr.index') }}"
                   class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 shadow-md shadow-blue-600/20 transition">
                    <span>Simpan & Buka Ruang Kerja LHR</span>
                    <span class="text-lg leading-none">&rarr;</span>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Script Helper Form LHR --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectSpt = document.getElementById('selectSpt');
        const inputMulai = document.getElementById('inputTanggalMulai');
        const inputSelesai = document.getElementById('inputTanggalSelesai');
        const selectJenis = document.getElementById('selectJenisDokumen');
        const selectUnit = document.getElementById('selectUnit');

        let tsUnit = null;
        let tsSpt = null;

        function updateSptSync(value) {
            const opt = selectSpt.querySelector(`option[value="${value}"]`);
            const syncBox = document.getElementById('sptSyncInfoBox');
            const syncTahun = document.getElementById('syncTahunText');
            const syncJadwal = document.getElementById('syncJadwalText');
            const hiddenTahun = document.getElementById('hiddenTahunAnggaran');
            const hiddenMulai = document.getElementById('hiddenTanggalMulai');
            const hiddenSelesai = document.getElementById('hiddenTanggalSelesai');
            const inputTanggalLhr = document.querySelector('input[name="tanggal_lhr"]');

            if (opt && value) {
                const tahun = opt.dataset.tahun || '';
                const mulai = opt.dataset.mulai || '';
                const selesai = opt.dataset.selesai || '';
                const mulaiFmt = opt.dataset.mulaiFormatted || '-';
                const selesaiFmt = opt.dataset.selesaiFormatted || '-';

                if (hiddenTahun) hiddenTahun.value = tahun;
                if (hiddenMulai) hiddenMulai.value = mulai;
                if (hiddenSelesai) hiddenSelesai.value = selesai;

                if (syncTahun) syncTahun.textContent = tahun ? `Tahun ${tahun}` : '-';
                if (syncJadwal) syncJadwal.textContent = `${mulaiFmt} s/d ${selesaiFmt}`;
                if (syncBox) syncBox.classList.remove('hidden');

                if (selesai && inputTanggalLhr && (!inputTanggalLhr.value || inputTanggalLhr.value < selesai)) {
                    inputTanggalLhr.value = selesai;
                    if (inputTanggalLhr._flatpickr) {
                        inputTanggalLhr._flatpickr.setDate(selesai);
                    }
                }
            } else {
                if (syncBox) syncBox.classList.add('hidden');
                if (hiddenTahun) hiddenTahun.value = '';
                if (hiddenMulai) hiddenMulai.value = '';
                if (hiddenSelesai) hiddenSelesai.value = '';
            }
        }

        // Inisialisasi Flatpickr Datepicker sesuai template TailAdmin
        if (typeof flatpickr !== 'undefined') {
            flatpickr('.datepickerTwo', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                monthSelectorType: 'static',
            });
        }

        if (typeof TomSelect !== 'undefined') {
            if (selectSpt) {
                tsSpt = new TomSelect(selectSpt, {
                    plugins: ['dropdown_input'],
                    create: false,
                    placeholder: '-- Pilih Surat Perintah Tugas (SPT) --',
                    maxOptions: null,
                    wrapperClass: 'ts-wrapper ts-form-select',
                    allowEmptyOption: true,
                    render: {
                        no_results: function() {
                            return '<div class="no-results text-xs text-gray-500 dark:text-gray-400 py-3 text-center">Data SPT tidak ditemukan</div>';
                        }
                    }
                });
                tsSpt.on('initialize', function() {
                    if (this.control_input) {
                        this.control_input.placeholder = 'Ketik untuk mencari SPT...';
                    }
                });
                tsSpt.on('dropdown_open', function() {
                    if (this.control_input) {
                        this.control_input.placeholder = 'Ketik untuk mencari SPT...';
                    }
                });

                tsSpt.on('change', function(value) {
                    clearSptError();
                    updateSptSync(value);
                });
            }

            if (selectUnit) {
                tsUnit = new TomSelect(selectUnit, {
                    plugins: ['dropdown_input'],
                    create: false,
                    placeholder: '-- Pilih Perangkat Daerah Pengampu --',
                    maxOptions: null,
                    wrapperClass: 'ts-wrapper ts-form-select',
                    allowEmptyOption: true,
                    render: {
                        no_results: function() {
                            return '<div class="no-results text-xs text-gray-500 dark:text-gray-400 py-3 text-center">Data Perangkat Daerah tidak ditemukan</div>';
                        }
                    }
                });
                tsUnit.on('initialize', function() {
                    if (this.control_input) {
                        this.control_input.placeholder = 'Ketik untuk mencari Perangkat Daerah...';
                    }
                });
                tsUnit.on('dropdown_open', function() {
                    if (this.control_input) {
                        this.control_input.placeholder = 'Ketik untuk mencari Perangkat Daerah...';
                    }
                });
            }
        }

        // Inisialisasi sinkronisasi saat halaman dimuat jika SPT sudah terpilih
        if (selectSpt && selectSpt.value) {
            updateSptSync(selectSpt.value);
        }

        // Event listener saat Jenis Dokumen dipilih -> Jika RKPD, otomatis arahkan OPD ke BAPPEDA
        if (selectJenis) {
            selectJenis.addEventListener('change', function() {
                clearJenisError();

                const opt = this.options[this.selectedIndex];
                const kode = opt ? opt.dataset.kode : '';

                if (kode === 'RKPD' && selectUnit) {
                    for (let i = 0; i < selectUnit.options.length; i++) {
                        if (selectUnit.options[i].text.includes('Perencanaan Pembangunan')) {
                            if (tsUnit) {
                                tsUnit.setValue(selectUnit.options[i].value);
                            } else {
                                selectUnit.selectedIndex = i;
                            }
                            break;
                        }
                    }
                }
            });
        }

        // Elements untuk Validasi Client-Side
        const formCreateLhr = document.getElementById('formCreateLhr');
        const clientValidationAlert = document.getElementById('clientValidationAlert');
        const clientValidationList = document.getElementById('clientValidationList');
        const clientValidationTitle = document.getElementById('clientValidationTitle');
        const serverValidationAlert = document.getElementById('serverValidationAlert');

        const fieldWrapperSpt = document.getElementById('fieldWrapperSpt');
        const errorSptClient = document.getElementById('errorSptClient');
        const errorSptServer = document.getElementById('errorSptServer');

        const fieldWrapperJenis = document.getElementById('fieldWrapperJenis');
        const errorJenisClient = document.getElementById('errorJenisClient');
        const errorJenisServer = document.getElementById('errorJenisServer');

        const fieldWrapperUnit = document.getElementById('fieldWrapperUnit');
        const errorUnitClient = document.getElementById('errorUnitClient');
        const errorUnitServer = document.getElementById('errorUnitServer');

        // Fungsi pembersihan error real-time saat user melakukan input/pilihan
        function clearSptError() {
            if (fieldWrapperSpt) fieldWrapperSpt.classList.remove('has-ts-error');
            if (errorSptClient) errorSptClient.classList.add('hidden');
            if (errorSptServer) errorSptServer.classList.add('hidden');
        }

        function clearJenisError() {
            if (selectJenis) {
                selectJenis.classList.remove('border-red-500', 'bg-red-50/50', 'dark:bg-red-950/20');
                selectJenis.classList.add('border-gray-300');
            }
            if (errorJenisClient) errorJenisClient.classList.add('hidden');
            if (errorJenisServer) errorJenisServer.classList.add('hidden');
        }

        function clearUnitError() {
            if (fieldWrapperUnit) fieldWrapperUnit.classList.remove('has-ts-error');
            if (errorUnitClient) errorUnitClient.classList.add('hidden');
            if (errorUnitServer) errorUnitServer.classList.add('hidden');
        }

        // Pasang event listeners untuk realtime clearing
        if (tsSpt) tsSpt.on('change', clearSptError);
        if (selectSpt) {
            selectSpt.addEventListener('change', function() {
                clearSptError();
                updateSptSync(this.value);
            });
        }

        if (tsUnit) tsUnit.on('change', clearUnitError);
        if (selectUnit) selectUnit.addEventListener('change', clearUnitError);

        if (selectJenis) selectJenis.addEventListener('change', clearJenisError);

        // Client-side Validation on Submit
        if (formCreateLhr) {
            formCreateLhr.addEventListener('submit', function(e) {
                const errors = [];

                // 1. Validasi SPT
                const sptVal = selectSpt ? selectSpt.value.trim() : '';
                if (!sptVal) {
                    errors.push({
                        label: 'Surat Perintah Tugas (SPT)',
                        message: 'Surat Perintah Tugas (SPT) Reviu wajib dipilih.',
                        wrapper: fieldWrapperSpt,
                        showClientError: () => {
                            if (fieldWrapperSpt) fieldWrapperSpt.classList.add('has-ts-error');
                            if (errorSptClient) errorSptClient.classList.remove('hidden');
                        },
                        focus: () => {
                            if (tsSpt) {
                                tsSpt.focus();
                                tsSpt.open();
                            } else if (selectSpt) {
                                selectSpt.focus();
                            }
                        }
                    });
                }

                // 2. Validasi Jenis Dokumen
                const jenisVal = selectJenis ? selectJenis.value.trim() : '';
                if (!jenisVal) {
                    errors.push({
                        label: 'Jenis Dokumen Reviu',
                        message: 'Jenis Dokumen Reviu wajib dipilih.',
                        wrapper: fieldWrapperJenis,
                        showClientError: () => {
                            if (selectJenis) {
                                selectJenis.classList.remove('border-gray-300');
                                selectJenis.classList.add('border-red-500', 'bg-red-50/50', 'dark:bg-red-950/20');
                            }
                            if (errorJenisClient) errorJenisClient.classList.remove('hidden');
                        },
                        focus: () => {
                            if (selectJenis) selectJenis.focus();
                        }
                    });
                }

                // 3. Validasi OPD Pengampu (Auditee)
                const unitVal = selectUnit ? selectUnit.value.trim() : '';
                if (!unitVal) {
                    errors.push({
                        label: 'Perangkat Daerah Pengampu',
                        message: 'Perangkat Daerah Pengampu (Auditee) wajib dipilih.',
                        wrapper: fieldWrapperUnit,
                        showClientError: () => {
                            if (fieldWrapperUnit) fieldWrapperUnit.classList.add('has-ts-error');
                            if (errorUnitClient) errorUnitClient.classList.remove('hidden');
                        },
                        focus: () => {
                            if (tsUnit) {
                                tsUnit.focus();
                                tsUnit.open();
                            } else if (selectUnit) {
                                selectUnit.focus();
                            }
                        }
                    });
                }

                // Jika terdapat kesalahan validasi:
                if (errors.length > 0) {
                    e.preventDefault();

                    // Sembunyikan server error lama jika ada
                    if (serverValidationAlert) serverValidationAlert.classList.add('hidden');

                    // Isi banner kesalahan client-side
                    if (clientValidationAlert && clientValidationList) {
                        clientValidationList.innerHTML = '';
                        clientValidationTitle.textContent = `Mohon lengkapi atau perbaiki ${errors.length} isian berikut:`;

                        errors.forEach(err => {
                            err.showClientError();

                            const li = document.createElement('li');
                            li.innerHTML = `<strong>${err.label}</strong>: ${err.message}`;
                            clientValidationList.appendChild(li);
                        });

                        clientValidationAlert.classList.remove('hidden');
                    }

                    // Scroll halus ke banner alert atau field pertama yang salah
                    const firstError = errors[0];
                    if (clientValidationAlert) {
                        clientValidationAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else if (firstError && firstError.wrapper) {
                        firstError.wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }

                    // Beri jeda halus sebelum memfokuskan field error pertama
                    setTimeout(() => {
                        if (firstError && typeof firstError.focus === 'function') {
                            firstError.focus();
                        }
                    }, 400);
                }
            });
        }
    });

    /**
     * Alpine.js Manager untuk Upload Dokumen Lampiran Pendukung
     */
    function lhrAttachmentManager() {
        return {
            isDragging: false,
            errorMessage: '',
            files: [],
            dataTransfer: new DataTransfer(),
            maxBytes: 15 * 1024 * 1024, // 15 MB per berkas
            allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],

            triggerFileInput() {
                const input = document.getElementById('fileAttachmentsInput');
                if (input) {
                    input.click();
                }
            },

            handleFileSelect(event) {
                if (event.target && event.target.files) {
                    this.processFiles(event.target.files);
                }
            },

            handleDrop(event) {
                this.isDragging = false;
                if (event.dataTransfer && event.dataTransfer.files) {
                    this.processFiles(event.dataTransfer.files);
                }
            },

            processFiles(fileList) {
                this.errorMessage = '';
                for (let i = 0; i < fileList.length; i++) {
                    const file = fileList[i];
                    const ext = this.getFileExtension(file.name).toLowerCase();

                    if (!this.allowedExtensions.includes(ext)) {
                        this.errorMessage = `Format berkas "${file.name}" tidak didukung. Harap unggah berkas PDF, Word, Excel, atau Foto.`;
                        continue;
                    }

                    if (file.size > this.maxBytes) {
                        this.errorMessage = `Ukuran berkas "${file.name}" (${this.formatBytes(file.size)}) melebihi batas maksimal 15 MB.`;
                        continue;
                    }

                    // Hindari duplikat berkas yang sama
                    const exists = this.files.some(f => f.name === file.name && f.size === file.size);
                    if (exists) {
                        continue;
                    }

                    this.dataTransfer.items.add(file);
                    this.files.push({
                        id: Date.now() + '_' + Math.random().toString(36).substring(2, 9),
                        name: file.name,
                        size: file.size,
                        file: file,
                        keterangan: file.name.substring(0, file.name.lastIndexOf('.')) || file.name
                    });
                }

                this.syncNativeInput();
            },

            removeFile(index) {
                this.files.splice(index, 1);
                this.rebuildDataTransfer();
            },

            removeAllFiles() {
                this.files = [];
                this.dataTransfer = new DataTransfer();
                this.syncNativeInput();
            },

            rebuildDataTransfer() {
                this.dataTransfer = new DataTransfer();
                this.files.forEach(f => {
                    this.dataTransfer.items.add(f.file);
                });
                this.syncNativeInput();
            },

            syncNativeInput() {
                const input = document.getElementById('fileAttachmentsInput');
                if (input) {
                    input.files = this.dataTransfer.files;
                }
            },

            getFileExtension(filename) {
                if (!filename) return 'FILE';
                const parts = filename.split('.');
                return parts.length > 1 ? parts.pop().toUpperCase() : 'FILE';
            },

            getFileBadgeClass(filename) {
                const ext = this.getFileExtension(filename).toLowerCase();
                if (ext === 'pdf') {
                    return 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-900/50';
                }
                if (['doc', 'docx'].includes(ext)) {
                    return 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50';
                }
                if (['xls', 'xlsx'].includes(ext)) {
                    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/50';
                }
                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    return 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-900/50';
                }
                return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700';
            },

            formatBytes(bytes) {
                if (!bytes || bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            },

            totalSizeFormatted() {
                const total = this.files.reduce((acc, f) => acc + (f.size || 0), 0);
                return this.formatBytes(total);
            }
        };
    }

    // Expose global window dan registrasi Alpine.js
    window.lhrAttachmentManager = lhrAttachmentManager;
    if (window.Alpine) {
        window.Alpine.data('lhrAttachmentManager', lhrAttachmentManager);
    } else {
        document.addEventListener('alpine:init', () => {
            if (window.Alpine) {
                window.Alpine.data('lhrAttachmentManager', lhrAttachmentManager);
            }
        });
    }
</script>

@endsection
