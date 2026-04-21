{{-- resources/views/pages/tindak-lanjuts/cicilans/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8 px-4">
<div class="max-w-5xl mx-auto">

    {{-- ── Breadcrumb ── --}}
    <nav class="mb-6 flex items-center gap-1.5 text-xs font-semibold text-slate-400">
        <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Tindak Lanjut</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('tindak-lanjuts.show', $tindakLanjut) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate max-w-xs">
            {{ Str::limit($tindakLanjut->recommendation->uraian_rekom ?? 'Detail', 45) }}
        </a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600 dark:text-slate-300">Cicilan</span>
    </nav>

    {{-- ── Page Header ── --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Daftar Cicilan</h1>
            <p class="text-sm text-slate-500 mt-1">
                LHP:
                <span class="font-semibold text-slate-700 dark:text-slate-300 font-mono">
                    {{ $tindakLanjut->recommendation->temuan->lhp->nomor_lhp ?? '-' }}
                </span>
            </p>
        </div>
        <a href="{{ route('tindak-lanjuts.cicilans.create', $tindakLanjut) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-indigo-100 dark:shadow-none whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Cicilan
        </a>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-semibold rounded-xl">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm font-semibold rounded-xl">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Summary Cards ── --}}
    <div class="mb-5 grid grid-cols-2 {{ $summary['is_uang'] ? 'md:grid-cols-4' : 'md:grid-cols-2' }} gap-3">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Rencana Cicilan</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $summary['total_rencana'] ?? '—' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Realisasi</p>
            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $summary['total_realisasi'] }}</p>
        </div>

        @if($summary['is_uang'])
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Terbayar</p>
            <p class="text-lg font-black text-emerald-600 dark:text-emerald-400">
                Rp {{ number_format($summary['total_terbayar'], 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Sisa</p>
            <p class="text-lg font-black {{ $summary['sisa'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                Rp {{ number_format($summary['sisa'], 0, ',', '.') }}
            </p>
        </div>
        @else
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 col-span-full md:col-span-2">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Jenis Rekomendasi</p>
            <p class="text-base font-bold text-slate-700 dark:text-slate-300">
                {{ ucfirst($tindakLanjut->recommendation->jenis_rekomendasi) }}
                <span class="text-sm font-normal text-slate-400">(non-uang)</span>
            </p>
        </div>
        @endif
    </div>

    {{-- ── Progress Bar (uang) ── --}}
    @if($summary['is_uang'] && ($summary['nilai_rekom'] ?? 0) > 0)
    @php $pct = min(100, round($summary['total_terbayar'] / $summary['nilai_rekom'] * 100, 1)); @endphp
    <div class="mb-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
        <div class="flex justify-between items-center mb-2.5">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Progress Pembayaran</span>
            <span class="text-xs font-black {{ $pct >= 100 ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $pct }}%</span>
        </div>
        <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all {{ $pct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}"
                 style="width: {{ $pct }}%"></div>
        </div>
        <div class="flex justify-between text-[11px] text-slate-400 mt-1.5 font-mono">
            <span>Rp {{ number_format($summary['total_terbayar'], 0, ',', '.') }}</span>
            <span>Rp {{ number_format($summary['nilai_rekom'], 0, ',', '.') }}</span>
        </div>
    </div>
    @endif

    {{-- ── Table ── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <th class="th">Ke</th>
                        <th class="th">Tanggal Bayar</th>
                        @if($summary['is_uang'])
                        <th class="th text-right">Nilai Bayar</th>
                        @endif
                        <th class="th">No. Bukti</th>
                        <th class="th text-center">Status</th>
                        <th class="th">Verifikator</th>
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($cicilans as $cicilan)
                    <tr class="group {{ $cicilan->trashed() ? 'opacity-50 bg-slate-50 dark:bg-slate-800/30' : 'hover:bg-slate-50/80 dark:hover:bg-slate-800/30' }} transition-colors">

                        {{-- Ke --}}
                        <td class="td">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-xs font-black">
                                {{ $cicilan->ke }}
                            </span>
                            @if($cicilan->trashed())
                                <span class="ml-1.5 text-[10px] font-bold text-rose-400 uppercase">dihapus</span>
                            @endif
                        </td>

                        {{-- Tanggal --}}
                        <td class="td text-slate-600 dark:text-slate-400">
                            <div>{{ $cicilan->tanggal_bayar?->format('d M Y') ?? '-' }}</div>
                            @if($cicilan->isTelat())
                                <span class="text-[10px] font-black text-rose-500 uppercase tracking-wide">Telat</span>
                            @endif
                        </td>

                        {{-- Nilai --}}
                        @if($summary['is_uang'])
                        <td class="td text-right font-mono font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($cicilan->nilai_bayar, 0, ',', '.') }}
                        </td>
                        @endif

                        {{-- Nomor Bukti --}}
                        <td class="td font-mono text-xs text-slate-500 dark:text-slate-400">
                            {{ $cicilan->nomor_bukti ?? '—' }}
                        </td>

                        {{-- Status --}}
                        <td class="td text-center">
                            @php
                            $sCls = match($cicilan->status) {
                                'diterima'            => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'ditolak'             => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                'menunggu_verifikasi' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                default               => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $sCls }}">
                                {{ $cicilan->label_status }}
                            </span>
                        </td>

                        {{-- Verifikator --}}
                        <td class="td text-xs text-slate-500 dark:text-slate-400">
                            <div class="font-semibold">{{ $cicilan->diverifikator?->name ?? '—' }}</div>
                            @if($cicilan->diverifikasi_pada)
                                <div class="text-[11px] text-slate-400">{{ $cicilan->diverifikasi_pada->format('d M Y') }}</div>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="td text-right">
                            @unless($cicilan->trashed())
                            <div class="flex items-center justify-end gap-1">

                                {{-- Detail --}}
                                <a href="{{ route('tindak-lanjuts.cicilans.show', [$tindakLanjut, $cicilan]) }}"
                                   title="Lihat Detail"
                                   class="icon-btn hover:text-indigo-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @if($cicilan->status === 'menunggu_verifikasi')
                                {{-- Terima --}}
                                <form action="{{ route('tindak-lanjuts.cicilans.verifikasi', [$tindakLanjut, $cicilan]) }}"
                                      method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="diterima">
                                    <button type="submit" title="Terima"
                                            onclick="return confirm('Terima cicilan ke-{{ $cicilan->ke }}?')"
                                            class="icon-btn hover:text-emerald-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>

                                {{-- Tolak --}}
                                <form action="{{ route('tindak-lanjuts.cicilans.verifikasi', [$tindakLanjut, $cicilan]) }}"
                                      method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="ditolak">
                                    <button type="submit" title="Tolak"
                                            onclick="return confirm('Tolak cicilan ke-{{ $cicilan->ke }}?')"
                                            class="icon-btn hover:text-rose-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>

                                {{-- Edit --}}
                                <a href="{{ route('tindak-lanjuts.cicilans.edit', [$tindakLanjut, $cicilan]) }}"
                                   title="Edit"
                                   class="icon-btn hover:text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L21 3.586a2 2 0 010 2.828l-7 7a2 2 0 01-.883.515l-3 1a1 1 0 01-1.265-1.265l1-3a1 1 0 01.515-.883l7-7z"/>
                                    </svg>
                                </a>

                                {{-- Hapus --}}
                                <form action="{{ route('tindak-lanjuts.cicilans.destroy', [$tindakLanjut, $cicilan]) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Yakin hapus cicilan ke-{{ $cicilan->ke }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Hapus" class="icon-btn hover:text-rose-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif

                            </div>
                            @endunless
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $summary['is_uang'] ? 7 : 6 }}" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-bold text-slate-500">Belum ada cicilan</p>
                                    <p class="text-xs mt-0.5">Klik "Tambah Cicilan" untuk memulai.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Back link ── --}}
    <div class="mt-5">
        <a href="{{ route('tindak-lanjuts.show', $tindakLanjut) }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Detail Tindak Lanjut
        </a>
    </div>

</div>
</div>

<style>
.th { padding: 12px 20px; font-size: 10px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #94a3b8; white-space: nowrap; }
.td { padding: 14px 20px; vertical-align: middle; }
.icon-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 2rem; height: 2rem; border-radius: 0.5rem;
    color: #94a3b8;
    transition: color 0.15s, background 0.15s;
}
.icon-btn:hover { background: rgba(0,0,0,0.04); }
.dark .icon-btn:hover { background: rgba(255,255,255,0.06); }
</style>
@endsection