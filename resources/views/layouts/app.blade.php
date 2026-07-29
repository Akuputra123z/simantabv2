<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    
    <title>{{ $title ?? 'Dashboard' }} | Admin</title>
    

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
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