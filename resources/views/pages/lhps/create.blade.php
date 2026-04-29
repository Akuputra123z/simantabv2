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
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Penugasan Audit <span class="text-red-500">*</span>
                        </label>
                        <select name="audit_assignment_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('audit_assignment_id') border-red-500 @enderror">
                            <option value="">-- Pilih Penugasan --</option>
                            @foreach ($assignments as $a)
                                <option value="{{ $a->id }}" {{ old('audit_assignment_id') == $a->id ? 'selected' : '' }}>
                                    {{ $a->auditProgram->nama_program ?? '-' }}
                                    @if($a->unitDiperiksa) — {{ $a->unitDiperiksa->nama_unit }} @endif
                                </option>
                            @endforeach
                        </select>
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

                    {{-- Semester --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Semester <span class="text-red-500">*</span>
                        </label>
                        <select name="semester"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('semester') border-red-500 @enderror">
                            <option value="">-- Pilih Semester --</option>
                            <option value="1" {{ old('semester') == '1' ? 'selected' : '' }}>Semester I</option>
                            <option value="2" {{ old('semester') == '2' ? 'selected' : '' }}>Semester II</option>
                        </select>
                        @error('semester')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- IRBAN --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            IRBAN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="irban" value="{{ old('irban') }}"
                               placeholder="Contoh: IRBAN I"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('irban') border-red-500 @enderror">
                        @error('irban')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jenis Pemeriksaan --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Pemeriksaan</label>
                        <input type="text" name="jenis_pemeriksaan" value="{{ old('jenis_pemeriksaan') }}"
                               placeholder="Contoh: Reguler / Khusus"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
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
/**
 * Data kode temuan dari server — sudah include nama/deskripsi.
 * Format: [{ id, kode, nama, deskripsi }]
 * 
 * CATATAN: pastikan KodeTemuan model punya kolom 'nama' atau 'deskripsi'.
 * Sesuaikan key di bawah dengan nama kolom aktual model Anda.
 */
@php
    $kodeTemuanData = $kodeTemuans->map(function ($k) {
        return [
            'id'        => $k->id,
            'kode'      => $k->kode,
            'nama'      => $k->pernyataan ?? $k->nama ?? '', // Sesuaikan dengan kolom nama/pernyataan di DB
            'deskripsi' => $k->deskripsi ?? null,
        ];
    });
@endphp


const KODE_TEMUANS = @json($kodeTemuanData);


/* ─────────────────────────────────────────────────────────
   STATE
───────────────────────────────────────────────────────── */
let temuanCount   = 0;
let lampiranCount = 0;

/* ─────────────────────────────────────────────────────────
   HELPERS UI
───────────────────────────────────────────────────────── */
function updateBadge(id, count) {
    document.getElementById(id).textContent = count;
}

function updateEmpty(containerId, emptyId, count) {
    const container = document.getElementById(containerId);
    const empty     = document.getElementById(emptyId);
    container.style.display = count ? '' : 'none';
    empty.style.display     = count ? 'none' : '';
}

/* ─────────────────────────────────────────────────────────
   TEMUAN
───────────────────────────────────────────────────────── */
document.getElementById('btn-add-temuan').addEventListener('click', addTemuan);

function addTemuan() {
    const idx = temuanCount++;
    const row = document.createElement('div');
    row.className = 'temuan-row p-6 transition-all';
    row.dataset.idx = idx;

    /**
     * Opsi kode temuan: tampilkan kode + nama + deskripsi (jika ada).
     * Contoh tampilan: [1.1] Kekurangan Volume — Pekerjaan tidak sesuai kontrak
     */
    const kodeOptions = KODE_TEMUANS.map(k => {
        const label = k.deskripsi
            ? `[${k.kode}] — ${k.deskripsi}`
            : `[${k.kode}]`;
        return `<option value="${k.id}" title="${label}">${label}</option>`;
    }).join('');

    row.innerHTML = `
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300 temuan-num-badge">${temuanCount}</span>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Temuan #<span class="temuan-num">${temuanCount}</span></span>
            </div>
            <button type="button" onclick="removeTemuan(this)"
                    class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors dark:hover:bg-red-900/20">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

            {{-- Kode Temuan --}}
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
                    Kode Temuan
                </label>
                <select name="temuans[${idx}][kode_temuan_id]"
                        class="kode-temuan-select form-select border rounded-lg px-3 py-2 w-full"
                        data-idx="${idx}">
                    <option value="">-- Pilih Kode Temuan --</option>
                     ${kodeOptions}
                </select>
                {{-- Info box: tampil saat kode dipilih --}}
                <div id="kode-info-${idx}" class="hidden mt-2 flex items-start gap-2 rounded-lg bg-blue-50 border border-blue-100 px-3 py-2 text-xs text-blue-700 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300">
                    <svg class="mt-0.5 h-3.5 w-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span id="kode-info-text-${idx}"></span>
                </div>
            </div>

            {{-- Kondisi / Uraian --}}
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
                    Kondisi / Uraian Temuan <span class="text-red-500">*</span>
                </label>
                <textarea name="temuans[${idx}][kondisi]" rows="3"
                          placeholder="Uraikan kondisi temuan secara singkat dan jelas..."
                          class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            </div>

            {{-- Nilai Kerugian Negara --}}
           {{-- Nilai Kerugian Negara --}}
<div>
    <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
        Kerugian Negara (Rp)
    </label>
    <div class="rupiah-wrap relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
        <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
               class="rupiah-field w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
               data-name="temuans[${idx}][nilai_kerugian_negara]" data-value="0">
    </div>
</div>

{{-- Nilai Kerugian Daerah --}}
<div>
    <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
        Kerugian Daerah (Rp)
    </label>
    <div class="rupiah-wrap relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
        <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
               class="rupiah-field w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
               data-name="temuans[${idx}][nilai_kerugian_daerah]" data-value="0">
    </div>
</div>

{{-- ✅ BARU: Nilai Kerugian Desa --}}
<div>
    <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
        Kerugian Desa (Rp)
    </label>
    <div class="rupiah-wrap relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
        <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
               class="rupiah-field w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
               data-name="temuans[${idx}][nilai_kerugian_desa]" data-value="0">
    </div>
</div>

{{-- ✅ BARU: Nilai Kerugian BOS/BLUD --}}
<div>
    <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
        Kerugian BOS/BLUD (Rp)
    </label>
    <div class="rupiah-wrap relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
        <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
               class="rupiah-field w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
               data-name="temuans[${idx}][nilai_kerugian_bos_blud]" data-value="0">
    </div>
</div>

            {{-- Total Nilai (computed, read-only) --}}
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
                    Total Nilai Kerugian (Otomatis)
                </label>
                <div class="flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-200 px-3 py-2.5 dark:bg-gray-900 dark:border-gray-700">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200 total-nilai-display" id="total-nilai-${idx}">Rp 0</span>
                    <input type="hidden" name="temuans[${idx}][nilai_temuan]" id="hidden-total-${idx}" value="0">
                </div>
            </div>

        </div>
    `;

    document.getElementById('temuan-container').appendChild(row);

    // Init rupiah inputs yang baru dibuat
    window.RupiahInput.initAll(row);

    // Wire-up: hitung total saat nilai berubah
    const hiddenNegara  = row.querySelector(`[name="temuans[${idx}][nilai_kerugian_negara]"]`);
    const hiddenDaerah  = row.querySelector(`[name="temuans[${idx}][nilai_kerugian_daerah]"]`);
    const hiddenDesa    = row.querySelector(`[name="temuans[${idx}][nilai_kerugian_desa]"]`);
    const hiddenBosBLud = row.querySelector(`[name="temuans[${idx}][nilai_kerugian_bos_blud]"]`);

    const totalDisplay = row.querySelector(`#total-nilai-${idx}`);
    const hiddenTotal  = row.querySelector(`#hidden-total-${idx}`);

    function recalcTotal() {
    const n = parseInt(hiddenNegara?.value   || '0', 10);
    const d = parseInt(hiddenDaerah?.value   || '0', 10);
    const s = parseInt(hiddenDesa?.value     || '0', 10); // desa
    const b = parseInt(hiddenBosBLud?.value  || '0', 10); // bos/blud
    const total = n + d + s + b;
    totalDisplay.textContent = 'Rp\u00a0' + window.RupiahInput.fmt(total);
    hiddenTotal.value = total;
}

    // Override onChange dari RupiahInput (via MutationObserver-lite: pakai event)
    row.querySelectorAll('.rupiah-field').forEach(field => {
        field.addEventListener('input', recalcTotal);
        field.addEventListener('paste', () => setTimeout(recalcTotal, 0));
    });

    // Wire-up: info box untuk kode temuan
    const kodeSelect = row.querySelector('.kode-temuan-select');

// ambil instance TomSelect (kalau ada)
const ts = kodeSelect.tomselect;

// fallback kalau belum ada TomSelect
const target = ts ? ts : kodeSelect;

// event change (support TomSelect & native)
target.on ? target.on('change', handleChange) : target.addEventListener('change', handleChange);

function handleChange(value) {
    // TomSelect kirim value langsung, native kirim event
    const selectedId = typeof value === 'string'
        ? parseInt(value, 10)
        : parseInt(this.value, 10);

    const found = KODE_TEMUANS.find(k => k.id === selectedId);

    const infoBox  = document.getElementById(`kode-info-${idx}`);
    const infoText = document.getElementById(`kode-info-text-${idx}`);

    if (!infoBox || !infoText) return; // biar gak error

    if (found && found.deskripsi) {
        infoText.textContent = `[${found.kode}] ${found.nama ?? ''} — ${found.deskripsi}`;
        infoBox.classList.remove('hidden');
    } else if (found) {
        infoText.textContent = `[${found.kode}] ${found.nama ?? ''}`;
        infoBox.classList.remove('hidden');
    } else {
        infoBox.classList.add('hidden');
    }
}

    // Update badge & empty state
    const count = document.querySelectorAll('.temuan-row').length;
    updateBadge('badge-temuan', count);
    updateEmpty('temuan-container', 'temuan-empty', count);

    // Smooth scroll ke temuan baru
    row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function removeTemuan(btn) {
    btn.closest('.temuan-row').remove();
    const rows = document.querySelectorAll('.temuan-row');
    rows.forEach((r, i) => {
        const numEl      = r.querySelector('.temuan-num');
        const badgeEl    = r.querySelector('.temuan-num-badge');
        if (numEl)   numEl.textContent   = i + 1;
        if (badgeEl) badgeEl.textContent = i + 1;
    });
    updateBadge('badge-temuan', rows.length);
    updateEmpty('temuan-container', 'temuan-empty', rows.length);
}

/* ─────────────────────────────────────────────────────────
   LAMPIRAN
───────────────────────────────────────────────────────── */
document.getElementById('btn-add-lampiran').addEventListener('click', addLampiran);

function addLampiran() {
    const idx = lampiranCount++;
    const row = document.createElement('div');
    row.className = 'lampiran-row flex items-center gap-4 px-6 py-4';
    row.dataset.idx = idx;

    row.innerHTML = `
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
            </svg>
        </div>
        <div class="grid flex-1 grid-cols-1 gap-3 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">File (PDF / Gambar)</label>
                <input type="file" name="attachments[${idx}][file_path]"
                       accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-primary-700 hover:file:bg-primary-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Nama File (opsional)</label>
                <input type="text" name="attachments[${idx}][file_name]"
                       placeholder="Nama tampilan file..."
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
        <button type="button" onclick="removeLampiran(this)"
                class="flex-shrink-0 rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors dark:hover:bg-red-900/20">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;

    document.getElementById('lampiran-container').appendChild(row);
    const count = document.querySelectorAll('.lampiran-row').length;
    updateBadge('badge-lampiran', count);
    updateEmpty('lampiran-container', 'lampiran-empty', count);
}

function removeLampiran(btn) {
    btn.closest('.lampiran-row').remove();
    const count = document.querySelectorAll('.lampiran-row').length;
    updateBadge('badge-lampiran', count);
    updateEmpty('lampiran-container', 'lampiran-empty', count);
}

/* ─────────────────────────────────────────────────────────
   FORM SUBMIT GUARD
───────────────────────────────────────────────────────── */
document.getElementById('form-lhp').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit');
    const text = document.getElementById('btn-submit-text');
    btn.disabled = true;
    text.textContent = 'Menyimpan...';
});

/* ─────────────────────────────────────────────────────────
   INISIALISASI AWAL
───────────────────────────────────────────────────────── */
// Sembunyikan container saat kosong (karena default display-nya block)
updateEmpty('temuan-container', 'temuan-empty', 0);
updateEmpty('lampiran-container', 'lampiran-empty', 0);
</script>
@endsection