@extends('layouts.app')

@section('content')

@php
    $temuans = $lhp->temuans;
    $allRekom = $temuans->flatMap->recommendations;
    $allTls = $allRekom->flatMap->tindakLanjuts;

    $totalTemuan = $temuans->count();
    $totalRekom = $allRekom->count();
    $totalKerugian = (float) $temuans->sum('nilai_temuan');
    $totalTargetRekom = (float) $allRekom->sum('nilai_rekom');
    $totalSetor = (float) $allTls->sum('total_terbayar');
    $sisa = max(0, $totalTargetRekom - $totalSetor);

    $progres = (float) ($lhp->statistik?->persen_selesai_gabungan ?? 0);
    if ($progres == 0 && $totalTargetRekom > 0 && $totalSetor > 0) {
        $progres = min(100, round(($totalSetor / $totalTargetRekom) * 100));
    }
@endphp

<div class="space-y-6" x-data="{
    activeTolakUrl: '',
    tolakOpen: false,
    openTolak(url) {
        this.activeTolakUrl = url;
        this.tolakOpen = true;
    },
    closeTolak() {
        this.tolakOpen = false;
        this.activeTolakUrl = '';
    }
}">

    {{-- TOP NAVIGATION & HEADER --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5 dark:border-gray-800">
        <div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-blue-600 transition-colors">
                    Monitoring Tindak Lanjut
                </a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white font-medium">Detail LHP {{ $lhp->nomor_lhp }}</span>
            </nav>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Tindak Lanjut Rekomendasi LHP
                </h1>
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-mono font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    {{ $lhp->nomor_lhp }}
                </span>
                @if($lhp->unitDiperiksa)
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                        {{ $lhp->unitDiperiksa->nama_unit }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Kelola, tinjau bukti fisik dari OPD, dan verifikasi tindak lanjut untuk setiap rekomendasi.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('tindak-lanjuts.create', ['lhp_id' => $lhp->id]) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-blue-700 shadow-sm transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Input Tindak Lanjut
            </a>
            <a href="{{ route('lhps.show', $lhp->id) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm transition-colors">
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Lihat LHP Asli
            </a>
            <a href="{{ route('tindak-lanjuts.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3.5 py-2 text-xs font-semibold text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 shadow-sm transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-xs font-semibold text-green-800 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-xs font-semibold text-red-800 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300 shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- EXECUTIVE CARD: KOMULATIF DATA LHP --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] space-y-5">
        
        {{-- Metadata Row --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 border-b border-gray-100 pb-4 dark:border-gray-800">
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Unit Diperiksa (OPD)</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">{{ $lhp->unitDiperiksa?->nama_unit ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Tanggal LHP</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                    {{ $lhp->tanggal_lhp ? $lhp->tanggal_lhp->format('d M Y') : '-' }}
                </p>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Kategori Program</p>
                <p class="mt-0.5">
                    <span class="inline-block rounded bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                        {{ $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->kategori ?? 'PKPT' }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Tim Pengawas / Surat</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                    Ketua: {{ $lhp->auditAssignment?->ketuaTim?->name ?? '-' }}
                </p>
                <p class="text-[11px] text-gray-400">{{ $lhp->auditAssignment?->nomor_surat ?? '-' }}</p>
            </div>
        </div>

        {{-- Kumulatif Data Angka --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            
            <div class="rounded-lg bg-gray-50 p-3.5 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Temuan & Rekomendasi</p>
                <p class="text-xl font-extrabold text-gray-900 dark:text-white mt-0.5">
                    {{ $totalTemuan }} <span class="text-xs font-normal text-gray-500">Temuan</span> {{ $totalRekom }} <span class="text-xs font-normal text-gray-500">Rekom</span>
                </p>
                <p class="text-[11px] text-gray-400 mt-1">Nilai Temuan: Rp{{ number_format($totalKerugian, 0, ',', '.') }}</p>
            </div>

            <div class="rounded-lg bg-gray-50 p-3.5 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Target Nilai Rekomendasi</p>
                <p class="text-xl font-extrabold text-gray-900 dark:text-white mt-0.5">
                    Rp{{ number_format($totalTargetRekom, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-gray-400 mt-1">Total kewajiban finansial</p>
            </div>

            <div class="rounded-lg bg-green-50/70 p-3.5 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <p class="text-[10px] font-bold uppercase tracking-wider text-green-700 dark:text-green-400">Realisasi Setoran (Lunas)</p>
                <p class="text-xl font-extrabold text-green-800 dark:text-green-300 mt-0.5">
                    Rp{{ number_format($totalSetor, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-green-600 dark:text-green-400 mt-1">Sisa: Rp{{ number_format($sisa, 0, ',', '.') }}</p>
            </div>

            <div class="rounded-lg bg-blue-50/70 p-3.5 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400 mb-1">
                    <span>Progres Komulatif LHP</span>
                    <span class="text-sm font-bold font-mono">{{ round($progres) }}%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-blue-200 dark:bg-blue-900 overflow-hidden mt-2">
                    <div class="h-full rounded-full {{ $progres >= 100 ? 'bg-green-500' : ($progres > 0 ? 'bg-blue-600' : 'bg-gray-300') }}"
                         style="width: {{ min(100, max(0, $progres)) }}%"></div>
                </div>
                <p class="text-[11px] text-blue-600 dark:text-blue-300 mt-2">
                    {{ $allTls->where('status_verifikasi', 'lunas')->count() }} dari {{ $totalRekom }} Rekomendasi Selesai
                </p>
            </div>

        </div>

    </div>

    {{-- SECTION DAFTAR TEMUAN & REKOMENDASI (DENGAN FITUR DROPDOWN / ACCORDION) --}}
    <div class="space-y-4">

        {{-- Section Header & Global Accordion Buttons --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-200 pb-3 dark:border-gray-800">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">
                    Daftar Temuan & Rekomendasi Tindak Lanjut
                </h2>
                <p class="text-xs text-gray-500">Klik judul temuan atau rekomendasi untuk membuka / menutup dropdown rincian</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="$dispatch('toggle-all-temuans', true)"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                    Buka Semua Temuan & Rekomendasi
                </button>
                <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                <button type="button" @click="$dispatch('toggle-all-temuans', false)"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400">
                    Tutup Semua
                </button>
            </div>
        </div>

        {{-- LOOP TEMUANS --}}
        @forelse($temuans as $tIndex => $temuan)
            <div x-data="{ openTemuan: true }"
                 @toggle-all-temuans.window="openTemuan = $event.detail"
                 class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden transition-all">
                
                {{-- TEMUAN ACCORDION HEADER --}}
                <button type="button" @click="openTemuan = !openTemuan"
                        class="w-full text-left bg-gray-50/80 hover:bg-gray-100/80 px-5 py-3.5 border-b border-gray-200 dark:bg-gray-800/60 dark:hover:bg-gray-800 dark:border-gray-700 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="inline-flex items-center rounded bg-gray-900 px-2 py-0.5 text-xs font-mono font-bold text-white dark:bg-gray-700">
                            TEMUAN {{ $tIndex + 1 }}
                        </span>
                        <span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-mono font-bold text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                            {{ $temuan->kodeTemuan?->kode ?? '-' }}
                        </span>
                        <span class="text-xs font-bold text-gray-900 dark:text-white truncate">
                            {{ $temuan->kondisi ?: ($temuan->kodeTemuan?->deskripsi ?? 'Kondisi Temuan') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4 shrink-0">
                        @if($temuan->nilai_temuan > 0)
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Nilai: Rp{{ number_format($temuan->nilai_temuan, 0, ',', '.') }}
                            </span>
                        @endif
                        <span class="rounded bg-gray-200 px-2 py-0.5 text-[11px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            {{ $temuan->recommendations->count() }} Rekomendasi
                        </span>
                        <svg class="h-4 w-4 text-gray-500 transition-transform duration-200"
                             :class="{ 'rotate-180': openTemuan }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                {{-- TEMUAN ACCORDION BODY --}}
                <div x-show="openTemuan" class="p-5 space-y-4">
                    
                    {{-- Uraian Kondisi Temuan --}}
                    @if($temuan->kondisi)
                        <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-700 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-700 dark:text-gray-300">
                            <strong class="font-semibold block mb-0.5 text-gray-800 dark:text-gray-200">Kondisi Temuan:</strong>
                            <p class="leading-relaxed">{{ $temuan->kondisi }}</p>
                        </div>
                    @endif

                    {{-- SUB-ACCORDION PER REKOMENDASI --}}
                    <div class="space-y-3">
                        @forelse($temuan->recommendations as $rIndex => $rekom)
                            @php
                                $tl = $rekom->tindakLanjuts->first();
                                $isUang = ($rekom->jenis_rekomendasi === 'uang');
                                $target = (float) ($tl?->nilai_tindak_lanjut > 0 ? $tl->nilai_tindak_lanjut : $rekom->nilai_rekom);
                                $terbayar = (float) ($tl?->total_terbayar ?? 0);
                                $sisaRekom = max(0, $target - $terbayar);

                                $statusVerif = $tl?->status_verifikasi ?? 'menunggu_verifikasi';
                                $statusOpd = $tl?->status_opd ?? 'belum_upload';

                                $vBadge = match($statusVerif) {
                                    'lunas' => 'bg-green-50 text-green-700 ring-1 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400',
                                    'berjalan' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400',
                                    default => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400'
                                };

                                $oBadge = match($statusOpd) {
                                    'dikirim' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'draft' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400',
                                    default => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                                };
                                $oLabel = match($statusOpd) {
                                    'dikirim' => 'Terkirim',
                                    'draft' => 'Draft OPD',
                                    default => 'Belum Upload'
                                };
                                if ($tl?->alasan_tolak_opd) {
                                    $oBadge = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400';
                                    $oLabel = 'Ditolak';
                                }

                                $rekomAttachments = $tl?->attachments ?? collect();
                            @endphp

                            <div x-data="{ openRekom: true }"
                                 @toggle-all-temuans.window="openRekom = $event.detail"
                                 class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/40 overflow-hidden">
                                
                                {{-- REKOMENDASI ACCORDION HEADER --}}
                                <button type="button" @click="openRekom = !openRekom"
                                        class="w-full text-left px-4 py-3 bg-white hover:bg-gray-50 dark:bg-gray-800/80 dark:hover:bg-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700 transition-colors">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <span class="rounded bg-indigo-50 px-1.5 py-0.5 text-[10px] font-bold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 shrink-0">
                                            Rekom {{ $tIndex + 1 }}.{{ $rIndex + 1 }}
                                        </span>
                                        @if($rekom->kodeRekomendasi)
                                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-mono font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300 shrink-0">
                                                {{ $rekom->kodeRekomendasi->kode_rekomendasi }}
                                            </span>
                                        @endif
                                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 line-clamp-1">
                                            {!! strip_tags($rekom->uraian_rekom) !!}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        @if($isUang)
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                Rp{{ number_format($target, 0, ',', '.') }}
                                            </span>
                                        @endif
                                        <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold {{ $vBadge }}">
                                            {{ strtoupper(str_replace('_', ' ', $statusVerif)) }}
                                        </span>
                                        <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold {{ $oBadge }}">
                                            OPD: {{ $oLabel }}
                                        </span>
                                        <svg class="h-3.5 w-3.5 text-gray-400 transition-transform duration-200"
                                             :class="{ 'rotate-180': openRekom }"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </button>

                                {{-- REKOMENDASI ACCORDION BODY --}}
                                <div x-show="openRekom" class="p-4 space-y-4 text-xs bg-gray-50/30 dark:bg-transparent">
                                    
                                    {{-- Uraian Lengkap Rekomendasi --}}
                                    <div>
                                        <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">Uraian Rekomendasi:</p>
                                        <div class="text-gray-800 dark:text-gray-200 leading-relaxed bg-white p-3 rounded border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                                            {!! $rekom->uraian_rekom !!}
                                        </div>
                                    </div>

                                    {{-- Finansial & Data Pengajuan OPD --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 bg-white p-3 rounded-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                                        <div>
                                            <p class="text-gray-400 uppercase text-[10px] font-bold">Target Nilai</p>
                                            <p class="font-bold text-gray-900 dark:text-white mt-0.5">
                                                @if($isUang)
                                                    Rp{{ number_format($target, 0, ',', '.') }}
                                                @else
                                                    Non-Finansial
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-gray-400 uppercase text-[10px] font-bold">Terverifikasi Lunas</p>
                                            <p class="font-bold text-green-600 dark:text-green-400 mt-0.5">
                                                Rp{{ number_format($terbayar, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-gray-400 uppercase text-[10px] font-bold">Sisa Belum Bayar</p>
                                            <p class="font-bold {{ $sisaRekom == 0 ? 'text-green-600' : 'text-red-600' }} mt-0.5">
                                                Rp{{ number_format($sisaRekom, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-gray-400 uppercase text-[10px] font-bold">Metode / Jenis</p>
                                            <p class="font-semibold text-gray-800 dark:text-gray-200 mt-0.5">
                                                {{ ucfirst(str_replace('_', ' ', $tl?->jenis_penyelesaian ?? 'Setor Kas')) }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Keterangan & Berkas Bukti OPD --}}
                                    <div class="rounded-lg bg-white p-3 border border-gray-100 dark:bg-gray-800 dark:border-gray-700 space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">
                                                Bukti & Keterangan Pengajuan OPD
                                            </p>
                                            @if($tl?->uploadOpdOleh)
                                                <span class="text-[11px] text-gray-400">
                                                    Diupload oleh {{ $tl->uploadOpdOleh->name }}
                                                    @if($tl->dikirim_pada)
                                                        &bull; {{ $tl->dikirim_pada->format('d/m/Y H:i') }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        @if($tl?->alasan_tolak_opd)
                                            <div class="rounded bg-red-50 p-2.5 text-red-700 border border-red-200 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                                                <strong>Catatan Penolakan:</strong> {{ $tl->alasan_tolak_opd }}
                                            </div>
                                        @endif

                                        @if($tl?->keterangan_pendukung_opd)
                                            <p class="whitespace-pre-line text-gray-700 dark:text-gray-300 bg-gray-50 p-2.5 rounded border border-gray-100 dark:bg-gray-900/40 dark:border-gray-800">
                                                {{ $tl->keterangan_pendukung_opd }}
                                            </p>
                                        @endif

                                        {{-- Lampiran Berkas / Screenshot --}}
                                        @if($rekomAttachments->isNotEmpty())
                                            <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                                                <p class="text-[11px] font-semibold text-gray-500 mb-1.5">Berkas Lampiran ({{ $rekomAttachments->count() }}):</p>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                    @foreach($rekomAttachments as $att)
                                                        @php
                                                            $ext = strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION));
                                                            $isImg = in_array($ext, ['png', 'jpg', 'jpeg', 'webp']);
                                                        @endphp
                                                        <div class="flex items-center justify-between p-2 rounded border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900 text-xs">
                                                            <div class="flex items-center gap-2 min-w-0">
                                                                <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                                <span class="truncate font-medium text-gray-800 dark:text-gray-200" title="{{ $att->file_name }}">{{ $att->file_name }}</span>
                                                            </div>
                                                            <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                                                <a href="{{ $att->file_url }}" target="_blank" class="text-blue-600 hover:underline font-semibold">Lihat</a>
                                                                <a href="{{ route('attachments.show', $att->id) }}" download class="text-gray-400 hover:text-gray-600">Unduh</a>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif(! $tl?->keterangan_pendukung_opd)
                                            <p class="italic text-gray-400 text-[11px]">Belum ada data tindak lanjut / berkas yang diunggah OPD.</p>
                                        @endif
                                    </div>

                                    {{-- AKSI VERIFIKASI LANGSUNG UNTUK SUPERADMIN --}}
                                    @if($tl)
                                        <div class="pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 dark:border-gray-700">
                                            <div class="flex flex-wrap items-center gap-2">
                                                {{-- Verifikasi Lunas --}}
                                                <form action="{{ route('tindak-lanjuts.verifikasi-opd', $tl) }}" method="POST"
                                                      onsubmit="return confirm('Verifikasi rekomendasi ini sebagai LUNAS? Realisasi Rp{{ number_format($target, 0, ',', '.') }} akan disetujui.')">
                                                    @csrf
                                                    <input type="hidden" name="status_verifikasi" value="lunas">
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 transition-colors shadow-sm">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        Verifikasi Lunas
                                                    </button>
                                                </form>

                                                {{-- Verifikasi Berjalan --}}
                                                <form action="{{ route('tindak-lanjuts.verifikasi-opd', $tl) }}" method="POST"
                                                      onsubmit="return confirm('Verifikasi rekomendasi ini sebagai BERJALAN / Proses?')">
                                                    @csrf
                                                    <input type="hidden" name="status_verifikasi" value="berjalan">
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm">
                                                        Verifikasi Berjalan
                                                    </button>
                                                </form>

                                                {{-- Tolak Bukti --}}
                                                <button type="button"
                                                        @click="openTolak('{{ route('tindak-lanjuts.tolak-opd', $tl) }}')"
                                                        class="inline-flex items-center gap-1 rounded border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-800 dark:bg-gray-800 dark:text-red-400 transition-colors">
                                                    Tolak Bukti
                                                </button>

                                                {{-- Buka Kunci --}}
                                                @if($tl->status_opd === 'dikirim')
                                                    <form action="{{ route('tindak-lanjuts.buka-kunci-opd', $tl) }}" method="POST"
                                                          onsubmit="return confirm('Buka kunci OPD agar OPD dapat mengunggah ulang?')">
                                                        @csrf @method('PATCH')
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 rounded border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                                            Buka Kunci
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            <div>
                                                <a href="{{ route('tindak-lanjuts.show', $tl->id) }}"
                                                   class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                                    Halaman Detail Penuh TL #{{ $tl->id }} &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">Belum ada rekomendasi untuk temuan ini.</p>
                        @endforelse
                    </div>

                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-400 dark:border-gray-800">
                Belum ada temuan atau rekomendasi yang tercatat pada LHP ini.
            </div>
        @endforelse

    </div>

    {{-- MODAL TOLAK OPD --}}
    <template x-teleport="body">
        <div x-show="tolakOpen"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             x-cloak>
            <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800"
                 @click.outside="closeTolak()">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Tolak Bukti Tindak Lanjut</h3>
                    <button @click="closeTolak()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="activeTolakUrl" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="alasan_tolak" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Alasan Penolakan / Permintaan Revisi <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alasan_tolak" id="alasan_tolak" rows="4" required
                                  class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                  placeholder="Jelaskan bagian bukti yang salah atau perlu diperbaiki oleh OPD..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeTolak()"
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

@endsection
