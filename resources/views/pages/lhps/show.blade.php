@extends('layouts.app')

@section('content')
@php
    $totalTemuan   = $lhp->temuans->count();
    $totalKerugian = $lhp->temuans->sum('nilai_temuan');
    $temuanSelesai = $lhp->temuans->where('status_tl', 'selesai')->count();
    $stat          = $lhp->statistik;
    $persenSelesai = $stat?->persen_selesai_gabungan ?? 0;
    $rekomSelesai  = $stat?->rekom_selesai ?? 0;
    $totalRekom    = $stat?->total_rekomendasi ?? 0;

    $statusConfig = match($lhp->status) {
        'draft'          => ['label' => 'Draft',          'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],
        'final'          => ['label' => 'Final',          'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
        'ditandatangani' => ['label' => 'Ditandatangani', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'],
        'batal'          => ['label' => 'Dibatalkan',     'class' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'],
        default          => ['label' => ucfirst($lhp->status), 'class' => 'bg-gray-100 text-gray-600'],
    };

    $progressColor = match(true) {
        $persenSelesai >= 100 => 'bg-green-500',
        $persenSelesai > 50   => 'bg-yellow-400',
        default               => 'bg-red-400',
    };
@endphp

<div class="mx-auto max-w-6xl space-y-6 pb-10">

    {{-- ── HEADER ── --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                <a href="{{ route('lhps.index') }}" class="hover:text-gray-600 dark:hover:text-gray-300">LHP</a>
                <span>/</span>
                <span>Detail</span>
            </nav>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    {{ $lhp->nomor_lhp }}
                </h1>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusConfig['class'] }}">
                    {{ $statusConfig['label'] }}
                </span>
            </div>

        </div>

        <div class="flex flex-shrink-0 flex-wrap items-center gap-2">
            <a href="{{ route('lhps.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700/60">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
            @if ($lhp->status !== 'batal')
            <a href="{{ route('lhps.edit', $lhp) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700/60">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            @endif
            <form action="{{ route('lhps.refresh', $lhp) }}" method="POST" class="contents">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700/60">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="23 4 23 10 17 10"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    Refresh
                </button>
            </form>
            <form action="{{ route('lhps.destroy', $lhp) }}" method="POST" class="contents"
                  onsubmit="return confirm('Yakin ingin menghapus LHP ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-100 bg-white px-3.5 py-2 text-sm font-medium text-red-500 hover:bg-red-50 dark:border-red-900/40 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    {{-- ── FLASH ── --}}
    @if (session('success'))
    <div class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
        <svg class="h-4 w-4 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── STAT CARDS ── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Temuan</p>
            <p class="mt-2 text-4xl font-semibold text-gray-900 dark:text-white">{{ $totalTemuan }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Selesai TL</p>
            <p class="mt-2 text-4xl font-semibold text-green-600 dark:text-green-400">{{ $temuanSelesai }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Kerugian</p>
            <p class="mt-2 text-xl font-semibold leading-tight text-red-600 dark:text-red-400">
                Rp {{ number_format($totalKerugian, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Progress TL</p>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                <div class="h-full rounded-full transition-all {{ $progressColor }}"
                     style="width: {{ min(100, $persenSelesai) }}%"></div>
            </div>
            <div class="mt-2.5 flex items-center justify-between">
                @if ($stat)
                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $rekomSelesai }}/{{ $totalRekom }} rekomendasi</span>
                @else
                <span class="text-xs text-gray-400">-</span>
                @endif
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($persenSelesai, 1) }}%</span>
            </div>
        </div>

    </div>

    {{-- ── MAIN GRID: Sidebar + Konten ── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ── SIDEBAR KIRI ── --}}
       {{-- Informasi Penugasan & Unit --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi LHP</h2>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-700">

        {{-- Nomor & Tanggal --}}
        <div class="grid grid-cols-2 gap-4 px-6 py-4">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Nomor LHP</p>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $lhp->nomor_lhp }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal LHP</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ \Carbon\Carbon::parse($lhp->tanggal_lhp)->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>

        {{-- Program Kerja --}}
        <div class="px-6 py-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Program Kerja (PKPT)</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                {{ $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->nama_program ?? '-' }}
            </p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                {{ $lhp->auditAssignment?->auditProgramDetail?->nama_detail_program ?? '' }}
            </p>
        </div>

        {{-- Unit Kerja --}}
        <div class="px-6 py-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Unit Kerja / Objek Audit</p>
            @if ($lhp->unitDiperiksa)
                <div class="mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        {{ $lhp->unitDiperiksa->kategori ?? 'Unit' }}
                    </span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $lhp->unitDiperiksa->nama_unit }}
                    </span>
                </div>
            @else
                <p class="mt-1 text-sm text-gray-400 italic">Unit tidak tersedia</p>
            @endif
        </div>

        {{-- Tim & Personil (Berdasarkan Kolom String di DB) --}}
        <div class="px-6 py-4 bg-gray-50/30 dark:bg-white/[0.01]">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Kelompok Tim</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $lhp->auditAssignment?->auditProgramDetail?->tim ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Jumlah/Keterangan Personil</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $lhp->auditAssignment?->auditProgramDetail?->personil ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Penugasan --}}
        <div class="px-6 py-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Nomor Surat Penugasan</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                {{ $lhp->auditAssignment?->nomor_surat ?? '-' }}
            </p>
        </div>

    </div>
</div>

        {{-- ── KOLOM KANAN: TEMUAN ── --}}
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">

                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Temuan</h2>
                        <span class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-gray-100 px-1.5 text-xs font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            {{ $totalTemuan }}
                        </span>
                    </div>
                    <a href="{{ route('temuan.create', ['lhp_id' => $lhp->id]) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Temuan
                    </a>
                </div>

                @forelse ($lhp->temuans as $i => $t)
                @php
                    $tlBadge = match($t->status_tl) {
                        'selesai'      => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                        'dalam_proses' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
                        default        => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
                    };
                @endphp

                <div class="border-b border-gray-100 p-6 last:border-b-0 dark:border-gray-700/60">

                    {{-- Baris meta --}}
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-gray-300 dark:text-gray-600">#{{ $i + 1 }}</span>
                        @if ($t->kodeTemuan)
                        <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            {{ $t->kodeTemuan->kode }}
                        </span>
                        @endif
                        <span class="rounded-md px-2 py-0.5 text-[11px] font-bold uppercase {{ $tlBadge }}">
                            {{ str_replace('_', ' ', $t->status_tl ?? 'Belum TL') }}
                        </span>
                        <div class="ml-auto flex items-center gap-4">
                            <a href="{{ route('temuan.edit', $t->id) }}"
                               class="text-xs font-medium text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                Edit
                            </a>
                            <form action="{{ route('temuan.destroy', $t->id) }}" method="POST" class="contents"
                                  onsubmit="return confirm('Hapus temuan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-xs font-medium text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Kondisi --}}
                    <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300">
                        {!! $t->kondisi ?? '<span class="text-gray-400">-</span>' !!}
                    </div>

                    {{-- Nilai --}}
                    @if ($t->nilai_temuan > 0)
                    <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-gray-50 px-4 py-2.5 dark:bg-gray-700/40">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total nilai temuan</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($t->nilai_temuan, 0, ',', '.') }}
                        </span>
                    </div>
                    @endif

                    {{-- Rekomendasi --}}
                    <div class="mt-5">
                        <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            Rekomendasi
                            @if ($t->recommendations->count())
                            <span class="ml-1 text-gray-300 dark:text-gray-600">({{ $t->recommendations->count() }})</span>
                            @endif
                        </p>
                        @forelse ($t->recommendations as $r)
                        @php
                            $rColor = match($r->status) {
                                'selesai'               => 'text-green-600 dark:text-green-400',
                                'proses', 'dalam_proses' => 'text-amber-600 dark:text-amber-400',
                                default                 => 'text-gray-400 dark:text-gray-500',
                            };
                        @endphp
                        <div class="mb-2 flex items-start justify-between gap-4 rounded-lg border border-gray-100 bg-gray-50/70 px-4 py-3 dark:border-gray-700 dark:bg-gray-700/30">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs leading-relaxed text-gray-700 dark:text-gray-300">
                                    {{ $r->uraian_rekom ?? '-' }}
                                </p>
                                <div class="mt-1.5 flex items-center gap-2">
                                    <span class="text-[11px] font-bold uppercase {{ $rColor }}">
                                        {{ str_replace('_', ' ', $r->status ?? 'Belum TL') }}
                                    </span>
                                    @if ($r->isUang() && $r->nilai_rekom > 0)
                                    <span class="text-gray-300 dark:text-gray-600">·</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                        Rp {{ number_format($r->nilai_tl_selesai, 0, ',', '.') }}
                                        / {{ number_format($r->nilai_rekom, 0, ',', '.') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('recommendations.show', $r->id) }}"
                               class="flex-shrink-0 text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                Detail →
                            </a>
                        </div>
                        @empty
                        <p class="text-xs italic text-gray-400 dark:text-gray-500">Belum ada rekomendasi.</p>
                        @endforelse
                    </div>

                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-16">
                    <svg class="mb-3 h-10 w-10 text-gray-200 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada temuan terdaftar.</p>
                </div>
                @endforelse

            </div>
        </div>

    </div>
</div>
@endsection