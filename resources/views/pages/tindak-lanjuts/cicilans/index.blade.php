@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950">
    <div class="max-w-5xl mx-auto">

        {{-- Breadcrumb --}}
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-indigo-600 transition-colors">Tindak Lanjut</a>
            <span>/</span>
            <a href="{{ route('tindak-lanjuts.show', $tindakLanjut) }}" class="hover:text-indigo-600 transition-colors truncate max-w-xs">
                {{ Str::limit($tindakLanjut->recommendation->uraian_rekom ?? 'Detail', 50) }}
            </a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">Cicilan</span>
        </nav>

        {{-- Header + Tombol --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Daftar Cicilan</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    LHP: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $tindakLanjut->recommendation->temuan->lhp->nomor_lhp ?? '-' }}</span>
                </p>
            </div>
            <a href="{{ route('tindak-lanjuts.cicilans.create', $tindakLanjut) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Cicilan
            </a>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg">{{ session('error') }}</div>
        @endif

        {{-- Summary Card --}}
        <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Rencana Cicilan</p>
                {{-- Ganti baris ini di Blade Anda --}}
<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
    {{ $summary['total_rencana'] ?? '-' }}
</p>
            </div>
            <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Realisasi</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $summary['total_realisasi'] }}</p>
            </div>

            @if($summary['is_uang'])
            <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Total Terbayar</p>
                <p class="text-lg font-bold text-green-600 mt-1">Rp {{ number_format($summary['total_terbayar'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Sisa</p>
                <p class="text-lg font-bold {{ $summary['sisa'] > 0 ? 'text-red-500' : 'text-green-600' }} mt-1">
                    Rp {{ number_format($summary['sisa'], 0, ',', '.') }}
                </p>
            </div>
            @else
            <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-800 rounded-xl p-4 col-span-2">
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Jenis Rekomendasi</p>
                <p class="text-lg font-bold text-gray-700 dark:text-gray-300 mt-1">
                    {{ ucfirst($tindakLanjut->recommendation->jenis_rekomendasi) }}
                    <span class="text-sm font-normal text-gray-400">(non-uang)</span>
                </p>
            </div>
            @endif
        </div>

        {{-- Progress bar (hanya uang) --}}
        @if($summary['is_uang'] && $summary['nilai_rekom'] > 0)
        @php $pct = min(100, round($summary['total_terbayar'] / $summary['nilai_rekom'] * 100, 1)); @endphp
        <div class="mb-6 bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
            <div class="flex justify-between text-xs text-gray-500 mb-2">
                <span>Progress pembayaran</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $pct }}%</span>
            </div>
            <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all {{ $pct >= 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
                     style="width: {{ $pct }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mt-1.5">
                <span>Rp {{ number_format($summary['total_terbayar'], 0, ',', '.') }}</span>
                <span>Rp {{ number_format($summary['nilai_rekom'], 0, ',', '.') }}</span>
            </div>
        </div>
        @endif

        {{-- Tabel Cicilan --}}
        <div class="bg-white dark:bg-white/5 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">Ke</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">Tanggal Bayar</th>
                            @if($summary['is_uang'])
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase text-right">Nilai Bayar</th>
                            @endif
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">No. Bukti</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase text-center">Status</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">Verifikator</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($cicilans as $cicilan)
                        <tr class="{{ $cicilan->trashed() ? 'opacity-50 bg-gray-50 dark:bg-white/[0.02]' : 'hover:bg-gray-50 dark:hover:bg-white/[0.03]' }} transition-colors">
                            <td class="px-5 py-3.5 font-mono font-semibold text-gray-700 dark:text-gray-300">
                                #{{ $cicilan->ke }}
                                @if($cicilan->trashed())
                                    <span class="ml-1 text-xs text-red-400">(dihapus)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">
                                {{ $cicilan->tanggal_bayar?->format('d M Y') ?? '-' }}
                                @if($cicilan->isTelat())
                                    <span class="ml-1 text-xs text-red-500">telat</span>
                                @endif
                            </td>
                            @if($summary['is_uang'])
                            <td class="px-5 py-3.5 text-right font-mono font-medium text-gray-900 dark:text-white">
                                Rp {{ number_format($cicilan->nilai_bayar, 0, ',', '.') }}
                            </td>
                            @endif
                            <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400 font-mono text-xs">
                                {{ $cicilan->nomor_bukti ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @php
                                    $cls = match($cicilan->status) {
                                        'diterima'            => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'ditolak'             => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                        'menunggu_verifikasi' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        default               => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $cls }}">
                                    {{ $cicilan->label_status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $cicilan->diverifikator?->name ?? '-' }}
                                @if($cicilan->diverifikasi_pada)
                                    <div class="text-gray-400">{{ $cicilan->diverifikasi_pada->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @unless($cicilan->trashed())
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('tindak-lanjuts.cicilans.show', [$tindakLanjut, $cicilan]) }}"
                                       class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if($cicilan->status === 'menunggu_verifikasi')
                                    {{-- Tombol Verifikasi --}}
                                    <form action="{{ route('tindak-lanjuts.cicilans.verifikasi', [$tindakLanjut, $cicilan]) }}"
                                          method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="diterima">
                                        <button type="submit"
                                                onclick="return confirm('Terima cicilan ke-{{ $cicilan->ke }}?')"
                                                class="p-1.5 text-gray-400 hover:text-green-600 transition-colors" title="Terima">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('tindak-lanjuts.cicilans.verifikasi', [$tindakLanjut, $cicilan]) }}"
                                          method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="ditolak">
                                        <button type="submit"
                                                onclick="return confirm('Tolak cicilan ke-{{ $cicilan->ke }}?')"
                                                class="p-1.5 text-gray-400 hover:text-red-600 transition-colors" title="Tolak">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <a href="{{ route('tindak-lanjuts.cicilans.edit', [$tindakLanjut, $cicilan]) }}"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L21 3.586a2 2 0 010 2.828l-7 7a2 2 0 01-.883.515l-3 1a1 1 0 01-1.265-1.265l1-3a1 1 0 01.515-.883l7-7z"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('tindak-lanjuts.cicilans.destroy', [$tindakLanjut, $cicilan]) }}"
                                          method="POST" class="inline"
                                          onsubmit="return confirm('Hapus cicilan ke-{{ $cicilan->ke }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif {{-- menunggu_verifikasi --}}
                                </div>
                                @endunless
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400 italic">
                                Belum ada cicilan. Klik "Tambah Cicilan" untuk memulai.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('tindak-lanjuts.show', $tindakLanjut) }}"
               class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                &larr; Kembali ke Detail Tindak Lanjut
            </a>
        </div>

    </div>
</div>
@endsection