@extends('layouts.app')

@section('content')
@php
    $stat   = $lhp->statistik;
    $persen = (float) ($stat?->persen_selesai_gabungan ?? 0);
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('laporan.index') }}" class="hover:text-primary-600">Laporan</a>
                <span>/</span>
                <span class="text-gray-700 dark:text-white">Detail LHP</span>
            </nav>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $lhp->nomor_lhp }}</h1>
            <p class="text-sm text-gray-500">{{ $lhp->auditAssignment?->auditProgram?->nama_program ?? '-' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                &larr; Kembali
            </a>
            <a href="{{ route('laporan.download-pdf-per-lhp', $lhp->id) }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download PDF
            </a>
            <a href="{{ route('laporan.download-excel-per-lhp', $lhp->id) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Excel
            </a>
        </div>
    </div>

    {{-- INFO + STAT GRID --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Info LHP --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-5 py-3.5 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi LHP</h2>
            </div>
            <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                @php
                    $infoRows = [
                        ['Tanggal LHP',      $lhp->tanggal_lhp?->translatedFormat('d F Y') ?? '-'],
                        ['Semester',         'Semester ' . $lhp->semester],
                        ['IRBAN',            $lhp->irban],
                        ['Jenis Pemeriksaan',$lhp->jenis_pemeriksaan ?? '-'],
                        ['Unit Diperiksa',   $lhp->auditAssignment?->unitDiperiksa?->nama_unit ?? '-'],
                        ['Dibuat Oleh',      $lhp->creator?->name ?? '-'],
                        ['Status',           ucfirst(str_replace('_', ' ', $lhp->status))],
                    ];
                @endphp
                @foreach ($infoRows as [$label, $value])
                <div class="flex justify-between gap-3 px-5 py-2.5">
                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                    <dd class="text-xs font-medium text-gray-900 dark:text-white text-right">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </div>

        {{-- Statistik --}}
        <div class="lg:col-span-2 grid grid-cols-2 gap-4 sm:grid-cols-4 content-start">
            @php
                $cards = [
                    ['Total Temuan',    $stat?->total_temuan ?? 0,      'blue',  null],
                    ['Total Rekom',     $stat?->total_rekomendasi ?? 0,  'blue',  null],
                    ['Rekom Selesai',   $stat?->rekom_selesai ?? 0,      'green', null],
                    ['Rekom Proses',    $stat?->rekom_proses ?? 0,       'amber', null],
                    ['Rekom Belum',     $stat?->rekom_belum ?? 0,        'red',   null],
                    ['Progress TL',     number_format($persen,1) . '%',  'gray',  null],
                    ['Total Kerugian',  'Rp ' . number_format($stat?->total_kerugian ?? 0, 0, ',', '.'), 'red', null],
                    ['Sisa Kerugian',   'Rp ' . number_format($stat?->total_sisa_kerugian ?? 0, 0, ',', '.'), 'amber', null],
                ];
                $colorMap = [
                    'blue'  => 'bg-blue-50  border-blue-200  dark:bg-blue-900/20  dark:border-blue-800  text-blue-700  dark:text-blue-300',
                    'green' => 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800 text-green-700 dark:text-green-300',
                    'amber' => 'bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-800 text-amber-700 dark:text-amber-300',
                    'red'   => 'bg-red-50   border-red-200   dark:bg-red-900/20   dark:border-red-800   text-red-700   dark:text-red-300',
                    'gray'  => 'bg-gray-50  border-gray-200  dark:bg-gray-700     dark:border-gray-600  text-gray-700  dark:text-gray-300',
                ];
            @endphp
            @foreach ($cards as [$label, $val, $color, $_])
            <div class="rounded-xl border p-4 {{ $colorMap[$color] }}">
                <p class="text-xs opacity-70">{{ $label }}</p>
                <p class="mt-1 text-lg font-bold leading-tight">{{ $val }}</p>
            </div>
            @endforeach
        </div>

    </div>

    {{-- TEMUAN --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                Daftar Temuan
                <span class="ml-1 text-xs font-normal text-gray-400">({{ $lhp->temuans->count() }})</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-center w-10">No</th>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Kondisi</th>
                        <th class="px-4 py-3 text-right">Total Kerugian</th>
                        <th class="px-4 py-3 text-center">Rekom</th>
                        <th class="px-4 py-3 text-center">Status TL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($lhp->temuans as $i => $t)
                    @php
                        $tlConf = match($t->status_tl) {
                            'selesai'               => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            'dalam_proses'          => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                            'belum_ditindaklanjuti' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                            default                 => 'bg-gray-100 text-gray-600',
                        };
                        $tlLabel = match($t->status_tl) {
                            'selesai'               => 'Selesai',
                            'dalam_proses'          => 'Dalam Proses',
                            'belum_ditindaklanjuti' => 'Belum TL',
                            default                 => $t->status_tl,
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                        <td class="px-4 py-3 text-center font-semibold text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ $t->kodeTemuan?->kode ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs">{{ Str::limit($t->kondisi, 120) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-red-600 dark:text-red-400 whitespace-nowrap">
                            Rp {{ number_format($t->total_nilai_temuan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ $t->recommendations->count() }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tlConf }}">
                                {{ $tlLabel }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada temuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- REKOMENDASI --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                Rekomendasi & Tindak Lanjut
                <span class="ml-1 text-xs font-normal text-gray-400">({{ $lhp->temuans->flatMap->recommendations->count() }})</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-center w-10">No</th>
                        <th class="px-4 py-3">Temuan</th>
                        <th class="px-4 py-3">Uraian Rekomendasi</th>
                        <th class="px-4 py-3 text-center">Jenis</th>
                        <th class="px-4 py-3 text-right">Nilai Rekom</th>
                        <th class="px-4 py-3 text-right">TL Selesai</th>
                        <th class="px-4 py-3 text-right">Sisa</th>
                        <th class="px-4 py-3 text-center">Progress</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @php $no = 1; @endphp
                    @forelse ($lhp->temuans as $ti => $t)
                        @foreach ($t->recommendations as $r)
                        @php
                            $pR = $r->progress();
                            $barR = $pR >= 100 ? 'bg-green-500' : ($pR >= 50 ? 'bg-amber-400' : ($pR > 0 ? 'bg-blue-500' : 'bg-gray-200 dark:bg-gray-600'));
                            $statusConf = match($r->status) {
                                'selesai'               => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                'proses'                => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                'belum_ditindaklanjuti' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                default                 => 'bg-gray-100 text-gray-600',
                            };
                            $statusLabel = match($r->status) {
                                'selesai'               => 'Selesai',
                                'proses'                => 'Proses',
                                'belum_ditindaklanjuti' => 'Belum TL',
                                default                 => $r->status,
                            };
                            $jenisConf = match($r->jenis_rekomendasi) {
                                'uang'         => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                'administrasi' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                'barang'       => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                default        => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-3 text-center font-semibold text-gray-500">{{ $no++ }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">T{{ $ti + 1 }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs text-xs">
                                {{ Str::limit($r->uraian_rekom, 100) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $jenisConf }}">
                                    {{ ucfirst($r->jenis_rekomendasi) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs font-medium whitespace-nowrap">
                                Rp {{ number_format($r->nilai_rekom ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-green-600 dark:text-green-400 whitespace-nowrap">
                                Rp {{ number_format($r->nilai_tl_selesai ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-red-500 whitespace-nowrap">
                                Rp {{ number_format($r->nilai_sisa ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="{{ $barR }} h-full rounded-full" style="width:{{ min(100,$pR) }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-gray-500">{{ number_format($pR,0) }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusConf }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">Tidak ada rekomendasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection