@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10 space-y-6">

    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5 text-xs font-semibold">
                <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5 text-xs font-semibold">
                <svg class="h-4 w-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
    @endif
    @if(session('info'))
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-800 dark:border-blue-800/60 dark:bg-blue-950/40 dark:text-blue-300 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5 text-xs font-semibold">
                <svg class="h-4 w-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700">&times;</button>
        </div>
    @endif

    {{-- HEADER & BREADCRUMB --}}
    @php
        $allTls = $lhr->pengujians->flatMap->catatans->flatMap->rekomendasis->flatMap->tindakLanjuts;
        $totalDraft = $allTls->filter(function($tl) {
            return ($tl->status_opd === 'draft') || (!empty($tl->alasan_tolak_opd)) || (empty($tl->status_opd) && (!empty($tl->uraian_tindak_lanjut) || $tl->attachments->isNotEmpty()));
        })->count();
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 dark:border-slate-800 pb-5">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('opd.lhr.index') }}" class="hover:text-blue-600 transition">Daftar LHR OPD</a>
                <span>&bull;</span>
                <span class="font-mono text-slate-700 dark:text-slate-300">{{ $lhr->nomor_lhr ?? 'Draf Dokumen' }}</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                Hasil Reviu: {{ $lhr->jenisDokumen->nama ?? 'Dokumen Perencanaan' }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Tahun Anggaran {{ $lhr->tahun_anggaran }} &bull; Perangkat Daerah: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $lhr->unitDiperiksa->nama_unit ?? '-' }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('opd.lhr.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition shadow-xs">
                &larr; Kembali
            </a>

            @if($totalDraft > 0)
                <form action="{{ route('opd.lhr.tindak-lanjut.kirim-semua', $lhr->uuid) }}" method="POST" onsubmit="return confirm('Kirim seluruh draf tindak lanjut ({{ $totalDraft }} butir) ke Inspektorat? Form akan terkunci setelah dikirim.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition shadow-xs cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Kirim Semua Revisi ({{ $totalDraft }})</span>
                    </button>
                </form>
            @endif

            {{-- Unduh PDF Resmi --}}
            <button type="button"
               onclick="window.requestSecurePdf('opd_lhr_pdf', '{{ $lhr->uuid }}', 'LHR: {{ addslashes($lhr->nomor_lhr ?? 'DRAFT') }}')"
               class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-red-700 transition shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Unduh PDF Resmi</span>
            </button>

            {{-- Unduh Word --}}
            <a href="{{ route('opd.lhr.word', $lhr->uuid) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 transition shadow-xs">
                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 17.5l-1.5-6h1.5l.8 3.8.8-3.8h1.5l.8 3.8.8-3.8H15l-1.5 6h-1.5l-.8-3.6-.8 3.6H8.5z"/>
                </svg>
                <span>Unduh Word (.docx)</span>
            </a>
        </div>
    </div>

    {{-- KARTU INFORMASI DOKUMEN RESMI --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900/80">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nomor Naskah LHR</p>
                <p class="mt-1 font-mono text-sm font-bold text-slate-900 dark:text-white">{{ $lhr->nomor_lhr ?? '-' }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Tanggal: {{ $lhr->tanggal_lhr ? $lhr->tanggal_lhr->translatedFormat('d F Y') : '-' }}</p>
            </div>

            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dasar Surat Tugas (SPT)</p>
                <p class="mt-1 font-mono text-sm font-bold text-slate-900 dark:text-white">{{ $lhr->auditAssignment->nomor_surat ?? '-' }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Ketua Tim: {{ $lhr->auditAssignment?->ketuaTim?->name ?? '-' }}</p>
            </div>

            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pejabat Penandatangan</p>
                <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $lhr->signer->name ?? 'Inspektur Daerah' }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">NIP: {{ $lhr->signer->nip ?? '-' }}</p>
            </div>

            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status Penyelesaian</p>
                <div class="mt-1 flex items-center gap-2">
                    @php
                        $badgeClass = match($lhr->status) {
                            \App\Enums\LhrStatus::Terbit       => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200',
                            \App\Enums\LhrStatus::TindakLanjut => 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200',
                            \App\Enums\LhrStatus::Selesai      => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200',
                            default                            => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                        };
                    @endphp
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $badgeClass }}">
                        {{ $lhr->status->label() }}
                    </span>
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                        {{ $lhr->statistik->rekomendasi_selesai ?? 0 }}/{{ $lhr->statistik->total_rekomendasi ?? 0 }} Selesai
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION: DAFTAR PENGUJIAN, CATATAN REVIU & REKOMENDASI PERBAIKAN --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">📋</span>
                Daftar Catatan & Rekomendasi Perbaikan Dokumen
            </h2>
            <span class="text-xs text-slate-500">
                Total {{ $lhr->statistik->total_rekomendasi ?? 0 }} butir rekomendasi
            </span>
        </div>

        @php
            $nomorRekom = 1;
        @endphp

        @forelse($lhr->pengujians as $pengujian)
            @php
                $catatans = $pengujian->catatans;
            @endphp

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900/80 space-y-4">
                {{-- Header Pengujian --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-start gap-2.5">
                        <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ $pengujian->kode ?? $pengujian->kode_tampilan ?? '-' }}
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ $pengujian->judul ?? $pengujian->nama ?? '-' }}
                            </h3>
                            @if(!empty($pengujian->kriteria))
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    Kriteria: {{ $pengujian->kriteria }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Simpulan Badge --}}
                    <div>
                        @php
                            $simpulanStatusVal = $pengujian->status_simpulan instanceof \BackedEnum
                                ? $pengujian->status_simpulan->value
                                : (string) $pengujian->status_simpulan;

                            $simpulanClass = match($simpulanStatusVal) {
                                'sesuai'           => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'sebagian_sesuai'  => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/40 dark:text-amber-300',
                                'belum_sesuai'     => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/40 dark:text-rose-300',
                                default            => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400',
                            };

                            $simpulanLabel = $pengujian->status_simpulan instanceof \App\Enums\SimpulanStatus
                                ? $pengujian->status_simpulan->label()
                                : ($simpulanStatusVal ? ucfirst(str_replace('_', ' ', $simpulanStatusVal)) : 'Belum Dinilai');
                        @endphp
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $simpulanClass }}">
                            Simpulan: {{ $simpulanLabel }}
                        </span>
                    </div>
                </div>

                {{-- Catatan Reviu & Rekomendasi --}}
                @if($catatans->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($catatans as $catatan)
                            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800/60 dark:bg-slate-800/40 space-y-3">
                                {{-- Judul & Referensi Catatan --}}
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                            Catatan Hasil Telaah:
                                        </span>
                                        @if($catatan->kategori)
                                             <span class="rounded bg-blue-100/70 px-2 py-0.5 text-[10px] font-semibold text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                                {{ $catatan->kategori->nama }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($catatan->referensi_bagian)
                                        <span class="text-[11px] text-slate-400 font-mono">
                                            Ref: {{ $catatan->referensi_bagian }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Isi Teks Catatan --}}
                                <div class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed pl-2 border-l-2 border-blue-400">
                                    {!! nl2br(e($catatan->isi)) !!}
                                </div>

                                {{-- Daftar Rekomendasi Perbaikan --}}
                                @if($catatan->rekomendasis->isNotEmpty())
                                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 space-y-2">
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Rekomendasi Perbaikan untuk Perangkat Daerah:
                                        </p>

                                        @foreach($catatan->rekomendasis as $rekom)
                                            @php
                                                $tl = $rekom->tindakLanjuts->first();
                                                $isDikirim = $tl && $tl->status_opd === 'dikirim' && empty($tl->alasan_tolak_opd);
                                                $isDitolak = $tl && !empty($tl->alasan_tolak_opd);
                                                $isSelesai = $tl && $tl->status_verifikasi === 'lunas';
                                                $isDraft = $tl && $tl->status_opd === 'draft' && empty($tl->alasan_tolak_opd);
                                                $isBelum = !$tl || (empty($tl->status_opd) && empty($tl->uraian_tindak_lanjut) && $tl->attachments->isEmpty());

                                                $rekomStatusVal = $rekom->status instanceof \BackedEnum
                                                    ? $rekom->status->value
                                                    : (string) ($rekom->status ?? 'belum_ditindaklanjuti');
                                                $rekomBadge = match($rekomStatusVal) {
                                                    'selesai' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
                                                    'proses', 'dalam_proses' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
                                                    default => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300',
                                                };
                                                $rekomLabel = ucfirst(str_replace('_', ' ', $rekomStatusVal));

                                                $opdStatusBadge = match(true) {
                                                    $isSelesai => ['label' => 'Selesai Diverifikasi', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border-emerald-300'],
                                                    $isDitolak => ['label' => 'Perlu Perbaikan / Ditolak', 'class' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300 border-rose-300 animate-pulse'],
                                                    $isDikirim => ['label' => 'Menunggu Telaah Reviu', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border-blue-300'],
                                                    $isDraft   => ['label' => 'Draf Tersimpan', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 border-amber-300'],
                                                    default    => ['label' => 'Belum Ditindaklanjuti', 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border-slate-200'],
                                                };
                                            @endphp

                                            <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-4 dark:border-amber-900/40 dark:bg-amber-950/20 space-y-3">
                                                {{-- Header Butir Rekomendasi --}}
                                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                                    <div class="space-y-1">
                                                        <div class="flex items-start gap-2">
                                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 text-[10px] font-bold text-amber-900 mt-0.5">
                                                                {{ $nomorRekom++ }}
                                                            </span>
                                                            <div>
                                                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-relaxed">
                                                                    {{ $rekom->uraian_rekom }}
                                                                </p>
                                                                @if($rekom->batas_waktu)
                                                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                                                        Batas Waktu Revisi: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $rekom->batas_waktu->translatedFormat('d F Y') }}</span>
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="shrink-0 flex items-center gap-2 pl-7 sm:pl-0">
                                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $rekomBadge }}">
                                                            {{ $rekomLabel }}
                                                        </span>
                                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $opdStatusBadge['class'] }}">
                                                            {{ $opdStatusBadge['label'] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Kotak Catatan Penolakan / Permintaan Revisi Tim Reviu (Jika ada) --}}
                                                @if($isDitolak)
                                                    <div class="rounded-lg border border-rose-300 bg-rose-50 p-3 dark:border-rose-900/60 dark:bg-rose-950/30 text-xs">
                                                        <div class="flex items-center gap-2 font-bold text-rose-800 dark:text-rose-300">
                                                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                            <span>Catatan Penolakan / Permintaan Revisi Tim Reviu:</span>
                                                        </div>
                                                        <p class="mt-1 pl-6 text-rose-700 dark:text-rose-400 font-medium">
                                                            {{ $tl->alasan_tolak_opd }}
                                                        </p>
                                                    </div>
                                                @endif

                                                {{-- Kotak Status Disetujui Tuntas --}}
                                                @if($isSelesai)
                                                    <div class="rounded-lg border border-emerald-300 bg-emerald-50/70 p-3 text-xs text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300 flex items-center gap-2">
                                                        <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Tindak lanjut rekomendasi ini telah diverifikasi dan disetujui tuntas oleh Inspektorat.</span>
                                                    </div>
                                                @endif

                                                {{-- Kotak Status Sedang Dikirim / Terkunci --}}
                                                @if($isDikirim)
                                                    <div class="rounded-lg border border-blue-200 bg-blue-50/60 p-3 text-xs text-blue-800 dark:border-blue-900/50 dark:bg-blue-950/20 dark:text-blue-300 flex items-center gap-2">
                                                        <svg class="h-4 w-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                        <span>Tindak lanjut telah dikirim ke Inspektorat pada {{ $tl->dikirim_pada ? $tl->dikirim_pada->translatedFormat('d M Y H:i') : '-' }}. Formulir terkunci selama proses penelaahan.</span>
                                                    </div>
                                                @endif

                                                {{-- Panel Tindak Lanjut & Bukti Unggahan --}}
                                                @if($tl)
                                                    <div class="rounded-lg border border-slate-200 bg-white p-3.5 dark:border-slate-800 dark:bg-slate-900 space-y-3">
                                                        <div class="flex items-center justify-between text-xs font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2">
                                                            <span class="flex items-center gap-1.5">
                                                                <span>📝</span> Lembar Kerja Revisi & Tindak Lanjut OPD
                                                            </span>
                                                            @if($tl->uploadOpdOleh)
                                                                <span class="text-[11px] font-normal text-slate-400">
                                                                    Diperbarui oleh: {{ $tl->uploadOpdOleh->name }} ({{ $tl->updated_at->diffForHumans() }})
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- Jika form read-only (dikirim atau selesai) --}}
                                                        @if($isDikirim || $isSelesai)
                                                            <div class="space-y-2">
                                                                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Uraian Tindak Lanjut:</span>
                                                                <div class="rounded-md bg-slate-50 p-2.5 text-xs text-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                                                                    {!! nl2br(e($tl->uraian_tindak_lanjut ?: 'Tidak ada uraian tertulis.')) !!}
                                                                </div>
                                                            </div>
                                                        @else
                                                            {{-- Form Input OPD Aktif --}}
                                                            <form action="{{ route('opd.lhr.tindak-lanjut.upload', [$lhr->uuid, $tl->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                                                @csrf
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                                        Uraian Tindak Lanjut / Penjelasan Naskah Revisi:
                                                                    </label>
                                                                    <textarea name="keterangan_pendukung_opd" rows="2"
                                                                              placeholder="Tuliskan uraian perbaikan dokumen, pasal/bab yang disesuaikan, atau tanggapan resmi OPD..."
                                                                              class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-xs text-slate-800 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ old('keterangan_pendukung_opd', $tl->keterangan_pendukung_opd ?: $tl->uraian_tindak_lanjut) }}</textarea>
                                                                </div>

                                                                <div>
                                                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                                        Unggah Berkas Naskah Revisi / Bukti Tindak Lanjut:
                                                                    </label>
                                                                    <input type="file" name="attachments[]" multiple
                                                                           class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950/50 dark:file:text-blue-300 cursor-pointer" />
                                                                    <span class="text-[10px] text-slate-400 mt-0.5 block">
                                                                        Format diperbolehkan: PDF, Word (.docx/.doc), Excel (.xlsx), Gambar, ZIP (Maksimal 20 MB per berkas).
                                                                    </span>
                                                                </div>

                                                                <div class="flex items-center gap-2 pt-1">
                                                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-900 shadow-xs transition cursor-pointer dark:bg-slate-700 dark:hover:bg-slate-600">
                                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                                                        <span>Simpan Draf</span>
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        @endif

                                                        {{-- Daftar Berkas Lampiran yang Telah Terunggah --}}
                                                        @if($tl->attachments->isNotEmpty())
                                                            <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                                                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Berkas Lampiran Terunggah ({{ $tl->attachments->count() }}):</span>
                                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                                    @foreach($tl->attachments as $att)
                                                                        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-800/60 text-xs">
                                                                            <div class="flex items-center gap-2 truncate mr-2">
                                                                                <svg class="h-4 w-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                                                <span class="truncate font-medium text-slate-800 dark:text-slate-200" title="{{ $att->file_name }}">
                                                                                    {{ $att->file_name }}
                                                                                </span>
                                                                                <span class="text-[10px] text-slate-400 shrink-0">
                                                                                    ({{ number_format($att->file_size / 1024, 0) }} KB)
                                                                                </span>
                                                                            </div>
                                                                            <div class="flex items-center gap-1.5 shrink-0">
                                                                                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank"
                                                                                   class="text-blue-600 hover:text-blue-800 font-semibold text-[11px] hover:underline" title="Unduh Berkas">
                                                                                    Unduh
                                                                                </a>
                                                                                @if(!$isDikirim && !$isSelesai)
                                                                                    <form action="{{ route('opd.lhr.tindak-lanjut.hapus-lampiran', [$lhr->uuid, $tl->id, $att->id]) }}" method="POST" onsubmit="return confirm('Hapus berkas lampiran ini?')">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-[11px] cursor-pointer" title="Hapus Berkas">
                                                                                            Hapus
                                                                                        </button>
                                                                                    </form>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif

                                                        {{-- Tombol Final Kirim ke Inspektorat (Jika belum dikirim / ditolak) --}}
                                                        @if(!$isDikirim && !$isSelesai)
                                                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                                                                <form action="{{ route('opd.lhr.tindak-lanjut.kirim', [$lhr->uuid, $tl->id]) }}" method="POST" onsubmit="return confirm('Kirim tindak lanjut ini ke Inspektorat? Form akan terkunci setelah dikirim.')">
                                                                    @csrf
                                                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 shadow-xs transition cursor-pointer">
                                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                                        <span>Kirim Tindak Lanjut ke Inspektorat</span>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg bg-slate-50 p-3 text-center text-xs text-slate-400 dark:bg-slate-800/40">
                        Tidak ada catatan ketidaksesuaian pada butir pengujian ini (Telah Sesuai).
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500 dark:border-slate-800 dark:bg-slate-900">
                Belum ada butir pengujian yang tercatat.
            </div>
        @endforelse
    </div>

    {{-- KOTAK INFORMASI TINDAK LANJUT DOKUMEN --}}
    <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-5 dark:border-blue-900/50 dark:bg-blue-950/30 flex items-start gap-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-xs">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-200">
                Petunjuk Pelaksanaan Tindak Lanjut Dokumen Reviu
            </h4>
            <p class="text-xs text-blue-800/80 dark:text-blue-300/80 mt-1 leading-relaxed">
                Perangkat Daerah diharapkan menindaklanjuti rekomendasi di atas dengan melakukan penyesuaian pada draf akhir dokumen perencanaan terkait. Berkas dokumen yang telah direvisi serta surat tanggapan perbaikan dapat disampaikan ke Inspektorat Daerah Kabupaten Rembang untuk penyusunan Laporan Hasil Tindak Lanjut (LHTL) dan penandatanganan Berita Acara.
            </p>
        </div>
    </div>

</div>
@endsection
