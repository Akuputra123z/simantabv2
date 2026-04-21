@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4">

    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('unit-diperiksa.show', $data->id) }}" 
           class="inline-flex items-center text-xs text-gray-400 hover:text-blue-500 transition mb-2">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor">
                <path stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>

        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Edit Unit
        </h2>
        <p class="text-sm text-gray-400">
            Perbarui informasi unit atau instansi
        </p>
    </div>

    <form action="{{ route('unit-diperiksa.update', $data->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-gray-100 bg-white/80 backdrop-blur p-6 shadow-sm">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Nama Unit --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm text-gray-500 mb-1">
                        Nama Unit / Instansi
                    </label>
                    <input type="text" name="nama_unit"
                        value="{{ old('nama_unit', $data->nama_unit) }}"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm 
                               focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 
                               outline-none transition dark:bg-white/5 dark:border-gray-700 dark:text-white">
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm text-gray-500 mb-1">
                        Kategori
                    </label>
                    <select name="kategori" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm
                               focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500
                               outline-none transition dark:bg-white/5 dark:border-gray-700 dark:text-white">
                        @foreach($kategoriOptions as $opt)
                            <option value="{{ $opt }}" 
                                {{ old('kategori', $data->kategori) == $opt ? 'selected' : '' }}>
                                {{ $opt }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kecamatan --}}
                <div>
                    <label class="block text-sm text-gray-500 mb-1">
                        Kecamatan
                    </label>
                    <input type="text" name="nama_kecamatan"
                        value="{{ old('nama_kecamatan', $data->nama_kecamatan) }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm
                               focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500
                               outline-none transition dark:bg-white/5 dark:border-gray-700 dark:text-white">
                </div>

                {{-- Telepon --}}
                <div>
                    <label class="block text-sm text-gray-500 mb-1">
                        Nomor Telepon
                    </label>
                    <input type="text" name="telepon"
                        value="{{ old('telepon', $data->telepon) }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm
                               focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500
                               outline-none transition dark:bg-white/5 dark:border-gray-700 dark:text-white">
                </div>

                {{-- Alamat --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm text-gray-500 mb-1">
                        Alamat Lengkap
                    </label>
                    <textarea name="alamat" rows="2"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm
                               focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500
                               outline-none transition dark:bg-white/5 dark:border-gray-700 dark:text-white">{{ old('alamat', $data->alamat) }}</textarea>
                </div>

                {{-- Keterangan --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm text-gray-500 mb-1">
                        Keterangan Tambahan
                    </label>
                    <textarea name="keterangan" rows="3"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm
                               focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500
                               outline-none transition dark:bg-white/5 dark:border-gray-700 dark:text-white">{{ old('keterangan', $data->keterangan) }}</textarea>
                </div>

            </div>

            {{-- Actions --}}
            <div class="mt-8 flex items-center justify-between border-t border-gray-100 pt-5">
                <a href="{{ route('unit-diperiksa.show', $data->id) }}" 
                   class="text-sm text-gray-400 hover:text-gray-600 transition">
                    Batal
                </a>

                <x-ui.button 
                    type="submit" 
                    variant="primary"
                    class="rounded-xl px-5 py-2 text-sm shadow-sm hover:shadow-md transition"
                >
                    Simpan Perubahan
                </x-ui.button>
            </div>

        </div>
    </form>
</div>
@endsection