@extends('layouts.app')

@section('content')

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Master Jenis Dokumen Reviu</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Daftar jenis dokumen perencanaan & keuangan daerah yang direviu oleh Inspektorat
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
            + Tambah Jenis Dokumen
        </h2>
        <form method="POST" action="{{ route('jenis-dokumen-reviu.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Kode Dokumen</label>
                <input type="text" name="kode" required placeholder="Mis. RKPD, RENSTRA, SHSD"
                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-xs uppercase text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Dokumen</label>
                <input type="text" name="nama" required placeholder="Mis. Standar Harga Satuan Daerah"
                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-xs text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Gaya Penomoran Bagian</label>
                <select name="gaya_penomoran_bagian" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="alfabet">Alfabet (A, B, C...)</option>
                    <option value="romawi">Romawi (I, II, III...)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tata Letak Hasil</label>
                <select name="tata_letak_hasil" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="tabel_dua_kolom">Tabel Dua Kolom (Catatan - Rekomendasi)</option>
                    <option value="naratif">Naratif</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">SLA Tindak Lanjut (Hari)</label>
                <input type="number" name="sla_tindak_lanjut_hari" value="30" min="1" max="365"
                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 text-xs font-semibold text-white hover:bg-blue-700 transition">
                Simpan Jenis Dokumen
            </button>
        </form>
    </div>

    {{-- Tabel Daftar --}}
    <div class="lg:col-span-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
            <thead class="border-b border-gray-200 bg-gray-50 uppercase font-semibold text-gray-700 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Nama Dokumen</th>
                    <th class="px-4 py-3">Format</th>
                    <th class="px-4 py-3">SLA</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($jenisList as $j)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 font-bold text-blue-600 dark:text-blue-400">{{ $j->kode }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $j->nama }}</td>
                        <td class="px-4 py-3">{{ ucfirst($j->gaya_penomoran_bagian) }} &bull; {{ str_replace('_', ' ', $j->tata_letak_hasil) }}</td>
                        <td class="px-4 py-3">{{ $j->sla_tindak_lanjut_hari ?? 30 }} hari</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-semibold {{ $j->aktif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $j->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada jenis dokumen reviu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($jenisList->hasPages())
            <div class="p-3 border-t border-gray-200 dark:border-gray-800">
                {{ $jenisList->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
