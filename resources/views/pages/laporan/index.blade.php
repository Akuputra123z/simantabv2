@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Laporan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Rekap dan unduh laporan LHP dalam format PDF atau Excel</p>
        </div>
        {{-- Download Rekap Semua --}}
        <div class="flex gap-2">
            <a href="{{ route('laporan.download-pdf-semua', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                PDF Semua
            </a>
            <a href="{{ route('laporan.download-excel-semua', request()->query()) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel Semua
            </a>
        </div>
    </div>

    {{-- RINGKASAN CARDS --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total LHP</p>
            <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $ringkasan['total_lhp'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Rekomendasi</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ $ringkasan['total_rekom'] }}</p>
            <div class="mt-1 flex gap-2 text-[10px]">
                <span class="text-green-600">✓ {{ $ringkasan['rekom_selesai'] }}</span>
                <span class="text-amber-500">● {{ $ringkasan['rekom_proses'] }}</span>
                <span class="text-red-500">✕ {{ $ringkasan['rekom_belum'] }}</span>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Kerugian</p>
            <p class="mt-1 text-base font-bold text-red-600 dark:text-red-400">Rp {{ number_format($ringkasan['total_kerugian'], 0, ',', '.') }}</p>
            <p class="mt-0.5 text-[10px] text-gray-400">Sisa: Rp {{ number_format($ringkasan['total_sisa'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs text-gray-500 dark:text-gray-400">Avg Progress TL</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ $ringkasan['avg_persen'] }}%</p>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                <div class="h-full rounded-full {{ $ringkasan['avg_persen'] >= 100 ? 'bg-green-500' : ($ringkasan['avg_persen'] >= 50 ? 'bg-amber-400' : 'bg-blue-500') }}"
                     style="width: {{ min(100, $ringkasan['avg_persen']) }}%"></div>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <form action="{{ route('laporan.index') }}" method="GET" id="filter-form">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Tahun</label>
                    <select name="tahun" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua</option>
                        @foreach ($tahunList as $t)
                            <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Semester</label>
                    <select name="semester" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua</option>
                        <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>Semester I</option>
                        <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>Semester II</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">IRBAN</label>
                    <select name="irban" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua</option>
                        @foreach ($irbanList as $irban)
                            <option value="{{ $irban }}" {{ request('irban') == $irban ? 'selected' : '' }}>{{ $irban }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Status</label>
                    <select name="status" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua</option>
                        <option value="draft"          {{ request('status') == 'draft'          ? 'selected' : '' }}>Draft</option>
                        <option value="final"          {{ request('status') == 'final'          ? 'selected' : '' }}>Final</option>
                        <option value="ditandatangani" {{ request('status') == 'ditandatangani' ? 'selected' : '' }}>Ditandatangani</option>
                        <option value="batal"          {{ request('status') == 'batal'          ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ request('dari') }}"
                        class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ request('sampai') }}"
                        class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    Terapkan Filter
                </button>
                <a href="{{ route('laporan.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    Reset
                </a>
            </div>
        </form>
    </div>

  {{-- TABEL LHP --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Daftar LHP
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ $lhps->total() }} data)</span>
                </h2>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">Nomor LHP</th>
                        <th class="px-4 py-3">Program Audit</th>
                        <th class="px-4 py-3">Tanggal</th>
                        {{-- Kolom Semester Dihapus --}}
                        <th class="px-4 py-3">IRBAN</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Temuan</th>
                        <th class="px-4 py-3 text-center">Rekom</th>
                        <th class="px-4 py-3">Kerugian</th>
                        <th class="px-4 py-3 text-center">Progress</th>
                        <th class="px-4 py-3 text-center">Unduh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($lhps as $lhp)
                    @php
                        $stat   = $lhp->statistik;
                        $persen = (float) ($stat?->persen_selesai_gabungan ?? 0);
                        $bar    = $persen >= 100 ? 'bg-green-500' : ($persen >= 50 ? 'bg-amber-400' : ($persen > 0 ? 'bg-blue-500' : 'bg-gray-300'));
                        $statusConf = match($lhp->status) {
                            'draft'          => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                            'final'          => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                            'ditandatangani' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                            'batal'          => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                            default          => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                        <td class="px-4 py-3">
                            <a href="{{ route('laporan.rekap-per-lhp', $lhp->id) }}"
                               class="font-semibold text-blue-600 hover:underline dark:text-blue-400">
                                {{ $lhp->nomor_lhp }}
                            </a>
                        </td>
                        <td class="px-4 py-3 max-w-[180px] truncate text-gray-600 dark:text-gray-400">
                            {{ $lhp->auditAssignment?->auditProgram?->nama_program ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-500 text-xs">
                            {{ $lhp->tanggal_lhp?->format('d/m/Y') ?? '-' }}
                        </td>
                        {{-- Data Semester Dihapus --}}
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $lhp->irban }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusConf }}">
                                {{ ucfirst($lhp->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-medium">{{ $stat?->total_temuan ?? 0 }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1 text-xs">
                                <span class="text-green-600">{{ $stat?->rekom_selesai ?? 0 }}</span>
                                <span class="text-gray-300">/</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $stat?->total_rekomendasi ?? 0 }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            Rp {{ number_format($stat?->total_kerugian ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col items-center gap-1">
                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div class="{{ $bar }} h-full rounded-full" style="width:{{ min(100,$persen) }}%"></div>
                                </div>
                                <span class="text-[10px] font-semibold {{ $persen >= 100 ? 'text-green-600' : ($persen >= 50 ? 'text-amber-500' : 'text-gray-500') }}">
                                    {{ number_format($persen, 1) }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                {{-- PDF Per LHP --}}
                                <a href="{{ route('laporan.download-pdf-per-lhp', $lhp->id) }}"
                                   target="_blank"
                                   title="Download PDF"
                                   class="rounded p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </a>
                                {{-- Excel Per LHP --}}
                                <a href="{{ route('laporan.download-excel-per-lhp', $lhp->id) }}"
                                   title="Download Excel"
                                   class="rounded p-1.5 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>
                                {{-- Detail --}}
                                <a href="{{ route('laporan.rekap-per-lhp', $lhp->id) }}"
                                   title="Lihat Detail"
                                   class="rounded p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        {{-- Colspan dikurangi menjadi 10 karena satu kolom dihapus --}}
                        <td colspan="10" class="px-4 py-16 text-center text-gray-400 dark:text-gray-500">
                            Tidak ada data LHP sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-700">
            {{ $lhps->withQueryString()->links() }}
        </div>
    </div>

</div>
@endsection