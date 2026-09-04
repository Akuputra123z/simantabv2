@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    {{-- Breadcrumb & Header --}}
    <div class="mb-8">
        <a href="{{ route('audit-program.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-blue-600 transition mb-2">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar PKPT
        </a>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Buat PKPT Baru</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Inisialisasi rencana pengawasan tahunan. Sistem akan otomatis menyiapkan 10 slot program detail setelah disimpan.</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
        <form action="{{ route('audit-program.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- Nama Program --}}
                <div class="sm:col-span-2">
                    <label for="nama_program" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Program Utama <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="nama_program"
                           id="nama_program"
                           value="{{ old('nama_program', 'PKPT Tahun ' . (date('Y') + 1)) }}"
                           placeholder="Contoh: PKPT Reguler Tahun 2026"
                           class="w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white @error('nama_program') border-red-500 @enderror"
                           required>
                    @error('nama_program')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ══ Tahun Anggaran — Year Picker Calendar ══ --}}
                <div x-data="yearPicker({{ (int) old('tahun', date('Y') + 1) }}, {{ (int) date('Y') }})"
                     x-init="init()"
                     class="relative">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tahun Anggaran <span class="text-red-500">*</span>
                    </label>

                    {{-- Hidden input untuk form submit --}}
                    <input type="hidden" name="tahun" :value="selected">

                    {{-- Trigger --}}
                    <button type="button"
                            @click="open = !open"
                            @keydown.escape.window="open = false"
                            class="flex h-10 w-full items-center justify-between rounded-lg border px-4 text-sm transition focus:outline-none
                                   @error('tahun') border-red-400 @else border-gray-200 dark:border-gray-700 @enderror
                                   bg-white dark:bg-gray-900 dark:text-white hover:border-blue-400"
                            :class="open ? 'border-blue-500 ring-2 ring-blue-500/20' : ''">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="label"
                                  :class="selected ? 'font-semibold text-gray-800 dark:text-white' : 'text-gray-400'">
                            </span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200"
                             :class="open ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Year Picker Panel --}}
                    <div x-show="open" x-cloak
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 top-full z-50 mt-2 w-72 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl shadow-black/10 dark:border-gray-700 dark:bg-gray-900">

                        {{-- Nav header --}}
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-4 py-3">
                            <button type="button" @click="pageStart -= 12"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            <span class="text-sm font-bold text-gray-700 dark:text-gray-200"
                                  x-text="pageStart + ' – ' + (pageStart + 11)">
                            </span>

                            <button type="button" @click="pageStart += 12"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Grid tahun 4 kolom × 3 baris = 12 tahun --}}
                        <div class="grid grid-cols-4 gap-1 p-3">
                            <template x-for="y in years" :key="y">
                                <button type="button"
                                        @click="pick(y)"
                                        class="rounded-lg py-2.5 text-[13px] font-medium transition-all duration-150 focus:outline-none"
                                        :class="{
                                            'bg-blue-600 text-white shadow-md shadow-blue-500/30 scale-105 font-bold': selected === y,
                                            'ring-1 ring-blue-400 text-blue-600 bg-blue-50 dark:bg-blue-900/20 dark:text-blue-300': y === now && selected !== y,
                                            'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.06]': selected !== y && y !== now
                                        }">
                                    <span x-text="y"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Footer shortcut --}}
                        <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-white/[0.02] px-4 py-2.5">
                            <button type="button" @click="pick(now)"
                                    class="text-[11px] font-bold text-blue-500 hover:text-blue-700 transition">
                                Tahun Ini (<span x-text="now"></span>)
                            </button>
                            <button type="button" @click="pick(now + 1)"
                                    class="text-[11px] font-bold text-emerald-500 hover:text-emerald-700 transition">
                                Tahun Depan (<span x-text="now + 1"></span>)
                            </button>
                        </div>
                    </div>

                    @error('tahun')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label for="kategori" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="kategori" id="kategori" required
                            class="w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white @error('kategori') border-red-500 @enderror">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoriOptions as $opt)
                            <option value="{{ $opt }}" {{ old('kategori') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('kategori')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('audit-program.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5 transition">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition shadow-sm">
                    Simpan & Inisialisasi Program
                </button>
            </div>
        </form>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>

<script>
function yearPicker(initial, now) {
    return {
        selected: initial,
        now: now,
        open: false,
        pageStart: initial - 3,   // tampilkan centered di tahun terpilih

        init() {
            // Pastikan grid selalu dimulai dari angka yang rapi
            this.pageStart = this.selected - 3;
        },

        get years() {
            return Array.from({ length: 12 }, (_, i) => this.pageStart + i);
        },

        get label() {
            if (!this.selected) return 'Pilih Tahun';
            const suffix = this.selected === this.now     ? ' (Tahun Ini)'
                         : this.selected === this.now + 1 ? ' (Tahun Depan)'
                         : this.selected === this.now - 1 ? ' (Tahun Lalu)'
                         : '';
            return this.selected + suffix;
        },

        pick(y) {
            this.selected = y;
            this.open = false;
        }
    };
}
</script>
@endsection