@extends('layouts.app')

@section('content')

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Format & Urutan Penomoran Surat</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Pengelolaan pola nomor dinas dan sequence counter atomik per tahun
        </p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="space-y-6">
    {{-- Tabel Format Pola --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-4">
            Pola Format Penomoran Naskah Dinas
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                <thead class="border-b border-gray-200 bg-gray-50 uppercase font-semibold text-gray-700 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">Jenis Dokumen</th>
                        <th class="px-4 py-3">Pola Nomor</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($formats as $f)
                        <tr>
                            <form method="POST" action="{{ route('nomor-formats.update', $f->id) }}">
                                @csrf
                                @method('PUT')
                                <td class="px-4 py-3 font-mono font-bold text-gray-900 dark:text-white">{{ $f->jenis }}</td>
                                <td class="px-4 py-3">
                                    <input type="text" name="pola" value="{{ $f->pola }}" required
                                           class="h-9 w-full rounded border border-gray-300 px-2 font-mono text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="keterangan" value="{{ $f->keterangan }}"
                                           class="h-9 w-full rounded border border-gray-300 px-2 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <select name="aktif" class="h-9 rounded border border-gray-300 px-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="1" @selected($f->aktif)>Aktif</option>
                                        <option value="0" @selected(! $f->aktif)>Nonaktif</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="submit" class="rounded bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                        Simpan
                                    </button>
                                </td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada format terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-[11px] text-gray-500 mt-3">
            Variabel yang dapat digunakan dalam pola: <code>{urut:3}</code> (001), <code>{urut}</code> (1), <code>{tahun}</code> (2026), <code>{.sufiks}</code> (.a).
        </p>
    </div>

    {{-- Tabel Sequence Counter Saat Ini --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-4">
            Counter Urutan Terakhir (Tabel Sequence)
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                <thead class="border-b border-gray-200 bg-gray-50 uppercase font-semibold text-gray-700 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">Jenis Dokumen</th>
                        <th class="px-4 py-3">Tahun</th>
                        <th class="px-4 py-3">Nomor Terakhir Teralokasi</th>
                        <th class="px-4 py-3">Diperbarui Pada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($sequences as $seq)
                        <tr>
                            <td class="px-4 py-3 font-mono font-bold text-gray-900 dark:text-white">{{ $seq->jenis }}</td>
                            <td class="px-4 py-3">{{ $seq->tahun }}</td>
                            <td class="px-4 py-3 font-mono font-bold text-blue-600 dark:text-blue-400">{{ $seq->nomor_terakhir }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $seq->updated_at ? $seq->updated_at->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada sequence yang aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
