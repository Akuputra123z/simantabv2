@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-6">
        <div class="space-y-1">
            <nav class="flex items-center gap-2 text-sm font-medium text-gray-400">
                <a href="{{ route('opd.tindak-lanjut.index') }}" class="transition hover:text-gray-900">
                    Tindak Lanjut
                </a>
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                </svg>
                <span class="text-gray-900">Detail</span>
            </nav>
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Detail Tindak Lanjut
            </h1>
        </div>
        <a href="{{ route('opd.tindak-lanjut.index') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-gray-800">
            Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-5 py-4 text-sm font-semibold text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-5 py-4 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">

        {{-- LEFT COLUMN --}}
        <div class="space-y-6 lg:col-span-8">

            {{-- Informasi Tindak Lanjut --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-widest text-gray-900">
                        Informasi Tindak Lanjut
                    </h2>
                    @php
                        $colors = [
                            'lunas' => 'bg-green-50 text-green-700 ring-green-600/10',
                            'berjalan' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
                            'menunggu_verifikasi' => 'bg-blue-50 text-blue-700 ring-blue-600/10',
                        ];
                        $color = $colors[$tindakLanjut->status_verifikasi] ?? 'bg-gray-50 text-gray-700 ring-gray-600/10';
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $color }}">
                        {{ str_replace('_', ' ', $tindakLanjut->status_verifikasi) }}
                    </span>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Uraian Rekomendasi</p>
                        <p class="text-sm leading-relaxed text-gray-800">
                            {{ $tindakLanjut->recommendation?->uraian_rekom ?? 'Tidak ada uraian.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">No. LHP</p>
                            <p class="text-sm font-bold text-gray-900">{{ $tindakLanjut->recommendation?->temuan?->lhp?->nomor_lhp ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Unit</p>
                            <p class="text-sm font-bold text-gray-900">{{ $tindakLanjut->recommendation?->temuan?->lhp?->unitDiperiksa?->nama_unit ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Jenis</p>
                            <p class="text-sm font-bold text-gray-900">{{ ucfirst($tindakLanjut->jenis_penyelesaian) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Batas Waktu</p>
                            <p class="text-sm font-bold text-gray-900">
                                {{ $tindakLanjut->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($tindakLanjut->tanggal_jatuh_tempo)->format('d M Y') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status OPD Banner --}}
            @if($tindakLanjut->status_opd === 'dikirim')
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-emerald-800">Tindak lanjut telah dikirim</p>
                            <p class="text-xs text-emerald-600">
                                Dikirim pada {{ $tindakLanjut->dikirim_pada->format('d M Y H:i') }}
                                @if($tindakLanjut->uploadOpdOleh)
                                    oleh {{ $tindakLanjut->uploadOpdOleh->name }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($tindakLanjut->alasan_tolak_opd)
                <div class="rounded-2xl border border-red-100 bg-red-50 p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-red-800">Draft — Ditolak oleh Inspektorat</p>
                            <p class="text-xs text-red-600 mt-2 bg-white rounded-lg p-3 border border-red-100">
                                <span class="font-bold">Alasan:</span> {{ $tindakLanjut->alasan_tolak_opd }}
                            </p>
                            <p class="text-xs text-red-500 mt-2">Silakan perbaiki dan upload ulang, lalu klik Kirim.</p>
                        </div>
                    </div>
                </div>
            @elseif($tindakLanjut->status_opd === 'draft')
                <div class="rounded-2xl border border-yellow-100 bg-yellow-50 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100">
                            <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-yellow-800">Draft — Belum dikirim</p>
                            <p class="text-xs text-yellow-600">Upload bukti dan klik Kirim untuk mengirimkan tindak lanjut.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Upload Bukti / Read-only Display --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xs font-bold uppercase tracking-widest text-gray-900">
                    Upload Bukti Tindak Lanjut
                </h2>

                @if($tindakLanjut->keterangan_pendukung_opd || $tindakLanjut->attachments->where('jenis_bukti', 'opd_upload')->isNotEmpty())
                    <div class="mb-6 rounded-xl bg-blue-50 border border-blue-100 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-3">Upload Sebelumnya</p>

                        @if($tindakLanjut->keterangan_pendukung_opd)
                            <div class="mb-3">
                                <p class="text-xs font-bold text-gray-500 mb-1">Keterangan Pendukung:</p>
                                <p class="text-sm text-gray-700 italic bg-white rounded-lg p-3 border border-blue-50">
                                    {{ $tindakLanjut->keterangan_pendukung_opd }}
                                </p>
                            </div>
                        @endif

                        @php
                            $opdFiles = $tindakLanjut->attachments->where('jenis_bukti', 'opd_upload')->values();
                            $hapusUrl = route('opd.tindak-lanjut.hapus-lampiran', [$tindakLanjut, '__ID__']);
                        @endphp
                        @if($opdFiles->isNotEmpty())
                            <div x-data='opdFiles(@json($opdFiles->map(fn($f) => ["id" => $f->id, "name" => $f->file_name, "url" => $f->file_url])), @json($hapusUrl), @json($tindakLanjut->status_opd === "dikirim"))'>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-bold text-gray-500">File Lampiran:</p>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(file, idx) in files" :key="file.id">
                                        <div class="flex items-center gap-2 rounded-lg border border-blue-100 bg-white px-4 py-2.5 text-sm transition-colors"
                                             :class="{'opacity-50': deleting === file.id}">
                                            <a :href="file.url" target="_blank"
                                               class="flex flex-1 items-center gap-3 text-gray-700 hover:text-blue-600 min-w-0">
                                                <svg class="h-4 w-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="truncate" x-text="file.name"></span>
                                            </a>
                                            <button x-show="!readonly" type="button" @click="confirmHapus(file.id, file.name)"
                                                    class="shrink-0 rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                                                    :disabled="deleting === file.id">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                {{-- Konfirmasi Hapus Modal --}}
                                <div x-show="showConfirm" x-cloak
                                     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40"
                                     @click.self="showConfirm = false">
                                    <div class="mx-4 w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                                        <h3 class="text-sm font-bold text-gray-900">Hapus Lampiran</h3>
                                        <p class="mt-2 text-sm text-gray-600">
                                            Yakin ingin menghapus <span class="font-semibold" x-text="hapusName"></span>?
                                        </p>
                                        <div class="mt-5 flex items-center justify-end gap-3">
                                            <button type="button" @click="showConfirm = false"
                                                    class="rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-200 transition-colors">
                                                Batal
                                            </button>
                                            <button type="button" @click="hapus"
                                                    class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition-colors"
                                                    x-text="deleting ? 'Menghapus...' : 'Ya, Hapus'"
                                                    :disabled="deleting">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($tindakLanjut->uploadOpdOleh)
                            <p class="mt-3 text-xs text-gray-400">
                                Diupload oleh: <span class="font-semibold">{{ $tindakLanjut->uploadOpdOleh->name }}</span>
                                @if($tindakLanjut->updated_at)
                                    &middot; {{ $tindakLanjut->updated_at->format('d M Y H:i') }}
                                @endif
                            </p>
                        @endif
                    </div>
                @endif

                @if($tindakLanjut->status_opd === 'dikirim')
                    <div class="rounded-xl bg-gray-50 border border-gray-100 p-5 text-center">
                        <p class="text-sm text-gray-500">
                            ✓ Tindak lanjut telah dikirim pada
                            <span class="font-bold text-gray-700">{{ $tindakLanjut->dikirim_pada->format('d M Y H:i') }}</span>.
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Tidak dapat diubah. Hubungi Inspektorat jika ada revisi.</p>
                    </div>
                @elseif($tindakLanjut->alasan_tolak_opd && $tindakLanjut->status_opd !== 'draft' && $tindakLanjut->status_opd !== 'dikirim')
                    <div class="rounded-xl bg-red-50 border border-red-100 p-5">
                        <p class="text-sm font-bold text-red-700">Ditolak oleh Inspektorat</p>
                        <p class="text-sm text-red-600 mt-1">{{ $tindakLanjut->alasan_tolak_opd }}</p>
                        <p class="text-xs text-red-500 mt-2">Silakan upload ulang bukti yang sudah diperbaiki.</p>
                    </div>
                @endif

                @if($tindakLanjut->status_opd !== 'dikirim')
                    <form action="{{ route('opd.tindak-lanjut.upload', $tindakLanjut) }}" method="POST"
                          enctype="multipart/form-data" class="space-y-5">
                        @csrf

                         <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">
                                Upload Bukti (Maks. 5 file, 10MB/file)
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <input type="file" name="attachments[]" multiple
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            @error('attachments.*')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            @error('attachments')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1.5 text-xs text-gray-400">Format: PDF, JPG, PNG, DOC, XLS. Maksimal 10MB per file.</p>
                        </div>

                        <div>
                            <label for="keterangan_pendukung" class="block text-sm font-bold text-gray-700 mb-1.5">
                                Keterangan Pendukung
                            </label>
                            <textarea name="keterangan_pendukung" id="keterangan_pendukung" rows="4"
                                      class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                      placeholder="Jelaskan tindak lanjut yang sudah dilakukan...">{{ old('keterangan_pendukung') }}</textarea>
                            @error('keterangan_pendukung')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                       

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Upload Bukti
                            </button>
                            @if($tindakLanjut->keterangan_pendukung_opd)
                                <p class="text-xs text-gray-400">Upload ulang akan mengganti data sebelumnya.</p>
                            @endif
                        </div>
                    </form>

                    @if($tindakLanjut->keterangan_pendukung_opd || $tindakLanjut->attachments->where('jenis_bukti', 'opd_upload')->isNotEmpty())
                        <div class="mt-6 border-t border-gray-100 pt-6">
                            <form action="{{ route('opd.tindak-lanjut.kirim', $tindakLanjut) }}" method="POST"
                                  onsubmit="return confirm('Kirim tindak lanjut ini? Setelah dikirim tidak bisa diubah.')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-8 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Kirim Tindak Lanjut
                                </button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-5 lg:col-span-4">

            {{-- Informasi Audit --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-gray-900 border-b border-gray-50 pb-2">
                    Informasi Audit
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-400">Kode Temuan</p>
                        <p class="font-bold text-gray-900">{{ $tindakLanjut->recommendation?->temuan?->kodeTemuan?->kode ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-400">Kode Rekomendasi</p>
                        <p class="font-bold text-gray-900">{{ $tindakLanjut->recommendation?->kodeRekomendasi?->kode ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-400">Kecamatan</p>
                        <p class="font-bold text-gray-900">{{ $tindakLanjut->recommendation?->temuan?->lhp?->unitDiperiksa?->nama_kecamatan ?? '-' }}</p>
                    </div>
                    @php $assignment = $tindakLanjut->recommendation?->temuan?->lhp?->auditAssignment; @endphp
                    @if($assignment)
                        <div class="pt-3 border-t border-gray-50">
                            <div>
                                <p class="text-xs font-bold uppercase text-gray-400">Nomor Surat Tugas</p>
                                <p class="font-bold text-gray-900">{{ $assignment->nomor_surat_tugas ?? '-' }}</p>
                            </div>
                            <div class="mt-3">
                                <p class="text-xs font-bold uppercase text-gray-400">Ketua Tim</p>
                                <p class="font-bold text-gray-900">{{ $assignment->ketuaTim?->name ?? '-' }}</p>
                            </div>
                            @if($assignment->members->isNotEmpty())
                                <div class="mt-3">
                                    <p class="text-xs font-bold uppercase text-gray-400 mb-1">Anggota Tim</p>
                                    <ul class="space-y-1">
                                        @foreach($assignment->members as $member)
                                            <li class="text-sm text-gray-600">
                                                &bull; {{ $member->name ?? '-' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Status OPD --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-gray-900 border-b border-gray-50 pb-2">
                    Status OPD
                </h3>
                @php
                    $opdBadgeColors = [
                        'dikirim' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                        'draft' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/10',
                    ];
                    $opdBadgeColor = $opdBadgeColors[$tindakLanjut->status_opd] ?? 'bg-gray-50 text-gray-700 ring-gray-600/10';
                    $opdLabel = $tindakLanjut->status_opd === 'dikirim' ? 'Terkirim' : ($tindakLanjut->status_opd === 'draft' ? 'Draft' : 'Belum Upload');
                @endphp
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $opdBadgeColor }}">
                        {{ $opdLabel }}
                    </span>
                    @if($tindakLanjut->dikirim_pada)
                        <span class="text-xs text-gray-400">{{ $tindakLanjut->dikirim_pada->format('d/m/y H:i') }}</span>
                    @endif
                </div>
                @if($tindakLanjut->uploadOpdOleh)
                    <p class="mt-2 text-xs text-gray-400">
                        Diupload oleh: {{ $tindakLanjut->uploadOpdOleh->name }}
                    </p>
                @endif
            </div>

            {{-- Bantuan --}}
            <div class="rounded-2xl border border-dashed border-gray-200 p-5">
                <p class="text-xs font-bold text-gray-900 uppercase mb-1">Butuh Bantuan?</p>
                <p class="text-xs leading-relaxed text-gray-500">
                    Hubungi Inspektorat jika ada pertanyaan mengenai tindak lanjut ini.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('opdFiles', (files, deleteUrl, readonly) => ({
        files: files,
        deleteUrl: deleteUrl,
        readonly: readonly,
        deleting: null,
        showConfirm: false,
        hapusId: null,
        hapusName: '',

        confirmHapus(id, name) {
            this.hapusId = id;
            this.hapusName = name;
            this.showConfirm = true;
        },

        async hapus() {
            this.deleting = this.hapusId;
            try {
                const res = await fetch(this.deleteUrl.replace('__ID__', this.hapusId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) {
                    const body = await res.json().catch(() => ({}));
                    throw new Error(body?.message || 'Gagal');
                }
                this.files = this.files.filter(f => f.id !== this.hapusId);
                this.showConfirm = false;
            } catch (e) {
                alert(e.message || 'Gagal menghapus lampiran.');
            } finally {
                this.deleting = null;
                this.hapusId = null;
                this.hapusName = '';
            }
        }
    }));
});
</script>
@endsection
