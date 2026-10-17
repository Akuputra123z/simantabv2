@extends('layouts.fullscreen-layout')

@section('content')
@php
    $currentYear = date('Y');
@endphp
<div class="relative flex flex-col items-center justify-center min-h-screen p-6 overflow-hidden z-1 bg-gray-50 dark:bg-gray-900">
    <x-common.common-grid-shape />
    
    <div class="mx-auto w-full max-w-[480px] text-center">
        <!-- Error Badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1 mb-6 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            Sesi Berakhir (419)
        </div>

        <h1 class="mb-3 font-bold text-gray-800 text-title-md dark:text-white/90 xl:text-title-2xl">
            Sesi Halaman Kedaluwarsa
        </h1>

        <p class="mb-8 text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
            Demi menjaga keamanan transaksi dan data Anda, sesi halaman atau token formulir ini telah kedaluwarsa karena tidak ada aktivitas. Silakan muat ulang halaman untuk memperbarui sesi dan melanjutkan.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="javascript:window.location.reload();"
                class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none">
                Muat Ulang Halaman
            </a>
            <a href="{{ url('/signin') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Masuk Kembali
            </a>
        </div>
    </div>

    <!-- Footer -->
    <p class="absolute text-xs text-center text-gray-500 -translate-x-1/2 bottom-6 left-1/2 dark:text-gray-400">
        &copy; {{ $currentYear }} SIMANTAB - Inspektorat Daerah
    </p>
</div>
@endsection
