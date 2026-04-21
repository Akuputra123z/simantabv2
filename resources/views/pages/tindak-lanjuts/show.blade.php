@extends('layouts.app')

@section('content')

@if(!isset($tindakLanjut) || !$tindakLanjut->id)
<div class="flex h-[60vh] flex-col items-center justify-center text-center">
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Data tidak ditemukan</h2>
    <a href="{{ route('tindak-lanjuts.index') }}"
       class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm hover:bg-gray-800">
       Kembali
    </a>
</div>
@php return; @endphp
@endif

<div class="max-w-6xl mx-auto px-4 py-8">

    {{-- HEADER --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="text-sm text-gray-400 mb-1">
                <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-gray-600">Tindak Lanjut</a>
                <span class="mx-1">/</span>
                <span class="text-gray-600">Detail</span>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900">
                Detail Tindak Lanjut
            </h1>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tindak-lanjuts.edit', $tindakLanjut->id) }}"
               class="px-4 py-2 text-sm border rounded-lg text-gray-700 hover:bg-gray-50">
               Edit
            </a>
            <a href="{{ route('tindak-lanjuts.index') }}"
               class="px-4 py-2 text-sm bg-gray-900 text-white rounded-lg hover:bg-gray-800">
               Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- CARD DETAIL --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xs text-gray-500">
                        Ref: #{{ $tindakLanjut->recommendation_id }}
                    </span>

                    <span class="text-xs px-2 py-1 rounded-md
                        {{ $tindakLanjut->status_verifikasi == 'disetujui' ? 'bg-green-100 text-green-600' :
                           ($tindakLanjut->status_verifikasi == 'ditolak' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600') }}">
                        {{ ucfirst(str_replace('_',' ',$tindakLanjut->status_verifikasi)) }}
                    </span>
                </div>

                <p class="text-gray-800 leading-relaxed mb-6">
                    {{ $tindakLanjut->recommendation->uraian_rekom ?? '-' }}
                </p>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <p class="text-gray-500 mb-1">Metode</p>
                        <p class="text-gray-900 font-medium">
                            {{ $tindakLanjut->jenis_penyelesaian }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Jatuh Tempo</p>
                        <p class="text-gray-900">
                            {{ $tindakLanjut->tanggal_jatuh_tempo 
                                ? \Carbon\Carbon::parse($tindakLanjut->tanggal_jatuh_tempo)->format('d M Y') 
                                : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Verifikator</p>
                        <p class="text-gray-900">
                            {{ $tindakLanjut->verifikator->name ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- CICILAN --}}
            @if($tindakLanjut->jenis_penyelesaian === 'cicilan')
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Riwayat Pembayaran
                    </h3>
                    <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut->id) }}"
                       class="text-xs text-gray-500 hover:text-gray-700">
                       Lihat semua →
                    </a>
                </div>

                <div class="bg-white border rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Ke</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-right">Nominal</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tindakLanjut->cicilans()->latest()->take(5)->get() as $cicilan)
                            <tr class="border-t">
                                <td class="px-4 py-3">#{{ $cicilan->ke }}</td>
                                <td class="px-4 py-3">
                                    {{ $cicilan->tanggal_bayar ? $cicilan->tanggal_bayar->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    Rp{{ number_format($cicilan->nilai_bayar, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs px-2 py-1 rounded
                                        {{ $cicilan->status == 'diterima' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                                        {{ $cicilan->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-gray-400 text-sm">
                                    Belum ada data
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- CATATAN --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">
                    Catatan
                </h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {!! nl2br(e($tindakLanjut->catatan_tl ?? 'Tidak ada catatan')) !!}
                </p>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="space-y-6">

            {{-- FINANSIAL --}}
            @if($tindakLanjut->recommendation->jenis_rekomendasi === 'uang')
            <div class="bg-gray-900 text-white rounded-xl p-6">
                <p class="text-sm text-gray-400 mb-1">Target</p>
                <p class="text-2xl font-semibold mb-4">
                    Rp{{ number_format($tindakLanjut->nilai_tindak_lanjut, 0, ',', '.') }}
                </p>

                <p class="text-sm text-gray-400">Realisasi</p>
                <p class="text-lg font-medium text-green-400">
                    Rp{{ number_format($tindakLanjut->total_terbayar ?? 0, 0, ',', '.') }}
                </p>

                <div class="mt-3 h-2 bg-gray-700 rounded">
                    <div class="h-2 bg-green-400 rounded"
                         style="width: {{ ($tindakLanjut->total_terbayar / max($tindakLanjut->nilai_tindak_lanjut,1))*100 }}%">
                    </div>
                </div>
            </div>
            @endif

            {{-- INFO --}}
            <div class="bg-white border rounded-xl p-6 text-sm">
                <h4 class="font-semibold text-gray-800 mb-4">Informasi</h4>

                <div class="space-y-3 text-gray-600">
                    <div>
                        <p class="text-gray-400 text-xs">Nomor LHP</p>
                        <p>{{ $tindakLanjut->recommendation->temuan->lhp->nomor_lhp ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-xs">Kode Temuan</p>
                        <p>{{ $tindakLanjut->recommendation->temuan->kodeTemuan->kode ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-xs">Petugas</p>
                        <p>{{ $tindakLanjut->creator->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection