{{-- resources/views/pages/audit-assignment/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
    input[type="date"] {
        color-scheme: light;
        -webkit-appearance: auto;
        appearance: auto;
    }
    .dark input[type="date"] { color-scheme: dark; }
    .pkpt-wrapper { position: relative; }
    .member-picker-box { overflow: visible !important; }
</style>

<div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 pb-12 space-y-6">

    {{-- Header --}}
    <div>
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit penugasan audit</h2>
        <p class="mt-1 text-sm text-gray-500">Perbarui rincian surat tugas dan tim audit</p>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700
            dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
            </svg>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('audit-assignment.update', $data->id) }}" method="POST"
        enctype="multipart/form-data" class="space-y-5" novalidate>
        @csrf
        @method('PUT')

        <div id="unit-hidden-inputs"></div>

        {{-- ═══ SECTION 1 — Informasi Audit ═══ --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-visible">
            <div class="flex items-center gap-3 border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-white/[0.02] rounded-t-2xl">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Informasi audit</h3>
                    <p class="text-xs text-gray-400">Program, jenis pengawasan, dan nomor surat</p>
                </div>
            </div>

            <div class="p-6 space-y-5 overflow-visible">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    {{-- Program Audit --}}
                    <div class="space-y-1.5">
                        <label for="audit_program_id" class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Program audit <span class="text-red-400">*</span>
                        </label>
                        <select id="audit_program_id" name="audit_program_id" required
                            class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none
                                   dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">Pilih program audit</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}"
                                    @selected(old('audit_program_id', $data->auditProgramDetail?->audit_program_id) == $program->id)>
                                    {{ $program->nama_program }} ({{ $program->tahun }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PKPT — diisi JS, value awal di-seed dari PHP --}}
                    <div class="space-y-1.5">
                        <label for="audit_program_detail_id" class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                            PKPT / detail program <span class="text-red-400">*</span>
                        </label>
                        <select id="audit_program_detail_id" name="audit_program_detail_id" required
                            class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none
                                   dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            @if($data->audit_program_detail_id)
                                <option value="{{ $data->audit_program_detail_id }}" selected>
                                    {{ $data->auditProgramDetail?->nama_detail_program ?? 'Data terpilih' }}
                                </option>
                            @else
                                <option value="">Pilih detail setelah memilih program</option>
                            @endif
                        </select>
                        <div id="pkpt-info" class="hidden"></div>
                    </div>

                    {{-- Nomor Surat --}}
                    <div class="space-y-1.5">
                        <label for="nomor_surat" class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Nomor surat tugas <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="nomor_surat" id="nomor_surat"
                            value="{{ old('nomor_surat', $data->nomor_surat) }}"
                            placeholder="700/001/INSPEKTORAT/2026"
                            class="h-10 w-full rounded-lg border border-gray-200 px-3 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none
                                   dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                            required>
                    </div>

                   
                </div>

                {{-- Unit Diperiksa --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 space-y-3 dark:border-gray-700 dark:bg-white/[0.02]">
                    <div class="flex items-center gap-2">
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Unit diperiksa (auditee)</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Kategori</label>
                            <select id="filter_kategori" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="">Semua kategori</option>
                                @foreach($kategoriOptions as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Kecamatan</label>
                            <select id="filter_kecamatan" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="">Semua kecamatan</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tampilkan</label>
                            <select id="filter_show" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="all">Semua unit</option>
                                <option value="selected">Terpilih saja</option>
                                <option value="unselected">Belum dipilih</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Nama unit <span class="text-red-400">*</span></span>
                        <span id="unit-count-pill" class="hidden rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"></span>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                        <div class="flex items-center gap-2 border-b border-gray-200 px-3 py-2 dark:border-gray-700">
                            <svg class="h-3.5 w-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input type="text" id="unit-search" placeholder="Cari nama unit..."
                                class="flex-1 bg-transparent py-1 text-sm outline-none dark:text-white">
                        </div>
                        <div id="select-all-row" class="flex cursor-pointer items-center gap-3 border-b border-gray-100 bg-gray-50 px-4 py-2.5 hover:bg-gray-100 transition-colors select-none dark:bg-gray-800/50">
                            <div id="cb-all" class="flex h-4 w-4 shrink-0 items-center justify-center rounded border border-gray-300 bg-white dark:bg-gray-900"></div>
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Pilih semua unit yang ditampilkan</span>
                        </div>
                        <div id="unit-option-list" class="max-h-60 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-800"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ SECTION 2 — Jadwal & Personel ═══ --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-visible">
            <div class="flex items-center gap-3 border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-white/[0.02] rounded-t-2xl">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Jadwal & personel</h3>
                    <p class="text-xs text-gray-400">Periode audit dan susunan tim pemeriksa</p>
                </div>
            </div>

            <div class="p-6 space-y-5 overflow-visible">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="space-y-1.5">
                        <label for="tanggal_mulai" class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Tanggal mulai <span class="text-red-400">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                            value="{{ old('tanggal_mulai', $data->tanggal_mulai?->format('Y-m-d')) }}"
                            class="h-10 w-full rounded-lg border border-gray-200 px-3 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none
                                   dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                            required>
                    </div>
                    <div class="space-y-1.5">
                        <label for="tanggal_selesai" class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Tanggal selesai <span class="text-red-400">*</span>
                        </label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                            value="{{ old('tanggal_selesai', $data->tanggal_selesai?->format('Y-m-d')) }}"
                            class="h-10 w-full rounded-lg border border-gray-200 px-3 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none
                                   dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                            required>
                    </div>
                    <div class="space-y-1.5">
                        <label for="status" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                        <select name="status" id="status"
                            class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm
                                   dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            @foreach(['draft' => 'Draft', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('status', $data->status) == $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="ketua_tim_id" class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Ketua tim <span class="text-red-400">*</span>
                        </label>
                        <select name="ketua_tim_id" id="ketua_tim_id" required
                            class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none
                                   dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">Pilih ketua tim</option>
                            @foreach($ketuaTim as $user)
                                <option value="{{ $user->id }}" @selected(old('ketua_tim_id', $data->ketua_tim_id) == $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @php $preselected = $data->members; @endphp
                    @include('pages.audit-assignment.partials._member_picker')
                </div>
            </div>
        </div>

        {{-- ═══ SECTION 3 — Lampiran ═══ --}}
        @include('pages.audit-assignment.partials._dropzone', ['existing' => $data->attachments])

        {{-- Actions --}}
        <div class="flex items-center justify-between border-t border-gray-100 pt-5 dark:border-gray-800">
            <a href="{{ route('audit-assignment.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke daftar
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-all active:scale-95">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@php
    // ✅ FIX: preselectedUnits dari pivot (unitDiperiksas), bukan unitDiperiksa singular
    $currentProgId    = old('audit_program_id', $data->auditProgramDetail?->audit_program_id ?? '');
    $currentDetId     = old('audit_program_detail_id', $data->audit_program_detail_id ?? '');
    $oldUnitIds       = old('unit_diperiksa_ids', []);
    $preselectedUnits = !empty($oldUnitIds)
        ? \App\Models\UnitDiperiksa::whereIn('id', (array)$oldUnitIds)->get()
        : $data->unitDiperiksas; // ← pivot collection
@endphp
@include('pages.audit-assignment.partials._audit_scripts')
@endpush