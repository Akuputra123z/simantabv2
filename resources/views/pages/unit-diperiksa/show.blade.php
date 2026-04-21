@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl">
    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('unit-diperiksa.index') }}" class="group inline-flex items-center text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400 hover:text-blue-600 transition-colors mb-3">
                <svg class="mr-2 h-3 w-3 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Daftar
            </a>
            <h2 class="text-3xl font-black tracking-tighter text-gray-900 dark:text-white">
                Profil Unit <span class="text-blue-600">.</span>
            </h2>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('unit-diperiksa.edit', $data->id) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl">
                Edit Profil
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-12">
        {{-- Card Kiri: Ringkasan --}}
        <div class="md:col-span-4 space-y-6">
            <div class="rounded-3xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.02]">
                <div class="mb-8 flex flex-col items-center text-center">
                    <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-50 text-blue-600 dark:bg-white/5 dark:text-blue-400 font-black text-2xl">
                        {{ substr($data->nama_unit, 0, 1) }}
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-xl font-black tracking-tight text-gray-900 dark:text-white leading-tight">{{ $data->nama_unit }}</h3>
                        <span class="inline-flex items-center rounded-full bg-blue-500/10 px-3 py-1 text-[10px] font-black uppercase text-blue-600">
                            {{ $data->kategori }}
                        </span>
                    </div>
                </div>

                <div class="space-y-5 border-t border-gray-50 pt-8 dark:border-gray-800/50">
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Kecamatan</span>
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 text-right">{{ $data->nama_kecamatan ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Telepon</span>
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $data->telepon ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Kanan: Detail Alamat & Keterangan --}}
        <div class="md:col-span-8 space-y-6">
            <div class="rounded-3xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.02]">
                <div class="mb-10">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="h-6 w-1 rounded-full bg-blue-600"></div>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Lokasi & Alamat</h4>
                    </div>
                    <p class="text-lg font-medium leading-relaxed text-gray-600 dark:text-gray-400">
                        {{ $data->alamat ?: 'Alamat belum diatur untuk unit ini.' }}
                    </p>
                </div>

                <div>
                    <div class="mb-4 flex items-center gap-3">
                        <div class="h-6 w-1 rounded-full bg-gray-300"></div>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Catatan Internal</h4>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-5 dark:bg-white/5">
                        <p class="text-sm leading-relaxed text-gray-500 dark:text-gray-400 italic">
                            {{ $data->keterangan ?: 'Tidak ada catatan tambahan.' }}
                        </p>
                    </div>
                </div>

                <div class="mt-12 flex justify-between border-t border-gray-50 pt-8 dark:border-gray-800/50">
                    <span class="text-[9px] font-bold uppercase tracking-widest text-gray-300">Data Terdaftar:{{ optional($data->created_at)->translatedFormat('d M Y') ?? '-' }}</span>
                    <span class="text-[9px] font-mono text-gray-300 uppercase">UID-{{ str_pad($data->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection