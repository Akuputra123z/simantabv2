@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('unit-diperiksa.index') }}" class="group inline-flex items-center text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-blue-600 transition-colors mb-2">
            <svg class="mr-2 h-3 w-3 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h2 class="text-3xl font-black tracking-tighter text-gray-900 dark:text-white">Tambah Unit Diperiksa</h2>
    </div>

    <form action="{{ route('unit-diperiksa.store') }}" method="POST">
        @csrf
        <div class="rounded-3xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.02]">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Nama Unit / Instansi</label>
                    <input type="text" name="nama_unit" value="{{ old('nama_unit') }}" required placeholder="Contoh: SMPN 1 Rembang"
                        class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm focus:border-blue-500 outline-none dark:border-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Kategori</label>
                    <select name="kategori" required class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm focus:border-blue-500 outline-none dark:border-gray-700 dark:text-white">
                        <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>Pilih Kategori</option>
                        @foreach($kategoriOptions as $opt)
                            <option value="{{ $opt }}" {{ old('kategori') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Kecamatan</label>
                    {{-- Mengubah Input menjadi Select --}}
                    <select name="nama_kecamatan" required class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm focus:border-blue-500 outline-none dark:border-gray-700 dark:text-white">
                        <option value="" disabled {{ old('nama_kecamatan') ? '' : 'selected' }}>Pilih Kecamatan</option>
                        @php
                            $kecamatans = [
                                'Bulu', 'Gunem', 'Kaliori', 'Kragan', 'Lasem', 'Pancur', 'Rembang', 
                                'Sale', 'Sarang', 'Sedan', 'Sluke', 'Sulang', 'Sumber', 'Pamotan'
                            ];
                        @endphp
                        @foreach($kecamatans as $kec)
                            <option value="{{ $kec }}" {{ old('nama_kecamatan') == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Nomor Telepon</label>
                    <input type="text" name="telepon" value="{{ old('telepon') }}" placeholder="08..."
                        class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm focus:border-blue-500 outline-none dark:border-gray-700 dark:text-white">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm focus:border-blue-500 outline-none dark:border-gray-700 dark:text-white">{{ old('alamat') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="3" class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm focus:border-blue-500 outline-none dark:border-gray-700 dark:text-white">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <div class="mt-10 flex items-center gap-3 pt-6 border-t border-gray-50 dark:border-gray-800">
                <x-ui.button type="submit" variant="primary">Simpan Unit Baru</x-ui.button>
                <a href="{{ route('unit-diperiksa.index') }}" class="text-xs font-bold uppercase tracking-widest text-gray-400">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection