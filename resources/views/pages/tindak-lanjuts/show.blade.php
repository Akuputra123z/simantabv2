@extends('layouts.app')

@section('content')

@if(!isset($tindakLanjut) || !$tindakLanjut->id)
<div class="flex h-[60vh] flex-col items-center justify-center text-center">
    <div class="mb-4 rounded-full bg-gray-100 p-4 dark:bg-gray-800">
        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Data Tidak Ditemukan</h2>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Data tindak lanjut tidak tersedia atau telah dihapus.</p>
    <a href="{{ route('tindak-lanjuts.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 transition-colors">
        Kembali ke Daftar
    </a>
</div>
@php return; @endphp
@endif

@php
    $rekom = $tindakLanjut->recommendation;
    $temuan = $rekom?->temuan;
    $lhp = $temuan?->lhp;
    $unit = $lhp?->unitDiperiksa;
    $isUang = ($rekom?->jenis_rekomendasi === 'uang');
    $targetNilai = (float) ($tindakLanjut->nilai_tindak_lanjut > 0 ? $tindakLanjut->nilai_tindak_lanjut : ($rekom?->nilai_rekom ?? 0));
    $totalTerbayar = (float) $tindakLanjut->total_terbayar;
    $sisaBayar = max(0, $targetNilai - $totalTerbayar);
    $percentRealisasi = $targetNilai > 0 ? min(100, round(($totalTerbayar / $targetNilai) * 100)) : ($tindakLanjut->status_verifikasi === 'lunas' ? 100 : 0);

    $allAttachments = $tindakLanjut->attachments;
    $hasOpdData = !empty($tindakLanjut->keterangan_pendukung_opd) || $allAttachments->isNotEmpty() || !empty($tindakLanjut->upload_opd_oleh);

    // Parse nomor bukti jika ada
    $nomorBukti = null;
    if (preg_match('/(?:No\.?\s*Bukti\s*\/?\s*STS|No\.?\s*STS|STS|No\.?\s*Dokumen|No\.?\s*BAST)\s*[:#]?\s*([A-Za-z0-9\/\-_.]+)/i', $tindakLanjut->keterangan_pendukung_opd ?? '', $matches)) {
        $nomorBukti = trim($matches[1]);
    }

    $jenisMap = [
        'setor_kas' => 'Setor Kas',
        'cicilan' => 'Cicilan / Bertahap',
        'pengembalian_barang' => 'Pengembalian Barang / Aset',
        'perbaikan_administrasi' => 'Perbaikan Administrasi',
    ];
    $jenisLabel = $jenisMap[$tindakLanjut->jenis_penyelesaian] ?? ucfirst(str_replace('_', ' ', $tindakLanjut->jenis_penyelesaian ?? '-'));

    $statusVerifBadge = match($tindakLanjut->status_verifikasi) {
        'lunas' => 'bg-green-50 text-green-700 ring-1 ring-green-600/20 dark:bg-green-900/20 dark:text-green-400',
        'berjalan' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400',
        default => 'bg-gray-100 text-gray-700 ring-1 ring-gray-600/20 dark:bg-gray-800 dark:text-gray-300'
    };

    $statusOpdBadge = match($tindakLanjut->status_opd) {
        'dikirim' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400',
        'draft' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-900/20 dark:text-amber-400',
        default => 'bg-gray-100 text-gray-600 ring-1 ring-gray-600/20 dark:bg-gray-800 dark:text-gray-400'
    };
    $statusOpdLabel = match($tindakLanjut->status_opd) {
        'dikirim' => 'Terkirim',
        'draft' => 'Draft',
        default => 'Belum Upload'
    };
    if ($tindakLanjut->alasan_tolak_opd) {
        $statusOpdBadge = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-900/20 dark:text-red-400';
        $statusOpdLabel = 'Ditolak';
    }
@endphp

<div class="space-y-6">

    {{-- HEADER & ACTIONS --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-gray-900 dark:hover:text-white">Tindak Lanjut</a>
                <span>/</span>
                @if($lhp)
                    <a href="{{ route('tindak-lanjuts.lhp', $lhp->id) }}" class="hover:text-gray-900 dark:hover:text-white">LHP {{ $lhp->nomor_lhp }}</a>
                    <span>/</span>
                @endif
                <span class="text-gray-900 font-medium dark:text-white">ID #{{ $tindakLanjut->id }}</span>
            </nav>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Tindak Lanjut</h1>
                @if($unit)
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                        {{ $unit->nama_unit }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($lhp)
                <a href="{{ route('tindak-lanjuts.lhp', $lhp->id) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3.5 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 transition-colors dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Semua Rekomendasi LHP
                </a>
            @endif

            <a href="{{ route('tindak-lanjuts.edit', $tindakLanjut->id) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Edit
            </a>

            @if(auth()->user()?->hasRole('super_admin'))
            <form action="{{ route('tindak-lanjuts.destroy', $tindakLanjut->id) }}" method="POST"
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus data tindak lanjut ini?')" class="inline">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3.5 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors dark:border-red-900/40 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </form>
            @endif

            <a href="{{ route('tindak-lanjuts.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-gray-800 transition-colors dark:bg-gray-700 dark:hover:bg-gray-600">
                Kembali
            </a>
        </div>
    </div>

    {{-- ALERT NOTIFICATIONS --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- STATUS SINKRONISASI INFO --}}
    @if($tindakLanjut->status_verifikasi === 'lunas')
        <div class="rounded-xl border border-green-200 bg-green-50/60 p-4 dark:border-green-800/40 dark:bg-green-900/10">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-green-900 dark:text-green-200">Tindak Lanjut Telah Terverifikasi Lunas</p>
                        <p class="text-xs text-green-700 dark:text-green-400">
                            Diverifikasi oleh {{ $tindakLanjut->verifikator->name ?? 'Verifikator' }}
                            @if($tindakLanjut->diverifikasi_pada)
                                pada {{ $tindakLanjut->diverifikasi_pada->format('d/m/Y H:i') }}
                            @endif
                            . Realisasi setoran Rp{{ number_format($totalTerbayar, 0, ',', '.') }} telah disinkronkan ke rekomendasi.
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/40 dark:text-green-300">
                    LUNAS 100%
                </span>
            </div>
        </div>
    @elseif($hasOpdData)
        <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-800/40 dark:bg-amber-900/10">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-900 dark:text-amber-200">Bukti Tindak Lanjut Menunggu Verifikasi</p>
                        <p class="text-xs text-amber-800 dark:text-amber-300">
                            OPD telah mengunggah bukti penyelesaian
                            @if($targetNilai > 0)
                                sebesar <strong>Rp{{ number_format($targetNilai, 0, ',', '.') }}</strong>
                            @endif
                            @if($nomorBukti)
                                (No. Bukti/STS: <strong>{{ $nomorBukti }}</strong>)
                            @endif
                            . Tinjau bukti lampiran di bawah dan pilih aksi verifikasi.
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center rounded-md bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                    Menunggu Verifikasi
                </span>
            </div>
        </div>
    @endif

    {{-- MAIN 2-COLUMN LAYOUT --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- LEFT COLUMN --}}
        <div class="space-y-6 lg:col-span-8">

            {{-- CARD 1: BUKTI & PENGAJUAN OPD --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 dark:border-gray-800 px-5 py-3.5 flex items-center justify-between">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Bukti Tindak Lanjut dari OPD
                    </h2>
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $statusOpdBadge }}">
                        {{ $statusOpdLabel }}
                    </span>
                </div>

                <div class="p-5 space-y-5">
                    @if($tindakLanjut->alasan_tolak_opd)
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3.5 text-xs text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                            <strong class="font-semibold block mb-0.5 text-red-800 dark:text-red-200">Catatan Penolakan / Permintaan Revisi:</strong>
                            {{ $tindakLanjut->alasan_tolak_opd }}
                        </div>
                    @endif

                    {{-- RINGKASAN DATA OPD --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 border-b border-gray-100 pb-4 dark:border-gray-800 text-sm">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Metode Penyelesaian</p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $jenisLabel }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Nomor Bukti / STS</p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $nomorBukti ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Nilai Setoran Diklaim</p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                @if($isUang)
                                    Rp{{ number_format($tindakLanjut->nilai_tindak_lanjut, 0, ',', '.') }}
                                @else
                                    Non-Finansial
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- KETERANGAN OPD --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Keterangan / Uraian OPD</p>
                        <div class="rounded-lg bg-gray-50 p-3.5 border border-gray-100 text-sm text-gray-800 dark:bg-gray-800/50 dark:border-gray-800 dark:text-gray-300">
                            @if($tindakLanjut->keterangan_pendukung_opd)
                                <p class="whitespace-pre-line leading-relaxed">{{ $tindakLanjut->keterangan_pendukung_opd }}</p>
                            @else
                                <p class="italic text-gray-400">Tidak ada keterangan tertulis dari OPD.</p>
                            @endif

                            @if($tindakLanjut->uploadOpdOleh)
                                <div class="mt-3 pt-2.5 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>Diunggah oleh: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $tindakLanjut->uploadOpdOleh->name }}</span></span>
                                    <span>{{ $tindakLanjut->dikirim_pada ? $tindakLanjut->dikirim_pada->format('d/m/Y H:i') : ($tindakLanjut->updated_at ? $tindakLanjut->updated_at->format('d/m/Y H:i') : '-') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- FILE LAMPIRAN BUKTI FISIK --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                            Berkas Bukti / Tangkapan Layar ({{ $allAttachments->count() }})
                        </p>

                        @if($allAttachments->isEmpty())
                            <div class="rounded-lg border border-dashed border-gray-200 p-4 text-center text-xs text-gray-400 dark:border-gray-800">
                                Belum ada berkas lampiran yang diunggah.
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach($allAttachments as $att)
                                    @php
                                        $ext = strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif']) || str_starts_with($att->file_type ?? '', 'image/');
                                        $sizeKb = $att->file_size ? number_format($att->file_size / 1024, 0) . ' KB' : null;
                                    @endphp
                                    <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-800/40">
                                        @if($isImage)
                                            <a href="{{ $att->file_url }}" target="_blank" class="block mb-2 overflow-hidden rounded border border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
                                                <img src="{{ $att->file_url }}" alt="{{ $att->file_name }}" class="h-32 w-full object-cover hover:opacity-90 transition-opacity" loading="lazy">
                                            </a>
                                        @else
                                            <div class="mb-2 flex h-20 w-full items-center justify-center rounded border border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-900 text-gray-400">
                                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        @endif

                                        <div class="flex-1 min-w-0">
                                            <p class="truncate text-xs font-semibold text-gray-900 dark:text-white" title="{{ $att->file_name }}">
                                                {{ $att->file_name }}
                                            </p>
                                            @if($sizeKb)
                                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $sizeKb }}</p>
                                            @endif
                                        </div>

                                        <div class="mt-2.5 pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs font-medium">
                                            <a href="{{ $att->file_url }}" target="_blank" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                                Lihat Berkas
                                            </a>
                                            <a href="{{ route('attachments.show', $att->id) }}" download class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                                                Unduh
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- TOMBOL AKSI VERIFIKASI SUPERADMIN / VERIFIKATOR --}}
                    @can('verifikasi', $tindakLanjut)
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                            Aksi Verifikasi Tindak Lanjut
                        </p>

                        <div class="flex flex-wrap items-center gap-2.5">
                            {{-- VERIFIKASI LUNAS --}}
                            <form action="{{ route('tindak-lanjuts.verifikasi-opd', $tindakLanjut) }}" method="POST"
                                  onsubmit="return confirm('Verifikasi tindak lanjut ini sebagai LUNAS? Realisasi Rp{{ number_format($targetNilai, 0, ',', '.') }} akan disinkronkan ke rekomendasi.')">
                                @csrf
                                <input type="hidden" name="status_verifikasi" value="lunas">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-xs font-semibold text-white hover:bg-green-700 transition-colors shadow-sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Verifikasi Lunas (Rp{{ number_format($targetNilai, 0, ',', '.') }})
                                </button>
                            </form>

                            {{-- VERIFIKASI BERJALAN --}}
                            <form action="{{ route('tindak-lanjuts.verifikasi-opd', $tindakLanjut) }}" method="POST"
                                  onsubmit="return confirm('Verifikasi tindak lanjut ini sebagai BERJALAN / Proses?')">
                                @csrf
                                <input type="hidden" name="status_verifikasi" value="berjalan">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Verifikasi Berjalan
                                </button>
                            </form>

                            {{-- TOLAK BUKTI --}}
                            @can('tolakOpd', $tindakLanjut)
                            <button type="button"
                                    x-on:click="$store.tolakModal.open()"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-3.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors dark:border-red-800 dark:bg-gray-800 dark:text-red-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tolak Bukti
                            </button>
                            @endcan

                            {{-- BUKA KUNCI OPD --}}
                            @can('bukaKunciOpd', $tindakLanjut)
                            @if($tindakLanjut->status_opd === 'dikirim')
                            <form action="{{ route('tindak-lanjuts.buka-kunci-opd', $tindakLanjut) }}" method="POST"
                                  onsubmit="return confirm('Buka kunci OPD agar OPD dapat mengunggah ulang?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                    Buka Kunci OPD
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </div>
                    @endcan

                </div>
            </div>

            {{-- CARD 2: REKOMENDASI & TEMUAN --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 dark:border-gray-800 px-5 py-3.5 flex items-center justify-between">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Uraian Rekomendasi & Temuan
                    </h2>
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $statusVerifBadge }}">
                        {{ strtoupper(str_replace('_', ' ', $tindakLanjut->status_verifikasi)) }}
                    </span>
                </div>

                <div class="p-5 space-y-4">
                    {{-- URAIAN REKOMENDASI --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Rekomendasi</p>
                        <div class="text-sm text-gray-900 leading-relaxed dark:text-gray-100">
                            {!! $rekom?->uraian_rekom ?? 'Tidak ada uraian rekomendasi.' !!}
                        </div>
                    </div>

                    {{-- TEMUAN KONDISI --}}
                    @if($temuan)
                    <div class="rounded-lg bg-gray-50 p-3.5 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-800">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Temuan: {{ $temuan->kodeTemuan?->kode ?? '-' }} - {{ $temuan->kodeTemuan?->deskripsi ?? 'Kondisi Pemeriksaan' }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ $temuan->kondisi ?? '-' }}
                        </p>
                    </div>
                    @endif

                    {{-- DETAIL INFO GRID --}}
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 border-t border-gray-100 pt-4 dark:border-gray-800 text-xs">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Batas Waktu</p>
                            <p class="mt-0.5 font-semibold text-gray-900 dark:text-white">
                                {{ $tindakLanjut->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($tindakLanjut->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Verifikator</p>
                            <p class="mt-0.5 font-semibold text-gray-900 dark:text-white truncate" title="{{ $tindakLanjut->verifikator->name ?? '-' }}">
                                {{ $tindakLanjut->verifikator->name ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Ketua Tim</p>
                            <p class="mt-0.5 font-semibold text-gray-900 dark:text-white truncate" title="{{ $lhp?->auditAssignment?->ketuaTim?->name ?? '-' }}">
                                {{ $lhp?->auditAssignment?->ketuaTim?->name ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Kode Rekomendasi</p>
                            <p class="mt-0.5 font-semibold text-gray-900 dark:text-white">
                                {{ $rekom?->kodeRekomendasi?->kode_rekomendasi ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 3: RIWAYAT CICILAN (JIKA ADA) --}}
            @if($tindakLanjut->jenis_penyelesaian === 'cicilan')
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 dark:border-gray-800 px-5 py-3.5 flex items-center justify-between">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Riwayat Pembayaran Cicilan
                    </h2>
                    <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut->id) }}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">
                        Kelola Cicilan &rarr;
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500">
                            <tr>
                                <th class="px-4 py-2.5 font-semibold">Ke</th>
                                <th class="px-4 py-2.5 font-semibold">Tanggal</th>
                                <th class="px-4 py-2.5 font-semibold">No. Bukti</th>
                                <th class="px-4 py-2.5 font-semibold text-right">Nominal</th>
                                <th class="px-4 py-2.5 font-semibold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($tindakLanjut->cicilans()->latest()->get() as $cicilan)
                            <tr>
                                <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-white">#{{ $cicilan->ke }}</td>
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $cicilan->tanggal_bayar ? $cicilan->tanggal_bayar->format('d/m/Y') : '-' }}</td>
                                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">{{ $cicilan->nomor_bukti ?: '-' }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-white">Rp{{ number_format($cicilan->nilai_bayar, 0, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $cicilan->status === 'diterima' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ strtoupper($cicilan->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-400">Belum ada cicilan tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- CARD 4: CATATAN & HAMBATAN --}}
            @if($tindakLanjut->catatan_tl || $tindakLanjut->hambatan || $tindakLanjut->catatan_verifikasi)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] space-y-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Catatan & Hambatan
                </h3>

                @if($tindakLanjut->catatan_verifikasi)
                <div>
                    <p class="text-xs font-medium text-blue-700 dark:text-blue-400">Catatan Verifikator:</p>
                    <p class="mt-1 text-xs text-gray-700 dark:text-gray-300 bg-blue-50/50 p-2.5 rounded border border-blue-100 dark:bg-blue-900/10 dark:border-blue-800">
                        {{ $tindakLanjut->catatan_verifikasi }}
                    </p>
                </div>
                @endif

                @if($tindakLanjut->catatan_tl)
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Catatan Tindak Lanjut:</p>
                    <p class="mt-1 text-xs text-gray-700 dark:text-gray-300 bg-gray-50 p-2.5 rounded border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                        {!! nl2br(e($tindakLanjut->catatan_tl)) !!}
                    </p>
                </div>
                @endif

                @if($tindakLanjut->hambatan)
                <div>
                    <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Hambatan:</p>
                    <p class="mt-1 text-xs text-gray-700 dark:text-gray-300 bg-amber-50/50 p-2.5 rounded border border-amber-100 dark:bg-amber-900/10 dark:border-amber-800">
                        {!! nl2br(e($tindakLanjut->hambatan)) !!}
                    </p>
                </div>
                @endif
            </div>
            @endif

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-6 lg:col-span-4">

            {{-- REALISASI FINANSIAL CARD (STANDARD CLEAN DESIGN) --}}
            @if($isUang)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Realisasi Finansial</span>
                    <span class="text-lg font-bold {{ $percentRealisasi >= 100 ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                        {{ $percentRealisasi }}%
                    </span>
                </div>

                {{-- PROGRESS BAR --}}
                <div class="mt-3.5">
                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="h-full {{ $percentRealisasi >= 100 ? 'bg-green-500' : 'bg-blue-600' }} transition-all"
                             style="width: {{ $percentRealisasi }}%">
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-2.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Target Rekomendasi:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">Rp{{ number_format($targetNilai, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Nilai Disetor OPD:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">Rp{{ number_format($tindakLanjut->nilai_tindak_lanjut, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Terverifikasi Lunas:</span>
                        <span class="font-bold text-green-600 dark:text-green-400">Rp{{ number_format($totalTerbayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2 dark:border-gray-800">
                        <span class="text-gray-500 dark:text-gray-400">Sisa Belum Lunas:</span>
                        <span class="font-semibold {{ $sisaBayar == 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            Rp{{ number_format($sisaBayar, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            {{-- METADATA INFO CARD --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                    Informasi Laporan
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Nomor LHP</span>
                        <p class="font-semibold text-blue-600 dark:text-blue-400 mt-0.5">
                            @if($lhp)
                                <a href="{{ route('lhps.show', $lhp->id) }}" class="hover:underline">{{ $lhp->nomor_lhp }}</a>
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Unit Terperiksa</span>
                        <p class="font-semibold text-gray-900 dark:text-white mt-0.5">{{ $unit->nama_unit ?? '-' }}</p>
                    </div>

                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Kode Temuan</span>
                        <p class="font-semibold text-gray-900 dark:text-white mt-0.5">{{ $temuan->kodeTemuan?->kode ?? '-' }}</p>
                    </div>

                    <div class="pt-2.5 border-t border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500 dark:text-gray-400">Dibuat Oleh</span>
                        <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $tindakLanjut->creator->name ?? 'Sistem' }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $tindakLanjut->created_at ? $tindakLanjut->created_at->format('d/m/Y H:i') : '-' }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL TOLAK OPD --}}
    <template x-teleport="body">
        <div x-show="$store.tolakModal.open"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             x-cloak>
            <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800"
                 @click.outside="$store.tolakModal.close()">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Tolak Bukti Tindak Lanjut</h3>
                    <button @click="$store.tolakModal.close()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('tindak-lanjuts.tolak-opd', $tindakLanjut) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="alasan_tolak" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Alasan Penolakan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alasan_tolak" id="alasan_tolak" rows="4" required
                                  class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                  placeholder="Jelaskan alasan penolakan agar OPD dapat memperbaiki dokumen..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="$store.tolakModal.close()"
                                class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            Batal
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700 transition-colors">
                            Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('tolakModal', {
        open: false,
        open() {
            this.open = true;
        },
        close() {
            this.open = false;
        }
    });
});
</script>
@endpush
@endsection