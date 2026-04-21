@extends('layouts.app')

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
    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Tambah Temuan
</button>
                </div>
            </div>
 
            <div id="temuan-container" class="divide-y divide-gray-100 dark:divide-gray-700">
                {{-- Rows injected by JS --}}
            </div>
 
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
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300/40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
    <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 disabled:opacity-60">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Simpan LHP
    </button>
</div>
 
    </form>
</div>
 
{{-- ── Kode Temuan Data for JS ── --}}
<script>
const KODE_TEMUANS = @json($kodeTemuans->map(fn($k) => ['id' => $k->id, 'kode' => $k->kode, 'nama' => $k->nama ?? $k->kode]));
 
let temuanCount  = 0;
let lampiranCount = 0;
 
function updateBadge(id, count) {
    document.getElementById(id).textContent = count;
}
 
function updateEmpty(containerId, emptyId, count) {
    document.getElementById(containerId).style.display = count ? 'block' : 'none';
    document.getElementById(emptyId).style.display     = count ? 'none' : 'flex';
}
 
// ──────────────────────────────────────────
// TEMUAN
// ──────────────────────────────────────────
document.getElementById('btn-add-temuan').addEventListener('click', addTemuan);
 
function addTemuan() {
    const idx = temuanCount++;
    const row = document.createElement('div');
    row.className = 'temuan-row p-6';
    row.dataset.idx = idx;
 
    const kodeOptions = KODE_TEMUANS.map(k =>
        `<option value="${k.id}">[${k.kode}] ${k.nama}</option>`
    ).join('');
 
    row.innerHTML = `
        <div class="mb-3 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Temuan #<span class="temuan-num">${temuanCount}</span></span>
            <button type="button" onclick="removeTemuan(this)" class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus
            </button>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Kode Temuan</label>
                <select name="temuans[${idx}][kode_temuan_id]"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">-- Pilih Kode --</option>
                    ${kodeOptions}
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Kondisi / Uraian Temuan</label>
                <textarea name="temuans[${idx}][kondisi]" rows="2"
                          placeholder="Uraikan kondisi temuan..."
                          class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Nilai Kerugian Negara (Rp)</label>
                <input type="number" name="temuans[${idx}][nilai_kerugian_negara]" value="0" min="0" step="0.01"
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Nilai Kerugian Daerah (Rp)</label>
                <input type="number" name="temuans[${idx}][nilai_kerugian_daerah]" value="0" min="0" step="0.01"
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
    `;
 
    document.getElementById('temuan-container').appendChild(row);
    updateBadge('badge-temuan', document.querySelectorAll('.temuan-row').length);
    updateEmpty('temuan-container', 'temuan-empty', 1);
}
 
function removeTemuan(btn) {
    btn.closest('.temuan-row').remove();
    const rows = document.querySelectorAll('.temuan-row');
    rows.forEach((r, i) => r.querySelector('.temuan-num').textContent = i + 1);
    updateBadge('badge-temuan', rows.length);
    updateEmpty('temuan-container', 'temuan-empty', rows.length);
}
 
// ──────────────────────────────────────────
// LAMPIRAN
// ──────────────────────────────────────────
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
                class="flex-shrink-0 rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
 
    document.getElementById('lampiran-container').appendChild(row);
    updateBadge('badge-lampiran', document.querySelectorAll('.lampiran-row').length);
    updateEmpty('lampiran-container', 'lampiran-empty', 1);
}
 
function removeLampiran(btn) {
    btn.closest('.lampiran-row').remove();
    const rows = document.querySelectorAll('.lampiran-row');
    updateBadge('badge-lampiran', rows.length);
    updateEmpty('lampiran-container', 'lampiran-empty', rows.length);
}
</script>
@endsection