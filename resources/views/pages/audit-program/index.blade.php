@extends('layouts.app')

@section('content')

{{-- Main TailAdmin Table Container --}}
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    
    {{-- Card Header --}}
    <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Program Kerja Pengawasan Tahunan (PKPT)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Monitoring progres audit, sub-program, dan penerbitan LHP
            </p>
        </div>
        
        <a href="{{ route('audit-program.create') }}"
           class="shadow-theme-xs inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-xs font-semibold text-white hover:bg-blue-700 active:scale-[0.98] transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah PKPT Baru
        </a>
    </div>

    {{-- DataTable Three Body --}}
    <div class="p-5 sm:p-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            
            {{-- Top Controls Bar: Show entries, Filter Kategori, Filter Status, Filter Tahun, Search & Reset --}}
            <form method="GET" action="{{ route('audit-program.index') }}" id="filterForm" class="mb-4 px-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    
                    {{-- Left Controls: Entries Per Page, Category & Status Select --}}
                    <div class="flex flex-wrap items-center gap-2.5">
                        
                        {{-- Entries Per Page --}}
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Show</span>
                            <div class="relative z-20">
                                <select name="per_page" onchange="this.form.submit()" data-no-ts
                                        class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                                    <option value="8" {{ request('per_page') == 8 ? 'selected' : '' }}>8</option>
                                    <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                                    <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">entries</span>
                        </div>

                        {{-- Kategori Dropdown --}}
                        <div class="relative z-20">
                            <select name="kategori" onchange="this.form.submit()" data-no-ts
                                    class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Semua Kategori</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                @endforeach
                            </select>
                            <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                                <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>

                        {{-- Status Dropdown --}}
                        <div class="relative z-20">
                            <select name="status" onchange="this.form.submit()" data-no-ts
                                    class="appearance-none cursor-pointer dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent py-1.5 pr-8 pl-3 text-xs font-semibold text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="berjalan" {{ request('status') === 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                            <span class="absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-500 pointer-events-none dark:text-gray-400">
                                <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>

                        {{-- Tahun Picker Filter --}}
                        <div class="relative z-20"
                             x-data="yearPickerFilter({{ (int) (request('tahun') ?: 0) }}, {{ (int) date('Y') }})"
                             x-init="init()">
                            <input type="hidden" name="tahun" :value="selected || ''">
                            <button type="button" @click="open = !open"
                                    class="shadow-theme-xs flex h-9 items-center justify-between gap-2 rounded-lg border border-gray-300 bg-transparent px-3 text-xs font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <span class="flex items-center gap-1.5">
                                    <span>📅</span>
                                    <span x-text="label"></span>
                                </span>
                                <svg class="h-3 w-3 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" x-cloak @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-0 top-full z-50 mt-1 w-64 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
                                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-3 py-2">
                                    <button type="button" @click.stop="pageStart -= 12" class="text-gray-400 hover:text-gray-700 dark:hover:text-white">&larr;</button>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="pageStart + ' – ' + (pageStart + 11)"></span>
                                    <button type="button" @click.stop="pageStart += 12" class="text-gray-400 hover:text-gray-700 dark:hover:text-white">&rarr;</button>
                                </div>
                                <div class="grid grid-cols-4 gap-1 p-2">
                                    <template x-for="y in years" :key="y">
                                        <button type="button" @click.stop="pick(y)"
                                                class="rounded-lg py-1.5 text-[11px] font-semibold transition-all"
                                                :class="selected === y ? 'bg-blue-600 text-white font-bold' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'">
                                            <span x-text="y"></span>
                                        </button>
                                    </template>
                                </div>
                                <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 px-3 py-1.5">
                                    <button type="button" @click.stop="pick(null)" class="text-[10px] font-bold text-gray-400 hover:text-red-500">✕ Semua</button>
                                    <button type="button" @click.stop="pick(now)" class="text-[10px] font-bold text-blue-600 dark:text-blue-400">Tahun Ini</button>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Right Controls: Search Box & Reset --}}
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

                        @if (request()->hasAny(['search','tahun','kategori','status','per_page']))
                        <a href="{{ route('audit-program.index') }}"
                           class="shadow-theme-xs inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                            Reset
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

                {{-- Table Area --}}
                <div class="max-w-full overflow-x-auto">
                    <div class="min-w-[1000px]">
                        @php
                            $sortUrl = fn(string $col) => request()->fullUrlWithQuery([
                                'sort'      => $col,
                                'direction' => ($sort === $col && $direction === 'desc') ? 'asc' : 'desc',
                                'page'      => 1,
                            ]);
                            $renderSortIcons = function(string $col) use ($sort, $direction): string {
                                $activeAsc = ($sort === $col && $direction === 'asc') ? 'fill-blue-600 dark:fill-blue-400' : 'fill-gray-300 dark:fill-gray-700';
                                $activeDesc = ($sort === $col && $direction === 'desc') ? 'fill-blue-600 dark:fill-blue-400' : 'fill-gray-300 dark:fill-gray-700';
                                return '<span class="flex flex-col gap-0.5">
                                    <svg class="'.$activeAsc.'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill=""></path></svg>
                                    <svg class="'.$activeDesc.'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill=""></path></svg>
                                </span>';
                            };
                        @endphp

                        {{-- Table Header --}}
                        <div class="grid grid-cols-12 border-t border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-900/40 text-xs font-semibold text-gray-700 dark:text-gray-400">
                            
                            {{-- Program Column (col-span-3) --}}
                            <div class="col-span-3 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex w-full items-center justify-between">
                                    <a href="{{ $sortUrl('nama_program') }}" class="flex items-center gap-2 hover:text-blue-600 dark:hover:text-blue-400">
                                        <span>Nama Program</span>
                                    </a>
                                    <a href="{{ $sortUrl('nama_program') }}">
                                        {!! $renderSortIcons('nama_program') !!}
                                    </a>
                                </div>
                            </div>

                            {{-- Kategori Column (col-span-2) --}}
                            <div class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex w-full items-center justify-between">
                                    <a href="{{ $sortUrl('kategori') }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        Kategori
                                    </a>
                                    <a href="{{ $sortUrl('kategori') }}">
                                        {!! $renderSortIcons('kategori') !!}
                                    </a>
                                </div>
                            </div>

                            {{-- Sub Program Column (col-span-1) --}}
                            <div class="col-span-1 flex items-center border-r border-gray-200 px-3 py-3 justify-center dark:border-gray-800">
                                <span>Sub Program</span>
                            </div>

                            {{-- Progress Column (col-span-2) --}}
                            <div class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                <span>Progress Audit</span>
                            </div>

                            {{-- Status Column (col-span-1) --}}
                            <div class="col-span-1 flex items-center border-r border-gray-200 px-3 py-3 justify-center dark:border-gray-800">
                                <span>Status</span>
                            </div>

                            {{-- Approval Column (col-span-1) --}}
                            <div class="col-span-1 flex items-center border-r border-gray-200 px-3 py-3 justify-center dark:border-gray-800">
                                <span>Approval</span>
                            </div>

                            {{-- Action Column (col-span-2) --}}
                            <div class="col-span-2 flex items-center justify-end px-4 py-3">
                                <span>Aksi</span>
                            </div>
                        </div>

                        {{-- Table Body --}}
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($data as $item)
                            @php
                                $percent = $item->progress_persen ?? 0;
                                $badgeClass = match($item->kategori) {
                                    'PKPT'   => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/25 dark:text-indigo-400',
                                    'BPK'    => 'bg-amber-50 text-amber-600 dark:bg-amber-900/25 dark:text-amber-400',
                                    'BPKP'   => 'bg-rose-50 text-rose-600 dark:bg-rose-900/25 dark:text-rose-400',
                                    'ITPROV' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/25 dark:text-cyan-400',
                                    'ITDA'   => 'bg-teal-50 text-teal-600 dark:bg-teal-900/25 dark:text-teal-400',
                                    default  => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                };
                                $displayStatus = $item->status_dinamis;
                                $statusBadge = match($displayStatus) {
                                    'selesai' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
                                    'berjalan' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                };
                                $approvalBadge = match($item->approval_status) {
                                    'disetujui' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/25 dark:text-emerald-400',
                                    'menunggu'  => 'bg-amber-50 text-amber-600 dark:bg-amber-900/25 dark:text-amber-400',
                                    'ditolak'   => 'bg-rose-50 text-rose-600 dark:bg-rose-900/25 dark:text-rose-400',
                                    default     => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                };
                            @endphp

                            <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800/80 hover:bg-gray-50/60 dark:hover:bg-gray-900/40 transition-colors">
                                
                                {{-- Program Name --}}
                                <div class="col-span-3 flex items-center border-r border-gray-100 px-4 py-3.5 dark:border-gray-800">
                                    <div class="min-w-0">
                                        <a href="{{ route('audit-program.show', $item->id) }}" class="text-xs font-bold text-gray-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400 truncate block">
                                            {{ $item->nama_program }}
                                        </a>
                                        <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">
                                            Tahun {{ $item->tahun }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Kategori --}}
                                <div class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3.5 dark:border-gray-800">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                                        {{ $item->kategori ?? '-' }}
                                    </span>
                                </div>

                                {{-- Sub Program Count --}}
                                <div class="col-span-1 flex items-center border-r border-gray-100 px-3 py-3.5 justify-center dark:border-gray-800">
                                    <span class="inline-flex h-6 min-w-[24px] items-center justify-center rounded-full bg-slate-100 px-2 text-xs font-bold text-slate-700 dark:bg-gray-800 dark:text-slate-300">
                                        {{ $item->details_count ?? 0 }}
                                    </span>
                                </div>

                                {{-- Progress --}}
                                <div class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3.5 dark:border-gray-800">
                                    <div class="w-full">
                                        <div class="flex items-center justify-between text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                            <span>{{ $percent }}%</span>
                                            <span class="text-[10px] text-gray-400 font-medium">{{ $item->sudah_lhp_count ?? 0 }}/{{ $item->details_count ?? 0 }} Selesai</span>
                                        </div>
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                            <div class="h-full rounded-full transition-all duration-500 {{ $percent >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ min(100, $percent) }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="col-span-1 flex items-center border-r border-gray-100 px-3 py-3.5 justify-center dark:border-gray-800">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $statusBadge }}">
                                        {{ $displayStatus }}
                                    </span>
                                </div>

                                {{-- Approval --}}
                                <div class="col-span-1 flex flex-col items-center border-r border-gray-100 px-3 py-3.5 justify-center dark:border-gray-800">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $approvalBadge }}">
                                        @if($item->isApproved())
                                            ✓ Disetujui
                                        @elseif($item->isWaiting())
                                            ⏳ Menunggu
                                        @elseif($item->approval_status === \App\Models\AuditProgram::APPROVAL_DITOLAK)
                                            ✕ Ditolak
                                        @else
                                            Draft
                                        @endif
                                    </span>
                                    @if($item->isApproved() && $item->approved_pdf)
                                    <a href="{{ Storage::url($item->approved_pdf) }}" target="_blank" class="mt-1 text-[10px] font-bold text-emerald-600 hover:underline dark:text-emerald-400">
                                        📄 PDF
                                    </a>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="col-span-2 flex items-center justify-end px-4 py-3.5">
                                    <div class="flex items-center gap-2">
                                        {{-- Detail Button --}}
                                        <a href="{{ route('audit-program.show', $item->id) }}"
                                           title="Detail Sub Program"
                                           class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10 4.5C5.5 4.5 2 10 2 10C2 10 5.5 15.5 10 15.5C14.5 15.5 18 10 18 10C18 10 14.5 4.5 10 4.5ZM10 13.5C8.07 13.5 6.5 11.93 6.5 10C6.5 8.07 8.07 6.5 10 6.5C11.93 6.5 13.5 8.07 13.5 10C13.5 11.93 11.93 13.5 10 13.5ZM10 8.5C9.17 8.5 8.5 9.17 8.5 10C8.5 10.83 9.17 11.5 10 11.5C10.83 11.5 11.5 10.83 11.5 10C11.5 9.17 10.83 8.5 10 8.5Z" fill=""></path>
                                            </svg>
                                        </a>

                                        {{-- Edit Button --}}
                                        <a href="{{ route('audit-program.edit', $item->id) }}"
                                           title="Edit Program"
                                           class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.0911 2.53206C15.2124 1.65338 13.7878 1.65338 12.9091 2.53206L4.6074 10.8337C4.29899 11.1421 4.08687 11.5335 3.99684 11.9603L3.26177 15.445C3.20943 15.6931 3.286 15.9508 3.46529 16.1301C3.64458 16.3094 3.90232 16.3859 4.15042 16.3336L7.63507 15.5985C8.06184 15.5085 8.45324 15.2964 8.76165 14.988L17.0633 6.68631C17.942 5.80763 17.942 4.38301 17.0633 3.50433L16.0911 2.53206ZM13.9697 3.59272C14.2626 3.29982 14.7375 3.29982 15.0304 3.59272L16.0027 4.56499C16.2956 4.85788 16.2956 5.33276 16.0027 5.62565L15.1043 6.52402L13.0714 4.49109L13.9697 3.59272ZM12.0107 5.55175L5.66806 11.8944C5.56526 11.9972 5.49455 12.1277 5.46454 12.2699L4.96704 14.6283L7.32547 14.1308C7.46772 14.1008 7.59819 14.0301 7.70099 13.9273L14.0436 7.58468L12.0107 5.55175Z" fill=""></path>
                                            </svg>
                                        </a>

                                        {{-- Delete Form Button --}}
                                        <form action="{{ route('audit-program.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus PKPT ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Program" class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04142 3.29199C6.04142 2.04935 7.04878 1.04199 8.29142 1.04199H10.7081C11.9507 1.04199 12.9581 2.04935 12.9581 3.29199V3.54199H15.1252H16.166C16.5802 3.54199 16.916 3.87778 16.916 4.29199C16.916 4.70621 16.5802 5.04199 16.166 5.04199H15.8752V7.74687V12.7469V15.7087C15.8752 16.9513 14.8678 17.9587 13.6252 17.9587H5.37516C4.13252 17.9587 3.12516 16.9513 3.12516 15.7087V12.7469V7.74687V5.04199H2.8335C2.41928 5.04199 2.0835 4.70621 2.0835 4.29199C2.0835 3.87778 2.41928 3.54199 2.8335 3.54199H3.87516H6.04142V3.29199ZM14.3752 12.7469V7.74687V5.04199H12.9581H12.2081H6.79142H6.04142H4.62516V7.74687V12.7469V15.7087C4.62516 16.1229 4.96095 16.4587 5.37516 16.4587H13.6252C14.0394 16.4587 14.3752 16.1229 14.3752 15.7087V12.7469ZM7.54142 3.54199H11.4581V3.29199C11.4581 2.87778 11.1223 2.54199 10.7081 2.54199H8.29142C7.87721 2.54199 7.54142 2.87778 7.54142 3.29199V3.54199ZM7.8335 7.50033C8.24771 7.50033 8.5835 7.83611 8.5835 8.25033V13.2503C8.5835 13.6645 8.24771 14.0003 7.8335 14.0003C7.41928 14.0003 7.0835 13.6645 7.0835 13.2503V8.25033C7.0835 7.83611 7.41928 7.50033 7.8335 7.50033ZM11.9168 8.25033C11.9168 7.83611 11.581 7.50033 11.1668 7.50033C10.7526 7.50033 10.4168 7.83611 10.4168 8.25033V13.2503C10.4168 13.6645 10.7526 14.0003 11.1668 14.0003C11.581 14.0003 11.9168 13.6645 11.9168 13.2503V8.25033Z" fill=""></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="px-6 py-12 text-center text-xs text-gray-400">
                                📋 Data Program Kerja Pengawasan Tahunan belum tersedia.
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
    if (el) {
        el.classList.add('opacity-0', 'scale-95');
        setTimeout(() => { el.remove(); }, 300);
    }
}
setTimeout(() => { dismissAlert(); }, 4000);

// Year Picker Filter
function yearPickerFilter(initial, now) {
    return {
        selected: initial || null,
        now: now,
        open: false,
        pageStart: 0,

        init() {
            const base = this.selected || this.now;
            this.pageStart = base - 3;
        },

        get years() {
            return Array.from({ length: 12 }, (_, i) => this.pageStart + i);
        },

        get label() {
            if (!this.selected) return 'Semua Tahun';
            const suffix = this.selected === this.now     ? ' (Tahun Ini)'
                         : this.selected === this.now + 1 ? ' (Tahun Depan)'
                         : this.selected === this.now - 1 ? ' (Tahun Lalu)'
                         : '';
            return this.selected + suffix;
        },

        pick(y) {
            this.selected = y;
            this.open = false;
            this.$el.closest('form').submit();
        }
    };
}
</script>

<style>[x-cloak] { display: none !important; }</style>

@endsection