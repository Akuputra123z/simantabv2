@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('kode-rekomendasi.index') }}" 
               class="inline-flex items-center text-xs text-gray-400 hover:text-blue-500 transition mb-1">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor">
                    <path stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>

            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                Detail Rekomendasi
            </h2>
        </div>

<x-ui.button 
    tag="a" 
    href="{{ route('kode-rekomendasi.edit', $data) }}" 
    variant="primary"
    class="rounded-xl px-4 py-2 shadow-sm hover:shadow-md transition"
>
    Edit
</x-ui.button>
    </div>

    {{-- Single Card --}}
    <div class="rounded-2xl border border-gray-100 bg-white/80 backdrop-blur p-6 shadow-sm">

        {{-- Header Card --}}
        <div class="flex items-center gap-4 mb-6">
            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50 text-blue-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor">
                    <path stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7"/>
                </svg>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ $data->kode }}
                </h3>
                <p class="text-xs text-gray-400">Kode Referensi</p>
            </div>
        </div>

        {{-- Info --}}
        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <p class="text-gray-400 text-xs">Numerik</p>
                <p class="text-gray-700 dark:text-gray-300">{{ $data->kode_numerik }}</p>
            </div>

            <div>
                <p class="text-gray-400 text-xs">Kategori</p>
                <p class="text-gray-700 dark:text-gray-300">{{ $data->kategori ?? 'Umum' }}</p>
            </div>

            <div>
                <p class="text-gray-400 text-xs">Status</p>
                @if($data->is_active)
                    <span class="inline-block mt-1 px-3 py-1 text-xs rounded-full bg-green-100 text-green-600">
                        Aktif
                    </span>
                @else
                    <span class="inline-block mt-1 px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-500">
                        Non-Aktif
                    </span>
                @endif
            </div>

            <div>
                <p class="text-gray-400 text-xs">Dibuat</p>
                <p class="text-gray-700 dark:text-gray-300">
                    {{ $data->created_at->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>

        {{-- Deskripsi --}}
        <div class="border-t border-gray-100 pt-4">
            <p class="text-sm text-gray-500 mb-2">Uraian</p>
            <p class="text-gray-600 leading-relaxed">
                {{ $data->deskripsi ?: 'Belum ada deskripsi untuk data ini.' }}
            </p>
        </div>

        {{-- Footer --}}
        <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end text-xs text-gray-400">
            <span>#REC-{{ str_pad($data->id, 4, '0', STR_PAD_LEFT) }}</span>
        </div>

    </div>

</div>
@endsection