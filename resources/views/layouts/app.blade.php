<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    
    <title>{{ $title ?? 'Dashboard' }} | Admin</title>
    

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .ts-wrapper {
            min-width: 0;
            max-width: 100%;
            vertical-align: middle;
        }
        .ts-wrapper .ts-control {
            border-radius: 0.5rem;
            border-color: #e5e7eb;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            min-height: 2.5rem;
            background: transparent;
            box-shadow: none;
        }
        .dark .ts-wrapper .ts-control {
            border-color: #374151;
            color: #f3f4f6;
            background: transparent;
        }
        .ts-wrapper.focus .ts-control {
            border-color: #4f46e5;
        }
        .ts-wrapper .ts-dropdown {
    position: absolute !important; /* Wajib melayang */
    z-index: 50 !important;        /* Wajib di atas elemen lain */
    border-radius: 0.5rem;
    border-color: #e5e7eb;
    box-shadow: 0 10px 30px -5px rgba(0,0,0,0.1);
    margin-top: 4px;
    width: 100% !important;
    box-sizing: border-box;
}
        .dark .ts-wrapper .ts-dropdown {
            background: #1f2937;
            border-color: #374151;
        }
        .ts-wrapper .ts-dropdown .active {
            background: #eef2ff;
        }
        .dark .ts-wrapper .ts-dropdown .active {
            background: rgba(79, 70, 229, 0.15);
        }
        .ts-wrapper .ts-dropdown .option {
            padding: 0.5rem 0.875rem;
            font-size: 0.8125rem;
        }
        .dark .ts-wrapper .ts-dropdown .option {
            color: #d1d5db;
        }
        .ts-wrapper .ts-dropdown .option.highlight {
            background: rgba(79, 70, 229, 0.08);
        }
        .ts-wrapper.multi .ts-control {
            padding: 0.375rem 0.5rem;
        }
        .ts-wrapper .ts-control input {
            font-size: 0.875rem;
        }
        .dark .ts-wrapper .ts-control input {
            color: #f3f4f6;
        }
    </style>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();

        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    document.documentElement.classList.toggle('dark', this.theme === 'dark');
                }
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,
                toggleExpanded() { this.isExpanded = !this.isExpanded; this.isMobileOpen = false; },
                toggleMobileOpen() { this.isMobileOpen = !this.isMobileOpen; },
                setMobileOpen(val) { this.isMobileOpen = val; },
                setHovered(val) { if (window.innerWidth >= 1280 && !this.isExpanded) this.isHovered = val; }
            });
        });
    </script>
</head>

<body
    class="h-full bg-white dark:bg-gray-900"
    x-init="window.addEventListener('resize', () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isExpanded = true;
            $store.sidebar.isMobileOpen = false;
        }
    })">

    <x-common.preloader/>

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            
            @include('layouts.app-header')
            
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @yield('content')
            </div>
        </div>
    </div>

    {{-- ✅ Global Pop-Up Alert Success Modal Ultra-Smooth --}}
    @if(session('success'))
    <div x-data="{ show: false }"
         x-init="$nextTick(() => { setTimeout(() => show = true, 50); })"
         @keydown.escape.window="show = false"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        
        <!-- Backdrop overlay with smooth fade -->
        <div x-show="show"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="show = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"></div>

        <!-- Modal Card -->
        <div x-show="show"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-6 blur-md"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave="transition cubic-bezier(0.7, 0, 0.84, 0) duration-300 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4 blur-sm"
             class="relative w-full max-w-[520px] rounded-[32px] bg-white p-8 sm:p-10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] dark:bg-gray-900 border border-slate-100 dark:border-gray-800/80 text-center overflow-hidden">
            
            <!-- Glowing background aura -->
            <div class="absolute -top-24 -left-24 h-48 w-48 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 h-48 w-48 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

            <!-- Animated Success SVG Badge -->
            <div class="mx-auto mb-6 flex items-center justify-center">
                <img src="{{ asset('images/success.svg') }}" alt="Success" class="h-20 w-20 sm:h-24 sm:w-24 transition-transform duration-300 hover:scale-105">
            </div>

            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white uppercase">
                SUCCESS !
            </h2>

            <p class="mt-3 text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-md mx-auto">
                {{ session('success') }}
            </p>

            <!-- Action Button -->
            <div class="mt-8 flex items-center justify-center">
                <button type="button"
                        @click="show = false"
                        class="group relative inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition-all duration-300 hover:from-emerald-500 hover:to-teal-500 hover:shadow-emerald-600/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                    <span>Tutup &amp; Lanjutkan</span>
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ⚠️ Global Pop-Up Alert Error Modal --}}
    @if(session('error'))
    <div x-data="{ show: false }"
         x-init="$nextTick(() => { setTimeout(() => show = true, 50); })"
         @keydown.escape.window="show = false"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        
        <!-- Backdrop overlay -->
        <div x-show="show"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="show = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"></div>

        <!-- Modal Card -->
        <div x-show="show"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-6 blur-md"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave="transition cubic-bezier(0.7, 0, 0.84, 0) duration-300 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4 blur-sm"
             class="relative w-full max-w-[520px] rounded-[32px] bg-white p-8 sm:p-10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] dark:bg-gray-900 border border-slate-100 dark:border-gray-800/80 text-center overflow-hidden">
            
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 ring-8 ring-red-50 dark:ring-red-900/10">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white uppercase">
                GAGAL !
            </h2>

            <p class="mt-3 text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-md mx-auto">
                {{ session('error') }}
            </p>

            <div class="mt-8 flex items-center justify-center">
                <button type="button"
                        @click="show = false"
                        class="group relative inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-600/25 transition-all duration-300 hover:from-red-500 hover:to-rose-500 hover:shadow-red-600/40 cursor-pointer">
                    <span>Tutup</span>
                </button>
            </div>
        </div>
    </div>
    @endif



    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('select:not([data-no-ts])').forEach(function(el) {
                if (el.tomselect) return;
                var origW = el.offsetWidth;
                try {
                    new TomSelect(el, {
                        maxItems: el.multiple ? undefined : 1,
                        hideSelected: true,
                        maxOptions: null,
                        allowEmptyOption: true,
                        plugins: el.multiple ? ['remove_button'] : [],
                        onChange: function() {
                            if (el.hasAttribute('data-auto-submit')) {
                                (el.form || el.closest('form')).submit();
                            }
                        },
                        onReady: function() {
                            if (origW > 0) this.wrapper.style.minWidth = origW + 'px';
                        }
                    });
                } catch(e) {
                    // fallback: leave as native
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
       
</body>
</html>