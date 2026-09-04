@extends('layouts.app')

@push('styles')
<style>
    input[type="date"] { color-scheme: light; }
    .dark input[type="date"] { color-scheme: dark; }
    input[type="date"]::-webkit-calendar-picker-indicator { display: block; }
    input[type="date"]::-webkit-calendar-picker-indicator:hover { cursor: pointer; opacity: 0.7; }
</style>
@endpush

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
            <p class="mt-1 text-2xl font-bold ">{{ $ringkasan['total_lhp'] }}</p>
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
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Nomor LHP, program, ST..."
                           class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
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
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Kategori</label>
                    <select name="kategori" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua</option>
                        @foreach ($kategoris as $k)
                            <option value="{{ $k }}" {{ request('kategori') == $k ? 'selected' : '' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Status</label>
                    <select name="status" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua</option>
                        <option value="lunas"    {{ request('status') == 'lunas'    ? 'selected' : '' }}>Lunas</option>
                        <option value="sebagian" {{ request('status') == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                        <option value="belum"    {{ request('status') == 'belum'    ? 'selected' : '' }}>Belum</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ request('dari') }}"
                        class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ request('sampai') }}"
                        class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
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
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Daftar LHP
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ $lhps->total() }} data)</span>
                </h2>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/70 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-5 py-3.5 w-[31%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Nomor LHP</th>
                        <th class="px-5 py-3.5 w-[14%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Unit</th>
                        <th class="px-5 py-3.5 w-[10%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Tanggal</th>
                        <th class="px-5 py-3.5 w-[8%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-center">Kategori</th>
                        <th class="px-5 py-3.5 w-[8%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-center">Status</th>
                        <th class="px-5 py-3.5 w-[15%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-right">Kerugian</th>
                        <th class="px-5 py-3.5 w-[10%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-right">Progress</th>
                        <th class="px-5 py-3.5 w-[10%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($lhps as $lhp)
                    @php
                        $stat   = $lhp->statistik;
                        $persen = (float) ($stat?->persen_selesai_gabungan ?? 0);
                        $bar    = $persen >= 100 ? 'bg-green-500' : ($persen >= 50 ? 'bg-amber-400' : ($persen > 0 ? 'bg-blue-500' : 'bg-gray-300'));
                        $statusConf = match($stat?->status_progress) {
                            'Lunas'    => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                            'Sebagian' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                            default    => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                        };
                        $k = $lhp->auditAssignment?->auditProgram?->kategori;
                        $kategoriBadge = match($k) {
                            'PKPT' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
                            'BPK'  => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
                            'BPKP' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400',
                            'ITPROV' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
                            'ITDA'   => 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400',
                            default  => 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                        {{-- Nomor LHP --}}
                        <td class="px-5 py-4">
                            <div class="text-sm font-bold">
                                <a href="{{ route('laporan.rekap-per-lhp', $lhp->id) }}" class="hover:underline">
                                    {{ $lhp->nomor_lhp }}
                                </a>
                            </div>
                            @if($lhp->auditAssignment?->nomor_surat)
                            <p class="mt-0.5 text-[10px] text-gray-400 dark:text-gray-500 leading-tight">
                                ST: {{ $lhp->auditAssignment->nomor_surat }}
                            </p>
                            @endif
                            @if($lhp->auditAssignment?->auditProgram?->nama_program)
                            <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400 leading-tight">
                                {{ $lhp->auditAssignment->auditProgram->nama_program }}
                            </p>
                            @endif
                            @if($lhp->auditAssignment?->auditProgramDetail?->jenis_kegiatan)
                            <p class="mt-0.5 text-[10px] text-gray-400 dark:text-gray-500 leading-tight">
                                {{ $lhp->auditAssignment->auditProgramDetail->jenis_kegiatan }}
                            </p>
                            @endif
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/20 text-[9px] font-semibold text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30">
                                    {{ $lhp->auditAssignment?->auditProgramDetail?->tim ?? '-' }}
                                </span>
                                @if($lhp->auditAssignment?->jenis_pengawasan)
                                <span class="px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-900/20 text-[9px] font-semibold text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800/30">
                                    {{ ucfirst($lhp->auditAssignment->jenis_pengawasan) }}
                                </span>
                                @endif
                            </div>
                        </td>
                        {{-- Unit --}}
                        <td class="px-5 py-4">
                            <div class="text-[11px] leading-snug text-gray-600 dark:text-gray-400 break-words max-w-[140px]">
                                {{ $lhp->unitDiperiksa?->label ?? $lhp->unitDiperiksa?->nama_unit ?? '-' }}
                            </div>
                        </td>
                        {{-- Tanggal --}}
                        <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                            {{ $lhp->tanggal_lhp?->format('d/m/Y') ?? '-' }}
                            @if($lhp->semester)
                            <span class="mt-1 block text-[10px] text-gray-400 dark:text-gray-500">Smt {{ $lhp->semester == 1 ? 'I' : 'II' }}</span>
                            @endif
                        </td>
                        {{-- Kategori --}}
                        <td class="px-5 py-4 text-center">
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase {{ $kategoriBadge }}">
                                {{ $k ?? '-' }}
                            </span>
                        </td>
                        {{-- Status --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $statusConf }}">
                                {{ $stat?->status_progress ?? 'Belum' }}
                            </span>
                        </td>
                        {{-- Kerugian --}}
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Rp {{ number_format($stat?->total_kerugian ?? 0, 0, ',', '.') }}
                            </div>
                            @if(($stat?->total_sisa ?? 0) > 0)
                            <div class="mt-0.5 text-[10px] text-red-500 dark:text-red-400">
                                Sisa: Rp {{ number_format($stat->total_sisa, 0, ',', '.') }}
                            </div>
                            @endif
                        </td>
                        {{-- Progress --}}
                        <td class="px-5 py-4">
                            <div class="flex flex-col items-end gap-1">
                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div class="{{ $bar }} h-full rounded-full transition-all" style="width:{{ min(100,$persen) }}%"></div>
                                </div>
                                <span class="text-[10px] font-bold {{ $persen >= 100 ? 'text-green-600' : ($persen >= 50 ? 'text-amber-500' : 'text-gray-400') }}">
                                    {{ number_format($persen, 1) }}%
                                </span>
                            </div>
                        </td>
                        {{-- Aksi --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ route('laporan.download-pdf-per-lhp', $lhp->id) }}"
                                   target="_blank" title="Download PDF"
                                   class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('laporan.download-excel-per-lhp', $lhp->id) }}"
                                   title="Download Excel"
                                   class="p-1.5 text-gray-400 hover:text-green-600 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('laporan.rekap-per-lhp', $lhp->id) }}"
                                   title="Lihat Detail"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors">
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
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada data LHP sesuai filter.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lhps->hasPages())
        <div class="border-t border-gray-100 px-5 py-3.5 dark:border-gray-800">
            {{ $lhps->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@push('scripts')
<script>
document.querySelectorAll('input[type="date"]').forEach(input => {
    input.addEventListener('click', function() { this.showPicker(); });
});
</script>
@endpush
@endsection