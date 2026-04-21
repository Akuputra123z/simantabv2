@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950 flex justify-center">
    <div class="w-full max-w-2xl">

        <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-indigo-600 transition-colors">Tindak Lanjut</a>
            <span>/</span>
            <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut) }}" class="hover:text-indigo-600 transition-colors">Cicilan</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">Ke-{{ $cicilan->ke }}</span>
        </nav>

        @php
            $statusCls = match($cicilan->status) {
                'diterima'            => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'ditolak'             => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                'menunggu_verifikasi' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                default               => 'bg-gray-100 text-gray-600',
            };
            $isUang = $tindakLanjut->recommendation?->isUang();
        @endphp

        <div class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Cicilan Ke-{{ $cicilan->ke }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $tindakLanjut->recommendation?->temuan?->lhp?->nomor_lhp ?? '-' }}
                    </p>
                </div>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $statusCls }}">
                    {{ $cicilan->label_status }}
                </span>
            </div>

            {{-- Detail --}}
            <div class="divide-y divide-gray-100 dark:divide-gray-800">

                @if($isUang)
                <div class="px-6 py-4 flex justify-between items-center">
                    <span class="text-sm text-gray-500">Nilai Bayar</span>
                    <span class="text-lg font-bold font-mono text-gray-900 dark:text-white">
                        Rp {{ number_format($cicilan->nilai_bayar, 0, ',', '.') }}
                    </span>
                </div>

                @php
                    $hasBreakdown = ($cicilan->nilai_bayar_negara + $cicilan->nilai_bayar_daerah + $cicilan->nilai_bayar_desa + $cicilan->nilai_bayar_bos_blud) > 0;
                @endphp
                @if($hasBreakdown)
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-500 mb-3">Breakdown Nilai</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['nilai_bayar_negara' => 'Negara', 'nilai_bayar_daerah' => 'Daerah', 'nilai_bayar_desa' => 'Desa', 'nilai_bayar_bos_blud' => 'BOS/BLUD'] as $col => $label)
                        @if($cicilan->$col > 0)
                        <div class="flex justify-between text-sm px-3 py-2 bg-gray-50 dark:bg-white/5 rounded-lg">
                            <span class="text-gray-500">{{ $label }}</span>
                            <span class="font-mono font-medium text-gray-800 dark:text-gray-200">Rp {{ number_format($cicilan->$col, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endif

                @foreach([
                    'Tanggal Bayar'               => $cicilan->tanggal_bayar?->format('d F Y'),
                    'Tanggal Jatuh Tempo'          => $cicilan->tanggal_jatuh_tempo_cicilan?->format('d F Y') ?? '-',
                    'Nomor Bukti'                  => $cicilan->nomor_bukti ?? '-',
                    'Jenis Bayar'                  => $cicilan->jenis_bayar ?? '-',
                    'Keterangan'                   => $cicilan->keterangan ?? '-',
                ] as $label => $value)
                <div class="px-6 py-4 flex justify-between gap-4">
                    <span class="text-sm text-gray-500 shrink-0">{{ $label }}</span>
                    <span class="text-sm text-gray-800 dark:text-gray-200 text-right">{{ $value }}</span>
                </div>
                @endforeach

                {{-- Verifikasi --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-white/[0.02]">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Informasi Verifikasi</p>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Verifikator</span>
                            <span class="text-gray-800 dark:text-gray-200">{{ $cicilan->diverifikator?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tanggal Verifikasi</span>
                            <span class="text-gray-800 dark:text-gray-200">{{ $cicilan->diverifikasi_pada?->format('d F Y H:i') ?? '-' }}</span>
                        </div>
                        @if($cicilan->catatan_verifikasi)
                        <div class="text-sm">
                            <span class="text-gray-500">Catatan</span>
                            <p class="mt-1 text-gray-800 dark:text-gray-200 bg-white dark:bg-white/5 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">
                                {{ $cicilan->catatan_verifikasi }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Audit --}}
                <div class="px-6 py-3 flex justify-between text-xs text-gray-400">
                    <span>Dibuat: {{ $cicilan->creator?->name ?? '-' }} &bull; {{ $cicilan->created_at?->format('d M Y H:i') }}</span>
                    @if($cicilan->updated_at != $cicilan->created_at)
                    <span>Diperbarui: {{ $cicilan->updated_at?->format('d M Y H:i') }}</span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
                <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut) }}"
                   class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">&larr; Kembali</a>

                <div class="flex gap-2">
                    @if($cicilan->status === 'menunggu_verifikasi')
                    <form action="{{ route('tindak-lanjuts.cicilans.verifikasi', [$tindakLanjut, $cicilan]) }}"
                          method="POST" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="diterima">
                        <button type="submit"
                                onclick="return confirm('Terima cicilan ini?')"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                            Terima
                        </button>
                    </form>
                    <form action="{{ route('tindak-lanjuts.cicilans.verifikasi', [$tindakLanjut, $cicilan]) }}"
                          method="POST" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="ditolak">
                        <button type="submit"
                                onclick="return confirm('Tolak cicilan ini?')"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                            Tolak
                        </button>
                    </form>
                    <a href="{{ route('tindak-lanjuts.cicilans.edit', [$tindakLanjut, $cicilan]) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        Edit
                    </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection