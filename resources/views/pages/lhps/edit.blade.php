@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl">
 
    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('lhps.index') }}" class="hover:text-primary-600">LHP</a>
                <span>/</span>
                <a href="{{ route('lhps.show', $lhp) }}" class="hover:text-primary-600 max-w-xs truncate">{{ $lhp->nomor_lhp }}</a>
                <span>/</span>
                <span class="text-gray-700 dark:text-white">Edit</span>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit LHP</h1>
        </div>
        <a href="{{ route('lhps.show', $lhp) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>
 
    {{-- Status Badge --}}
    @php
        $statusConfig = match($lhp->status) {
            'draft'           => ['label' => 'Draft',           'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
            'final'           => ['label' => 'Final',           'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
            'ditandatangani'  => ['label' => 'Ditandatangani',  'class' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'],
            'batal'           => ['label' => 'Dibatalkan',      'class' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'],
            default           => ['label' => ucfirst($lhp->status), 'class' => 'bg-gray-100 text-gray-600'],
        };
    @endphp
    <div class="mb-6 flex items-center gap-3">
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">
            {{ $statusConfig['label'] }}
        </span>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            Terakhir diperbarui: {{ $lhp->updated_at->format('d M Y, H:i') }}
        </span>
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
 
    <form action="{{ route('lhps.update', $lhp) }}" method="POST" enctype="multipart/form-data" id="form-lhp-edit">
        @csrf
        @method('PUT')
 
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
 
                    {{-- Penugasan (read-only on edit) --}}
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Penugasan Audit</label>
                        <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 dark:border-gray-600 dark:bg-gray-700/50">
                            <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $lhp->auditAssignment->auditProgram->nama_program ?? '-' }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Penugasan tidak dapat diubah setelah LHP dibuat.</p>
                    </div>
 
                    {{-- Nomor LHP --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nomor LHP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nomor_lhp"
                               value="{{ old('nomor_lhp', $lhp->nomor_lhp) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('nomor_lhp') border-red-500 @enderror">
                        @error('nomor_lhp')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
 
                    {{-- Tanggal LHP --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal LHP <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_lhp"
                               value="{{ old('tanggal_lhp', $lhp->tanggal_lhp?->format('Y-m-d')) }}"
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
                            <option value="1" {{ old('semester', $lhp->semester) == 1 ? 'selected' : '' }}>Semester I</option>
                            <option value="2" {{ old('semester', $lhp->semester) == 2 ? 'selected' : '' }}>Semester II</option>
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
                        <input type="text" name="irban"
                               value="{{ old('irban', $lhp->irban) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('irban') border-red-500 @enderror">
                        @error('irban')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
 
                    {{-- Jenis Pemeriksaan --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Pemeriksaan</label>
                        <input type="text" name="jenis_pemeriksaan"
                               value="{{ old('jenis_pemeriksaan', $lhp->jenis_pemeriksaan) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
 
                    {{-- Catatan Umum --}}
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Umum</label>
                        <textarea name="catatan_umum" rows="3"
                                  class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ old('catatan_umum', $lhp->catatan_umum) }}</textarea>
                    </div>
 
                </div>
            </div>
        </div>
 
        {{-- ── SECTION 2: Temuan (Read-only info, edit via separate route) ── --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-300">2</div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Data Temuan</h2>
                        <span class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-100 px-1.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $lhp->temuans->count() }}</span>
                    </div>
                    <a href="{{ route('lhps.show', $lhp) }}#temuan"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Kelola Temuan
                    </a>
                </div>
            </div>
 
            @if ($lhp->temuans->count())
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($lhp->temuans as $i => $t)
                <div class="flex items-start gap-4 px-6 py-4">
                    <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            @if ($t->kodeTemuan)
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ $t->kodeTemuan->kode }}
                            </span>
                            @endif
                            @php
                                $tlConfig = match($t->status_tl) {
                                    'selesai'               => ['label' => 'Selesai',      'class' => 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-300'],
                                    'dalam_proses'          => ['label' => 'Dalam Proses', 'class' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-900/30 dark:text-yellow-300'],
                                    default                 => ['label' => 'Belum',        'class' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-300'],
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $tlConfig['class'] }}">{{ $tlConfig['label'] }}</span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">{{ $t->kondisi ?? '-' }}</p>
                        @if ($t->nilai_temuan > 0)
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Nilai: <span class="font-medium text-gray-700 dark:text-gray-300">Rp {{ number_format($t->nilai_temuan, 0, ',', '.') }}</span>
                        </p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="py-8 text-center">
                <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada temuan terdaftar.</p>
            </div>
            @endif
        </div>
 
        {{-- ── SECTION 3: Lampiran Baru ── --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-300">3</div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Lampiran Dokumen</h2>
                    </div>
                    <button type="button" id="btn-add-lampiran"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Lampiran Baru
                    </button>
                </div>
            </div>
 
            {{-- Lampiran yang sudah ada --}}
            @if ($lhp->attachments->count())
            <div class="border-b border-gray-100 dark:border-gray-700">
                <p class="px-6 py-2 text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Lampiran Tersimpan</p>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($lhp->attachments as $att)
                    <div class="flex items-center gap-3 px-6 py-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20">
                            <svg class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-300">{{ $att->file_name ?? basename($att->file_path) }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $att->created_at->format('d M Y') }}</p>
                        </div>
                        <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                           class="flex-shrink-0 rounded-md px-2.5 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20">
                            Lihat
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
 
            {{-- Upload lampiran baru --}}
            <div id="lampiran-container" class="divide-y divide-gray-100 dark:divide-gray-700"></div>
            <div id="lampiran-empty" class="flex flex-col items-center justify-center py-8 text-center {{ $lhp->attachments->count() ? 'hidden' : '' }}">
                <p class="text-sm text-gray-400 dark:text-gray-500">Klik <strong class="font-medium text-gray-500">Tambah Lampiran Baru</strong> untuk mengunggah file tambahan.</p>
            </div>
 
            <div class="border-t border-gray-100 px-6 py-3 dark:border-gray-700">
                <p class="text-xs text-gray-400 dark:text-gray-500">Format: PDF, JPG, PNG, JPEG. Maks. 10 MB per file.</p>
            </div>
        </div>
 
        {{-- ── Footer Action ── --}}
        <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <button type="button" onclick="confirmRefresh()"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh Statistik
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('lhps.show', $lhp) }}"
                   class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
 
    </form>
</div>
 
<script>
let lampiranCount = 0;
 
document.getElementById('btn-add-lampiran').addEventListener('click', addLampiran);
 
function addLampiran() {
    const idx = lampiranCount++;
    const row = document.createElement('div');
    row.className = 'lampiran-row flex items-center gap-4 px-6 py-4';
 
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
        <button type="button" onclick="this.closest('.lampiran-row').remove(); syncEmpty()"
                class="flex-shrink-0 rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
 
    document.getElementById('lampiran-container').appendChild(row);
    document.getElementById('lampiran-empty').classList.add('hidden');
}
 
function syncEmpty() {
    const count = document.querySelectorAll('.lampiran-row').length;
    const emptyEl = document.getElementById('lampiran-empty');
    @if (!$lhp->attachments->count())
    emptyEl.classList.toggle('hidden', count > 0);
    @endif
}
 
function confirmRefresh() {
    if (confirm('Refresh statistik LHP ini? Proses ini akan menghitung ulang semua data.')) {
        window.location.href = '{{ route('lhps.refresh', $lhp) }}';
    }
}
</script>
@endsection