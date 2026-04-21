@extends('layouts.app')

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
                <div class="rounded-lg bg-gray-50 p-4 text-gray-800 dark:bg-gray-800 dark:text-white">
                    {{ $recommendation->uraian_rekom }}
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
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800 flex justify-between">
                <h3 class="font-semibold text-gray-800 dark:text-white">Tindak Lanjut</h3>
            </div>

            <div class="p-6">
                @forelse($recommendation->tindakLanjuts as $tl)
                    <div class="mb-4 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <p class="text-sm text-gray-500 mb-1">
                            Tanggal: {{ \Carbon\Carbon::parse($tl->tanggal)->format('d M Y') }}
                        </p>

                        <p class="text-gray-800 dark:text-white mb-2">
                            {{ $tl->uraian }}
                        </p>
                          <p class="text-gray-800 dark:text-white mb-2">
                            {{ $tl->jenis_penyelesaian }}
                        </p>
                          <p class="text-gray-800 dark:text-white mb-2">
                            {{ $tl->nilai_tindak_lanjut }}
                        </p>

                        <p class="text-sm font-semibold text-green-600">
                            Nilai: Rp {{ number_format($tl->nilai, 0, ',', '.') }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Belum ada tindak lanjut.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection