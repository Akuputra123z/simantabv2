@extends('layouts.app')

@php
    $lhpOptions = $lhps->map(function ($l) {
        $nomor = $l->nomor_lhp;
        $unit = $l->unitDiperiksa?->nama_unit ?? '-';
        $program = $l->auditAssignment?->auditProgramDetail?->auditProgram?->nama_program ?? '-';
        return [
            'id'        => $l->id,
            'nomor_lhp' => $nomor,
            'unit'      => $unit,
            'program'   => $program,
            'label'     => '[' . $nomor . '] ' . $unit . ' — ' . $program,
        ];
    })->values();

    $initialRekomList = ($initialRekomendasis ?? collect())->values();
@endphp

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-12"
     x-data="tindakLanjutCreateForm({
         lhps: {{ Js::from($lhpOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }},
         initialRekomendasis: {{ Js::from($initialRekomList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }},
         initialLhpId: {{ Js::from(old('lhp_id', $selectedLhpId ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }},
         initialRekomId: {{ Js::from(old('recommendation_id', $selectedRekomId ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }},
         initialJenis: {{ Js::from(old('jenis_penyelesaian', 'setor_kas'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }},
         initialNilai: {{ Js::from(old('nilai_tindak_lanjut', ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }},
         verifikators: {{ Js::from($verifikatorUsers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }},
         initialVerifikatorId: {{ Js::from(old('diverifikasi_oleh', auth()->id()), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
     })">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-4 dark:border-gray-800">
        <div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-blue-600 transition-colors">Monitoring Tindak Lanjut</a>
                <template x-if="lhpId">
                    <span class="flex items-center gap-2">
                        <span>/</span>
                        <a :href="'{{ url('/tindak-lanjuts/lhp') }}/' + lhpId" class="hover:text-blue-600 transition-colors">Detail LHP</a>
                    </span>
                </template>
                <span>/</span>
                <span class="text-gray-900 dark:text-white font-medium">Input Tindak Lanjut Baru</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Pencatatan Tindak Lanjut Rekomendasi
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Input tindak lanjut dari OPD atau Inspektorat berdasarkan LHP, rekomendasi temuan, dan metode penyelesaian.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <template x-if="lhpId">
                <a :href="'{{ url('/tindak-lanjuts/lhp') }}/' + lhpId"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm transition-colors">
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke LHP
                </a>
            </template>
            <template x-if="!lhpId">
                <a href="{{ route('tindak-lanjuts.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm transition-colors">
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Batal
                </a>
            </template>
        </div>
    </div>

    {{-- Error Validation Summary --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-900/20">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-xs font-bold text-red-800 dark:text-red-300">Terdapat kesalahan pengisian formulir:</h3>
                    <ul class="mt-1 list-disc list-inside text-xs text-red-700 dark:text-red-400 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Accessible Hidden Pre-rendered List for SSR / SEO / Tests --}}
    <div class="sr-only" aria-hidden="true">
        @foreach($lhpOptions as $item)
            <span>{{ $item['nomor_lhp'] }} {{ $item['unit'] }} {{ $item['program'] }}</span>
        @endforeach
        @foreach($initialRekomList as $r)
            <span>{{ $r['kode'] }} {{ $r['uraian'] }}</span>
        @endforeach
    </div>

    {{-- Main Form Card --}}
    <form action="{{ route('tindak-lanjuts.store') }}" method="POST" id="main-tl-form" enctype="multipart/form-data" @submit="handleSubmit($event)">
        @csrf

        {{-- Hidden input for recommendation_id guarantees reliable form submission --}}
        <input type="hidden" name="recommendation_id" id="hidden_recommendation_id" :value="selectedRekomId">

        <div class="space-y-6">

            {{-- ═══════════════════════════════════════════════════════════════════
                 BAGIAN 1: PEMILIHAN LHP & REKOMENDASI (SEARCHABLE & SCROLLABLE)
            ═══════════════════════════════════════════════════════════════════ --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">1</span>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Pilih Objek Pengawasan (LHP & Rekomendasi)</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    {{-- ── 1. DROPDOWN LHP (SEARCHABLE & SCROLLABLE) ── --}}
                    <div class="relative" @click.outside="openLhpDropdown = false" @keydown.escape="openLhpDropdown = false">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Laporan Hasil Pemeriksaan (LHP) <span class="text-red-500">*</span>
                        </label>
                        
                        <input type="hidden" name="lhp_id" :value="lhpId">

                        {{-- Trigger Button --}}
                        <button type="button"
                                @click="openLhpDropdown = !openLhpDropdown; if(openLhpDropdown) $nextTick(() => $refs.searchLhpInput?.focus())"
                                class="flex items-center justify-between w-full rounded-lg border bg-white px-3 py-2 text-xs font-medium text-left transition-colors dark:bg-gray-900 dark:text-white"
                                :class="openLhpDropdown ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-300 dark:border-gray-700 hover:border-gray-400'">
                            <span class="truncate" :class="selectedLhp ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-400'">
                                <span x-text="selectedLhp ? selectedLhp.label : '-- Cari & Pilih LHP --'"></span>
                            </span>
                            <svg class="h-4 w-4 text-gray-400 shrink-0 ml-2 transition-transform duration-200"
                                 :class="{'rotate-180': openLhpDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Floating Dropdown Panel --}}
                        <div x-show="openLhpDropdown" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 right-0 z-50 mt-1 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
                            
                            {{-- Sticky Search Input --}}
                            <div class="p-2 border-b border-gray-100 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-900/80 sticky top-0 z-10">
                                <div class="relative">
                                    <svg class="absolute left-3 top-2.5 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" x-ref="searchLhpInput" x-model="searchLhp"
                                           placeholder="Cari nomor LHP, entitas OPD / Desa, program..."
                                           class="w-full rounded-lg border border-gray-200 bg-white pl-8 pr-7 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                    <template x-if="searchLhp">
                                        <button type="button" @click="searchLhp = ''" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Scrollable List Container --}}
                            <ul class="max-h-60 overflow-y-auto py-1 divide-y divide-gray-50 dark:divide-gray-800/50">
                                <template x-for="item in filteredLhps" :key="item.id">
                                    <li>
                                        <button type="button" @click="selectLhp(item.id)"
                                                class="w-full text-left px-3 py-2.5 text-xs transition-colors flex items-start justify-between gap-2"
                                                :class="String(item.id) === String(lhpId)
                                                    ? 'bg-blue-50 text-blue-800 font-semibold dark:bg-blue-950/40 dark:text-blue-200'
                                                    : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="item.nomor_lhp"></span>
                                                    <span class="inline-flex items-center rounded px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-[10px] font-semibold text-gray-700 dark:text-gray-300" x-text="item.unit"></span>
                                                </div>
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5" x-text="item.program"></p>
                                            </div>
                                            <template x-if="String(item.id) === String(lhpId)">
                                                <svg class="h-4 w-4 text-blue-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </template>
                                        </button>
                                    </li>
                                </template>
                                <template x-if="filteredLhps.length === 0">
                                    <li class="px-3 py-5 text-center text-xs text-gray-400">
                                        Tidak ada LHP yang sesuai dengan pencarian
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Cari dan pilih LHP berdasarkan nomor LHP atau entitas OPD / Desa.</p>
                    </div>

                    {{-- ── 2. DROPDOWN REKOMENDASI (SEARCHABLE & SCROLLABLE) ── --}}
                    <div class="relative" @click.outside="openRekomDropdown = false" @keydown.escape="openRekomDropdown = false">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Rekomendasi yang Ditindaklanjuti <span class="text-red-500">*</span>
                        </label>

                        {{-- Trigger Button --}}
                        <button type="button"
                                @click="if(lhpId && !loadingRekom && rekomendasis.length > 0) { openRekomDropdown = !openRekomDropdown; if(openRekomDropdown) $nextTick(() => $refs.searchRekomInput?.focus()) }"
                                :disabled="!lhpId || loadingRekom || rekomendasis.length === 0"
                                class="flex items-center justify-between w-full rounded-lg border bg-white px-3 py-2 text-xs font-medium text-left transition-colors dark:bg-gray-900 dark:text-white disabled:bg-gray-100 disabled:cursor-not-allowed dark:disabled:bg-gray-800"
                                :class="(rekomError || {{ $errors->has('recommendation_id') ? 'true' : 'false' }})
                                    ? 'border-red-500 ring-1 ring-red-500'
                                    : (openRekomDropdown ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-300 dark:border-gray-700 hover:border-gray-400')">
                            <span class="truncate" :class="selectedRekom ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-400'">
                                <span x-text="loadingRekom ? 'Memuat rekomendasi...' : (!lhpId ? '-- Pilih LHP terlebih dahulu --' : (rekomendasis.length === 0 ? '-- Tidak ada rekomendasi di LHP ini --' : (selectedRekom ? selectedRekom.label : '-- Cari & Pilih Rekomendasi --')))"></span>
                            </span>
                            <svg class="h-4 w-4 text-gray-400 shrink-0 ml-2 transition-transform duration-200"
                                 :class="{'rotate-180': openRekomDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Floating Dropdown Panel --}}
                        <div x-show="openRekomDropdown" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 right-0 z-50 mt-1 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
                            
                            {{-- Sticky Search Input --}}
                            <div class="p-2 border-b border-gray-100 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-900/80 sticky top-0 z-10">
                                <div class="relative">
                                    <svg class="absolute left-3 top-2.5 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" x-ref="searchRekomInput" x-model="searchRekom"
                                           placeholder="Cari kode rekomendasi, temuan, atau uraian..."
                                           class="w-full rounded-lg border border-gray-200 bg-white pl-8 pr-7 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                    <template x-if="searchRekom">
                                        <button type="button" @click="searchRekom = ''" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Scrollable List Container --}}
                            <ul class="max-h-60 overflow-y-auto py-1 divide-y divide-gray-50 dark:divide-gray-800/50">
                                <template x-for="r in filteredRekomendasis" :key="r.id">
                                    <li>
                                        <button type="button" @click="selectRekom(r.id)"
                                                class="w-full text-left px-3 py-2.5 text-xs transition-colors flex items-start justify-between gap-2"
                                                :class="String(r.id) === String(selectedRekomId)
                                                    ? 'bg-blue-50 text-blue-800 font-semibold dark:bg-blue-950/40 dark:text-blue-200'
                                                    : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="'[' + r.kode + ']'"></span>
                                                    <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold"
                                                          :class="r.jenis === 'uang' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'"
                                                          x-text="r.jenis === 'uang' ? 'Sisa: ' + formatRupiah(r.nilai_sisa) : r.jenis"></span>
                                                    <template x-if="r.temuan_kode && r.temuan_kode !== '-'">
                                                        <span class="text-[10px] text-gray-400 font-mono" x-text="'Temuan: ' + r.temuan_kode"></span>
                                                    </template>
                                                </div>
                                                <p class="text-[11px] text-gray-600 dark:text-gray-400 line-clamp-2 mt-1 leading-relaxed" x-text="r.uraian"></p>
                                            </div>
                                            <template x-if="String(r.id) === String(selectedRekomId)">
                                                <svg class="h-4 w-4 text-blue-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </template>
                                        </button>
                                    </li>
                                </template>
                                <template x-if="filteredRekomendasis.length === 0">
                                    <li class="px-3 py-5 text-center text-xs text-gray-400">
                                        Tidak ada rekomendasi yang sesuai pencarian
                                    </li>
                                </template>
                            </ul>
                        </div>

                        {{-- Error message display --}}
                        @error('recommendation_id')
                            <p class="mt-1 text-[11px] font-semibold text-red-600 flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p x-show="rekomError" x-text="rekomError" class="mt-1 text-[11px] font-semibold text-red-600 flex items-center gap-1" x-cloak>
                            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span x-text="rekomError"></span>
                        </p>

                        <template x-if="loadingRekom">
                            <p class="mt-1 text-[11px] text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Mengambil daftar rekomendasi LHP...
                            </p>
                        </template>
                    </div>

                </div>

                {{-- Live Rekomendasi Summary Card --}}
                <template x-if="selectedRekom">
                    <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50/50 p-3.5 text-xs dark:border-blue-900/40 dark:bg-blue-950/20">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="md:col-span-2">
                                <span class="font-bold text-blue-800 dark:text-blue-300 block mb-0.5">Uraian Rekomendasi:</span>
                                <p class="text-gray-800 dark:text-gray-200 line-clamp-3 leading-relaxed" x-text="selectedRekom.uraian"></p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                    Kode Rekomendasi: <span class="font-mono font-semibold" x-text="selectedRekom.kode"></span> &bull;
                                    Temuan: <span class="font-mono font-semibold" x-text="selectedRekom.temuan_kode"></span>
                                </p>
                            </div>
                            <div>
                                <span class="font-bold text-gray-500 dark:text-gray-400 block mb-0.5">Jenis & Status:</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold"
                                          :class="selectedRekom.jenis === 'uang' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'"
                                          x-text="selectedRekom.jenis === 'uang' ? 'Finansial (Uang)' : 'Non-Finansial (' + selectedRekom.jenis + ')'">
                                    </span>
                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                                          x-text="'Verif: ' + selectedRekom.status_verifikasi">
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="font-bold text-gray-500 dark:text-gray-400 block mb-0.5">Ringkasan Nilai:</span>
                                <template x-if="selectedRekom.jenis === 'uang'">
                                    <div class="space-y-0.5 text-[11px]">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Target:</span>
                                            <span class="font-bold font-mono" x-text="formatRupiah(selectedRekom.nilai_rekom)"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Disetor:</span>
                                            <span class="font-bold font-mono text-emerald-600" x-text="formatRupiah(selectedRekom.total_terbayar)"></span>
                                        </div>
                                        <div class="flex justify-between border-t border-blue-200 dark:border-blue-800 pt-0.5">
                                            <span class="font-bold text-red-600">Sisa:</span>
                                            <span class="font-bold font-mono text-red-600" x-text="formatRupiah(selectedRekom.nilai_sisa)"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="selectedRekom.jenis !== 'uang'">
                                    <p class="text-[11px] text-gray-600 dark:text-gray-400 italic">
                                        Tindak lanjut fisik / administratif (Tanpa nilai rupiah setoran).
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════
                 BAGIAN 2: METODE PENYELESAIAN & BUKTI FISIK
            ═══════════════════════════════════════════════════════════════════ --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">2</span>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Metode Penyelesaian & Dokumen Bukti</h2>
                </div>

                {{-- 4 Metode Penyelesaian Radio Cards --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Pilih Metode Penyelesaian <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        {{-- 1. Setor Kas --}}
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all text-xs"
                               :class="jenisPenyelesaian === 'setor_kas'
                                   ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20 dark:bg-blue-950/20 dark:border-blue-500'
                                   : 'border-gray-200 hover:border-gray-300 bg-white dark:bg-gray-900 dark:border-gray-700'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Setor Kas (STS)
                                </span>
                                <input type="radio" name="jenis_penyelesaian" value="setor_kas" x-model="jenisPenyelesaian" @change="onMethodChange('setor_kas')" class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                Setor langsung ke Kas Daerah / Kas Desa dengan Surat Tanda Setor (STS) atau slip transfer.
                            </p>
                        </label>

                        {{-- 2. Cicilan --}}
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all text-xs"
                               :class="jenisPenyelesaian === 'cicilan'
                                   ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20 dark:bg-blue-950/20 dark:border-blue-500'
                                   : 'border-gray-200 hover:border-gray-300 bg-white dark:bg-gray-900 dark:border-gray-700'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Cicilan / Bertahap
                                </span>
                                <input type="radio" name="jenis_penyelesaian" value="cicilan" x-model="jenisPenyelesaian" @change="onMethodChange('cicilan')" class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                Pembayaran angsuran bertahap dengan rencana termin dan komitmen jatuh tempo.
                            </p>
                        </label>

                        {{-- 3. Pengembalian Barang --}}
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all text-xs"
                               :class="jenisPenyelesaian === 'pengembalian_barang'
                                   ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20 dark:bg-blue-950/20 dark:border-blue-500'
                                   : 'border-gray-200 hover:border-gray-300 bg-white dark:bg-gray-900 dark:border-gray-700'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    Pengembalian Aset
                                </span>
                                <input type="radio" name="jenis_penyelesaian" value="pengembalian_barang" x-model="jenisPenyelesaian" @change="onMethodChange('pengembalian_barang')" class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                Penyerahan fisik barang / aset / inventaris dengan Berita Acara Serah Terima (BAST).
                            </p>
                        </label>

                        {{-- 4. Perbaikan Administrasi --}}
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all text-xs"
                               :class="jenisPenyelesaian === 'perbaikan_administrasi'
                                   ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20 dark:bg-blue-950/20 dark:border-blue-500'
                                   : 'border-gray-200 hover:border-gray-300 bg-white dark:bg-gray-900 dark:border-gray-700'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Administrasi / SOP
                                </span>
                                <input type="radio" name="jenis_penyelesaian" value="perbaikan_administrasi" x-model="jenisPenyelesaian" @change="onMethodChange('perbaikan_administrasi')" class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                Pembenahan tata kelola, penyusunan LPJ, regulasi, SK, teguran, atau SOP perbaikan.
                            </p>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nomor Bukti Legalitas --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span x-text="nomorBuktiLabel"></span> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nomor_bukti" required
                               x-model="nomorBukti"
                               :placeholder="nomorBuktiPlaceholder"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Cantumkan nomor dokumen resmi untuk kemudahan verifikasi & audit silang.</p>
                    </div>

                    {{-- Nilai Tindak Lanjut --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Nominal Nilai Tindak Lanjut (Rp)
                            <template x-if="isFinancial">
                                <span class="text-red-500">*</span>
                            </template>
                        </label>

                        <template x-if="isFinancial">
                            <div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-500 pointer-events-none">Rp</span>
                                    <input type="text"
                                           x-model="displayNilai"
                                           @input="handleNilaiInput($event)"
                                           placeholder="0"
                                           class="w-full rounded-lg border bg-white pl-9 pr-3 py-2 text-xs font-bold text-gray-900 focus:outline-none dark:bg-gray-900 dark:text-white"
                                           :class="isNilaiExceeds ? 'border-red-500 focus:border-red-500 focus:ring-1 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700'">
                                    <input type="hidden" name="nilai_tindak_lanjut" :value="nilaiRaw">
                                </div>
                                <template x-if="isNilaiExceeds">
                                    <p class="mt-1 text-[11px] font-semibold text-red-600 flex items-center gap-1">
                                        <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        Nilai melebihi sisa kewajiban (<span x-text="formatRupiah(selectedRekom ? selectedRekom.nilai_sisa : 0)"></span>)
                                    </p>
                                </template>
                                <template x-if="!isNilaiExceeds">
                                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                        Total nilai yang disetor pada tindak lanjut ini.
                                    </p>
                                </template>
                            </div>
                        </template>

                        <template x-if="!isFinancial">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-800">
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Non-Finansial: Nilai diset Rp 0 otomatis.
                                </p>
                                <input type="hidden" name="nilai_tindak_lanjut" value="0">
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Subform Rencana Cicilan (Jika Metode Cicilan) --}}
                <div x-show="jenisPenyelesaian === 'cicilan'" x-cloak class="mt-4 rounded-xl border border-dashed border-blue-300 bg-blue-50/40 p-4 dark:border-blue-800 dark:bg-blue-950/20">
                    <h3 class="text-xs font-bold text-blue-900 dark:text-blue-300 mb-3 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Rencana Jadwal Cicilan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Jumlah Termin / Tenor Cicilan (Bulan)
                            </label>
                            <input type="number" name="jumlah_cicilan_rencana" min="1" max="120"
                                   value="{{ old('jumlah_cicilan_rencana') }}"
                                   placeholder="Contoh: 6"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Tanggal Mulai Cicilan
                            </label>
                            <input type="date" name="tanggal_mulai_cicilan"
                                   value="{{ old('tanggal_mulai_cicilan') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════
                 BAGIAN 3: VERIFIKASI & TANGGAL TARGET
            ═══════════════════════════════════════════════════════════════════ --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">3</span>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Jatuh Tempo, Status & Verifikator</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    {{-- Tanggal Jatuh Tempo --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_jatuh_tempo" required
                               value="{{ old('tanggal_jatuh_tempo', now()->addDays(60)->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Batas akhir toleransi tindak lanjut rekomendasi (standar 60 hari kerja).</p>
                    </div>

                    {{-- Status Verifikasi --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Status Verifikasi Awal <span class="text-red-500">*</span>
                        </label>
                        <select name="status_verifikasi" data-no-ts required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="menunggu_verifikasi" {{ old('status_verifikasi', 'menunggu_verifikasi') == 'menunggu_verifikasi' ? 'selected' : '' }}>
                                Menunggu Verifikasi
                            </option>
                            <option value="berjalan" {{ old('status_verifikasi') == 'berjalan' ? 'selected' : '' }}>
                                Berjalan (Dalam Proses / Cicilan)
                            </option>
                            <option value="lunas" {{ old('status_verifikasi') == 'lunas' ? 'selected' : '' }}>
                                Lunas / Tuntas Sesuai Rekomendasi
                            </option>
                        </select>
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Pilih status verifikasi tindak lanjut saat ini.</p>
                    </div>

                    {{-- Verifikator (Searchable & Scrollable) --}}
                    <div class="relative" @click.outside="openVerifikatorDropdown = false" @keydown.escape="openVerifikatorDropdown = false">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Petugas Verifikator
                        </label>
                        
                        <input type="hidden" name="diverifikasi_oleh" :value="diverifikasiOleh">

                        {{-- Trigger Button --}}
                        <button type="button"
                                @click="openVerifikatorDropdown = !openVerifikatorDropdown; if(openVerifikatorDropdown) $nextTick(() => $refs.searchVerifikatorInput?.focus())"
                                class="flex items-center justify-between w-full rounded-lg border bg-white px-3 py-2 text-xs font-medium text-left transition-colors dark:bg-gray-900 dark:text-white"
                                :class="openVerifikatorDropdown ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-300 dark:border-gray-700 hover:border-gray-400'">
                            <span class="truncate" :class="selectedVerifikator ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-400'">
                                <span x-text="selectedVerifikator ? selectedVerifikator.name : '-- Tetapkan Petugas Verifikator --'"></span>
                            </span>
                            <svg class="h-4 w-4 text-gray-400 shrink-0 ml-2 transition-transform duration-200"
                                 :class="{'rotate-180': openVerifikatorDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Floating Dropdown Panel --}}
                        <div x-show="openVerifikatorDropdown" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 right-0 z-50 mt-1 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
                            
                            {{-- Sticky Search Input --}}
                            <div class="p-2 border-b border-gray-100 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-900/80 sticky top-0 z-10">
                                <div class="relative">
                                    <svg class="absolute left-3 top-2.5 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" x-ref="searchVerifikatorInput" x-model="searchVerifikator"
                                           placeholder="Cari nama auditor / verifikator..."
                                           class="w-full rounded-lg border border-gray-200 bg-white pl-8 pr-7 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                    <template x-if="searchVerifikator">
                                        <button type="button" @click="searchVerifikator = ''" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Scrollable List Container --}}
                            <ul class="max-h-56 overflow-y-auto py-1 divide-y divide-gray-50 dark:divide-gray-800/50">
                                <li>
                                    <button type="button" @click="selectVerifikator('')"
                                            class="w-full text-left px-3 py-2 text-xs transition-colors flex items-center justify-between text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 italic">
                                        <span>-- Belum Ditetapkan (Kosongkan) --</span>
                                        <template x-if="!diverifikasiOleh">
                                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </template>
                                    </button>
                                </li>
                                <template x-for="u in filteredVerifikators" :key="u.id">
                                    <li>
                                        <button type="button" @click="selectVerifikator(u.id)"
                                                class="w-full text-left px-3 py-2.5 text-xs transition-colors flex items-center justify-between gap-2"
                                                :class="String(u.id) === String(diverifikasiOleh)
                                                    ? 'bg-blue-50 text-blue-800 font-semibold dark:bg-blue-950/40 dark:text-blue-200'
                                                    : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'">
                                            <span class="truncate" x-text="u.name"></span>
                                            <template x-if="String(u.id) === String(diverifikasiOleh)">
                                                <svg class="h-4 w-4 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </template>
                                        </button>
                                    </li>
                                </template>
                                <template x-if="filteredVerifikators.length === 0">
                                    <li class="px-3 py-4 text-center text-xs text-gray-400">
                                        Tidak ada verifikator yang sesuai pencarian
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Auditor atau penanggung jawab verifikasi dari Inspektorat.</p>
                    </div>
                </div>

                {{-- Catatan & Hambatan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Catatan Tindak Lanjut
                        </label>
                        <textarea name="catatan_tl" rows="3" maxlength="2000"
                                  placeholder="Uraikan rincian tindak lanjut yang telah dilakukan oleh pihak yang diperiksa..."
                                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-normal text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('catatan_tl') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Kendala / Hambatan (Opsional)
                        </label>
                        <textarea name="hambatan" rows="3" maxlength="2000"
                                  placeholder="Tuliskan jika terdapat hambatan atau kendala dalam pemenuhan rekomendasi..."
                                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-normal text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('hambatan') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════
                 BAGIAN 4: UPLOAD BUKTI / LAMPIRAN
            ═══════════════════════════════════════════════════════════════════ --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">4</span>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white">Berkas Bukti / Lampiran Tindak Lanjut</h2>
                    </div>
                    <button type="button" @click="addFileInput()"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Tambah File Lampiran
                    </button>
                </div>

                <div class="space-y-3" id="attachments-wrapper">
                    <template x-for="(att, idx) in attachments" :key="att.id">
                        <div class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 bg-gray-50/50 dark:border-gray-700 dark:bg-gray-800/40">
                            <svg class="h-5 w-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <input type="file" name="attachments[]"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp"
                                   class="block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-300">
                            <button type="button" @click="removeFileInput(idx)"
                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors" title="Hapus baris berkas">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <p class="mt-3 text-[11px] text-gray-500 dark:text-gray-400">
                    Format file yang didukung: <strong>PDF, JPG, JPEG, PNG, WEBP</strong> (Maksimal 10 MB per berkas).
                </p>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════
                 TOMBOL AKSI / SUBMIT
            ═══════════════════════════════════════════════════════════════════ --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <template x-if="lhpId">
                    <a :href="'{{ url('/tindak-lanjuts/lhp') }}/' + lhpId"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm transition-colors">
                        Batal
                    </a>
                </template>
                <template x-if="!lhpId">
                    <a href="{{ route('tindak-lanjuts.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm transition-colors">
                        Batal
                    </a>
                </template>

                <button type="submit"
                        :disabled="submitting || isNilaiExceeds"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors">
                    <template x-if="submitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="submitting ? 'Menyimpan...' : 'Simpan Tindak Lanjut'"></span>
                </button>
            </div>

        </div>
    </form>
</div>

<script>
function tindakLanjutCreateForm(config) {
    return {
        lhps: config.lhps || [],
        lhpId: String(config.initialLhpId || ''),
        selectedRekomId: String(config.initialRekomId || ''),
        rekomendasis: config.initialRekomendasis || [],
        loadingRekom: false,
        rekomError: '',
        jenisPenyelesaian: config.initialJenis || 'setor_kas',
        nomorBukti: '',
        nilaiRaw: parseInt(config.initialNilai || '0', 10),
        displayNilai: config.initialNilai ? new Intl.NumberFormat('id-ID').format(config.initialNilai) : '',
        attachments: [{ id: Date.now() }],
        submitting: false,

        // LHP dropdown states
        openLhpDropdown: false,
        searchLhp: '',

        // Rekomendasi dropdown states
        openRekomDropdown: false,
        searchRekom: '',

        // Verifikator dropdown states
        verifikators: config.verifikators || [],
        diverifikasiOleh: String(config.initialVerifikatorId || ''),
        openVerifikatorDropdown: false,
        searchVerifikator: '',

        get selectedVerifikator() {
            if (!this.diverifikasiOleh) return null;
            return this.verifikators.find(u => String(u.id) === String(this.diverifikasiOleh)) || null;
        },

        get filteredVerifikators() {
            if (!this.searchVerifikator) return this.verifikators;
            const q = this.searchVerifikator.toLowerCase();
            return this.verifikators.filter(u => u.name && u.name.toLowerCase().includes(q));
        },

        selectVerifikator(id) {
            this.diverifikasiOleh = id ? String(id) : '';
            this.openVerifikatorDropdown = false;
            this.searchVerifikator = '';
        },

        init() {
            if (this.selectedRekomId) {
                this.onRekomChange();
            }
            if (this.lhpId && this.rekomendasis.length === 0) {
                this.fetchRekomendasis(this.lhpId);
            }
        },

        get selectedLhp() {
            if (!this.lhpId) return null;
            return this.lhps.find(l => String(l.id) === String(this.lhpId)) || null;
        },

        get filteredLhps() {
            if (!this.searchLhp) return this.lhps;
            const q = this.searchLhp.toLowerCase();
            return this.lhps.filter(l =>
                (l.nomor_lhp && l.nomor_lhp.toLowerCase().includes(q)) ||
                (l.unit && l.unit.toLowerCase().includes(q)) ||
                (l.program && l.program.toLowerCase().includes(q)) ||
                (l.label && l.label.toLowerCase().includes(q))
            );
        },

        selectLhp(id) {
            this.lhpId = String(id);
            this.openLhpDropdown = false;
            this.searchLhp = '';
            this.onLhpChange();
        },

        get selectedRekom() {
            if (!this.selectedRekomId) return null;
            return this.rekomendasis.find(r => String(r.id) === String(this.selectedRekomId)) || null;
        },

        get filteredRekomendasis() {
            if (!this.searchRekom) return this.rekomendasis;
            const q = this.searchRekom.toLowerCase();
            return this.rekomendasis.filter(r =>
                (r.kode && r.kode.toLowerCase().includes(q)) ||
                (r.temuan_kode && r.temuan_kode.toLowerCase().includes(q)) ||
                (r.uraian && r.uraian.toLowerCase().includes(q)) ||
                (r.label && r.label.toLowerCase().includes(q))
            );
        },

        selectRekom(id) {
            this.selectedRekomId = String(id);
            this.openRekomDropdown = false;
            this.searchRekom = '';
            this.rekomError = '';
            this.onRekomChange();
        },

        get isFinancial() {
            if (!this.selectedRekom) return true;
            if (this.selectedRekom.jenis !== 'uang') return false;
            return ['setor_kas', 'cicilan'].includes(this.jenisPenyelesaian);
        },

        get isNilaiExceeds() {
            if (!this.isFinancial || !this.selectedRekom) return false;
            const sisa = this.selectedRekom.nilai_sisa || 0;
            return sisa > 0 && this.nilaiRaw > sisa;
        },

        get nomorBuktiLabel() {
            switch (this.jenisPenyelesaian) {
                case 'pengembalian_barang':
                    return 'Nomor Berita Acara Serah Terima (BAST)';
                case 'perbaikan_administrasi':
                    return 'Nomor Surat Keputusan (SK) / Dokumen SOP';
                case 'cicilan':
                    return 'Nomor STS / Bukti Setoran Awal';
                default:
                    return 'Nomor Surat Tanda Setor (STS) / Bukti Transfer';
            }
        },

        get nomorBuktiPlaceholder() {
            switch (this.jenisPenyelesaian) {
                case 'pengembalian_barang':
                    return 'Contoh: 028/BAST-ASET/VIII/2026';
                case 'perbaikan_administrasi':
                    return 'Contoh: 800/124/SK-BUP/2026';
                case 'cicilan':
                    return 'Contoh: STS-CICILAN-01/2026';
                default:
                    return 'Contoh: STS-2026/08/14-0012';
            }
        },

        onLhpChange() {
            this.selectedRekomId = '';
            this.rekomError = '';
            this.fetchRekomendasis(this.lhpId);
        },

        async fetchRekomendasis(lhpId) {
            if (!lhpId) {
                this.rekomendasis = [];
                this.selectedRekomId = '';
                return;
            }
            this.loadingRekom = true;
            try {
                const res = await fetch(`{{ url('/tindak-lanjuts/rekomendasis-by-lhp') }}/${lhpId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                this.rekomendasis = await res.json();

                if (this.selectedRekomId) {
                    const match = this.rekomendasis.find(r => String(r.id) === String(this.selectedRekomId));
                    if (match) {
                        this.onRekomChange();
                    } else {
                        this.selectedRekomId = '';
                    }
                }
            } catch (err) {
                console.error('[tindakLanjutCreateForm] Error fetching recommendations:', err);
                this.rekomendasis = [];
            } finally {
                this.loadingRekom = false;
            }
        },

        onRekomChange() {
            const rekom = this.selectedRekom;
            if (!rekom) return;

            // Auto-adjust resolution method based on recommendation type
            if (rekom.jenis !== 'uang') {
                if (rekom.jenis === 'barang') {
                    this.jenisPenyelesaian = 'pengembalian_barang';
                } else {
                    this.jenisPenyelesaian = 'perbaikan_administrasi';
                }
                this.nilaiRaw = 0;
                this.displayNilai = '0';
            } else {
                if (['pengembalian_barang', 'perbaikan_administrasi'].includes(this.jenisPenyelesaian)) {
                    this.jenisPenyelesaian = 'setor_kas';
                }
                // Pre-fill full remaining value if not yet set
                if ((this.nilaiRaw === 0 || !this.nilaiRaw) && rekom.nilai_sisa > 0) {
                    this.nilaiRaw = rekom.nilai_sisa;
                    this.displayNilai = new Intl.NumberFormat('id-ID').format(rekom.nilai_sisa);
                }
            }
        },

        onMethodChange(method) {
            this.jenisPenyelesaian = method;
            if (['pengembalian_barang', 'perbaikan_administrasi'].includes(method)) {
                this.nilaiRaw = 0;
                this.displayNilai = '0';
            } else if (this.selectedRekom && this.selectedRekom.jenis === 'uang') {
                if (this.nilaiRaw === 0 && this.selectedRekom.nilai_sisa > 0) {
                    this.nilaiRaw = this.selectedRekom.nilai_sisa;
                    this.displayNilai = new Intl.NumberFormat('id-ID').format(this.selectedRekom.nilai_sisa);
                }
            }
        },

        handleNilaiInput(e) {
            const raw = e.target.value.replace(/\D/g, '');
            const num = parseInt(raw || '0', 10);
            this.nilaiRaw = num;
            this.displayNilai = raw ? new Intl.NumberFormat('id-ID').format(num) : '';
        },

        formatRupiah(num) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(num || 0);
        },

        addFileInput() {
            this.attachments.push({ id: Date.now() });
        },

        removeFileInput(index) {
            if (this.attachments.length > 1) {
                this.attachments.splice(index, 1);
            }
        },

        handleSubmit(e) {
            if (!this.selectedRekomId) {
                e.preventDefault();
                this.rekomError = 'Rekomendasi yang Ditindaklanjuti wajib dipilih.';
                this.openRekomDropdown = true;
                return;
            }

            if (this.isNilaiExceeds) {
                e.preventDefault();
                alert('Nilai tindak lanjut melebihi sisa kewajiban rekomendasi.');
                return;
            }

            this.submitting = true;
        }
    };
}
</script>
@endsection