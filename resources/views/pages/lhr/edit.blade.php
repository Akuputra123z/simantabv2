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
        <div class="flex items-center gap-2">
            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                {{ $lhr->jenisDokumen->kode }}
            </span>
            <span class="text-xs text-gray-500">TA {{ $lhr->tahun_anggaran }}</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $lhr->status->label() }}
            </span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Edit Informasi Dokumen LHR</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Perbarui nomor surat, tanggal pengawasan, OPD yang direviu, atau pejabat penandatangan resmi
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('lhr.show', $lhr->uuid) }}"
           class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition">
            &larr; Kembali ke Ruang Kerja
        </a>
    </div>
</div>

@if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 border border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800">
        {{ session('error') }}
    </div>
@endif

{{-- Container Form Full-Width --}}
<div class="w-full rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <form method="POST" action="{{ route('lhr.update', $lhr->uuid) }}" enctype="multipart/form-data" class="space-y-8" id="formEditLhr" novalidate>
        @csrf
        @method('PUT')

        {{-- Alert Validasi Server-Side --}}
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
                    </div>
                </div>
            </div>
        @endif

        {{-- Alert Validasi Client-Side --}}
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
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 1: DASAR PENUGASAN (READONLY CARD) ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-4 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm">1</span>
                    Dasar Penugasan & Klasifikasi Dokumen
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dasar SPT dan jenis dokumen terhubung permanen dengan template pengawasan</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Surat Perintah Tugas (SPT)</span>
                    <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">
                        {{ $lhr->auditAssignment->nomor_surat ?? 'SPT Tanpa Nomor' }}
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        Tim: {{ $lhr->auditAssignment->nama_tim ?? 'Tim Reviu' }} &bull; Ketua: {{ $lhr->auditAssignment->ketuaTim?->name ?? '-' }}
                    </p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Dokumen Reviu</span>
                    <p class="text-sm font-bold text-blue-700 dark:text-blue-400 mt-1">
                        {{ $lhr->jenisDokumen->kode }} &bull; {{ $lhr->jenisDokumen->nama }}
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        Menggunakan {{ $lhr->pengujians->count() }} Butir Pengujian Standar
                    </p>
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 2: OBJEK & PERANGKAT DAERAH ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-4 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm">2</span>
                    Objek Reviu & Perangkat Daerah
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Perangkat daerah (OPD) penanggung jawab penyusunan dokumen perencanaan</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-12">
                {{-- Perangkat Daerah (OPD) (8 cols) --}}
                <div class="md:col-span-8 @error('unit_diperiksa_id') has-ts-error @enderror" id="fieldWrapperUnitEdit">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                        <span>Perangkat Daerah (OPD) Direviu <span class="text-red-500">*</span></span>
                        <span class="text-xs font-normal text-gray-500">Wajib dipilih</span>
                    </label>
                    <select name="unit_diperiksa_id" id="selectUnitEdit" data-no-ts
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition cursor-pointer">
                        <option value="">-- Pilih Perangkat Daerah --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_diperiksa_id', $lhr->unit_diperiksa_id) == $u->id)>
                                {{ $u->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_diperiksa_id')
                        <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400">
                            <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- Tahun Anggaran Dokumen (4 cols) --}}
                <div class="md:col-span-4" id="fieldWrapperTahunEdit">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                        <span>Tahun Anggaran Dokumen <span class="text-red-500">*</span></span>
                        <span class="text-xs font-normal text-gray-500">Wajib diisi</span>
                    </label>
                    <input type="number" name="tahun_anggaran" id="inputTahunEdit" min="2020" max="2035"
                           value="{{ old('tahun_anggaran', $lhr->tahun_anggaran) }}"
                           class="h-12 w-full rounded-xl border @error('tahun_anggaran') border-red-500 bg-red-50/50 @else border-gray-300 @enderror bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                    @error('tahun_anggaran')
                        <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400">
                            <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 3: NOMOR NASKAH & PENANDATANGAN ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-4 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm">3</span>
                    Penomoran Naskah & Pejabat Penandatangan
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tata naskah dinas resmi LHR untuk sampul, lembar pengantar, dan kolofon</p>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Nomor LHR --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Nomor Surat LHR Resmi
                        </label>
                        <input type="text" name="nomor_lhr"
                               placeholder="Contoh: 700/026/REVIU/INSP/2026"
                               value="{{ old('nomor_lhr', $lhr->nomor_lhr) }}"
                               class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 font-mono focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Dapat diisi manual atau dibiarkan kosong untuk alokasi otomatis saat penerbitan.</p>
                        @error('nomor_lhr')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Naskah LHR --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Tanggal Terbit / Naskah LHR
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="tanggal_lhr"
                                   name="tanggal_lhr"
                                   value="{{ old('tanggal_lhr', $lhr->tanggal_lhr ? $lhr->tanggal_lhr->format('Y-m-d') : '') }}"
                                   placeholder="Pilih tanggal terbit"
                                   class="datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border @error('tanggal_lhr') border-red-500 @else border-gray-300 @enderror bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden rtl:pr-4 rtl:pl-11 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 cursor-pointer transition"
                                   readonly="readonly">
                            <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 rtl:right-auto rtl:left-3 dark:text-gray-400">
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""></path>
                                </svg>
                            </span>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Tanggal penandatanganan laporan di Rembang.</p>
                        @error('tanggal_lhr')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Pejabat Penandatangan --}}
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Pejabat Penandatangan (Inspektur)
                    </label>
                    <select name="ditandatangani_oleh" data-no-ts
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition cursor-pointer">
                        <option value="">-- Pilih Pejabat Penandatangan --</option>
                        @foreach($signers as $s)
                            <option value="{{ $s->id }}" @selected(old('ditandatangani_oleh', $lhr->ditandatangani_oleh) == $s->id)>
                                {{ $s->name }} (NIP: {{ $s->nip ?? '-' }}) &bull; {{ $s->jabatan ?? 'Inspektur Daerah' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Nama, NIP, dan pangkat pejabat ini akan dicantumkan pada lembar tanda tangan akhir dokumen PDF.</p>
                    @error('ditandatangani_oleh')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 4: SURAT PERMOHONAN & WAKTU PELAKSANAAN ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-4 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm">4</span>
                    Surat Permohonan Reviu & Waktu Pelaksanaan
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Data rujukan surat permohonan dari BAPPEDA dan jadwal pelaksanaan reviu</p>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Nomor Surat Permohonan OPD
                        </label>
                        <input type="text" name="permohonan_nomor"
                               placeholder="Contoh: 000.7/250/2026"
                               value="{{ old('permohonan_nomor', $lhr->permohonan_nomor) }}"
                               class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 font-mono focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        @error('permohonan_nomor')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Tanggal Surat Permohonan OPD
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="permohonan_tanggal"
                                   name="permohonan_tanggal"
                                   value="{{ old('permohonan_tanggal', $lhr->permohonan_tanggal ? $lhr->permohonan_tanggal->format('Y-m-d') : '') }}"
                                   placeholder="Pilih tanggal permohonan"
                                   class="datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border @error('permohonan_tanggal') border-red-500 @else border-gray-300 @enderror bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden rtl:pr-4 rtl:pl-11 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 cursor-pointer transition"
                                   readonly="readonly">
                            <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 rtl:right-auto rtl:left-3 dark:text-gray-400">
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""></path>
                                </svg>
                            </span>
                        </div>
                        @error('permohonan_tanggal')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Tanggal Mulai Reviu
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="tanggal_mulai"
                                   name="tanggal_mulai"
                                   value="{{ old('tanggal_mulai', $lhr->tanggal_mulai ? $lhr->tanggal_mulai->format('Y-m-d') : '') }}"
                                   placeholder="Pilih tanggal mulai"
                                   class="datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border @error('tanggal_mulai') border-red-500 @else border-gray-300 @enderror bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden rtl:pr-4 rtl:pl-11 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 cursor-pointer transition"
                                   readonly="readonly">
                            <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 rtl:right-auto rtl:left-3 dark:text-gray-400">
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""></path>
                                </svg>
                            </span>
                        </div>
                        @error('tanggal_mulai')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Tanggal Selesai Reviu
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="tanggal_selesai"
                                   name="tanggal_selesai"
                                   value="{{ old('tanggal_selesai', $lhr->tanggal_selesai ? $lhr->tanggal_selesai->format('Y-m-d') : '') }}"
                                   placeholder="Pilih tanggal selesai"
                                   class="datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border @error('tanggal_selesai') border-red-500 @else border-gray-300 @enderror bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden rtl:pr-4 rtl:pl-11 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 cursor-pointer transition"
                                   readonly="readonly">
                            <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 rtl:right-auto rtl:left-3 dark:text-gray-400">
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""></path>
                                </svg>
                            </span>
                        </div>
                        @error('tanggal_selesai')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        {{-- ==================== SEKSI 5: DOKUMEN LAMPIRAN PENDUKUNG ==================== --}}
        <div id="seksi-lampiran"
             class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30"
             x-data="lhrEditAttachmentManager()">
            <div class="mb-5 border-b border-gray-200/80 pb-3 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm">5</span>
                        <span>Dokumen Lampiran Pendukung Reviu</span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Kelola berkas bukti atau dokumen pendukung reviu. Berkas otomatis disinkronkan ke Unduh PDF, Word, dan Pratinjau.
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60 shadow-2xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sinkron ke PDF, Word &amp; Pratinjau
                    </span>
                </div>
            </div>

            {{-- 1. Berkas Tersimpan Saat Ini (Existing Attachments) --}}
            @if($lhr->attachments->isNotEmpty())
                <div class="mb-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                            <span>Berkas Lampiran Tersimpan</span>
                            <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-bold text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                {{ $lhr->attachments->count() }} berkas
                            </span>
                        </span>
                        <span class="text-[11px] text-gray-400">Klik tombol hapus bila ingin menghapus berkas saat disimpan</span>
                    </div>

                    <div class="grid grid-cols-1 gap-2.5">
                        @foreach($lhr->attachments as $att)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border p-3.5 shadow-xs transition"
                                 :class="isExistingDeleted({{ $att->id }}) ? 'border-red-300 bg-red-50/50 opacity-60 dark:border-red-900/60 dark:bg-red-950/30' : 'border-gray-200 bg-white dark:border-gray-700/80 dark:bg-gray-900'">
                                
                                {{-- Hidden Input for Marked Deletion --}}
                                <template x-if="isExistingDeleted({{ $att->id }})">
                                    <input type="hidden" name="delete_attachments[]" value="{{ $att->id }}">
                                </template>

                                {{-- Info File & Icon --}}
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    @php
                                        $ext = strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION) ?: 'file');
                                        $badgeColor = match($ext) {
                                            'pdf' => 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 border-red-200 dark:border-red-900/50',
                                            'doc', 'docx' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-900/50',
                                            'xls', 'xlsx' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/50',
                                            'jpg', 'jpeg', 'png' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-900/50',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700'
                                        };
                                    @endphp
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-xs font-bold shadow-xs border {{ $badgeColor }}">
                                        <span>{{ strtoupper($ext) }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $att->file_name }}</span>
                                            <template x-if="isExistingDeleted({{ $att->id }})">
                                                <span class="rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-900/60 dark:text-red-300">
                                                    Akan Dihapus Saat Disimpan
                                                </span>
                                            </template>
                                        </div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-2">
                                            <span>{{ $att->file_size_formatted }}</span>
                                            @if($att->keterangan)
                                                <span>&bull;</span>
                                                <span class="truncate italic">{{ $att->keterangan }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Links & Delete Toggle --}}
                                <div class="shrink-0 flex items-center gap-2 justify-end">
                                    <a href="{{ route('attachments.show', $att) }}" target="_blank"
                                       class="flex h-8 items-center gap-1.5 px-2.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-blue-600 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                                       title="Lihat Berkas">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Buka</span>
                                    </a>

                                    <button type="button"
                                            @click="toggleDeleteExisting({{ $att->id }})"
                                            :class="isExistingDeleted({{ $att->id }}) ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300' : 'border-gray-200 text-gray-400 hover:border-red-300 hover:bg-red-50 hover:text-red-600 dark:border-gray-700 dark:hover:border-red-800 dark:hover:bg-red-950/40 dark:hover:text-red-400'"
                                            class="flex h-8 items-center gap-1 px-2.5 rounded-lg border text-xs font-semibold transition cursor-pointer"
                                            :title="isExistingDeleted({{ $att->id }}) ? 'Batalkan Hapus' : 'Hapus Berkas Ini'">
                                        <template x-if="isExistingDeleted({{ $att->id }})">
                                            <span class="flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                                <span>Batal Hapus</span>
                                            </span>
                                        </template>
                                        <template x-if="!isExistingDeleted({{ $att->id }})">
                                            <span class="flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span>Hapus</span>
                                            </span>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 2. Dropzone Area Unggah Berkas Baru --}}
            <div class="relative rounded-2xl border-2 border-dashed border-gray-300 bg-white p-6 sm:p-8 text-center transition hover:border-blue-500 hover:bg-blue-50/20 dark:border-gray-700 dark:bg-gray-900/60 dark:hover:border-blue-400 dark:hover:bg-blue-950/20 cursor-pointer group"
                 :class="{ 'border-blue-500 bg-blue-50/40 ring-4 ring-blue-500/10': isDragging }"
                 @click="triggerFileInput()"
                 @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="handleDrop($event)">
                
                {{-- Native hidden multi-file input --}}
                <input type="file"
                       id="fileAttachmentsInputEdit"
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
                            <span>Pilih Berkas Lampiran Baru</span>
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
                <button type="button" @click="errorMessage = ''" class="text-red-500 hover:text-red-700 font-bold ml-2 cursor-pointer">&times;</button>
            </div>

            {{-- Error Message dari Server (Laravel Validation) --}}
            @error('attachments')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
            @error('attachments.*')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror

            {{-- List Berkas Baru Terpilih --}}
            <div x-show="files.length > 0" x-cloak class="mt-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                        <span>Berkas Baru Akan Diunggah</span>
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

                            {{-- Tombol Hapus Berkas Baru --}}
                            <div class="shrink-0 flex items-center justify-end">
                                <button type="button"
                                        @click="removeFile(index)"
                                        title="Hapus berkas ini"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:border-red-300 hover:bg-red-50 hover:text-red-600 dark:border-gray-700 dark:hover:border-red-800 dark:hover:bg-red-950/40 dark:hover:text-red-400 transition cursor-pointer">
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

        {{-- Tombol Aksi --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
            <a href="{{ route('lhr.show', $lhr->uuid) }}"
               class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 text-sm font-semibold text-white hover:bg-blue-700 shadow-md shadow-blue-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan Dokumen & Lampiran
            </button>
        </div>

    </form>
</div>

{{-- Script Helper Form Edit LHR --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('.datepickerTwo', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                monthSelectorType: 'static',
            });
        }

        const selectUnit = document.getElementById('selectUnitEdit');

        if (typeof TomSelect !== 'undefined' && selectUnit) {
            const tsUnit = new TomSelect(selectUnit, {
                plugins: ['dropdown_input'],
                create: false,
                placeholder: '-- Pilih Perangkat Daerah --',
                maxOptions: null,
                wrapperClass: 'ts-wrapper ts-form-select',
                allowEmptyOption: false,
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

        const formEditLhr = document.getElementById('formEditLhr');
        const clientAlert = document.getElementById('clientValidationAlert');
        const clientList = document.getElementById('clientValidationList');
        const clientTitle = document.getElementById('clientValidationTitle');
        const serverAlert = document.getElementById('serverValidationAlert');
        const inputTahun = document.getElementById('inputTahunEdit');
        const wrapperUnit = document.getElementById('fieldWrapperUnitEdit');
        const inputMulai = document.querySelector('input[name="tanggal_mulai"]');
        const inputSelesai = document.querySelector('input[name="tanggal_selesai"]');

        if (formEditLhr) {
            formEditLhr.addEventListener('submit', function(e) {
                const errors = [];

                if (!selectUnit || !selectUnit.value.trim()) {
                    errors.push({
                        label: 'Perangkat Daerah Direviu',
                        message: 'Perangkat Daerah (OPD) Direviu wajib dipilih.',
                        focus: () => {
                            if (typeof TomSelect !== 'undefined' && selectUnit.tomselect) {
                                selectUnit.tomselect.focus();
                                selectUnit.tomselect.open();
                            } else if (selectUnit) {
                                selectUnit.focus();
                            }
                        }
                    });
                    if (wrapperUnit) wrapperUnit.classList.add('has-ts-error');
                }

                const tahunVal = inputTahun ? parseInt(inputTahun.value) : NaN;
                if (isNaN(tahunVal) || tahunVal < 2020 || tahunVal > 2035) {
                    errors.push({
                        label: 'Tahun Anggaran Dokumen',
                        message: 'Tahun Anggaran Dokumen wajib diisi angka antara 2020 sampai 2035.',
                        focus: () => inputTahun && inputTahun.focus()
                    });
                    if (inputTahun) {
                        inputTahun.classList.remove('border-gray-300');
                        inputTahun.classList.add('border-red-500', 'bg-red-50/50');
                    }
                }

                if (inputMulai && inputSelesai && inputMulai.value && inputSelesai.value) {
                    if (inputSelesai.value < inputMulai.value) {
                        errors.push({
                            label: 'Tanggal Pelaksanaan Reviu',
                            message: `Tanggal Selesai Reviu (${inputSelesai.value}) tidak boleh lebih awal dari Tanggal Mulai Reviu (${inputMulai.value}).`,
                            focus: () => inputSelesai.focus()
                        });
                        inputSelesai.classList.remove('border-gray-300');
                        inputSelesai.classList.add('border-red-500', 'bg-red-50/50');
                    }
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    if (serverAlert) serverAlert.classList.add('hidden');
                    if (clientAlert && clientList) {
                        clientList.innerHTML = '';
                        clientTitle.textContent = `Mohon lengkapi atau perbaiki ${errors.length} isian berikut:`;
                        errors.forEach(err => {
                            const li = document.createElement('li');
                            li.innerHTML = `<strong>${err.label}</strong>: ${err.message}`;
                            clientList.appendChild(li);
                        });
                        clientAlert.classList.remove('hidden');
                        clientAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    setTimeout(() => {
                        if (errors[0] && typeof errors[0].focus === 'function') {
                            errors[0].focus();
                        }
                    }, 400);
                }
            });
        }
    });

    /**
     * Alpine.js Manager untuk Kelola & Upload Dokumen Lampiran di Halaman Edit
     */
    function lhrEditAttachmentManager() {
        return {
            isDragging: false,
            errorMessage: '',
            files: [],
            deletedExisting: [],
            dataTransfer: new DataTransfer(),
            maxBytes: 15 * 1024 * 1024, // 15 MB per berkas
            allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],

            toggleDeleteExisting(id) {
                const idx = this.deletedExisting.indexOf(id);
                if (idx > -1) {
                    this.deletedExisting.splice(idx, 1);
                } else {
                    this.deletedExisting.push(id);
                }
            },

            isExistingDeleted(id) {
                return this.deletedExisting.includes(id);
            },

            triggerFileInput() {
                const input = document.getElementById('fileAttachmentsInputEdit');
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

                    // Hindari duplikat berkas
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
                const input = document.getElementById('fileAttachmentsInputEdit');
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
                    return 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50';
                }
                return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700';
            },

            formatBytes(bytes, decimals = 1) {
                if (!bytes || bytes === 0) return '0 B';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            },

            totalSizeFormatted() {
                const total = this.files.reduce((acc, f) => acc + f.size, 0);
                return this.formatBytes(total);
            }
        };
    }
    window.lhrEditAttachmentManager = lhrEditAttachmentManager;
</script>

@endsection
