@extends('layouts.fullscreen-layout')

@section('content')
@php
    $currentYear = date('Y');
@endphp
<div class="relative flex flex-col items-center justify-center min-h-screen p-6 overflow-hidden z-1 bg-gray-50 dark:bg-gray-900">
    <x-common.common-grid-shape />
    
    <div class="mx-auto w-full max-w-[480px] text-center">
        <!-- Error Badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1 mb-6 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            Kesalahan Sistem (500)
        </div>

        <h1 class="mb-3 font-bold text-gray-800 text-title-md dark:text-white/90 xl:text-title-2xl">
            Terjadi Kendala Teknis
        </h1>

        <p class="mb-6 text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
            Mohon maaf atas ketidaknyamanan ini. Terjadi kendala saat memproses permintaan Anda. Tim kami telah mencatat peristiwa ini untuk segera dilakukan penanganan.
        </p>

        @if(!empty($incidentId))
            <div class="mb-8 p-3 rounded-lg bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 text-center">
                <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1">ID Referensi Insiden (Berikan kode ini kepada administrator):</span>
                <code class="font-mono text-sm font-semibold text-brand-600 dark:text-brand-400 select-all">{{ $incidentId }}</code>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="javascript:window.location.reload();"
                class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none">
                Muat Ulang Halaman
            </a>
            <a href="{{ url('/') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <!-- Footer -->
    <p class="absolute text-xs text-center text-gray-500 -translate-x-1/2 bottom-6 left-1/2 dark:text-gray-400">
        &copy; {{ $currentYear }} SIMANTAB - Inspektorat Daerah
    </p>
</div>
@endsection
