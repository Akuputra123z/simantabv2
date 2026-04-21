@extends('layouts.app')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow max-w-3xl">

    <h2 class="text-xl font-semibold mb-4">Tambah Kode Rekomendasi</h2>

    <form action="{{ route('kode-rekomendasi.store') }}" method="POST">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 sm:px-6 sm:py-5">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
            Konfigurasi Kode Rekomendasi
        </h3>
    </div>

    <div class="p-5 space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Kode
                </label>
                <input type="text" name="kode" value="{{ old('kode', $data->kode ?? '') }}"
                    placeholder="Masukkan kode unik"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Kode Numerik
                </label>
                <input type="number" name="kode_numerik" value="{{ old('kode_numerik', $data->kode_numerik ?? '') }}"
                    placeholder="Contoh: 101"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Kategori
                </label>
                <input type="text" name="kategori" value="{{ old('kategori', $data->kategori ?? '') }}"
                    placeholder="Kategori sistem"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Deskripsi
                </label>
                <textarea name="deskripsi" rows="4"
                    placeholder="Tuliskan deskripsi lengkap di sini..."
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ old('deskripsi', $data->deskripsi ?? '') }}</textarea>
            </div>
            <div class="flex items-center md:pt-7">
                <div x-data="{ switcherToggle: {{ old('is_active', $data->is_active ?? true) ? 'true' : 'false' }} }">
                    <label for="toggle1" class="flex items-center gap-3 text-sm font-medium text-gray-700 cursor-pointer select-none dark:text-gray-400">
                        <div class="relative">
                            <input type="checkbox" id="toggle1" name="is_active" value="1" class="sr-only"
                                @change="switcherToggle = !switcherToggle"
                                :checked="switcherToggle" />
                            
                            <div class="block h-6 w-11 rounded-full transition-colors duration-300 ease-in-out"
                                :class="switcherToggle ? 'bg-brand-500 dark:bg-brand-500' : 'bg-gray-200 dark:bg-white/10'">
                            </div>
                            
                            <div class="shadow-theme-sm absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white transition-transform duration-300 ease-linear"
                                :class="switcherToggle ? 'translate-x-full' : 'translate-x-0'">
                            </div>
                        </div>
                        Status Aktif
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-all focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/20">
                Simpan Perubahan
            </button>
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-400 dark:hover:bg-gray-800 transition-all">
                Kembali
            </a>
        </div>
    </div>
</div>
    </form>

</div>

@endsection