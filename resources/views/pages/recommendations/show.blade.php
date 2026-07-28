@extends('layouts.app')

@push('scripts')
<style>
    .uraian-content table {
        border-collapse: collapse !important;
        width: 100% !important;
    }
    .uraian-content table th,
    .uraian-content table td {
        border: 1px solid #d1d5db !important;
        padding: 0.5rem 0.75rem !important;
        vertical-align: top;
    }
    .uraian-content table th {
        background-color: #f3f4f6 !important;
        font-weight: 600;
        text-align: left;
    }
    .dark .uraian-content table th,
    .dark .uraian-content table td {
        border-color: #4b5563 !important;
    }
    .dark .uraian-content table th {
        background-color: #374151 !important;
    }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8">

    {{-- HEADER --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Detail Rekomendasi
            </h2>
            <p class="text-sm text-gray-500">
                Informasi lengkap rekomendasi dan tindak lanjut
            </p>
        </div>

        <a href="{{ route('recommendations.index') }}"
           class="text-sm font-medium text-brand-500 hover:text-brand-600">
            &larr; Kembali
        </a>
    </div>

    {{-- CARD --}}
    <div class="space-y-6">

        {{-- INFORMASI UTAMA --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="font-semibold text-gray-800 dark:text-white">Informasi Rekomendasi</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">

                <div>
                    <p class="text-gray-500">Nomor LHP</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ $recommendation->temuan->lhp->nomor_lhp ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Tanggal LHP</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ optional($recommendation->temuan->lhp->tanggal_lhp)->format('d M Y') ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Kode Rekomendasi</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ $recommendation->kodeRekomendasi->label ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Jenis</p>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                        @if($recommendation->jenis_rekomendasi == 'uang') bg-green-100 text-green-700
                        @elseif($recommendation->jenis_rekomendasi == 'barang') bg-blue-100 text-blue-700
                        @else bg-yellow-100 text-yellow-700 @endif">
                        {{ ucfirst($recommendation->jenis_rekomendasi) }}
                    </span>
                </div>

                <div>
                    <p class="text-gray-500">Nilai Rekomendasi</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        Rp {{ number_format($recommendation->nilai_rekom, 0, ',', '.') }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Sisa</p>
                    <p class="font-semibold text-red-600">
                        Rp {{ number_format($recommendation->nilai_sisa, 0, ',', '.') }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Batas Waktu</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($recommendation->batas_waktu)->format('d M Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Status</p>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                        @if($recommendation->status == 'selesai') bg-green-100 text-green-700
                        @elseif($recommendation->status == 'proses') bg-yellow-100 text-yellow-700
                        @else bg-red-100 text-red-700 @endif">
                        {{ ucfirst(str_replace('_', ' ', $recommendation->status)) }}
                    </span>
                </div>

            </div>

            <div class="px-6 pb-6">
                <p class="text-gray-500 mb-1">Uraian Rekomendasi</p>
                <div class="rounded-lg bg-gray-50 p-4 text-gray-800 dark:bg-gray-800 dark:text-white/90 prose prose-sm max-w-none uraian-content">
                    {!! $recommendation->uraian_rekom !!}
                </div>
            </div>
        </div>

        {{-- TEMUAN --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="font-semibold text-gray-800 dark:text-white">Data Temuan</h3>
            </div>

            <div class="p-6 text-sm space-y-3">
                <div>
                    <p class="text-gray-500">Kode Temuan</p>
                    <p class="font-medium">{{ $recommendation->temuan->kodeTemuan->kode ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-gray-500">Kondisi</p>
                    <p>{{ $recommendation->temuan->kondisi }}</p>
                </div>

                <div>
                    <p class="text-gray-500">Nilai Temuan</p>
                    <p class="font-semibold">
                        Rp {{ number_format($recommendation->temuan->nilai_temuan, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- TINDAK LANJUT --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 dark:text-white">Tindak Lanjut</h3>
                <span class="text-xs text-gray-400">{{ $recommendation->tindakLanjuts->count() }} item</span>
            </div>

            <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-800">
                <table class="w-full text-left text-sm table-fixed">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/40">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-5 py-3.5 text-[10px] font-bold uppercase tracking-wide text-gray-400 w-[13%]">Tanggal</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold uppercase tracking-wide text-gray-400">Uraian</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold uppercase tracking-wide text-gray-400 w-[11%]">Jenis</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold uppercase tracking-wide text-gray-400 w-[13%]">Nilai TL</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold uppercase tracking-wide text-gray-400 w-[13%]">Terbayar</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold uppercase tracking-wide text-gray-400 w-[13%]">Sisa</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold uppercase tracking-wide text-gray-400 w-[14%]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recommendation->tindakLanjuts as $tl)
                            @php
                                $statusCls = match($tl->status_verifikasi) {
                                    'lunas'              => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                    'berjalan'           => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                    'menunggu_verifikasi'=> 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    default              => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap text-gray-900 dark:text-white font-medium">
                                    {{ $tl->tanggal?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-gray-700 dark:text-gray-300 max-w-[300px]">
                                    <p class="truncate">{{ $tl->catatan_tl ?: '-' }}</p>
                                    @if($tl->hambatan)
                                        <p class="text-[11px] text-red-400 mt-0.5 truncate">⚠ {{ $tl->hambatan }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        @if($tl->jenis_penyelesaian == 'langsung') bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300
                                        @elseif($tl->jenis_penyelesaian == 'cicilan') bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300
                                        @else bg-gray-100 text-gray-600 @endif">
                                        {{ ucfirst($tl->jenis_penyelesaian ?? '-') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                    Rp {{ number_format($tl->nilai_tindak_lanjut, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    Rp {{ number_format($tl->total_terbayar, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-medium {{ $tl->sisa_belum_bayar > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Rp {{ number_format($tl->sisa_belum_bayar, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $statusCls }}">
                                        {{ ucfirst(str_replace('_', ' ', $tl->status_verifikasi)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">
                                    Belum ada tindak lanjut.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection