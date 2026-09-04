@extends('layouts.app')

@section('content')

{{-- Outer Container --}}
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    
    {{-- Card Header --}}
    <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Daftar Unit Periksa (OPD & Objek Pengawasan)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Kelola informasi OPD, BUMD, Sekolah, Desa, dan BLUD
            </p>
        </div>
        
        <a href="{{ route('unit-diperiksa.create') }}"
           class="shadow-theme-xs inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-xs font-semibold text-white hover:bg-blue-700 active:scale-[0.98] transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Unit Baru
        </a>
    </div>

    {{-- DataTable Three Layout Container --}}
    <div class="p-5 sm:p-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            
            {{-- Top Controls Bar: Filter & Search --}}
            <form method="GET" action="{{ route('unit-diperiksa.index') }}" id="filterForm">
                <div class="mb-4 flex flex-col gap-3 px-4 sm:flex-row sm:items-center sm:justify-between">
                    
                    {{-- Left Side: Show Entries & Dropdown Filters --}}
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400"> Show </span>
                            <div class="relative z-20 bg-transparent">
                                <select name="per_page" onchange="this.form.submit()" data-no-ts
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-medium text-gray-800 placeholder:text-gray-400 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                                    <option value="8" {{ request('per_page') == 8 ? 'selected' : '' }}>8</option>
                                    <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <span class="absolute top-1/2 right-2 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400"> entries </span>
                        </div>

                        {{-- Kategori Dropdown --}}
                        <div class="relative z-20">
                            <select name="kategori" onchange="this.form.submit()" data-no-ts
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-medium text-gray-800 appearance-none focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Semua Kategori</option>
                                @foreach(['BUMD', 'Sekolah', 'OPD', 'Desa', 'BLUD'] as $cat)
                                    <option value="{{ $cat }}" {{ request('kategori') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                            <span class="absolute top-1/2 right-2 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                                <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>

                        {{-- Kecamatan Dropdown --}}
                        <div class="relative z-20">
                            <select name="kecamatan" onchange="this.form.submit()" data-no-ts
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-medium text-gray-800 appearance-none focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Semua Kecamatan</option>
                                @foreach($kecamatanList as $kec)
                                    <option value="{{ $kec }}" {{ request('kecamatan') == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                                @endforeach
                            </select>
                            <span class="absolute top-1/2 right-2 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                                <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Right Side: Search Box & Reset --}}
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1 sm:flex-initial">
                            <button type="submit" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""></path>
                                </svg>
                            </button>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Search..."
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full sm:w-[220px] rounded-lg border border-gray-300 bg-transparent py-2 pr-3 pl-9 text-xs text-gray-800 placeholder:text-gray-400 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>

                        @if (request()->hasAny(['search','kategori','kecamatan','per_page']))
                        <a href="{{ route('unit-diperiksa.index') }}"
                           class="shadow-theme-xs inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                            Reset
                        </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Table Wrapper --}}
            <div class="max-w-full overflow-x-auto">
                <div class="min-w-[900px]">
                    
                    {{-- Table Header --}}
                    <div class="grid grid-cols-12 border-t border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-900/40 text-xs font-semibold text-gray-700 dark:text-gray-400">
                        
                        {{-- Nama Unit --}}
                        <div class="col-span-4 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <div class="flex w-full items-center justify-between">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Nama Unit / OPD
                                </span>
                                <span class="flex flex-col gap-0.5">
                                    <svg class="fill-gray-300 dark:fill-gray-700" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill=""></path>
                                    </svg>
                                    <svg class="fill-gray-300 dark:fill-gray-700" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill=""></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Kategori --}}
                        <div class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 justify-center dark:border-gray-800">
                            <div class="flex w-full items-center justify-between">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Kategori
                                </span>
                                <span class="flex flex-col gap-0.5">
                                    <svg class="fill-gray-300 dark:fill-gray-700" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill=""></path>
                                    </svg>
                                    <svg class="fill-gray-300 dark:fill-gray-700" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill=""></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Kecamatan & Alamat --}}
                        <div class="col-span-3 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <div class="flex w-full items-center justify-between">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Kecamatan & Alamat
                                </span>
                                <span class="flex flex-col gap-0.5">
                                    <svg class="fill-gray-300 dark:fill-gray-700" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill=""></path>
                                    </svg>
                                    <svg class="fill-gray-300 dark:fill-gray-700" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill=""></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Action --}}
                        <div class="col-span-3 flex items-center px-4 py-3 justify-end">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-400">
                                Action
                            </span>
                        </div>
                    </div>

                    {{-- Table Body --}}
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data as $item)
                        @php
                            $badgeClass = match($item->kategori) {
                                'OPD'     => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/25 dark:text-indigo-400',
                                'Desa'    => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/25 dark:text-emerald-400',
                                'Sekolah' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/25 dark:text-cyan-400',
                                'BUMD'    => 'bg-amber-50 text-amber-600 dark:bg-amber-900/25 dark:text-amber-400',
                                'BLUD'    => 'bg-purple-50 text-purple-600 dark:bg-purple-900/25 dark:text-purple-400',
                                default   => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            };
                        @endphp

                        <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50/60 dark:hover:bg-gray-900/40 transition-colors">
                            
                            {{-- Nama Unit --}}
                            <div class="col-span-4 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                                <div>
                                    <a href="{{ route('unit-diperiksa.show', $item->id) }}" class="text-xs font-bold text-gray-800 dark:text-white/90 hover:text-blue-600 dark:hover:text-blue-400 block truncate">
                                        {{ $item->nama_unit }}
                                    </a>
                                    @if($item->telepon)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        📞 {{ $item->telepon }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Kategori --}}
                            <div class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3 justify-center dark:border-gray-800">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium uppercase tracking-wider {{ $badgeClass }}">
                                    {{ $item->kategori ?? '-' }}
                                </span>
                            </div>

                            {{-- Kecamatan & Alamat --}}
                            <div class="col-span-3 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                                <div>
                                    <p class="text-xs font-medium text-gray-700 dark:text-gray-400">
                                        📍 {{ $item->nama_kecamatan ?? 'Kecamatan tidak diatur' }}
                                    </p>
                                    @if($item->alamat)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[220px]" title="{{ $item->alamat }}">
                                        {{ Str::limit($item->alamat, 35) }}
                                    </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Action --}}
                            <div class="col-span-3 flex items-center px-4 py-3 justify-end">
                                <div class="flex items-center gap-2">
                                    {{-- Detail Eye Button --}}
                                    <a href="{{ route('unit-diperiksa.show', $item->id) }}" title="Detail Unit"
                                       class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10 4.5C5.5 4.5 2 10 2 10C2 10 5.5 15.5 10 15.5C14.5 15.5 18 10 18 10C18 10 14.5 4.5 10 4.5ZM10 13.5C8.07 13.5 6.5 11.93 6.5 10C6.5 8.07 8.07 6.5 10 6.5C11.93 6.5 13.5 8.07 13.5 10C13.5 11.93 11.93 13.5 10 13.5ZM10 8.5C9.17 8.5 8.5 9.17 8.5 10C8.5 10.83 9.17 11.5 10 11.5C10.83 11.5 11.5 10.83 11.5 10C11.5 9.17 10.83 8.5 10 8.5Z" fill=""></path>
                                        </svg>
                                    </a>

                                    {{-- Delete Button (Template Trash Icon 21x21) --}}
                                    <form action="{{ route('unit-diperiksa.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data unit periksa ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus Unit"
                                                class="hover:text-red-500 dark:hover:text-red-500 text-gray-500 dark:text-gray-400 p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                            <svg class="fill-current" width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.04142 4.29199C7.04142 3.04935 8.04878 2.04199 9.29142 2.04199H11.7081C12.9507 2.04199 13.9581 3.04935 13.9581 4.29199V4.54199H16.1252H17.166C17.5802 4.54199 17.916 4.87778 17.916 5.29199C17.916 5.70621 17.5802 6.04199 17.166 6.04199H16.8752V8.74687V13.7469V16.7087C16.8752 17.9513 15.8678 18.9587 14.6252 18.9587H6.37516C5.13252 18.9587 4.12516 17.9513 4.12516 16.7087V13.7469V8.74687V6.04199H3.8335C3.41928 6.04199 3.0835 5.70621 3.0835 5.29199C3.0835 4.87778 3.41928 4.54199 3.8335 4.54199H4.87516H7.04142V4.29199ZM15.3752 13.7469V8.74687V6.04199H13.9581H13.2081H7.79142H7.04142H5.62516V8.74687V13.7469V16.7087C5.62516 17.1229 5.96095 17.4587 6.37516 17.4587H14.6252C15.0394 17.4587 15.3752 17.1229 15.3752 16.7087V13.7469ZM8.54142 4.54199H12.4581V4.29199C12.4581 3.87778 12.1223 3.54199 11.7081 3.54199H9.29142C8.87721 3.54199 8.54142 3.87778 8.54142 4.29199V4.54199ZM8.8335 8.50033C9.24771 8.50033 9.5835 8.83611 9.5835 9.25033V14.2503C9.5835 14.6645 9.24771 15.0003 8.8335 15.0003C8.41928 14.0003 8.0835 13.6645 8.0835 13.2503V9.25033C8.0835 8.83611 8.41928 8.50033 8.8335 8.50033ZM12.9168 9.25033C12.9168 8.83611 12.581 8.50033 12.1668 8.50033C11.7526 8.50033 11.4168 8.83611 11.4168 9.25033V14.2503C11.4168 14.6645 11.7526 15.0003 12.1668 15.0003C12.581 15.0003 12.9168 14.6645 12.9168 14.2503V9.25033Z" fill=""></path>
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Edit Button (Template Pencil Icon 21x21) --}}
                                    <a href="{{ route('unit-diperiksa.edit', $item->id) }}" title="Edit Unit"
                                       class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white/90 p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <svg class="fill-current" width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.0911 3.53206C16.2124 2.65338 14.7878 2.65338 13.9091 3.53206L5.6074 11.8337C5.29899 12.1421 5.08687 12.5335 4.99684 12.9603L4.26177 16.445C4.20943 16.6931 4.286 16.9508 4.46529 17.1301C4.64458 17.3094 4.90232 17.3859 5.15042 17.3336L8.63507 16.5985C9.06184 16.5085 9.45324 16.2964 9.76165 15.988L18.0633 7.68631C18.942 6.80763 18.942 5.38301 18.0633 4.50433L17.0911 3.53206ZM14.9697 4.59272C15.2626 4.29982 15.7375 4.29982 16.0304 4.59272L17.0027 5.56499C17.2956 5.85788 17.2956 6.33276 17.0027 6.62565L16.1043 7.52402L14.0714 5.49109L14.9697 4.59272ZM13.0107 6.55175L6.66806 12.8944C6.56526 12.9972 6.49455 13.1277 6.46454 13.2699L5.96704 15.6283L8.32547 15.1308C8.46772 15.1008 8.59819 15.0301 8.70099 14.9273L15.0436 8.58468L13.0107 6.55175Z" fill=""></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="px-6 py-12 text-center text-xs text-gray-400">
                            📋 Tidak ada data unit periksa ditemukan.
                        </div>
                        @endforelse
                    </div>

                </div>
            </div>

            {{-- Pagination Controls --}}
            <div class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
                    <p class="border-b border-gray-100 pb-3 text-center text-xs font-medium text-gray-500 xl:border-b-0 xl:pb-0 xl:text-left dark:border-gray-800 dark:text-gray-400">
                        Showing <span class="font-bold text-gray-800 dark:text-white">{{ $data->firstItem() ?? 0 }}</span> to
                        <span class="font-bold text-gray-800 dark:text-white">{{ $data->lastItem() ?? 0 }}</span> of
                        <span class="font-bold text-gray-800 dark:text-white">{{ $data->total() }}</span> entries
                    </p>
                    <div class="flex items-center justify-center xl:justify-end">
                        {{ $data->links('vendor.pagination.custom-tailwind') }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function dismissAlert() {
        const el = document.getElementById('alert-success');
        if (el) { el.classList.add('opacity-0'); setTimeout(() => el.remove(), 500); }
    }
    setTimeout(dismissAlert, 5000);
</script>

@endsection
