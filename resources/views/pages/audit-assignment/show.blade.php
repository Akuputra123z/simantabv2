{{-- resources/views/pages/audit-assignment/show.blade.php --}}
@extends('layouts.app')

@section('content')
@php
    $canSign = auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('kepala_inspektorat');
    $progKategori = strtoupper($data->auditProgramDetail?->auditProgram?->kategori ?? 'PKPT');
    $isBpkBpkp = in_array($progKategori, ['BPK', 'BPKP']);
    $program = $data->auditProgramDetail?->auditProgram;
    $detail = $data->auditProgramDetail;
    $units = $data->unitDiperiksas->sortBy('nama_unit')->values();
    $lhps = $data->lhps ?? collect();
    $durasiHari = ($data->tanggal_mulai && $data->tanggal_selesai) 
        ? $data->tanggal_mulai->diffInDays($data->tanggal_selesai) + 1 
        : null;

    $statusBadge = match(strtolower($data->status ?? '')) {
        'selesai'  => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300',
        'berjalan' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:border-blue-800 dark:text-blue-300',
        default    => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300',
    };
@endphp

<div class="w-full space-y-6" x-data="{ 
    searchUnit: '',
    units: @js($units->map(fn($u) => ['id' => $u->id, 'nama' => $u->nama_unit, 'kecamatan' => $u->nama_kecamatan ?? 'Kab. Rembang'])),
    get filteredUnits() {
        if (!this.searchUnit.trim()) return this.units;
        const q = this.searchUnit.toLowerCase();
        return this.units.filter(u => u.nama.toLowerCase().includes(q) || u.kecamatan.toLowerCase().includes(q));
    }
}">

    {{-- ═════════════════════════════════════════════════════════════ --}}
    {{-- ── 1. PAGE BREADCRUMB & EXECUTIVE TOOLBAR ── --}}
    {{-- ═════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('audit-assignment.index') }}" class="font-medium hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    Penugasan Audit
                </a>
                <span>/</span>
                <span class="rounded bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 font-mono text-[11px] text-gray-700 dark:text-gray-300">
                    {{ $progKategori }}
                </span>
                <span>/</span>
                <span class="text-gray-900 dark:text-white font-semibold truncate max-w-[200px]">
                    {{ $data->nomor_surat }}
                </span>
            </nav>

            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Surat Perintah Tugas (SPT)
                </h1>

                {{-- Status Penugasan --}}
                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-0.5 text-xs font-bold capitalize shadow-xs {{ $statusBadge }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ strtolower($data->status ?? '') === 'selesai' ? 'bg-emerald-500' : (strtolower($data->status ?? '') === 'berjalan' ? 'bg-blue-500 animate-pulse' : 'bg-gray-400') }}"></span>
                    {{ $data->status ?: 'Draft' }}
                </span>

                {{-- Status Tanda Tangan --}}
                @if($data->isSigned())
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-0.5 text-xs font-bold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Ditandatangani Digital
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                        Belum Ditandatangani
                    </span>
                @endif
            </div>

            <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-mono">
                Nomor Surat: <strong class="text-blue-600 dark:text-blue-400 font-semibold">{{ $data->nomor_surat }}</strong>
            </p>
        </div>

        {{-- Toolbar Actions --}}
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('audit-assignment.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Kembali
            </a>

            {{-- Tombol Tanda Tangani (Bagi Pejabat) --}}
            @if($canSign && !$data->isSigned())
                <form action="{{ route('audit-assignment.sign', $data) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Tanda Tangani SPT
                    </button>
                </form>
            @endif

            <a href="{{ route('audit-assignment.edit', $data) }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50/70 px-4 py-2 text-xs font-bold text-blue-700 shadow-xs hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Edit Penugasan
            </a>

            {{-- Dropdown Pratinjau & Cetak Naskah SPT --}}
            <div class="relative" x-data="{ openPrint: false }">
                <button type="button" @click="openPrint = !openPrint" @click.outside="openPrint = false"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition active:scale-95 cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Naskah SPT
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="openPrint" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     class="absolute right-0 z-20 mt-1.5 w-48 rounded-xl border border-gray-100 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900">
                    <a href="{{ route('audit-assignment.preview', $data) }}" target="_blank"
                       class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Pratinjau Layar</span>
                    </a>
                    <a href="{{ route('audit-assignment.print', $data) }}" target="_blank"
                       class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Unduh PDF SPT</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════ --}}
    {{-- ── 2. EXECUTIVE METRIC SUMMARY RIBBON ── --}}
    {{-- ═════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Card 1: Unit Kerja Diperiksa --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Objek Audit</span>
                <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">Unit</span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $units->count() }}</span>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Unit Kerja Terdaftar</span>
            </div>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 truncate">
                Objek sasaran pemeriksaan penugasan ini
            </p>
        </div>

        {{-- Card 2: Durasi & Jadwal --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Jadwal Pemeriksaan</span>
                <span class="rounded-lg bg-purple-50 px-2 py-0.5 text-[10px] font-bold text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">Periode</span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $durasiHari ? $durasiHari . ' Hari' : '-' }}</span>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Kerja</span>
            </div>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 truncate">
                {{ optional($data->tanggal_mulai)->format('d M') }} s.d. {{ optional($data->tanggal_selesai)->format('d M Y') }}
            </p>
        </div>

        {{-- Card 3: Dokumen LHP Terbit --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">LHP Terbit</span>
                <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">Output</span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $lhps->count() }}</span>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Dokumen LHP</span>
            </div>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 truncate">
                Dari {{ $units->count() }} unit kerja objek audit
            </p>
        </div>

        {{-- Card 4: Personil Tim Audit --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Kekuatan Tim</span>
                <span class="rounded-lg bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">Personil</span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white">
                    {{ $isBpkBpkp ? '1' : ($data->members->count() + ($data->ketuaTim ? 1 : 0)) }}
                </span>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Orang Auditor</span>
            </div>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 truncate">
                {{ $data->ketuaTim ? 'Ketua: ' . $data->ketuaTim->name : 'Ketua tim belum ditentukan' }}
            </p>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════ --}}
    {{-- ── 3. TWO-COLUMN ASYMMETRIC MAIN WORKSPACE ── --}}
    {{-- ═════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">

        {{-- LEFT COLUMN: DETAIL PENUGASAN, TIM & OBJEK AUDIT (COL 8) --}}
        <div class="space-y-6 lg:col-span-8">

            {{-- Card 1: Identitas & Ruang Lingkup Penugasan --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 text-xs font-bold">📋</span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                            Identitas &amp; Ruang Lingkup Penugasan
                        </h2>
                    </div>
                    <span class="rounded bg-gray-100 px-2 py-0.5 font-mono text-[11px] text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        ID #{{ $data->id }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-2 text-xs">
                    <div>
                        <span class="text-gray-400">Program Kerja Induk:</span>
                        <p class="mt-0.5 text-sm font-bold text-gray-900 dark:text-white">
                            {{ $program?->nama_program ?? '-' }}
                        </p>
                        <p class="text-[11px] text-gray-500">Kategori: {{ $progKategori }} (TA {{ $program?->tahun ?? date('Y') }})</p>
                    </div>

                    <div>
                        <span class="text-gray-400">Rincian Kegiatan (PKPT):</span>
                        <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $detail?->nama_detail_program ?? '-' }}
                        </p>
                        <p class="text-[11px] text-gray-500">Kegiatan: {{ $detail?->jenis_kegiatan ?? 'Audit Kepatuhan' }}</p>
                    </div>

                    <div>
                        <span class="text-gray-400">Tim Pengawasan:</span>
                        <p class="mt-0.5 font-semibold text-gray-800 dark:text-gray-200">
                            {{ $detail?->tim ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-gray-400">Tingkat Risiko:</span>
                        <div class="mt-1">
                            @php
                                $risikoClass = match(strtolower($detail?->tingkat_resiko ?? '')) {
                                    'tinggi' => 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300',
                                    'sedang' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
                                    'rendah' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
                                    default  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 font-bold uppercase text-[10px] {{ $risikoClass }}">
                                {{ $detail?->tingkat_resiko ?? 'Normal' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi Ruang Lingkup & Tujuan --}}
                @if($detail?->ruang_lingkup || $detail?->tujuan)
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-3">
                        @if($detail->ruang_lingkup)
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Ruang Lingkup Pemeriksaan:</span>
                                <p class="mt-1 text-xs text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                                    {{ $detail->ruang_lingkup }}
                                </p>
                            </div>
                        @endif
                        @if($detail->tujuan)
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Tujuan Audit:</span>
                                <p class="mt-1 text-xs text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                                    {{ $detail->tujuan }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Card 2: Struktur Personil Tim Audit --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 text-xs font-bold">👥</span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                            {{ $isBpkBpkp ? 'Penanggung Jawab Tindak Lanjut' : 'Struktur Tim Pemeriksa' }}
                        </h2>
                    </div>
                    <span class="text-xs font-semibold text-gray-500">
                        {{ $isBpkBpkp ? '1 Penanggung Jawab' : ($data->members->count() + ($data->ketuaTim ? 1 : 0)) . ' Personil' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Ketua Tim / Penanggung Jawab --}}
                    <div class="rounded-xl border border-blue-200/80 bg-blue-50/30 p-4 dark:border-blue-900/50 dark:bg-blue-950/20 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">
                            {{ $isBpkBpkp ? 'Penanggung Jawab Utama' : 'Ketua Tim Pemeriksa' }}
                        </span>
                        @if($data->ketuaTim)
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white font-bold text-sm shadow-sm">
                                    {{ strtoupper(substr($data->ketuaTim->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                        {{ $data->ketuaTim->name }}
                                    </p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                        {{ $data->ketuaTim->email ?? 'Ketua Tim' }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Belum ditentukan</p>
                        @endif
                    </div>

                    {{-- Pengendali Teknis (Dalnis) --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Pengendali Teknis (Dalnis)
                        </span>
                        @if($data->pengendaliTeknis)
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-700 text-white font-bold text-sm">
                                    {{ strtoupper(substr($data->pengendaliTeknis->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                        {{ $data->pengendaliTeknis->name }}
                                    </p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                        Pengendali Teknis
                                    </p>
                                </div>
                            </div>
                        @elseif($data->pengendali_teknis)
                            <p class="text-xs font-bold text-gray-900 dark:text-white pt-2">{{ $data->pengendali_teknis }}</p>
                        @else
                            <p class="text-xs text-gray-400 italic pt-2">Belum ditentukan</p>
                        @endif
                    </div>
                </div>

                {{-- Anggota Tim Pemeriksa --}}
                @if(!$isBpkBpkp)
                    <div class="space-y-3 pt-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Anggota Tim ({{ $data->members->count() }})
                        </span>

                        @if($data->members->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach($data->members as $member)
                                    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-800/60 shadow-2xs">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                                {{ $member->name }}
                                            </p>
                                            <p class="text-[10px] text-gray-400 uppercase tracking-tight">
                                                {{ $member->pivot->jabatan_tim ?? 'Anggota Pemeriksa' }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-4 text-center dark:border-gray-800 dark:bg-gray-800/30">
                                <p class="text-xs text-gray-400 italic">Tidak ada anggota tim yang terdaftar.</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Card 3: Daftar Unit Kerja / Objek Audit (Live Reaktif Alpine) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 text-xs font-bold">🏢</span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                Objek Pemeriksaan / Unit Kerja
                            </h2>
                            <p class="text-[11px] text-gray-500">
                                Total <strong x-text="units.length"></strong> unit kerja terdaftar pada penugasan ini
                            </p>
                        </div>
                    </div>

                    {{-- Search Input Reaktif --}}
                    <div class="relative w-full sm:w-64">
                        <input type="text" x-model="searchUnit" placeholder="Cari nama unit..."
                               class="w-full rounded-xl border border-gray-200 bg-gray-50/70 py-1.5 pl-8 pr-3 text-xs text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        <svg class="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Grid List Unit Kerja --}}
                <div class="max-h-80 overflow-y-auto space-y-2 pr-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                        <template x-for="u in filteredUnits" :key="u.id">
                            <div class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-blue-200 hover:shadow-2xs dark:border-gray-800 dark:bg-gray-800/40 dark:hover:bg-gray-800 transition">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 text-xs font-bold">
                                    🏢
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="u.nama"></p>
                                    <p class="text-[10px] text-gray-400 truncate" x-text="u.kecamatan"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <template x-if="filteredUnits.length === 0">
                        <div class="py-8 text-center">
                            <p class="text-xs text-gray-400 italic">Tidak ada unit kerja yang sesuai dengan kata kunci pencarian.</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Card 4: Dokumen LHP Terbit dari Penugasan Ini --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 text-xs font-bold">📑</span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                Dokumen LHP Terbit
                            </h2>
                            <p class="text-[11px] text-gray-500">
                                Laporan Hasil Pemeriksaan yang dibuat dari penugasan ini
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('lhps.create', ['assignment_id' => $data->id]) }}"
                       class="inline-flex items-center gap-1 rounded-xl bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition">
                        + Terbitkan LHP Baru
                    </a>
                </div>

                @if($lhps->isNotEmpty())
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($lhps as $lhp)
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('lhps.show', $lhp) }}" class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $lhp->nomor_lhp }}
                                        </a>
                                        @if($lhp->is_nihil)
                                            <span class="rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 px-2 py-0.2 text-[10px] font-bold">Nihil</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-0.5">
                                        Unit: <strong class="text-gray-900 dark:text-white">{{ $lhp->unitDiperiksa?->nama_unit ?? ($lhp->unitDiperiksas->first()?->nama_unit ?? 'Unit Kerja') }}</strong>
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] text-gray-400">
                                        {{ optional($lhp->tanggal_lhp)->translatedFormat('d M Y') ?? '-' }}
                                    </span>
                                    <a href="{{ route('lhps.show', $lhp) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition">
                                        Buka LHP →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-6 text-center dark:border-gray-800 dark:bg-gray-800/30">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Belum ada LHP yang diterbitkan dari penugasan audit ini.</p>
                        <a href="{{ route('lhps.create', ['assignment_id' => $data->id]) }}" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline">
                            + Buat LHP Pertama dari Penugasan Ini
                        </a>
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: SIDEBAR INFORMASI, PENGESAHAN, DASAR HUKUM (COL 4) --}}
        <aside class="space-y-6 lg:col-span-4">

            {{-- 1. Status Pengesahan Digital SPT --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Pengesahan Surat Tugas
                    </h3>
                    @if($data->isSigned())
                        <span class="rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 px-2 py-0.5 text-[10px] font-bold">
                            Valid / Sah
                        </span>
                    @else
                        <span class="rounded bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 px-2 py-0.5 text-[10px] font-bold">
                            Draft Naskah
                        </span>
                    @endif
                </div>

                @if($data->isSigned())
                    <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/60 dark:bg-emerald-950/30 space-y-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-white text-[10px] font-bold">✓</span>
                            <span class="font-bold text-emerald-900 dark:text-emerald-200">Ditandatangani Secara Elektronik</span>
                        </div>
                        <p class="text-[11px] text-emerald-800 dark:text-emerald-300">
                            Oleh: <strong class="text-emerald-950 dark:text-white">{{ $data->signer->name ?? 'Kepala Inspektorat' }}</strong>
                        </p>
                        <p class="text-[10px] text-emerald-700/80 dark:text-emerald-400">
                            Waktu: {{ $data->approved_at?->translatedFormat('d F Y, H:i') }} WIB
                        </p>

                        @if($canSign)
                            <div class="pt-2 border-t border-emerald-200/60 dark:border-emerald-900/60">
                                <form action="{{ route('audit-assignment.batal-sign', $data) }}" method="POST"
                                      onsubmit="return confirm('Apakah Anda yakin ingin membatalkan tanda tangan digital pada Surat Tugas ini?')">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-bold text-amber-600 hover:text-amber-700 underline cursor-pointer">
                                        Batalkan Tanda Tangan
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-3.5 rounded-xl border border-dashed border-amber-200 bg-amber-50/50 dark:border-amber-900/50 dark:bg-amber-950/20 text-xs text-amber-800 dark:text-amber-300 space-y-1.5">
                        <p class="font-bold">Surat Tugas Belum Disahkan</p>
                        <p class="text-[11px]">Naskah belum ditandatangani oleh Kepala Inspektorat atau Super Admin.</p>
                        @if($canSign)
                            <div class="pt-2">
                                <form action="{{ route('audit-assignment.sign', $data) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full rounded-lg bg-emerald-600 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition cursor-pointer">
                                        Tanda Tangani Sekarang
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- 2. Card Dasar Hukum Penugasan (SPT) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Dasar Hukum Penugasan (SPT)
                    </h3>
                    <span class="rounded bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 px-2 py-0.5 text-[10px] font-bold">
                        {{ $data->effective_dasar_peraturans->count() }} Aturan
                    </span>
                </div>

                <div class="max-h-56 overflow-y-auto space-y-2 pr-1">
                    @forelse($data->effective_dasar_peraturans as $dp)
                        <div class="p-2.5 rounded-xl border border-gray-100 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40 text-xs leading-relaxed">
                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $dp->nama }}
                            </p>
                            @if($dp->nomor)
                                <p class="mt-0.5 font-mono text-[10px] text-gray-400">
                                    Nomor: {{ $dp->nomor }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">Tidak ada dasar hukum yang dipilih.</p>
                    @endforelse
                </div>
            </div>

            {{-- 3. Card Anggaran Penugasan --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Alokasi Anggaran
                </h3>
                <dl class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                        <dt class="text-gray-400">Pagu Program</dt>
                        <dd class="font-semibold text-gray-800 dark:text-gray-200">
                            Rp {{ number_format($detail?->anggaran ?? 0, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Anggaran Disetujui</dt>
                        <dd class="font-bold text-blue-600 dark:text-blue-400 text-sm">
                            Rp {{ number_format($data->anggaran_disetujui ?? 0, 0, ',', '.') }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- 4. Card Berkas Lampiran SPT --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Berkas Lampiran
                    </h3>
                    <span class="text-xs font-semibold text-gray-400">
                        {{ $data->attachments->count() }} Berkas
                    </span>
                </div>

                @if($data->attachments->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($data->attachments as $att)
                            @php
                                $ext = strtoupper(pathinfo($att->file_name, PATHINFO_EXTENSION) ?: 'FILE');
                            @endphp
                            <div class="flex items-center justify-between gap-2.5 p-2.5 rounded-xl border border-gray-100 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 text-[9px] font-bold">
                                        {{ $ext }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 dark:text-white truncate">
                                            {{ $att->file_name }}
                                        </p>
                                        <p class="text-[10px] text-gray-400">
                                            {{ number_format($att->file_size / 1024, 1) }} KB
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-600 dark:bg-gray-700 dark:text-gray-300 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic">Tidak ada berkas lampiran pendukung.</p>
                @endif
            </div>

            {{-- 5. Delete Action Zone --}}
            <div class="pt-2">
                <form action="{{ route('audit-assignment.destroy', $data) }}" method="POST"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data Penugasan Audit ini? Seluruh relasi terkait akan ikut terhapus.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50/60 py-2.5 text-xs font-bold text-red-600 hover:bg-red-100 hover:border-red-300 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400 transition cursor-pointer">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus Penugasan Ini
                    </button>
                </form>
            </div>

        </aside>

    </div>

</div>
@endsection