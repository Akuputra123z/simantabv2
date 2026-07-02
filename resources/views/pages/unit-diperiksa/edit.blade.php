@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('unit-diperiksa.show', $data->id) }}"
           class="group mb-2 inline-flex items-center text-[10px] font-bold uppercase tracking-widest text-gray-400 transition-colors hover:text-blue-600">
            <svg class="mr-2 h-3 w-3 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Edit Unit</h2>
        <p class="text-sm text-gray-400">Perbarui informasi unit atau instansi</p>
    </div>

    <form action="{{ route('unit-diperiksa.update', $data->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Nama Unit / Instansi</label>
                    <input type="text" name="nama_unit" value="{{ old('nama_unit', $data->nama_unit) }}" required
                           class="h-11 w-full rounded-xl border border-gray-200 bg-transparent px-4 text-sm outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white">
                    @error('nama_unit') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Kategori</label>
                    <select name="kategori" required
                            class="h-11 w-full cursor-pointer rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-800 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach($kategoriOptions as $opt)
                            <option value="{{ $opt }}" {{ old('kategori', $data->kategori) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('kategori') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Kecamatan</label>
                    <select name="nama_kecamatan" required
                            class="h-11 w-full cursor-pointer rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-800 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="" disabled {{ old('nama_kecamatan', $data->nama_kecamatan) ? '' : 'selected' }}>Pilih Kecamatan</option>
                        @foreach($kecamatanList as $kec)
                            <option value="{{ $kec }}" {{ old('nama_kecamatan', $data->nama_kecamatan) == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                        @endforeach
                    </select>
                    @error('nama_kecamatan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Nomor Telepon</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $data->telepon) }}"
                           class="h-11 w-full rounded-xl border border-gray-200 bg-transparent px-4 text-sm outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white">
                    @error('telepon') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2"
                              class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white">{{ old('alamat', $data->alamat) }}</textarea>
                    @error('alamat') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="3"
                              class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white">{{ old('keterangan', $data->keterangan) }}</textarea>
                    @error('keterangan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

            </div>

            <div class="mt-8 flex items-center justify-between border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('unit-diperiksa.show', $data->id) }}"
                   class="text-xs font-bold uppercase tracking-widest text-gray-400 transition-colors hover:text-gray-600 dark:hover:text-gray-300">Batal</a>
                <button type="submit"
                        class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700 active:scale-95">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
