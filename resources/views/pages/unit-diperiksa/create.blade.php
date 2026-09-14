@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('unit-diperiksa.index') }}"
               class="group mb-1.5 inline-flex items-center text-xs font-semibold text-gray-500 hover:text-blue-600 transition-colors">
                <svg class="mr-1.5 h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Daftar Unit
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Tambah Unit Diperiksa</h1>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tambahkan OPD, BUMD, Sekolah, Desa, atau BLUD baru ke dalam sistem.</p>
        </div>
    </div>

    <form action="{{ route('unit-diperiksa.store') }}" method="POST">
        @csrf
        <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                {{-- Nama Unit --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Nama Unit / Instansi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_unit" value="{{ old('nama_unit') }}" required placeholder="Contoh: SMPN 1 Rembang / Desa Sumber"
                           class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500">
                    @error('nama_unit') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Kategori Unit <span class="text-red-500">*</span>
                    </label>
                    <div class="relative z-20">
                        <select name="kategori" required data-no-ts
                                class="h-11 w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-900 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer pr-10">
                            <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>Pilih Kategori</option>
                            @foreach($kategoriOptions as $opt)
                                <option value="{{ $opt }}" {{ old('kategori') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </div>
                    @error('kategori') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Kecamatan --}}
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Kecamatan <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="relative z-20">
                        <select name="nama_kecamatan" data-no-ts
                                class="h-11 w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-900 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer pr-10">
                            <option value="" {{ old('nama_kecamatan') ? '' : 'selected' }}>-- Pilih Kecamatan (Opsional) --</option>
                            @foreach($kecamatanList as $kec)
                                <option value="{{ $kec }}" {{ old('nama_kecamatan') == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </div>
                    @error('nama_kecamatan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Telepon --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Nomor Telepon / Kontak</label>
                    <input type="text" name="telepon" value="{{ old('telepon') }}" placeholder="Contoh: 08xxxxxxxxxx / (0295) xxxxxx"
                           class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500">
                    @error('telepon') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Alamat --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" placeholder="Tuliskan alamat lengkap lokasi unit..."
                              class="w-full resize-none rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500">{{ old('alamat') }}</textarea>
                    @error('alamat') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Keterangan --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="3" placeholder="Catatan tambahan (opsional)..."
                              class="w-full resize-none rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500">{{ old('keterangan') }}</textarea>
                    @error('keterangan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

            </div>

            <div class="mt-8 flex items-center justify-between border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('unit-diperiksa.index') }}"
                   class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-xl bg-blue-600 px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-500/20 hover:bg-blue-700 active:scale-95 transition-all cursor-pointer">
                    Simpan Unit Baru
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
