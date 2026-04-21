{{-- resources/views/pages/tindak-lanjuts/cicilans/show.blade.php --}}
@extends('layouts.app')

@section('content')

@php
    $isUang = $tindakLanjut->recommendation?->isUang();
    $statusCls = match($cicilan->status) {
        'diterima'            => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'ditolak'             => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
        'menunggu_verifikasi' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        default               => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
    };
    $hasBreakdown = $isUang &&
        (($cicilan->nilai_bayar_negara ?? 0)
       + ($cicilan->nilai_bayar_daerah ?? 0)
       + ($cicilan->nilai_bayar_desa ?? 0)
       + ($cicilan->nilai_bayar_bos_blud ?? 0)) > 0;
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8 px-4">
<div class="max-w-2xl mx-auto">

    {{-- ── Breadcrumb ── --}}
    <nav class="mb-6 flex items-center gap-1.5 text-xs font-semibold text-slate-400">
        <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Tindak Lanjut</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Cicilan</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600 dark:text-slate-300">Ke-{{ $cicilan->ke }}</span>
    </nav>

    {{-- ── Main Card ── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">

        {{-- Card Header --}}
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-8 h-8 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs font-black">
                        {{ $cicilan->ke }}
                    </div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">
                        Cicilan Ke-{{ $cicilan->ke }}
                    </h2>
                </div>
                <p class="text-xs text-slate-400 font-mono pl-11">
                    {{ $tindakLanjut->recommendation?->temuan?->lhp?->nomor_lhp ?? '-' }}
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex-shrink-0 {{ $statusCls }}">
                {{ $cicilan->label_status }}
            </span>
        </div>

        {{-- ── Nilai Utama (uang) ── --}}
        @if($isUang)
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nilai Bayar</p>
            <p class="text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">
                Rp {{ number_format($cicilan->nilai_bayar, 0, ',', '.') }}
            </p>
        </div>

        {{-- Breakdown (jika ada) --}}
        @if($hasBreakdown)
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Rincian Distribusi</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach([
                    'nilai_bayar_negara'   => 'Negara',
                    'nilai_bayar_daerah'   => 'Daerah',
                    'nilai_bayar_desa'     => 'Desa',
                    'nilai_bayar_bos_blud' => 'BOS / BLUD',
                ] as $col => $label)
                @if(($cicilan->$col ?? 0) > 0)
                <div class="flex justify-between items-center text-sm bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-2.5">
                    <span class="text-slate-500 font-medium">{{ $label }}</span>
                    <span class="font-bold font-mono text-slate-800 dark:text-slate-200">
                        Rp {{ number_format($cicilan->$col, 0, ',', '.') }}
                    </span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif
        @endif

        {{-- ── Detail Rows ── --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-800">

            @php
            $rows = [
                'Tanggal Bayar'        => $cicilan->tanggal_bayar?->translatedFormat('d F Y'),
                'Tanggal Jatuh Tempo'  => $cicilan->tanggal_jatuh_tempo_cicilan?->translatedFormat('d F Y') ?? '—',
                'Nomor Bukti'          => $cicilan->nomor_bukti ?? '—',
                'Metode / Jenis Bayar' => $cicilan->jenis_bayar ?? '—',
            ];
            @endphp

            @foreach($rows as $label => $value)
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <span class="text-sm text-slate-500 font-medium flex-shrink-0">{{ $label }}</span>
                <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 text-right">{{ $value }}</span>
            </div>
            @endforeach

            @if($cicilan->keterangan)
            <div class="px-6 py-4">
                <p class="text-sm text-slate-500 font-medium mb-2">Keterangan</p>
                <p class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 leading-relaxed">
                    {{ $cicilan->keterangan }}
                </p>
            </div>
            @endif

        </div>

        {{-- ── Verifikasi Section ── --}}
        <div class="mx-6 mb-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 p-4">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Informasi Verifikasi</p>
            <div class="space-y-2.5">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500">Verifikator</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $cicilan->diverifikator?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500">Tanggal Verifikasi</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ $cicilan->diverifikasi_pada?->translatedFormat('d F Y, H:i') ?? '—' }}
                    </span>
                </div>
                @if($cicilan->catatan_verifikasi)
                <div class="text-sm pt-1">
                    <p class="text-slate-500 mb-1.5">Catatan Verifikasi</p>
                    <p class="text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 leading-relaxed">
                        {{ $cicilan->catatan_verifikasi }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Audit trail ── --}}
        <div class="px-6 pb-4 flex justify-between text-[11px] text-slate-400 font-mono">
            <span>Dibuat: {{ $cicilan->creator?->name ?? '—' }} · {{ $cicilan->created_at?->format('d M Y, H:i') }}</span>
            @if($cicilan->updated_at?->ne($cicilan->created_at))
            <span>Edit: {{ $cicilan->updated_at?->format('d M Y, H:i') }}</span>
            @endif
        </div>

        {{-- ── Actions ── --}}
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut) }}"
               class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>

            @if($cicilan->status === 'menunggu_verifikasi')
            <div class="flex gap-2">
                {{-- Terima --}}
                <form action="{{ route('tindak-lanjuts.cicilans.verifikasi', [$tindakLanjut, $cicilan]) }}"
                      method="POST" class="inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="diterima">
                    <button type="submit"
                            onclick="return confirm('Terima cicilan ini?')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Terima
                    </button>
                </form>

                {{-- Tolak --}}
                <form action="{{ route('tindak-lanjuts.cicilans.verifikasi', [$tindakLanjut, $cicilan]) }}"
                      method="POST" class="inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="ditolak">
                    <button type="submit"
                            onclick="return confirm('Tolak cicilan ini?')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tolak
                    </button>
                </form>

                {{-- Edit --}}
                <a href="{{ route('tindak-lanjuts.cicilans.edit', [$tindakLanjut, $cicilan]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L21 3.586a2 2 0 010 2.828l-7 7a2 2 0 01-.883.515l-3 1a1 1 0 01-1.265-1.265l1-3a1 1 0 01.515-.883l7-7z"/>
                    </svg>
                    Edit
                </a>
            </div>
            @endif
        </div>

    </div>{{-- /card --}}

</div>
</div>
@endsection