@extends('layouts.app')

@section('content')

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Master Kategori Catatan Reviu</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Klasifikasi catatan temuan pada reviu dokumen untuk keperluan analisis dan pelaporan
        </p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Form Tambah --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-4">
            + Tambah Kategori Catatan
        </h2>
        <form method="POST" action="{{ route('lhr-kategori-catatans.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Kode Kategori</label>
                <input type="text" name="kode" required placeholder="Mis. KEWAJARAN_HARGA"
                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-xs uppercase text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Kategori</label>
                <input type="text" name="nama" required placeholder="Mis. Kewajaran & Kepatutan Harga"
                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-xs text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 text-xs font-semibold text-white hover:bg-blue-700 transition">
                Simpan Kategori
            </button>
        </form>
    </div>

    {{-- Tabel Daftar --}}
    <div class="lg:col-span-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
            <thead class="border-b border-gray-200 bg-gray-50 uppercase font-semibold text-gray-700 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Nama Kategori</th>
                    <th class="px-4 py-3 text-center">Jumlah Catatan</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($kategoris as $kat)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 font-mono font-bold text-gray-900 dark:text-white">{{ $kat->kode }}</td>
                        <td class="px-4 py-3 font-medium">{{ $kat->nama }}</td>
                        <td class="px-4 py-3 text-center">{{ $kat->catatans_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-semibold {{ $kat->aktif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $kat->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada kategori catatan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($kategoris->hasPages())
            <div class="p-3 border-t border-gray-200 dark:border-gray-800">
                {{ $kategoris->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
