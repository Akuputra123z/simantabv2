@extends('layouts.app')

@push('scripts')
    @include('components._rupiah-input')
@endpush

@section('content')
<div class="mx-auto max-w-5xl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('lhps.index') }}" class="hover:text-primary-600">LHP</a>
                <span>/</span>
                <span class="text-gray-700 dark:text-white">Tambah LHP</span>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tambah LHP Baru</h1>
        </div>
        <a href="{{ route('lhps.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- Alert Error --}}
    @if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-red-800 dark:text-red-300">Terdapat kesalahan input:</p>
                <ul class="mt-1 list-inside list-disc text-sm text-red-700 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('lhps.store') }}" method="POST" enctype="multipart/form-data" id="form-lhp">
        @csrf

        {{-- ── SECTION 1: Informasi Utama ── --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-300">1</div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Utama LHP</h2>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Penugasan Audit --}}
                    {{-- 1. Pilih Program Kerja (PKPT) --}}
<div class="md:col-span-2">
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Program Kerja (PKPT) <span class="text-red-500">*</span>
    </label>
    <select id="select-program"
            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        <option value="">-- Pilih Program Kerja --</option>
    </select>
</div>

{{-- 2. Pilih Penugasan (Berdasarkan Program) --}}
<div class="md:col-span-1">
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Penugasan Audit <span class="text-red-500">*</span>
    </label>
    <select id="select-assignment" disabled
            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        <option value="">-- Pilih Program Terlebih Dahulu --</option>
    </select>
</div>
{{-- Ganti field ini: dari name="audit_assignment_id" ke readonly display --}}
{{-- Dan tambah dua hidden input terpisah --}}

{{-- 3. Pilih Unit Kerja (Berdasarkan Penugasan) --}}
<div class="md:col-span-1">
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Unit Kerja / Objek Audit <span class="text-red-500">*</span>
    </label>
    <select id="select-unit" disabled
            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3.5 py-2.5 text-sm
                   text-gray-900 focus:border-primary-500 dark:border-gray-600 
                   dark:bg-gray-800 dark:text-white 
                   @error('unit_diperiksa_id') border-red-500 @enderror">
        <option value="">-- Pilih Penugasan Terlebih Dahulu --</option>
    </select>

    {{-- Hidden inputs yang benar-benar dikirim ke server --}}
    <input type="hidden" name="audit_assignment_id" id="hidden-assignment-id" value="">
    <input type="hidden" name="unit_diperiksa_id"   id="hidden-unit-id"       value="">

    @error('unit_diperiksa_id')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
    @error('audit_assignment_id')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

                    {{-- Nomor LHP --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nomor LHP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nomor_lhp" value="{{ old('nomor_lhp') }}"
                               placeholder="Contoh: LHP/001/IRBAN/2024"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('nomor_lhp') border-red-500 @enderror">
                        @error('nomor_lhp')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal LHP --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal LHP <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_lhp" value="{{ old('tanggal_lhp') }}"
                               onclick="this.showPicker()"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('tanggal_lhp') border-red-500 @enderror">
                        @error('tanggal_lhp')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Catatan Umum --}}
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Umum</label>
                        <textarea name="catatan_umum" rows="3"
                                  placeholder="Tuliskan catatan umum terkait LHP ini..."
                                  class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ old('catatan_umum') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── SECTION 2: Temuan ── --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-300">2</div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Data Temuan</h2>
                        <span id="badge-temuan" class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-100 px-1.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">0</span>
                    </div>
                    <button type="button" id="btn-add-temuan"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Temuan
                    </button>
                </div>
            </div>

            <div id="temuan-container" class="divide-y divide-gray-100 dark:divide-gray-700"></div>

            <div id="temuan-empty" class="flex flex-col items-center justify-center py-12 text-center">
                <svg class="mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada temuan. Klik <strong class="font-medium text-gray-500">Tambah Temuan</strong> untuk mulai.</p>
            </div>
        </div>

        {{-- ── SECTION 3: Lampiran ── --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-300">3</div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Lampiran Dokumen</h2>
                        <span id="badge-lampiran" class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-100 px-1.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">0</span>
                    </div>
                    <button type="button" id="btn-add-lampiran"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300/40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        Tambah Lampiran
                    </button>
                </div>
            </div>

            <div id="lampiran-container" class="divide-y divide-gray-100 dark:divide-gray-700"></div>

            <div id="lampiran-empty" class="flex flex-col items-center justify-center py-10 text-center">
                <svg class="mb-3 h-9 w-9 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada lampiran ditambahkan.</p>
            </div>

            <div class="border-t border-gray-100 px-6 py-3 dark:border-gray-700">
                <p class="text-xs text-gray-400 dark:text-gray-500">Format: PDF, JPG, PNG, JPEG. Maks. 10 MB per file.</p>
            </div>
        </div>

        {{-- ── Footer Action ── --}}
        <div class="flex items-center justify-end gap-3 rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <a href="{{ route('lhps.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                Batal
            </a>
            <button type="submit" id="btn-submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 disabled:opacity-60 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span id="btn-submit-text">Simpan LHP</span>
            </button>
        </div>

    </form>
</div>

{{-- ── Data Kode Temuan untuk JS ── --}}
<script>
@php
    // Siapkan semua data assignments sebagai JSON untuk JS
    $assignmentData = $assignments->map(function ($a) {
        return [
            'id'                     => $a->id,
            'audit_program_detail_id'=> $a->audit_program_detail_id,
            'program_id'             => $a->auditProgramDetail?->audit_program_id,
            'program_nama'           => $a->auditProgramDetail?->auditProgram?->nama_program ?? '-',
            'detail_nama'            => $a->auditProgramDetail?->nama_detail_program ?? '-',
            'nomor_surat'            => $a->nomor_surat,
            'units'                  => $a->unitDiperiksas->map(fn($u) => [
                'id'   => $u->id,
                'nama' => $u->nama_unit,
            ])->values(),
        ];
    });

    $kodeTemuanData = $kodeTemuans->map(fn($k) => [
        'id'        => $k->id,
        'kode'      => $k->kode,
        'nama'      => $k->pernyataan ?? $k->nama ?? '',
        'deskripsi' => $k->deskripsi ?? null,
    ]);
@endphp

const ALL_ASSIGNMENTS = @json($assignmentData);
const KODE_TEMUANS    = @json($kodeTemuanData);
const USED_UNIT_MAP   = @json($usedUnitMap);

// ── Cascade Logic ──────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    const selProgram    = document.getElementById('select-program');
    const selAssignment = document.getElementById('select-assignment');
    const selUnit       = document.getElementById('select-unit');

    // Bangun daftar program unik dari ALL_ASSIGNMENTS pada element select asli
    const seen = new Set();
    ALL_ASSIGNMENTS.forEach(a => {
        if (!seen.has(a.program_id)) {
            seen.add(a.program_id);
            const opt = new Option(a.program_nama, a.program_id);
            selProgram.add(opt);
        }
    });

    // Inisialisasi TomSelect untuk ketiga dropdown
    const tsProgram = new TomSelect('#select-program', {
        create: false,
        placeholder: '-- Pilih Program Kerja --',
        controlInput: '<input>',
        maxOptions: null
    });

    const tsAssignment = new TomSelect('#select-assignment', {
        create: false,
        placeholder: '-- Pilih Penugasan --',
        controlInput: '<input>',
        maxOptions: null
    });
    tsAssignment.disable();

    const tsUnit = new TomSelect('#select-unit', {
        create: false,
        placeholder: '-- Pilih Unit Kerja / Objek Audit --',
        controlInput: '<input>',
        maxOptions: null
    });
    tsUnit.disable();

    tsProgram.on('change', function (programId) {
        // Reset downstream
        tsAssignment.clear();
        tsAssignment.clearOptions();
        tsAssignment.disable();

        tsUnit.clear();
        tsUnit.clearOptions();
        tsUnit.disable();

        document.getElementById('hidden-assignment-id').value = '';
        document.getElementById('hidden-unit-id').value = '';

        if (!programId) return;

        // Filter assignments berdasarkan program
        const filtered = ALL_ASSIGNMENTS.filter(a => String(a.program_id) === String(programId));

        filtered.forEach(a => {
            const label = a.detail_nama + (a.nomor_surat ? ' — ' + a.nomor_surat : '');
            tsAssignment.addOption({ value: a.id, text: label });
        });

        if (filtered.length > 0) {
            tsAssignment.enable();
        }
    });

    tsAssignment.on('change', function (assignmentId) {
        // Reset unit dropdown
        tsUnit.clear();
        tsUnit.clearOptions();
        tsUnit.disable();

        document.getElementById('hidden-assignment-id').value = assignmentId || '';
        document.getElementById('hidden-unit-id').value       = '';

        if (!assignmentId) return;

        const assignment = ALL_ASSIGNMENTS.find(a => String(a.id) === String(assignmentId));
        const usedIds    = USED_UNIT_MAP[assignmentId] || [];

        if (assignment && assignment.units.length > 0) {
            const available = assignment.units.filter(u => !usedIds.includes(u.id));

            if (available.length === 0) {
                tsUnit.addOption({
                    value: '',
                    text: '-- Semua unit sudah dibuatkan LHP --',
                });
                return;
            }

            available.forEach(u => {
                tsUnit.addOption({ value: u.id, text: u.nama });
            });
            tsUnit.enable();

            // Jika hanya 1 unit tersedia, otomatis pilih
            if (available.length === 1) {
                tsUnit.setValue(available[0].id);
                document.getElementById('hidden-unit-id').value = available[0].id;
            }
        }
    });

    tsUnit.on('change', function (unitId) {
        document.getElementById('hidden-unit-id').value = unitId || '';
    });
});
// ── Badge & Empty State ────────────────────────────────────────────

let temuanCount   = 0;
let lampiranCount = 0;

function updateBadge(id, count) {
    const el = document.getElementById(id);
    if (el) el.textContent = count;
}

function updateEmpty(containerId, emptyId, count) {
    const container = document.getElementById(containerId);
    const empty     = document.getElementById(emptyId);
    if (container && empty) {
        container.style.display = count ? '' : 'none';
        empty.style.display     = count ? 'none' : '';
    }
}

// ── Temuan Logic ───────────────────────────────────────────────────

document.getElementById('btn-add-temuan').addEventListener('click', addTemuan);

function addTemuan() {
    const idx = temuanCount++;
    const row = document.createElement('div');
    row.className = 'temuan-row p-6 transition-all border-b border-gray-100 dark:border-gray-700';
    row.dataset.idx = idx;

    const kodeOptions = KODE_TEMUANS.map(k => {
        const label = k.deskripsi ? `[${k.kode}] — ${k.deskripsi}` : `[${k.kode}]`;
        return `<option value="${k.id}">${label}</option>`;
    }).join('');

    row.innerHTML = `
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300 temuan-num-badge">${idx + 1}</span>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Temuan #<span class="temuan-num">${idx + 1}</span></span>
            </div>
            <button type="button" onclick="removeTemuan(this)" class="text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus
            </button>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase">Kode Temuan</label>
                <select name="temuans[${idx}][kode_temuan_id]" class="kode-temuan-select w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:bg-gray-700 dark:text-white" data-idx="${idx}">
                    <option value="">-- Pilih Kode Temuan --</option>
                    ${kodeOptions}
                </select>
                <div id="kode-info-${idx}" class="hidden mt-2 rounded-lg bg-blue-50 border border-blue-100 px-3 py-2 text-xs text-blue-700 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300">
                    <span id="kode-info-text-${idx}"></span>
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase">Kondisi / Uraian Temuan <span class="text-red-500">*</span></label>
                <textarea name="temuans[${idx}][kondisi]" rows="3" class="w-full rounded-lg border border-gray-300 p-2.5 text-sm dark:bg-gray-700 dark:text-white" required></textarea>
            </div>
            ${['negara', 'daerah', 'desa', 'bos_blud'].map(type => `
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase">Kerugian ${type.replace('_', '/').toUpperCase()} (Rp)</label>
                    <div class="rupiah-wrap relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
                        <input type="text" class="rupiah-field w-full rounded-lg border border-gray-300 py-2.5 pl-9 pr-3 text-sm font-medium dark:bg-gray-700 dark:text-white"
                               data-name="temuans[${idx}][nilai_kerugian_${type}]" data-value="0">
                    </div>
                </div>
            `).join('')}
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase">Total Nilai Kerugian (Otomatis)</label>
                <div class="flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-200 px-3 py-2.5 dark:bg-gray-900 dark:border-gray-700">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200" id="total-nilai-display-${idx}">Rp 0</span>
                    <input type="hidden" name="temuans[${idx}][nilai_temuan]" id="hidden-total-${idx}" value="0">
                </div>
            </div>
        </div>
    `;

    document.getElementById('temuan-container').appendChild(row);

    if (window.RupiahInput) window.RupiahInput.initAll(row);

    const fields       = row.querySelectorAll('.rupiah-field');
    const totalDisplay = row.querySelector(`#total-nilai-display-${idx}`);
    const hiddenTotal  = row.querySelector(`#hidden-total-${idx}`);

    const recalcTotal = () => {
        let total = 0;
        row.querySelectorAll(`input[type="hidden"][name^="temuans[${idx}]"]`).forEach(h => {
            if (h.id !== `hidden-total-${idx}`) total += parseInt(h.value || 0);
        });
        totalDisplay.textContent = 'Rp ' + (window.RupiahInput ? window.RupiahInput.fmt(total) : total.toLocaleString('id-ID'));
        hiddenTotal.value = total;
    };

    fields.forEach(f => f.addEventListener('input', () => setTimeout(recalcTotal, 50)));

    const kodeSelect = row.querySelector('.kode-temuan-select');
    const tsKode = new TomSelect(kodeSelect, {
        create: false,
        placeholder: '-- Pilih Kode Temuan --',
        controlInput: '<input>',
        maxOptions: null
    });

    const handleKodeChange = (val) => {
        const found   = KODE_TEMUANS.find(k => String(k.id) === String(val));
        const infoBox = document.getElementById(`kode-info-${idx}`);
        const infoText= document.getElementById(`kode-info-text-${idx}`);
        if (found) {
            infoText.textContent = `[${found.kode}] ${found.nama}${found.deskripsi ? ' — ' + found.deskripsi : ''}`;
            infoBox.classList.remove('hidden');
        } else {
            infoBox.classList.add('hidden');
        }
    };
    tsKode.on('change', handleKodeChange);

    const count = document.querySelectorAll('.temuan-row').length;
    updateBadge('badge-temuan', count);
    updateEmpty('temuan-container', 'temuan-empty', count);
    row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function removeTemuan(btn) {
    btn.closest('.temuan-row').remove();
    const rows = document.querySelectorAll('.temuan-row');
    rows.forEach((r, i) => {
        r.querySelector('.temuan-num').textContent       = i + 1;
        r.querySelector('.temuan-num-badge').textContent = i + 1;
    });
    updateBadge('badge-temuan', rows.length);
    updateEmpty('temuan-container', 'temuan-empty', rows.length);
}

// ── Lampiran Logic ─────────────────────────────────────────────────

document.getElementById('btn-add-lampiran').addEventListener('click', addLampiran);

function addLampiran() {
    const idx = lampiranCount++;
    const row = document.createElement('div');
    row.className = 'lampiran-row flex flex-col md:flex-row md:items-start gap-4 px-6 py-5 border-b border-gray-100 dark:border-gray-700 transition-all hover:bg-gray-50 dark:hover:bg-gray-800/50';
    row.innerHTML = `
        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase">Pilih Berkas</label>
                <input type="file" name="attachments[${idx}][file_path]" accept=".pdf,.jpg,.jpeg,.png"
                       class="block w-full text-sm text-gray-500 dark:text-gray-400
                              file:mr-3 file:py-2.5 file:px-4
                              file:rounded-lg file:border-0
                              file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100
                              dark:file:bg-blue-900/30 dark:file:text-blue-400
                              dark:hover:file:bg-blue-900/50
                              focus:outline-none cursor-pointer">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase">Keterangan / Nama File (Opsional)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </span>
                    <input type="text" name="attachments[${idx}][file_name]" placeholder="Cth: Bukti Transfer, Kwitansi..."
                           class="w-full rounded-lg border border-gray-300 py-2.5 pl-9 pr-3 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end md:mt-6">
            <button type="button" onclick="this.closest('.lampiran-row').remove(); updateLampiranCount();"
                    class="inline-flex items-center justify-center h-10 w-10 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-600 focus:outline-none transition-colors dark:hover:bg-red-900/20" title="Hapus Lampiran">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    `;
    document.getElementById('lampiran-container').appendChild(row);
    updateLampiranCount();
}

function updateLampiranCount() {
    const count = document.querySelectorAll('.lampiran-row').length;
    updateBadge('badge-lampiran', count);
    updateEmpty('lampiran-container', 'lampiran-empty', count);
}

// ── Submit Guard ───────────────────────────────────────────────────

// ── Submit Guard ───────────────────────────────────────────────────

document.getElementById('form-lhp').addEventListener('submit', function (e) {
    // ✅ FIX: pakai preventDefault() agar benar-benar bisa stop submit
    if (!document.getElementById('hidden-unit-id').value) {
        e.preventDefault();
        alert('Pilih Unit Kerja / Objek Audit terlebih dahulu.');
        return;
    }
    if (!document.getElementById('hidden-assignment-id').value) {
        e.preventDefault();
        alert('Pilih Penugasan Audit terlebih dahulu.');
        return;
    }
    const btn = document.getElementById('btn-submit');
    if (btn) {
        btn.disabled = true;
        const txt = document.getElementById('btn-submit-text');
        if (txt) txt.textContent = 'Menyimpan...';
    }
});
</script>
@endsection