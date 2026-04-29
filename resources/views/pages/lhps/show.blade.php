@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('lhps.index') }}" class="hover:text-primary-600">LHP</a>
                <span>/</span>
                <span class="text-gray-700 dark:text-white">Detail</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $lhp->nomor_lhp }}</h1>
                @php
                    $statusConfig = match($lhp->status) {
                        'draft'          => ['label' => 'Draft',          'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                        'final'          => ['label' => 'Final',          'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
                        'ditandatangani' => ['label' => 'Ditandatangani', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'],
                        'batal'          => ['label' => 'Dibatalkan',     'class' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'],
                        default          => ['label' => ucfirst($lhp->status), 'class' => 'bg-gray-100 text-gray-600'],
                    };
                @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusConfig['class'] }}">
                    {{ $statusConfig['label'] }}
                </span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('lhps.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
            @if ($lhp->status !== 'batal')
            <a href="{{ route('lhps.edit', $lhp) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            @endif

            {{-- Refresh Statistik --}}
            <form action="{{ route('lhps.refresh', $lhp) }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    title="Hitung ulang statistik">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    Refresh
                </button>
            </form>

            <form action="{{ route('lhps.destroy', $lhp) }}" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus LHP ini? Tindakan tidak dapat dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3.5 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    {{-- Flash Alert --}}
    @if (session('success'))
    <div class="mb-5 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-800 dark:bg-green-900/20">
        <svg class="h-5 w-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <p class="text-sm text-green-800 dark:text-green-300">{{ session('success') }}</p>
    </div>
    @endif

    {{-- ── STATISTIK CARDS ── --}}
  @php
    // 1. Ambil data riil langsung dari database (bukan cache statistik)
    $totalTemuan   = $lhp->temuans->count();
    $totalKerugian = $lhp->temuans->sum('nilai_temuan'); 
    
    // 2. Ambil progres dari statistik (karena hitungannya kompleks melibatkan TL)
    $stat          = $lhp->statistik;
    $persenSelesai = $stat?->persen_selesai_gabungan ?? 0;
    $rekomSelesai  = $stat?->rekom_selesai  ?? 0;
    $totalRekom    = $stat?->total_rekomendasi ?? 0;

    // 3. Hitung temuan selesai secara dinamis
    $temuanSelesai = $lhp->temuans->where('status_tl', 'selesai')->count();
@endphp

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Temuan</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalTemuan }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Temuan Selesai TL</p>
            <p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $temuanSelesai }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Kerugian</p>
            <p class="mt-1 text-lg font-semibold text-red-600 dark:text-red-400">
                Rp {{ number_format($totalKerugian, 0, ',', '.') }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="mb-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">Progress Tindak Lanjut</p>
            <div class="flex items-center gap-2">
                <div class="flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700" style="height:6px">
                    <div class="h-full rounded-full transition-all
                        {{ $persenSelesai >= 100 ? 'bg-green-500' : ($persenSelesai > 50 ? 'bg-yellow-500' : 'bg-red-400') }}"
                         style="width: {{ min(100, $persenSelesai) }}%"></div>
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($persenSelesai, 1) }}%</span>
            </div>
            @if($stat)
            <p class="mt-1 text-[10px] text-gray-400">{{ $rekomSelesai }}/{{ $totalRekom }} rekomendasi</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ── KOLOM KIRI ── --}}
        <div class="space-y-6 lg:col-span-1">

            {{-- Info Utama --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-3.5 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi LHP</h2>
                </div>
                <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Tanggal LHP</dt>
                        <dd class="text-xs font-medium text-gray-900 dark:text-white text-right">{{ $lhp->tanggal_lhp?->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Semester</dt>
                        <dd class="text-xs font-medium text-gray-900 dark:text-white">Semester {{ $lhp->semester }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">IRBAN</dt>
                        <dd class="text-xs font-medium text-gray-900 dark:text-white text-right">{{ $lhp->irban }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Jenis Pemeriksaan</dt>
                        <dd class="text-xs font-medium text-gray-900 dark:text-white text-right">{{ $lhp->jenis_pemeriksaan ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Program Audit</dt>
                        <dd class="text-xs font-medium text-gray-900 dark:text-white text-right">{{ $lhp->auditAssignment?->auditProgram?->nama_program ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Unit Diperiksa</dt>
                        <dd class="text-xs font-medium text-gray-900 dark:text-white text-right">{{ $lhp->auditAssignment?->unitDiperiksa?->nama_unit ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Dibuat Oleh</dt>
                        <dd class="text-xs font-medium text-gray-900 dark:text-white text-right">{{ $lhp->creator?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Tanggal Dibuat</dt>
                        <dd class="text-xs text-gray-600 dark:text-gray-400">{{ $lhp->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    @if ($stat)
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Statistik Dihitung</dt>
                        <dd class="text-xs text-gray-600 dark:text-gray-400">{{ $stat->dihitung_pada?->format('d M Y H:i') ?? '-' }}</dd>
                    </div>
                    @endif
                    @if ($lhp->status === 'batal')
                    <div class="px-5 py-3">
                        <dt class="mb-1 text-xs text-red-500 dark:text-red-400">Keterangan Pembatalan</dt>
                        <dd class="text-xs text-gray-700 dark:text-gray-300">{{ $lhp->status_batal_info }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Catatan Umum --}}
            @if ($lhp->catatan_umum)
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-3.5 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Catatan Umum</h2>
                </div>
                <div class="px-5 py-4">
                    <p class="whitespace-pre-line text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $lhp->catatan_umum }}</p>
                </div>
            </div>
            @endif

            {{-- Lampiran --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-3.5 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Lampiran</h2>
                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-100 px-1.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            {{ $lhp->attachments->count() }}
                        </span>
                    </div>
                </div>
                @if ($lhp->attachments->count())
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($lhp->attachments as $att)
                    <li class="flex items-center gap-3 px-5 py-3">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-md bg-red-50 dark:bg-red-900/20">
                            <svg class="h-3.5 w-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium text-gray-700 dark:text-gray-300">{{ $att->file_name ?? basename($att->file_path) }}</p>
                            <p class="text-xs text-gray-400">{{ $att->created_at->format('d M Y') }}</p>
                        </div>
                        <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                           class="flex-shrink-0 text-xs text-primary-600 hover:underline dark:text-primary-400">Lihat</a>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="py-6 text-center">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Belum ada lampiran.</p>
                </div>
                @endif
            </div>

        </div>

        {{-- ── KOLOM KANAN: Temuan ── --}}
        {{-- ── KOLOM KANAN: DAFTAR TEMUAN (SIMPLE MODERN) ── --}}
<div class="lg:col-span-2" id="temuan">
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        {{-- Card Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white">Daftar Temuan</h2>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    {{ $lhp->temuans->count() }}
                </span>
            </div>
            <a href="{{ route('temuan.create', ['lhp_id' => $lhp->id]) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700 transition-all">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round"/></svg>
                Tambah Temuan
            </a>
        </div>

        @if ($lhp->temuans->count())
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($lhp->temuans as $i => $t)
            <div class="p-6 transition-colors hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                {{-- Meta Info --}}
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-400">#{{ $i + 1 }}</span>
                        @if ($t->kodeTemuan)
                        <span class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            {{ $t->kodeTemuan->kode }}
                        </span>
                        @endif
                        @php
                            $badge = match($t->status_tl) {
                                'selesai'      => 'text-green-600 bg-green-50 dark:bg-green-900/20',
                                'dalam_proses' => 'text-amber-600 bg-amber-50 dark:bg-amber-900/20',
                                default        => 'text-rose-600 bg-rose-50 dark:bg-rose-900/20',
                            };
                        @endphp
                        <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $badge }}">
                            {{ str_replace('_', ' ', $t->status_tl ?? 'BELUM TL') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('temuan.edit', $t->id) }}" class="text-[11px] font-medium text-gray-400 hover:text-primary-600">Edit</a>
                        <form action="{{ route('temuan.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus temuan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-[11px] font-medium text-gray-400 hover:text-rose-600">Hapus</button>
                        </form>
                    </div>
                </div>

                {{-- Konten Utama --}}
                <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                    {{ $t->kondisi ?? '-' }}
                </p>

                {{-- Info Kerugian (Jika Ada) --}}
                @if ($t->nilai_temuan > 0)
                <div class="mt-4 flex gap-4 border-t border-gray-50 pt-4 dark:border-gray-700/50">
                    <div class="flex flex-col">
                        <span class="text-[10px] text-gray-400 uppercase font-semibold">Total Nilai Temuan</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($t->nilai_temuan, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif

                {{-- Box Rekomendasi --}}
<div class="mt-5 space-y-2">
    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rekomendasi</p>
    <div class="space-y-2">
        @forelse ($t->recommendations as $r)
        <div class="flex items-start justify-between gap-4 rounded-lg border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-700 dark:bg-gray-800/40">
            <div class="min-w-0 flex-1">
                {{-- Uraian --}}
                <p class="text-xs leading-relaxed text-gray-700 dark:text-gray-300">
                    {{ $r->uraian_rekom ?? '-' }}
                </p>
                
                {{-- Info Status & Nilai --}}
                <div class="mt-2 flex items-center gap-3 text-[10px]">
                    @php
                        $statusText = match($r->status) {
                            'selesai' => 'text-green-600',
                            'proses', 'dalam_proses' => 'text-yellow-600',
                            default => 'text-gray-400',
                        };
                    @endphp
                    <span class="font-bold uppercase {{ $statusText }}">
                        {{ str_replace('_', ' ', $r->status ?? 'BELUM TL') }}
                    </span>

                    @if ($r->isUang() && $r->nilai_rekom > 0)
                    <span class="text-gray-400">|</span>
                    <span class="font-medium text-gray-500">
                        Rp {{ number_format($r->nilai_tl_selesai, 0, ',', '.') }} / {{ number_format($r->nilai_rekom, 0, ',', '.') }}
                    </span>
                    @endif
                </div>
            </div>

            <a href="{{ route('recommendations.show', $r->id) }}" 
               class="shrink-0 text-[10px] font-bold text-primary-600 hover:text-primary-700">
                DETAIL
            </a>
        </div>
        @empty
        <p class="text-[11px] italic text-gray-400 px-1">Belum ada rekomendasi.</p>
        @endforelse
    </div>
</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-12">
            <p class="text-sm text-gray-400">Tidak ada temuan terdaftar.</p>
        </div>
        @endif
    </div>
</div>
    </div>
</div>
@endsection