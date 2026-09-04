@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 pb-12">

    {{-- Notifikasi Error --}}
    @if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-500/20 dark:bg-red-500/10">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Gagal Menyimpan</h3>
                <ul class="mt-1 list-disc pl-5 text-xs text-red-700 dark:text-red-300 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('audit-program.index') }}" class="group inline-flex items-center gap-2 text-sm text-gray-400 hover:text-blue-600 transition-colors">
            <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        <h1 class="mt-3 text-2xl font-medium tracking-tight text-gray-900 dark:text-white">Edit Program Kerja</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui data program pengawasan tahunan.</p>
    </div>

    {{-- Card --}}
    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <form action="{{ route('audit-program.update', $program->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-7 p-6 sm:p-8">

                {{-- Nama Program --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black dark:text-gray-500">Nama Program</label>
                    <input type="text" name="nama_program" value="{{ old('nama_program', $program->nama_program) }}"
                           placeholder="Contoh: Audit Operasional BOS"
                           class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                {{-- Grid 2 Kolom + Kategori --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">

                    {{-- Tahun — Year Picker --}}
                    <div x-data="yearPicker({{ (int) old('tahun', $program->tahun) }}, {{ (int) date('Y') }})"
                         x-init="init()"
                         class="relative">
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black dark:text-gray-500">Tahun PKPT</label>

                        {{-- Hidden input untuk form submit --}}
                        <input type="hidden" name="tahun" :value="selected">

                        {{-- Trigger --}}
                        <button type="button"
                                @click="open = !open"
                                @keydown.escape.window="open = false"
                                class="flex h-[50px] w-full items-center justify-between rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 text-sm transition focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white hover:border-blue-400"
                                :class="open ? 'border-blue-500 ring-4 ring-blue-500/10' : ''">
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

                        {{-- Panel --}}
                        <div x-show="open" x-cloak
                             @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
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
                                      x-text="pageStart + ' – ' + (pageStart + 11)"></span>
                                <button type="button" @click="pageStart += 12"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Grid 4×3 --}}
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
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black dark:text-gray-500">Kategori</label>
                        <select name="kategori"
                                class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($kategoriOptions as $opt)
                                <option value="{{ $opt }}" {{ old('kategori', $program->kategori) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black dark:text-gray-500">Status Program</label>
                        <select name="status"
                                class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="draft" {{ old('status', $program->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="berjalan" {{ old('status', $program->status) == 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                            <option value="selesai" {{ old('status', $program->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/30 px-6 py-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <a href="{{ route('audit-program.index') }}"
                   class="rounded-xl border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-xl bg-blue-600 px-8 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

<style>[x-cloak] { display: none !important; }</style>

<script>
function yearPicker(initial, now) {
    return {
        selected: initial,
        now: now,
        open: false,
        pageStart: initial - 3,

        init() {
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
