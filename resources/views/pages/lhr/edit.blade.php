@extends('layouts.app')

@section('content')

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
    <form method="POST" action="{{ route('lhr.update', $lhr->uuid) }}" class="space-y-8" id="formEditLhr">
        @csrf
        @method('PUT')

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
                <div class="md:col-span-8">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Perangkat Daerah (OPD) Direviu <span class="text-red-500">*</span>
                    </label>
                    <select name="unit_diperiksa_id" required
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_diperiksa_id', $lhr->unit_diperiksa_id) == $u->id)>
                                {{ $u->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_diperiksa_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tahun Anggaran Dokumen (4 cols) --}}
                <div class="md:col-span-4">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Tahun Anggaran Dokumen <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="tahun_anggaran" min="2020" max="2035" required
                           value="{{ old('tahun_anggaran', $lhr->tahun_anggaran) }}"
                           class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                    @error('tahun_anggaran')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
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
                        <input type="date" name="tanggal_lhr"
                               value="{{ old('tanggal_lhr', $lhr->tanggal_lhr ? $lhr->tanggal_lhr->format('Y-m-d') : '') }}"
                               class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
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
                    <select name="ditandatangani_oleh"
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
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
                        <input type="date" name="permohonan_tanggal"
                               value="{{ old('permohonan_tanggal', $lhr->permohonan_tanggal ? $lhr->permohonan_tanggal->format('Y-m-d') : '') }}"
                               class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
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
                        <input type="date" name="tanggal_mulai"
                               value="{{ old('tanggal_mulai', $lhr->tanggal_mulai ? $lhr->tanggal_mulai->format('Y-m-d') : '') }}"
                               class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        @error('tanggal_mulai')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Tanggal Selesai Reviu
                        </label>
                        <input type="date" name="tanggal_selesai"
                               value="{{ old('tanggal_selesai', $lhr->tanggal_selesai ? $lhr->tanggal_selesai->format('Y-m-d') : '') }}"
                               class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        @error('tanggal_selesai')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
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
                Simpan Perubahan Informasi Dokumen
            </button>
        </div>

    </form>
</div>

@endsection
