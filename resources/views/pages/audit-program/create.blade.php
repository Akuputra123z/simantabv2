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

                {{-- Tahun Anggaran --}}
                <div>
                    <label for="tahun" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tahun Anggaran <span class="text-red-500">*</span>
                    </label>
                    <select name="tahun" 
                            id="tahun" 
                            class="w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 outline-none focus:border-blue-500 dark:border-gray-700 dark:text-white @error('tahun') border-red-500 @enderror"
                            required>
                        @foreach(range(date('Y') + 1, 2024) as $year)
                            <option value="{{ $year }}" {{ old('tahun') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    @error('tahun')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Inisial --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status Awal</label>
                    <input type="text" value="Draft" disabled class="w-full rounded-lg border border-gray-100 bg-gray-50 px-4 py-2.5 text-gray-500 dark:border-gray-800 dark:bg-gray-900">
                    <p class="mt-1 text-[10px] text-gray-400 italic">Status akan otomatis menjadi 'Draft' saat pembuatan.</p>
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
@endsection